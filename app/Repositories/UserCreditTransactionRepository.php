<?php

namespace App\Repositories;

use App\Models\UserCreditTransaction;

class UserCreditTransactionRepository
{
    /**
     * Add a credit transaction for a user.
     *
     * @param int $userId
     * @param string $referenceType
     * @param int|null $referenceId
     * @param float $amount
     * @param string|null $transactionId
     * @return UserCreditTransaction
     */
    public function addCredit($userId, $referenceType, $referenceId, $amount, $transactionId = null)
    {
        return UserCreditTransaction::create([
            'user_id'        => $userId,
            'type'           => UserCreditTransaction::TYPE_CREDIT,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'amount'         => $amount,
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Add a debit transaction for a user.
     *
     * @param int $userId
     * @param string $referenceType
     * @param int|null $referenceId
     * @param float $amount
     * @param string|null $transactionId
     * @return UserCreditTransaction
     */
    public function addDebit($userId, $referenceType, $referenceId, $amount, $transactionId = null)
    {
        return UserCreditTransaction::create([
            'user_id'        => $userId,
            'type'           => UserCreditTransaction::TYPE_DEBIT,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'amount'         => $amount,
            'transaction_id' => $transactionId,
        ]);
    }
}
