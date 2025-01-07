<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AkunPeruri extends Authenticatable
{
    protected $table = 'akun_peruri';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'type',
        'ktp',
        'ktp_photo',
        'npwp',
        'npwp_photo',
        'self_photo',
        'address',
        'city',
        'province',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'org_unit',
        'work_unit',
        'position'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];
}