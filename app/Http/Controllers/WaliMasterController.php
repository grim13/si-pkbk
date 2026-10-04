<?php

namespace App\Http\Controllers;

use App\Models\Wali;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WaliMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = Wali::with('user');

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })
                ->orWhere('nik', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('sortField')) {
            $direction = $request->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortField, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $walis = $query->paginate(10)->withQueryString();

        return Inertia::render('WaliMaster/Index', [
            'walis' => $walis,
            'filters' => $request->only(['search', 'sortField', 'sortDirection']),
        ]);
    }

    public function update(Request $request, Wali $wali)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($wali->user_id)],
            'nik' => ['required', 'string', Rule::unique('wali')->ignore($wali->id)],
            'alamat' => ['required', 'string'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'nomor_hp' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($wali, $validated) {
            $wali->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $wali->update([
                'nik' => $validated['nik'],
                'alamat' => $validated['alamat'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'nomor_hp' => $validated['nomor_hp'],
            ]);
        });

        return back();
    }

    public function destroy(Wali $wali)
    {
        $wali->delete();

        return back();
    }
}
