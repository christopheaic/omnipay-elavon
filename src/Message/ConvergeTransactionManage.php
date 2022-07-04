<?php

namespace Omnipay\Elavon\Message;

/**
 * Class ConvergeTransactionManage
 *
 * This is a base class used in requests that manage an existing transaction
 *
 * @package Omnipay\Elavon\Message
 */
abstract class ConvergeTransactionManage extends ConvergeAbstractRequest
{

    protected $transactionType;

    /**
     * Get the data needed for a transaction modification
     *
     * @return array
     */
    public function getData()
    {
        $this->manageValidate();

        $data =
            '<ssl_transaction_type>' . '$this->transactionType' . '</ssl_transaction_type>' .
            '<ssl_txn_id>' . $this->getTransactionReference() . '</ssl_txn_id>' .
            '<ssl_amount>' . $this->getAmount() . '</ssl_amount>';

        return $this->getBaseData() . $data;
    }

    abstract protected function manageValidate();
}
