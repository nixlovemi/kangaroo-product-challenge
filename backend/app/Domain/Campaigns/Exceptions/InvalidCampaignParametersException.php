<?php

namespace App\Domain\Campaigns\Exceptions;

use App\Domain\Campaigns\Enums\CampaignType;
use RuntimeException;

final class InvalidCampaignParametersException extends RuntimeException
{
    public static function for(CampaignType $campaignType): self
    {
        return new self(sprintf('Invalid parameters for campaign type "%s".', $campaignType->value));
    }
}
