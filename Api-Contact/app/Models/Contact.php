<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'avatar',
        'nom',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function telephones()
    {
        return $this->hasMany(Telephone::class);
    }
}