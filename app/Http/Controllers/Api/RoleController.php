<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('view_roles')) {
            return $this->error('You do not have permission to view roles', 403);
        }
        $roles = Role::with('permissions')->get();
        return $this->success($roles, 'Roles retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        if (!$request->user()->can('create_roles')) {
            return $this->error('You do not have permission to create roles', 403);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api'
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'Role created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->can('view_roles')) {
            return $this->error('You do not have permission to view roles', 403);
        }
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return $this->error('Role not found', 404);
        }

        return $this->success($role, 'Role retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->error('Role not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors());
        }

        if (!$request->user()->can('edit_roles')) {
            return $this->error('You do not have permission to edit roles', 403);
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->can('delete_roles')) {
            return $this->error('You do not have permission to delete roles', 403);
        }
        $role = Role::find($id);

        if (!$role) {
            return $this->error('Role not found', 404);
        }

        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }
}
