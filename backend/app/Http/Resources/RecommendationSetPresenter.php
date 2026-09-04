<?php

namespace App\Http\Resources;

use App\Domain\Recommendations\DTOs\RecommendationDTO;
use App\Domain\Recommendations\DTOs\RecommendationSetDTO;

final class RecommendationSetPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(RecommendationSetDTO $set): array
    {
        return [
            'target_roi_percentage' => $set->targetRoiPercentage,
            'already_meets_target' => $set->alreadyMeetsTarget,
            'summary_message' => $set->summaryMessage,
            'items' => array_map(
                fn (RecommendationDTO $recommendation): array => [
                    'lever' => $recommendation->lever->value,
                    'label' => $recommendation->lever->label(),
                    'value_type' => $recommendation->lever->valueType(),
                    'outcome' => $recommendation->outcome->value,
                    'message' => $recommendation->message,
                    'current_value' => $recommendation->currentValue,
                    'suggested_value' => $recommendation->suggestedValue,
                    'projected_roi' => $recommendation->projectedRoi,
                ],
                $set->recommendations,
            ),
        ];
    }
}
