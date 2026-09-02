<?php

namespace App\Domain\Campaigns\Enums;

enum HealthStatus: string
{
    case HEALTHY = 'healthy';
    case CAUTION = 'caution';
    case RISKY = 'risky';
}
