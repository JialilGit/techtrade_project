<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $table = 'contacts';

    protected $fillable = ['name', 'email', 'subject', 'message'];

}

