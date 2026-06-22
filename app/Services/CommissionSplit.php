<?php

namespace App\Services;

use App\Models\User;

/**
 * Value object produced by {@see CommissionDistributor::splitForSingleTicket()}.
 *
 * One row = one ticket sold.  Multiply `managerAmount` and `sellerAmount`
 * by quantity to get the line total.
 */
class CommissionSplit
{
    public function __construct(
        public readonly User $seller,
        public readonly ?User $manager,
        public readonly int $ticketTypeId,
        public readonly float $managerAmount,
        public readonly float $sellerAmount,
        public readonly bool $overrodeDefault = false,
        public readonly bool $overrodeManager = false,
    ) {}

    public function total(): float
    {
        return round($this->managerAmount + $this->sellerAmount, 2);
    }
}
