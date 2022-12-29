<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\HasMany;

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


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|HasMany
     */
    public function receivers()
    {
        return $this->hasMany(ReceiverModel::class, 'campaign_uuid', '_id');
    }
}
