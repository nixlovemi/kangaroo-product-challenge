<?php

namespace App\Domain\Recommendations\Enums;

enum RecommendationLever: string
{
    case DISCOUNT_PERCENTAGE = 'discount_percentage';
    case POINTS_MULTIPLIER = 'points_multiplier';
    case FIXED_CAMPAIGN_COST = 'fixed_campaign_cost';
    case AUDIENCE_SIZE = 'audience_size';

    public function label(): string
    {
        return match ($this) {
            self::DISCOUNT_PERCENTAGE => 'Discount percentage',
            self::POINTS_MULTIPLIER => 'Points multiplier',
            self::FIXED_CAMPAIGN_COST => 'Fixed campaign cost',
            self::AUDIENCE_SIZE => 'Audience size',
        };
    }

    public function valueType(): string
    {
        return match ($this) {
            self::DISCOUNT_PERCENTAGE => 'percentage',
            self::POINTS_MULTIPLIER => 'multiplier',
            self::FIXED_CAMPAIGN_COST => 'currency',
            self::AUDIENCE_SIZE => 'count',
        };
    }
}
