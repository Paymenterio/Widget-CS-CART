<?php

require_once ('paymenterio-lib/sdk/Shop.php');

use Paymenterio\Payments\Shop;
use Paymenterio\Payments\Helpers\SignatureGenerator;
use Paymenterio\Payments\Services\PaymenterioException;

error_reporting(-1);

use Tygh\Registry;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function getAmountForOrder($total, $currency)
{
    return array(
        "value"=>$total,
        "currencyCode"=>$currency
    );
}

function getNameForOrder($orderID) {
    return "Płatność za zamówienie {$orderID}";
}

function getReturnUrlsForOrder($storeURL, $shopID, $orderID)
{
    $successURL = $storeURL . "&success=true&order=" . $orderID;
    $failURL = $storeURL . "&success=false&order=" . $orderID;
    $notifyPattern = $storeURL . '&callback=true&hash={{$hash}}';
    return array(
        'successUrl' =>  $successURL,
        'failUrl' => $failURL,
        'notifyUrl' => buildNotifyUrl($orderID, $shopID, $notifyPattern)
    );
}

function buildNotifyUrl($orderID, $shopID, $notifyPattern) {
    return str_replace('{{$hash}}', SignatureGenerator::generateSHA1Signature($orderID, $shopID), $notifyPattern);
}

if (!defined('PAYMENT_NOTIFICATION')) {
    $paymentId = $_REQUEST['payment_id'];
    $processor_data = fn_get_payment_method_data($paymentId);

    $orderID = $order_info['order_id'];
    $orderData = fn_get_order_info($orderID);
    $config = $orderData['payment_method']['processor_params'];
    $shopID = $config['paymenterio_shop_id'];
    $apiKey = $config['paymenterio_api_key'];
    $currency = 'PLN';
    $total = $orderData['total'];

    $shop = new Shop($shopID, $apiKey);

    $storeURL = Registry::get('config.http_location');
    $returnURL = $storeURL . "/index.php?dispatch=payment_notification.notify&payment=paymenterio";

    $urls = getReturnUrlsForOrder($returnURL, $shopID, $orderID);

    try {
        $paymentData = $shop->createPayment(
            1,
            $orderID,
            getAmountForOrder($total, $currency),
            getNameForOrder($orderID),
            $urls['successUrl'],
            $urls['failUrl'],
            $urls['notifyUrl']
        );
    } catch (PaymenterioException $e) {
        exit ($e);
    }

    fn_change_order_status($orderID, 'O', '', false);
    header("Location: ".$paymentData->payment_link);
    exit();
    
} else {

    if (isset($_GET['success'])) {

        if ($_GET ['success'] == "true") {

            $orderID = $_GET ['order'];
            fn_order_placement_routines ( 'route', $orderID );


        } elseif ($_GET ['success'] == "false") {

            $orderID = $_GET ['order'];
            fn_change_order_status ( $orderID, "F" );
            fn_order_placement_routines ( 'route', $orderID );

        }

    } elseif (isset($_GET ['callback']) && $_GET ['callback'] == "true") {

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            Header('HTTP/1.1 400 Bad Request');
            exit("BadRequest - The request could not be resolved, try again.");
        }

        $hash = $_GET['hash'];
        $body = json_decode(file_get_contents("php://input"), true);
        $orderID = 0;
        $statusID = 0;

        if (isset($body['order']) && !empty($body['order'])) {
            $orderID = $body['order'];
        }

        if (isset($body['status']) && !empty($body['status'])) {
            $statusID = $body['status'];
        }

        $orderData = fn_get_order_info($orderID);
        if (empty($orderData)) {
            Header('HTTP/1.1 404 Not Found');
            exit("OrderNotFoundException - The order was not found or was completed successfully.");
        }

        $config = $orderData['payment_method']['processor_params'];
        $shopID = $config['paymenterio_shop_id'];

        $isSignatureValid = SignatureGenerator::verifySHA1Signature($orderID, $shopID, $hash);
        if (!$isSignatureValid) {
            Header('HTTP/1.1 400 Bad Request');
            exit("WrongSignatureException - Signature mismatch.");
        }
        $transactionHash = $body['transaction_hash'];
        if ($statusID == 5) {
            $response = array('order_status'=>"P",'reason_text'=>'Zamówienie nr : '.$orderID.' zostało poprawnie opłacone. Hash transakcji: ' . $transactionHash . '.');
            fn_finish_payment($orderID, $response, false);;
            exit('Success');
        } elseif ($statusID <= 4) {
            $response = array('order_status'=>"O",'reason_text'=>'Zmienił się status płatności dla zamówienia nr : '.$orderID.'. Aktualny status to ' . $statusID . '. Hash transakcji: ' . $transactionHash . '.');
            fn_finish_payment($orderID, $response, false);
            exit('Changed');
        } else {
            $response = array('order_status'=>"F",'reason_text'=>'Zamówienie nr : '. $orderID .' nie zostało poprawnie opłacone lub wystąpił błąd podczas jego przetwarzania. Hash transakcji: ' . $transactionHash . '.');
            fn_finish_payment($orderID, $response, false);
            exit('Cancelled');
        }
			
	} else {
        Header('HTTP/1.1 400 Bad Request');
        exit("Nothing to do, try again.");
    }
}


?>