<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranPeserta;
use App\Models\PendaftaranPesertaBerkas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendaftaranController extends Controller
{
    public function index(Request $request)
    {
        $query = PendaftaranPeserta::where('status_pendaftaran', '!=', 'draft');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_pendaftaran', 'like', '%'.$search.'%')
                  ->orWhere('nama', 'like', '%'.$search.'%')
                  ->orWhere('nik', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('sortField')) {
            $direction = $request->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortField, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $pendaftarans = $query->paginate(10)->withQueryString();
            
        return Inertia::render('Admin/Pendaftaran/Index', [
            'pendaftarans' => $pendaftarans,
            'filters' => $request->only(['search', 'sortField', 'sortDirection']),
        ]);
    }

    public function show(PendaftaranPeserta $pendaftaran)
    {
        $pendaftaran->load(['berkas.jenisBerkasPendaftaran', 'wali.user']);
        
        return Inertia::render('Admin/Pendaftaran/Show', [
            'pendaftaran' => $pendaftaran
        ]);
    }

    public function verify(Request $request, PendaftaranPeserta $pendaftaran)
    {
        $validated = $request->validate([
            'action' => 'required|in:acc,revisi',
            'catatan_verifikasi' => 'nullable|string',
            'berkas' => 'nullable|array',
            'berkas.*.status' => 'required|in:diterima,perbaikan,menunggu_verifikasi',
            'berkas.*.keterangan_perbaikan' => 'nullable|string',
            'jadwal_asesmen' => 'required_if:action,acc|nullable|date',
            'tempat_asesmen' => 'required_if:action,acc|nullable|string',
        ]);

        if ($validated['action'] === 'revisi') {
            $pendaftaran->status_pendaftaran = 'perbaikan_berkas';
        } else {
            $pendaftaran->status_pendaftaran = 'asesmen';
            $pendaftaran->jadwal_asesmen = $validated['jadwal_asesmen'] ?? null;
            $pendaftaran->tempat_asesmen = $validated['tempat_asesmen'] ?? null;
        }

        $pendaftaran->catatan_verifikasi = $validated['catatan_verifikasi'] ?? null;
        $pendaftaran->save();

        if (isset($validated['berkas'])) {
            foreach ($validated['berkas'] as $id => $data) {
                PendaftaranPesertaBerkas::where('id', $id)
                    ->where('pendaftaran_peserta_id', $pendaftaran->id)
                    ->update([
                        'status' => $data['status'],
                        'keterangan_perbaikan' => $data['keterangan_perbaikan'] ?? null,
                    ]);
            }
        }

        return redirect()->route('admin.pendaftaran.index')->with('success', 'Verifikasi berhasil disimpan.');
    }
}
