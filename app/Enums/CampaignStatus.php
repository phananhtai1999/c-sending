<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case new = 'new';
    case active  = 'active';
    case complete  = 'complete';
}
