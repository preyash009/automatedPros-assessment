<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('view_permissions')) {
            return $this->error('You do not have permission to view permissions', 403);
        }
        $permissions = Permission::all();
        return $this->success($permissions, 'Permissions retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        if (!$request->user()->can('create_permissions')) {
            return $this->error('You do not have permission to create permissions', 403);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'api'
        ]);

        return $this->success($permission, 'Permission created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->can('view_permissions')) {
            return $this->error('You do not have permission to view permissions', 403);
        }
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->error('Permission not found', 404);
        }

        return $this->success($permission, 'Permission retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->error('Permission not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        if (!$request->user()->can('edit_permissions')) {
            return $this->error('You do not have permission to edit permissions', 403);
        }

        $permission->update(['name' => $request->name]);

        return $this->success($permission, 'Permission updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->can('delete_permissions')) {
            return $this->error('You do not have permission to delete permissions', 403);
        }
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->error('Permission not found', 404);
        }

        $permission->delete();

        return $this->success(null, 'Permission deleted successfully');
    }
}
