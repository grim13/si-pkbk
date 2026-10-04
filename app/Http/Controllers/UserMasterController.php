<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('sortField')) {
            $direction = $request->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortField, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('UserMaster/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'sortField', 'sortDirection']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return back();
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return back();
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back();
    }
}
