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
namespace Paymenterio\Payments\Model;

use Paymenterio\Payments\Interfaces\Data;
use Paymenterio\Payments\Services\PaymenterioTransactionException;

class Transaction implements Data
{
    
    /**
     *
     * @var int $system
     * @var string $shop
     * @var int $order
     * @var float $amount_value
     * @var string $amount_currencyCode
     * @var string $name
     * @var string $success_url
     * @var string $fail_url
     * @var string $notify_url
     */

    public $system;
    public $shop;
    public $order;
    public $amount;
    public $currency;
    public $name;
    public $success_url;
    public $fail_url;
    public $notify_url;
    
    /**
     *
     * @param int $system
     * @param string $shop
     * @param string $order
     * @param float $amount_value
     * @param string $amount_currencyCode
     * @param string $name
     * @throws PaymenterioTransactionException
     */
    public function __construct(int $system, string $shop, string $order, $amount, string $name, string $successUrl, string $failUrl, string $notifyUrl)
    {
        if (empty($system) || empty($amount) || empty($shop) || empty($order) || empty($name) || empty($successUrl) || empty($failUrl) || empty($notifyUrl)) {
            throw new PaymenterioTransactionException("Required params not set");
        }
        $amount = $amount->toArray();
        $this->system = $system;
        $this->shop = $shop;
        $this->order = $order;
        $this->amount = $amount['amount.value'];
        $this->currency = $amount['amount.currencyCode'];
        $this->name = $name;

        $this->success_url = $successUrl;
        $this->fail_url = $failUrl;
        $this->notify_url = $notifyUrl;
    }

    /**
     *
     * @see PaymenterioData::toArray()
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $key => $value) {
            if (!is_object($value)) {
                $array [str_replace("_", ".", $key)] = $value;
            } else {
                $array = array_merge($array, $value->toArray());
            }
        }
        
        return $array;
    }
}
