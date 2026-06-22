<?php

namespace App\Services;

use RuntimeException;

/**
 * Thrown by {@see CommissionCalculator} when the requested order's
 * date falls outside the validity window of every commission tier
 * for the given ticket type.
 *
 * M-007: callers should catch this and decide whether to treat
 * "no tier" as "no commission earned" (default for historical
 * orders created before tiers were defined) or as a hard error
 * (for new orders where it indicates a missing configuration).
 */
class NoCommissionTierException extends RuntimeException
{
}