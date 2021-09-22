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

class Amount implements Data
{
	/**
	 *
	 * @var string $value
	 * @var string $currencyCode
	 */
	private $value, $currencyCode;

	/**
	 *
	 * @param float $value        	
	 * @param string $currencyCode        	
	 */
	function __construct($value, $currencyCode)
	{

		if (!is_numeric($value)) {
			throw new PaymenterioTransactionException("Amount value not numeric");
		}

		if (strlen($currencyCode) !== 3) {
			throw new PaymenterioTransactionException("Currency code not valid");
		}

		$this->value = number_format($value, 2, ".", "");
		$this->currencyCode = $currencyCode;
	}
	public static function fromArray($array)
	{
		return new Amount($array['value'], $array['currencyCode']);
	}

	/**
	 *
	 * @see PaymenterioData::toArray()
	 */
	public function toArray()
	{
		$array = array();
		foreach ($this as $key => $value) {
			$array["amount." . $key] = $value;
		}
		return $array;
	}
}
