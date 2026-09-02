<?php

namespace App\Infrastructure\Campaigns\Repositories;

use App\Domain\Campaigns\DTOs\MerchantProfileDTO;
use App\Domain\Campaigns\Exceptions\MerchantProfileNotFoundException;
use App\Domain\Campaigns\Repositories\HistoricalDataRepository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use JsonException;
use RuntimeException;

final class JsonHistoricalDataRepository implements HistoricalDataRepository
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $dataPath,
    ) {
    }

    public function getMerchantProfile(int $merchantId): MerchantProfileDTO
    {
        $profiles = $this->readProfiles();
        $profile = $profiles[(string) $merchantId] ?? null;

        if ($profile === null) {
            throw MerchantProfileNotFoundException::for($merchantId);
        }

        return new MerchantProfileDTO(
            merchantId: $merchantId,
            merchantName: $profile['merchant_name'],
            currency: $profile['currency'],
            averageOrderValue: (float) $profile['average_order_value'],
            grossMarginPercentage: (float) $profile['gross_margin_percentage'],
            historicalConversionRate: (float) $profile['historical_conversion_rate'],
            historicalCampaignLiftPercentage: (float) $profile['historical_campaign_lift_percentage'],
            pointsCostPerUnit: (float) $profile['points_cost_per_unit'],
            pointsRedemptionPercentage: (float) $profile['points_redemption_percentage'],
            pointsEarnedPerCurrency: (float) ($profile['points_earned_per_currency'] ?? 1),
        );
    }

    /**
     * @return array<string, array<string, int|float|string>>
     */
    private function readProfiles(): array
    {
        try {
            return json_decode(
                $this->filesystem->get($this->dataPath),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (FileNotFoundException|JsonException $exception) {
            throw new RuntimeException('Historical merchant data could not be loaded.', 0, $exception);
        }
    }
}
