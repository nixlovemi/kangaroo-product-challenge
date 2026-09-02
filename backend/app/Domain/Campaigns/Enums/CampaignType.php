<?php

namespace App\Domain\Campaigns\Enums;

enum CampaignType: string
{
    case PERCENTAGE_DISCOUNT = 'percentage_discount';
    case DOUBLE_POINTS = 'double_points';
}
