<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('view_events')) {
            return $this->error('You do not have permission to view events', 403);
        }
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $search = $request->get('search', '');
        $date = $request->get('date', '');
        $location = $request->get('location', '');

        $cacheKey = "events_p{$page}_l{$limit}_s{$search}_d{$date}_loc{$location}";

        $events = Cache::remember($cacheKey, 3600, function () use ($request, $limit) {
            $query = Event::query();

            if ($request->has('search')) {
                $query->searchByTitle($request->search);
            }

            if ($request->has('date')) {
                $query->filterByDate($request->date);
            }

            if ($request->has('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            return $query->with('tickets')->paginate($request->get('limit', $limit));
        });

        return $this->success($events, 'Events retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after:today',
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        // Permission check
        if (!$request->user()->can('create_events')) {
            return $this->error('You do not have permission to create events', 403);
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'location' => $request->location,
            'created_by' => $request->user()->id,
        ]);

        Cache::flush();

        return $this->success($event, 'Event created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->can('view_events')) {
            return $this->error('You do not have permission to view event details', 403);
        }
        $event = Event::with('tickets')->find($id);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        return $this->success($event, 'Event details retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        // Permission check
        if (!$request->user()->can('edit_events')) {
            return $this->error('You do not have permission to edit events', 403);
        }

        // Ownership check: Organizer only their own, Admin can update any
        if ($request->user()->role !== 'admin' && $event->created_by !== $request->user()->id) {
            return $this->error('You are not authorized to update this event', 403);
        }

        if (empty($request->only(['title', 'description', 'date', 'location']))) {
            return $this->error('At least one field (title, description, date, or location) is required for update.', 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'date' => 'sometimes|required|date|after:today',
            'location' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        $event->update($request->all());

        Cache::flush();

        return $this->success($event, 'Event updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->error('Event not found', 404);
        }

        // Permission check
        if (!$request->user()->can('delete_events')) {
            return $this->error('You do not have permission to delete events', 403);
        }

        // Ownership check
        if ($request->user()->role !== 'admin' && $event->created_by !== $request->user()->id) {
            return $this->error('You are not authorized to delete this event', 403);
        }

        $event->delete();

        Cache::flush();

        return $this->success(null, 'Event deleted successfully');
    }
}
