<?php

namespace Tests\Unit\Domain\Recommendations\Services;

use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\DTOs\SimulationInputDTO;
use App\Domain\Campaigns\DTOs\SimulationResultDTO;
use App\Domain\Campaigns\Services\CampaignSimulatorInterface;
use App\Domain\Campaigns\Strategies\PercentageDiscountStrategy;
use App\Domain\Recommendations\Services\SimulationMemo;
use PHPUnit\Framework\TestCase;

final class SimulationMemoTest extends TestCase
{
    public function test_it_simulates_identical_inputs_only_once(): void
    {
        $service = $this->countingSimulationService();
        $memo = new SimulationMemo($service);

        $memo->simulate($this->input(10));
        $memo->simulate($this->input(10));
        $memo->simulate($this->input(10));

        self::assertSame(1, $service->calls);
    }

    public function test_it_simulates_again_when_a_parameter_changes(): void
    {
        $service = $this->countingSimulationService();
        $memo = new SimulationMemo($service);

        $memo->simulate($this->input(10));
        $memo->simulate($this->input(8));

        self::assertSame(2, $service->calls);
    }

    public function test_it_simulates_again_when_the_fixed_cost_changes(): void
    {
        $service = $this->countingSimulationService();
        $memo = new SimulationMemo($service);

        $memo->simulate($this->input(10, fixedCampaignCost: 250));
        $memo->simulate($this->input(10, fixedCampaignCost: 125));

        self::assertSame(2, $service->calls);
    }

    private function input(float $discountPercentage, float $fixedCampaignCost = 250): SimulationInputDTO
    {
        return new SimulationInputDTO(
            audienceSize: 1200,
            averageOrderValue: 112,
            grossMarginPercentage: 34,
            historicalConversionRate: 3.1,
            campaignConversionRate: 3.66,
            fixedCampaignCost: $fixedCampaignCost,
            parameters: new PercentageDiscountParametersDTO($discountPercentage),
        );
    }

    private function countingSimulationService(): CampaignSimulatorInterface
    {
        return new class implements CampaignSimulatorInterface {
            public int $calls = 0;

            public function simulate(SimulationInputDTO $input): SimulationResultDTO
            {
                $this->calls++;

                return (new PercentageDiscountStrategy())->simulate($input);
            }
        };
    }
}

