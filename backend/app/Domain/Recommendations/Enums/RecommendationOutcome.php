<?php

namespace App\Domain\Recommendations\Enums;

enum RecommendationOutcome: string
{
    /** Reachable and commercially plausible: a real suggestion the merchant can act on. */
    case ACTIONABLE = 'actionable';

    /** Mathematically reachable but below a real-world floor, so it is a diagnosis, not advice. */
    case IMPLAUSIBLE = 'implausible';

    /** Unreachable at any valid value for this lever. */
    case INFEASIBLE = 'infeasible';

    public function isActionable(): bool
    {
        return $this === self::ACTIONABLE;
    }
}
