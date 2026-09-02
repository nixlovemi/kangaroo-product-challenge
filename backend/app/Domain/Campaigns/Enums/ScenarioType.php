<?php

namespace App\Domain\Campaigns\Enums;

enum ScenarioType: string
{
    private const CONSERVATIVE_LIFT_MULTIPLIER = 0.5;
    private const EXPECTED_LIFT_MULTIPLIER = 1.0;
    private const STRONG_RESPONSE_LIFT_MULTIPLIER = 1.5;

    case CONSERVATIVE = 'conservative';
    case EXPECTED = 'expected';
    case STRONG_RESPONSE = 'strong_response';
    case CUSTOM = 'custom';

    public function historicalLiftMultiplier(): float
    {
        return match ($this) {
            self::CONSERVATIVE => self::CONSERVATIVE_LIFT_MULTIPLIER,
            self::EXPECTED => self::EXPECTED_LIFT_MULTIPLIER,
            self::STRONG_RESPONSE => self::STRONG_RESPONSE_LIFT_MULTIPLIER,
            self::CUSTOM => throw new \LogicException('Custom scenarios do not use a historical lift multiplier.'),
        };
    }
}
