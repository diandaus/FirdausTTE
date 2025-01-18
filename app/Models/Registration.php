<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $table = 'akun_peruri'; // Sesuaikan dengan nama tabel yang sudah ada
    
    protected $fillable = [
        'name',
        'phone',
        'email',
        'ktp',
        'ktp_photo',
        'address',
        'city',
        'province',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'org_unit',
        'work_unit',
        'position',
        'peruri_status',
        'peruri_response'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'peruri_response' => 'array'
    ];
} 