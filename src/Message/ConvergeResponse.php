<?php

namespace Omnipay\Elavon\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

class ConvergeResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;

        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $this->data = json_decode($json, TRUE);
    }

    public function isSuccessful()
    {
        return (isset($this->data['ssl_result']) && $this->data['ssl_result'] == 0);
    }

    public function getTransactionReference()
    {
        return (isset($this->data['ssl_txn_id'])) ? $this->data['ssl_txn_id'] : null;
    }

    public function getMessage()
    {
        if (isset($this->data['errorCode']) && !$this->isSuccessful()) {
            return isset($this->data['errorMessage']) ? $this->data['errorMessage'] : null;
        }

        return isset($this->data['ssl_result_message']) ? $this->data['ssl_result_message'] : null;
    }

    public function getCode()
    {
        if (isset($this->data['errorCode']) && !$this->isSuccessful()) {
            return $this->data['errorCode'];
        }

        return $this->data['ssl_result'];
    }

    /**
     * Get the credit card token.
     *
     * Note that this function should be called as getCardReference() as per omnipay standards.
     *
     * @return string
     */
    public function getCardToken()
    {
        return (isset($this->data['ssl_token'])) ? $this->data['ssl_token'] : null;
    }

    public function getCardReference()
    {
        return $this->getCardToken();
    }
}
