<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 30 MAR 2021
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Credentials: true');
}

require_once('config.php');
require_once('db-config.php');
require_once('./opennode-php/init.php');

require 'log-autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use OpenNode\OpenNode;


$logger = new Logger('Authentication');
$logger->pushHandler(new StreamHandler('var/logs/opennode_auth_log.txt', Logger::INFO));
$logger->info("invoiceId: ".$_REQUEST['invoiceId']);

if(isset($_REQUEST['invoiceId'])){
	$invoiceId = json_decode(base64_decode($_REQUEST['invoiceId']),true);
	$conn = getConnection();
	
	$stmt_order_payment = $conn->prepare("select * from order_payment_details where order_id='".$invoiceId."'");
	$stmt_order_payment->execute();
	$stmt_order_payment->setFetchMode(PDO::FETCH_ASSOC);
	$result_order_payment = $stmt_order_payment->fetchAll();
	if (isset($result_order_payment[0])) {
		$result_order_payment = $result_order_payment[0];
		
		$string = base64_decode($result_order_payment['params']);
		$string = preg_replace("/[\r\n]+/", " ", $string);
		$json = utf8_encode($string);
		$cartData = json_decode($json,true);
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$result_order_payment['email_id']."' and validation_id='".$result_order_payment['token_validation_id']."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			$result = $result[0];
			$payment_option = $result['payment_option'];
			$action = "CFO";
			if($payment_option == "CFO"){
				$action = "SALE";
			}
			if($payment_option == "CFS"){
				$action = "captureDelay";
			}
			$billingAddress = $cartData['billing_address'];
			
			\OpenNode\OpenNode::config(array(
				'environment'               => 'dev', // dev OR live
				'auth_token'                => $result['api_auth_token'],
				'curlopt_ssl_verifypeer'    => TRUE // default is false
			));
			$tokenData = array("email_id"=>$result_order_payment['email_id'],"key"=>$result_order_payment['token_validation_id'],"invoice_id"=>$invoiceId);
			$charge_params = array(
                   'description'       => 'Order Payment', //Optional
                   'amount'            => sprintf("%.2f",$result_order_payment['total_amount']),
                   'currency'          => $cartData['cart']['currency']['code'], //Optional
                   'order_id'          => $invoiceId, //Optional
                   'email'             => $billingAddress['email'], //Optional
                   'name'              => $billingAddress['first_name'].' '.$billingAddress['last_name'], //Optional
                   'callback_url'      => BASE_URL."updateOrder.php", //Optional
                   'success_url'       => BASE_URL."success.php?authKey=".base64_encode(json_encode($tokenData)), //Optional
                   'auto_settle'       => true, //Optional
				   'notif_email'	   => $billingAddress['email']
            );


			$response = array();
			try {
				$charge = \OpenNode\Merchant\Charge::create($charge_params);
				header("Location:".OPENNODE_CHECKOUT_URL.''.$charge->id);exit;
			} catch (Exception $e) {
				$response['status'] = 'failed';
				$response['message'] = $e->getMessage();
				////echo $e->getMessage(); 
			}
			echo json_encode($response);exit;
		}
	}
}
?>