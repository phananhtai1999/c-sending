<?php

namespace App\Enums;

enum ReceiverStatus: string
{
    case new = 'new';
    case active = 'active';
    case success = 'success';
    case failed = 'failed';
    case pending = 'pending';
}
