<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersyaratanAdministrasi extends Model
{
    protected $table = 'persyaratan_administrasi';

    protected $fillable = [
        'deskripsi',
    ];
}
