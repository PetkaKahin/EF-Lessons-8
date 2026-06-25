<?php

namespace App\Enums;

enum WebhookStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
}
