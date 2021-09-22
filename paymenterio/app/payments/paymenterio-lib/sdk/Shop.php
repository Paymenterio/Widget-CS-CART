<?php
/**
 *
 * Paymenterio Payment PHP SDK
 *
 * @author Paymenterio Development Team
 * @version 1.0.0
 * @license MIT
 * @copyright Paymenterio Sp. z o.o.
 *
 * https://paymenterio.pl
 *
 */
namespace Paymenterio\Payments;

require_once dirname(__FILE__) . '/Interface/Data.php';
require_once dirname(__FILE__) . '/Model/Transaction.php';
require_once dirname(__FILE__) . '/Model/Amount.php';
require_once dirname(__FILE__) . '/Services/CurlConnection.php';
require_once dirname(__FILE__) . '/Services/Exception.php';
require_once dirname(__FILE__) . '/Helpers/SignatureGenerator.php';

use Paymenterio\Payments\Services\CurlConnection;
use Paymenterio\Payments\Services\PaymenterioException;
use Paymenterio\Payments\Services\PaymenterioConfigurationException;
use Paymenterio\Payments\Services\PaymenterioCurlException;
use Paymenterio\Payments\Model\Transaction;
use Paymenterio\Payments\Model\Amount;

class Shop
{
    const productionEndpoint = 'https://api.paymenterio.pl/v1/';
    /**
     *
     * @var string $shopID
     * @var string $apiKey
     * @var CurlConnection $curlConnection
     * @var Transaction $transaction
     */
    private $shopID;
    private $apiKey;
    private $curlConnection;
    
    /**
     *
     * @param string $pointId
     * @param string $pointKey
     * @param boolean $production
     * @throws PaymenterioConfigurationException
     */
    public function __construct($shopID, $apiKey)
    {
        if (empty($shopID) || empty($apiKey)) {
            throw new PaymenterioConfigurationException("Configuration required params not set");
        }
        
        if (strlen($apiKey) < 30 && strlen($apiKey) > 50) {
            throw new PaymenterioConfigurationException("Payment API Key invalid value");
        }
        
        $this->shopID = $shopID;
        $this->apiKey = $apiKey;
        $this->curlConnection = new CurlConnection(self::productionEndpoint, $apiKey);
    }

    /**
     *
     * @param int $system
     * @param string $orderID
     * @param PaymenterioAmount | array $amount
     * @param string $name
     * @throws PaymenterioException
     * @return mixed
     */
    public function createPayment(int $system, string $orderID, $amount, string $name, string $successUrl, string $failUrl, string $notifyUrl, $fake = false)
    {
        try {

            if (! ($amount instanceof Amount)) {
                $amount = Amount::fromArray($amount);
            }

            $transactionData = new Transaction($system, $this->shopID, $orderID, $amount, $name, $successUrl, $failUrl, $notifyUrl);

            if ($fake) {
                $paymentData = array(
                  'status' => 5,
                  'order' => $orderID
                );
                return json_decode(json_encode($paymentData));
            }
            return $this->curlConnection->post("pay", $transactionData);
        } catch (PaymenterioException $exception) {
            throw new PaymenterioCurlException("Create Payment Exception " . $exception->getMessage());
        }
    }
}
