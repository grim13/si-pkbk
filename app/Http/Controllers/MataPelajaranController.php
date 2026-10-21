<?php

namespace App\Http\Controllers;

use App\Models\JenisDisabilitas;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MataPelajaranController extends Controller
{
    public function index(Request $request): Response
    {
        $query = MataPelajaran::with([
            'jenisDisabilitas:id,nama',
        ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('keterangan', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('sortField') && in_array($request->sortField, ['id', 'nama'], true)) {
            $direction = $request->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortField, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = (int) $request->input('perPage', 10);
        $mataPelajaran = $query->paginate($perPage)->withQueryString();
        $jenisDisabilitasList = JenisDisabilitas::select('id', 'nama')->orderBy('id')->get();

        return Inertia::render('MataPelajaran/Index', [
            'mataPelajaran' => $mataPelajaran,
            'jenisDisabilitasList' => $jenisDisabilitasList,
            'filters' => $request->only(['search', 'sortField', 'sortDirection', 'perPage']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $mataPelajaran = MataPelajaran::create([
            'nama' => $validated['nama'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $mataPelajaran->jenisDisabilitas()->sync($validated['jenis_disabilitas_ids']);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $mataPelajaran->update([
            'nama' => $validated['nama'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        $mataPelajaran->jenisDisabilitas()->sync($validated['jenis_disabilitas_ids']);

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * @return array{nama: string, keterangan?: string|null, jenis_disabilitas_ids: array<int, int>}
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'jenis_disabilitas_ids' => ['required', 'array', 'min:1'],
            'jenis_disabilitas_ids.*' => ['integer', 'exists:jenis_disabilitas,id'],
        ], [
            'nama.required' => 'Nama mata pelajaran wajib diisi.',
            'jenis_disabilitas_ids.required' => 'Minimal pilih satu jenis disabilitas.',
            'jenis_disabilitas_ids.min' => 'Minimal pilih satu jenis disabilitas.',
        ]);
    }
}
