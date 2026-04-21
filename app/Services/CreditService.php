<?php

namespace App\Services;

use App\Models\Customer;

class CreditService
{
    /**
     * Apply available customer credit against a charge.
     * Decrements customer.credit by the applied amount.
     * Returns the applied amount and the remaining balance owed.
     */
    public static function apply(Customer $customer, float $chargeAmount): array
    {
        $available = (float) $customer->credit;
        $applied   = round(min($available, $chargeAmount), 2);
        $balance   = round($chargeAmount - $applied, 2);

        if ($applied > 0) {
            $customer->decrement('credit', $applied);
        }

        return ['applied' => $applied, 'balance' => $balance];
    }
}
