<?php

namespace App\Http\Controllers;

use App\Models\JenisBerkasPendaftaran;
use App\Models\PendaftaranPeserta;
use App\Models\PendaftaranPesertaBerkas;
use App\Models\PersyaratanAdministrasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendaftaranController extends Controller
{
    public function index()
    {
        $wali = auth()->user()->wali;
        if (! $wali) {
            abort(403, 'Anda bukan wali.');
        }

        $persyaratan = PersyaratanAdministrasi::orderBy('id', 'asc')->get();
        $jenis_berkas = JenisBerkasPendaftaran::orderBy('id', 'asc')->get();
        $pendaftaran = PendaftaranPeserta::with('berkas')->where('wali_id', $wali->id)->latest()->first();

        return Inertia::render('Pendaftaran/Index', [
            'persyaratan' => $persyaratan,
            'jenis_berkas' => $jenis_berkas,
            'pendaftaran' => $pendaftaran,
        ]);
    }

    public function storeDraft(Request $request)
    {
        $wali = auth()->user()->wali;

        $validated = $request->validate([
            'nik' => 'required|string',
            'nama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date|before_or_equal:-9 years|after:-36 years',
            'alamat_asal' => 'required|string',
            'pendidikan' => 'required|string',
            'keterampilan_yang_diminati' => 'required|string',
        ], [
            'tanggal_lahir.before_or_equal' => 'Umur peserta minimal 9 tahun.',
            'tanggal_lahir.after' => 'Umur peserta maksimal 35 tahun.',
        ]);

        $existing = PendaftaranPeserta::where('wali_id', $wali->id)->first();
        if ($existing && $existing->status_pendaftaran !== 'draft') {
            return redirect()->back()->withErrors(['error' => 'Anda sudah memiliki pendaftaran yang sedang diproses. Tidak dapat membuat pendaftaran baru.']);
        }

        $draft = PendaftaranPeserta::updateOrCreate(
            [
                'wali_id' => $wali->id,
            ],
            array_merge($validated, [
                'no_pendaftaran' => $existing?->no_pendaftaran ?? 'REG-'.time().'-'.rand(100, 999),
                'status_pendaftaran' => 'draft',
            ])
        );

        return redirect()->back()->with('success', 'Data klien berhasil disimpan.');
    }

    public function uploadBerkas(Request $request)
    {
        $wali = auth()->user()->wali;
        $draft = PendaftaranPeserta::where('wali_id', $wali->id)
            ->whereIn('status_pendaftaran', ['draft', 'perbaikan_berkas'])
            ->firstOrFail();

        $request->validate([
            'berkas' => 'required|array',
            'berkas.*' => 'file|max:5120', // 5MB
        ]);

        foreach ($request->file('berkas') as $jenis_id => $file) {
            $path = $file->store('berkas_pendaftaran', 'public');

            PendaftaranPesertaBerkas::updateOrCreate(
                [
                    'pendaftaran_peserta_id' => $draft->id,
                    'jenis_berkas_pendaftaran_id' => $jenis_id,
                ],
                [
                    'file_path' => $path,
                    'status' => 'menunggu_verifikasi',
                ]
            );
        }

        return redirect()->back()->with('success', 'Berkas berhasil diupload.');
    }

    public function finalize(Request $request)
    {
        $wali = auth()->user()->wali;
        $draft = PendaftaranPeserta::where('wali_id', $wali->id)
            ->whereIn('status_pendaftaran', ['draft', 'perbaikan_berkas'])
            ->firstOrFail();

        $requiredBerkas = JenisBerkasPendaftaran::where('required', true)->pluck('id');
        $uploadedBerkas = $draft->berkas()->pluck('jenis_berkas_pendaftaran_id');
        $missing = $requiredBerkas->diff($uploadedBerkas);

        if ($missing->count() > 0) {
            return back()->withErrors(['berkas' => 'Masih ada berkas wajib yang belum diunggah.']);
        }

        $draft->update([
            'status_pendaftaran' => 'verifikasi_berkas',
        ]);

        return redirect()->back()->with('success', 'Pendaftaran berhasil difinalisasi.');
    }
}
