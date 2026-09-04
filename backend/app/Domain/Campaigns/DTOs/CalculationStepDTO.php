<?php

namespace App\Domain\Campaigns\DTOs;

final class CalculationStepDTO
{
    public function __construct(
        public readonly string $label,
        public readonly string $formula,
        public readonly float $value,
        public readonly string $valueType,
    ) {
    }
}
