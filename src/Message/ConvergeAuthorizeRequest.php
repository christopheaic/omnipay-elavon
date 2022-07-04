<?php

namespace Omnipay\Elavon\Message;

/**
 * Elavon's Converge Authorize Request
 *
 * This class processes form post requests using the Elavon/Converge gateway as documented here:
 * https://resourcecentre.elavonpaymentgateway.com/index.php/download-developer-guide
 *
 * Also here: https://www.convergepay.com/converge-webapp/developer/#/welcome
 *
 * ### Test Mode
 *
 * In order to begin testing you will need the following parameters from Elavon/Converge:
 *
 * * merchantId, aka ssl_merchant_id
 * * username, aka ssl_user_id
 * * password, aka ssl_pin
 *
 * These parameters are issued for a short time only.  You need to contact Converge to request an extension
 * a few days before these parameters expire.
 *
 * ### Example
 *
 * #### Initialize Gateway
 *
 * <code>
 * //
 * // Put your gateway credentials here.
 * //
 * $credentials = array(
 *     'merchantId'        => '000000',
 *     'username'          => 'USERNAME',
 *     'password'          => 'PASSWORD'
 *     'testMode'          => true,            // Or false if you want to test production mode
 * );
 *
 * // Create a gateway object
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('Elavon_Converge');
 *
 * // Initialise the gateway
 * $gateway->initialize($credentials);
 * </code>
 *
 * #### Direct Credit Card Authorize
 *
 * <code>
 * // Create a credit card object
 * // The card number doesn't appear to matter in test mode.
 * $card = new CreditCard(array(
 *     'firstName'             => 'Example',
 *     'lastName'              => 'Customer',
 *     'number'                => '4444333322221111',
 *     'expiryMonth'           => '01',
 *     'expiryYear'            => '2020',
 *     'cvv'                   => '123',
 *     'billingAddress1'       => '1 Scrubby Creek Road',
 *     'billingCountry'        => 'AU',
 *     'billingCity'           => 'Scrubby Creek',
 *     'billingPostcode'       => '4999',
 *     'billingState'          => 'QLD',
 * ));
 *
 * // Do an authorize transaction on the gateway
 * try {
 *     $transaction = $gateway->authorize(array(
 *         'amount'        => '10.00',
 *         'currency'      => 'USD',
 *         'description'   => 'This is a test purchase transaction.',
 *         'card'          => $card,
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway authorize response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Authorize transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting authorize.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * ### Quirks
 *
 * Two additional parameters need to be sent with every request.  These should be set to defaults
 * in the Gateway class but in case they are not, set them to the following values as shown on
 * every transaction request before calling $transaction->send():
 *
 * <code>
 * $transaction->setSslShowForm('false');
 * $transaction->setSslResultFormat('ASCII');
 * </code>
 *
 * @link https://www.myvirtualmerchant.com/VirtualMerchant/
 * @link https://resourcecentre.elavonpaymentgateway.com/index.php/download-developer-guide
 * @see \Omnipay\Elavon\ConvergeGateway
 */
class ConvergeAuthorizeRequest extends ConvergeAbstractRequest
{
    protected $transactionType = 'ccauthonly';

    public function getData()
    {
        if ($this->getCard() != null) {
            $this->validate('amount', 'card');
            $this->getCard()->validate();

            $data =
                '<ssl_amount>' . $this->getAmount() . '</ssl_amount>' .
                '<ssl_salestax>' . $this->getSslSalesTax() . '</ssl_salestax>' .
                '<ssl_transaction_type>' . $this->transactionType . '</ssl_transaction_type>' .
                '<ssl_card_number>' . $this->getCard()->getNumber() . '</ssl_card_number>' .
                '<ssl_exp_date>' . $this->getCard()->getExpiryDate('my') . '</ssl_exp_date>' .
                '<ssl_cvv2cvc2>' . $this->getCard()->getCvv() . '</ssl_cvv2cvc2>' .
                '<ssl_cvv2cvc2_indicator>' . (($this->getCard()->getCvv()) ? 1 : 0) . '</ssl_cvv2cvc2_indicator>' .
                '<ssl_first_name>' . $this->getCard()->getFirstName() . '</ssl_first_name>' .
                '<ssl_last_name>' . $this->getCard()->getLastName() . '</ssl_last_name>' .
                '<ssl_avs_address>' . $this->getCard()->getAddress1() . '</ssl_avs_address>' .
                '<ssl_address2>' . $this->getCard()->getAddress2() . '</ssl_address2>' .
                '<ssl_city>' . $this->getCard()->getCity() . '</ssl_city>' .
                '<ssl_state>' . $this->getCard()->getState() . '</ssl_state>' .
                '<ssl_country>' . $this->getCard()->getCountry() . '</ssl_country>' .
                '<ssl_customer_code>' . $this->getDescription() . '</ssl_customer_code>' .
                '<ssl_avs_zip>' . $this->getCard()->getPostcode() . '</ssl_avs_zip>';

        } elseif ($this->getCardReference() != null) {
            $this->validate('amount');
            $data =
                '<ssl_show_form>' . 'false' . '</ssl_show_form>' .
                '<ssl_result_format>' . 'ASCII' . '</ssl_result_format>' .
                '<ssl_amount>' . $this->getAmount() . '</ssl_amount>' .
                '<ssl_salestax>' . $this->getSslSalesTax() . '</ssl_salestax>' .
                '<ssl_transaction_type>' . $this->transactionType . '</ssl_transaction_type>' .
                '<ssl_token>' . $this->getCardReference() . '</ssl_token>' .
                '<ssl_customer_code>' . $this->getDescription() . '</ssl_customer_code>';
        }

        return $this->getBaseData() . $data;
    }
}
