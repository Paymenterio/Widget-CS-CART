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
namespace Paymenterio\Payments\Services;

use Exception;

class PaymenterioException extends Exception {
}
class PaymenterioCurlException extends PaymenterioException {
}
class PaymenterioConfigurationException extends PaymenterioException {
}
class PaymenterioTransactionException extends PaymenterioException {
}