<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('sortField')) {
            $direction = $request->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortField, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $roles = $query->paginate(10)->withQueryString();
        $permissions = Permission::select('id', 'name')->get();

        return Inertia::render('RoleMaster/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'sortField', 'sortDirection']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back();
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back();
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return back();
    }
}
