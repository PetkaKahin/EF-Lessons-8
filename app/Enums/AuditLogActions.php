<?php

namespace App\Enums;

enum AuditLogActions: string
{
    case Created = 'created';
    case Completed = 'completed';
    case StatusChanged = 'status_changed';
}
