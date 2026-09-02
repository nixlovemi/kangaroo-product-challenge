<?php

namespace App\Domain\Campaigns\Exceptions;

use RuntimeException;

final class MerchantProfileNotFoundException extends RuntimeException
{
    public static function for(int $merchantId): self
    {
        return new self(sprintf('Merchant profile "%d" was not found.', $merchantId));
    }
}
