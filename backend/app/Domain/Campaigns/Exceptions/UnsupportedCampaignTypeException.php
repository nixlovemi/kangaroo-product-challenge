<?php

namespace App\Domain\Campaigns\Exceptions;

use App\Domain\Campaigns\Enums\CampaignType;
use RuntimeException;

final class UnsupportedCampaignTypeException extends RuntimeException
{
    public static function for(CampaignType $campaignType): self
    {
        return new self(sprintf('Campaign type "%s" is not supported yet.', $campaignType->value));
    }
}
