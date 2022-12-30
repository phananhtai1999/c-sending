<?php

namespace App\Enums;

enum CampaignType: string
{
    case sms = 'sms';
    case email  = 'email';
    case telegram  = 'telegram';
}
