<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinFoto extends Model
{
    protected $table = 'checkin_foto';
    protected $fillable = ['checkin_id', 'path'];
}