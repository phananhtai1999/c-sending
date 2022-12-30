<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class CampaignModel extends Model
{
    use HasFactory;

    protected $table = "campaigns";
    protected $fillable = [
        'template',
        'type',
        'status',
        'config',
    ];
}
