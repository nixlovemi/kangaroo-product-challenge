<?php

namespace App\Http\Requests;

use App\Domain\Campaigns\DTOs\CampaignDraftDTO;
use App\Domain\Campaigns\DTOs\DoublePointsParametersDTO;
use App\Domain\Campaigns\DTOs\PercentageDiscountParametersDTO;
use App\Domain\Campaigns\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Traits\ApiResponseTrait;

final class SimulationRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException($this->errorResponse(
            'The simulation request is invalid.',
            422,
            $validator->errors(),
        ));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'merchant_id' => ['required', 'integer', 'min:1'],
            'audience_size' => ['required', 'integer', 'min:1', 'max:1000000'],
            'fixed_campaign_cost' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'campaign_type' => ['required', Rule::enum(CampaignType::class)],
            'campaign_conversion_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'parameters' => ['required', 'array'],
            'parameters.discount_percentage' => [
                'required_if:campaign_type,'.CampaignType::PERCENTAGE_DISCOUNT->value,
                'numeric',
                'min:0',
                'max:100',
            ],
            'parameters.points_multiplier' => [
                'required_if:campaign_type,'.CampaignType::DOUBLE_POINTS->value,
                'numeric',
                'min:1',
                'max:10',
            ],
        ];
    }

    public function toCampaignDraft(): CampaignDraftDTO
    {
        $campaignType = CampaignType::from($this->string('campaign_type')->value());

        // The MVP keeps this transport mapping explicit; it can move to a provider-registered factory as campaign types grow.
        $parameters = match ($campaignType) {
            CampaignType::PERCENTAGE_DISCOUNT => new PercentageDiscountParametersDTO(
                discountPercentage: (float) $this->input('parameters.discount_percentage'),
            ),
            CampaignType::DOUBLE_POINTS => new DoublePointsParametersDTO(
                pointsMultiplier: (float) $this->input('parameters.points_multiplier'),
            ),
        };

        return new CampaignDraftDTO(
            merchantId: (int) $this->input('merchant_id'),
            audienceSize: (int) $this->input('audience_size'),
            fixedCampaignCost: (float) $this->input('fixed_campaign_cost'),
            parameters: $parameters,
            campaignType: $campaignType,
            campaignConversionRate: $this->input('campaign_conversion_rate') === null
                ? null
                : (float) $this->input('campaign_conversion_rate'),
        );
    }
}
