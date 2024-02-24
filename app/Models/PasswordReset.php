<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;
    const UPDATED_AT = null;
    /**
     * The attribute that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token'
    ];
}
