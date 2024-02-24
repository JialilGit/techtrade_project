<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
