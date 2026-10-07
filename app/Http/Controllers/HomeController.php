<?php

namespace App\Http\Controllers;

use App\Models\JenisBerkasPendaftaran;
use App\Models\PersyaratanAdministrasi;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Landing page: informasi layanan & persyaratan pendaftaran.
     */
    public function __invoke()
    {
        return Inertia::render('Welcome', [
            'persyaratan' => PersyaratanAdministrasi::orderBy('id')->pluck('deskripsi'),
            'berkas' => JenisBerkasPendaftaran::orderBy('id')
                ->get(['id', 'nama_berkas', 'keterangan', 'required', 'template_surat']),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
