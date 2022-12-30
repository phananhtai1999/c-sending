<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;


class ReceiverModel extends Model
{
    use HasFactory;

    protected $table = "receivers";
    protected $fillable = [
        'campaign_uuid',
        'destination',
        'status',
        'parameters',
    ];
}
