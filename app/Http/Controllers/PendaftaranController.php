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
    public function index(Request $request)
    {
        $wali = auth()->user()->wali;
        if (! $wali) {
            abort(403, 'Anda bukan wali.');
        }

        $persyaratan = PersyaratanAdministrasi::orderBy('id', 'asc')->get();
        $jenis_berkas = JenisBerkasPendaftaran::orderBy('id', 'asc')->get();
        $pendaftaranList = PendaftaranPeserta::with('berkas')
            ->where('wali_id', $wali->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $isCreatingNew = $request->boolean('baru');

        if ($isCreatingNew) {
            $pendaftaran = null;
        } elseif ($request->filled('pendaftaran_id')) {
            $pendaftaran = $pendaftaranList->firstWhere('id', (int) $request->pendaftaran_id);
        } else {
            $pendaftaran = $pendaftaranList->first();
        }

        return Inertia::render('Pendaftaran/Index', [
            'persyaratan' => $persyaratan,
            'jenis_berkas' => $jenis_berkas,
            'pendaftaran' => $pendaftaran,
            'pendaftaran_list' => $pendaftaranList,
            'is_creating_new' => $isCreatingNew,
        ]);
    }

    public function storeDraft(Request $request)
    {
        $wali = auth()->user()->wali;

        $validated = $request->validate([
            'pendaftaran_id' => 'nullable|integer',
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

        $pendaftaranId = $validated['pendaftaran_id'] ?? null;
        unset($validated['pendaftaran_id']);

        if ($pendaftaranId) {
            $draft = PendaftaranPeserta::where('wali_id', $wali->id)
                ->where('id', $pendaftaranId)
                ->where('status_pendaftaran', 'draft')
                ->firstOrFail();

            $draft->update($validated);
        } else {
            $draft = PendaftaranPeserta::create(array_merge($validated, [
                'wali_id' => $wali->id,
                'no_pendaftaran' => $this->generateNoPendaftaran(),
                'status_pendaftaran' => 'draft',
            ]));
        }

        return redirect()->route('pendaftaran.index', ['pendaftaran_id' => $draft->id])
            ->with('success', 'Data klien berhasil disimpan.');
    }

    public function uploadBerkas(Request $request)
    {
        $wali = auth()->user()->wali;
        $query = PendaftaranPeserta::where('wali_id', $wali->id)
            ->whereIn('status_pendaftaran', ['draft', 'perbaikan_berkas']);

        if ($request->filled('pendaftaran_id')) {
            $query->where('id', $request->pendaftaran_id);
        }

        $draft = $query->latest('id')->firstOrFail();

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

        return redirect()->route('pendaftaran.index', ['pendaftaran_id' => $draft->id])
            ->with('success', 'Berkas berhasil diupload.');
    }

    public function finalize(Request $request)
    {
        $wali = auth()->user()->wali;
        $query = PendaftaranPeserta::where('wali_id', $wali->id)
            ->whereIn('status_pendaftaran', ['draft', 'perbaikan_berkas']);

        if ($request->filled('pendaftaran_id')) {
            $query->where('id', $request->pendaftaran_id);
        }

        $draft = $query->latest('id')->firstOrFail();

        $requiredBerkas = JenisBerkasPendaftaran::where('required', true)->pluck('id');
        $uploadedBerkas = $draft->berkas()->pluck('jenis_berkas_pendaftaran_id');
        $missing = $requiredBerkas->diff($uploadedBerkas);

        if ($missing->count() > 0) {
            return back()->withErrors(['berkas' => 'Masih ada berkas wajib yang belum diunggah.']);
        }

        $draft->update([
            'status_pendaftaran' => 'verifikasi_berkas',
        ]);

        return redirect()->route('pendaftaran.index', ['pendaftaran_id' => $draft->id])
            ->with('success', 'Pendaftaran berhasil difinalisasi.');
    }

    private function generateNoPendaftaran(): string
    {
        $nextNumber = PendaftaranPeserta::count() + 1;

        do {
            $noPendaftaran = str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (PendaftaranPeserta::where('no_pendaftaran', $noPendaftaran)->exists());

        return $noPendaftaran;
    }
}
