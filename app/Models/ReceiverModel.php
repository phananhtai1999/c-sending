<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\BelongsTo;


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

    /**
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CampaignModel::class, 'campaign_uuid', '_id');
    }
}
