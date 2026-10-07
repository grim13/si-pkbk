<?php

namespace App\Http\Controllers\KepalaSeksi;

use App\Http\Controllers\Controller;
use App\Models\AsesmenSeksi;
use App\Models\PendaftaranPeserta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelulusanController extends Controller
{
    /**
     * Status pendaftaran yang tampil di menu penentuan kelulusan.
     */
    private const STATUS_LIST = ['selesai_asesmen', 'diterima', 'ditolak'];

    /**
     * Display a listing of participants who have finished assessment.
     */
    public function index()
    {
        $peserta = PendaftaranPeserta::with(['wali.user', 'asesmen:id,pendaftaran_peserta_id,tanggal_asesmen,status'])
            ->whereIn('status_pendaftaran', self::STATUS_LIST)
            ->orderByRaw("CASE WHEN status_pendaftaran = 'selesai_asesmen' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('KepalaSeksi/Kelulusan/Index', [
            'peserta' => $peserta,
        ]);
    }

    /**
     * Show full registration, documents and assessment result.
     */
    public function show(PendaftaranPeserta $pendaftaran)
    {
        if (!in_array($pendaftaran->status_pendaftaran, self::STATUS_LIST)) {
            return redirect()->route('kepala-seksi.kelulusan.index')
                ->with('error', 'Peserta belum menyelesaikan tahap asesmen.');
        }

        $pendaftaran->load([
            'wali.user',
            'wali.anggotaKeluarga',
            'berkas.jenisBerkasPendaftaran',
            'asesmen.jawaban',
            'asesmen.pekerja:id,name',
        ]);

        $seksi = AsesmenSeksi::with('pertanyaan')->orderBy('urutan')->get();

        return Inertia::render('KepalaSeksi/Kelulusan/Show', [
            'pendaftaran' => $pendaftaran,
            'seksiMaster' => $seksi,
        ]);
    }

    /**
     * Decide whether the participant is accepted or rejected.
     */
    public function update(Request $request, PendaftaranPeserta $pendaftaran)
    {
        $validated = $request->validate([
            'keputusan' => 'required|in:diterima,ditolak',
        ]);

        if (!in_array($pendaftaran->status_pendaftaran, self::STATUS_LIST)) {
            return back()->with('error', 'Peserta belum menyelesaikan tahap asesmen.');
        }

        $pendaftaran->update(['status_pendaftaran' => $validated['keputusan']]);

        $label = $validated['keputusan'] === 'diterima' ? 'DITERIMA' : 'DITOLAK';

        return redirect()->route('kepala-seksi.kelulusan.index')
            ->with('success', "Peserta {$pendaftaran->nama} dinyatakan {$label}.");
    }
}
