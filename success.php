<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('config.php');
require_once('db-config.php');

require 'log-autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$logger = new Logger('OPENNODE Success Order');
$logger->pushHandler(new StreamHandler('var/logs/opennode_success.txt', Logger::INFO));
$logger->info("authKey: ".$_REQUEST['authKey']);

if(isset($_REQUEST['authKey'])){
	$tokenData = json_decode(base64_decode($_REQUEST['authKey']),true);

	$email_id = $tokenData['email_id'];
	$invoice_id = $tokenData['invoice_id'];
	$validation_id = $tokenData['key'];
	if(filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
		$conn = getConnection();
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$email_id."' and validation_id='".$validation_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result);exit;
		if(isset($result[0])) {
			$result = $result[0];
			redirectBigcommerce($result,$email_id,$invoice_id,$validation_id);
		}
	}
}
function redirectBigcommerce($result,$email_id,$invoice_id,$validation_id){
	global $logger;
	$conn = getConnection();
	$acess_token = $result['acess_token'];
	$store_hash = $result['store_hash'];
	
	$header = array(
		"store_hash: ".$store_hash,
		"X-Auth-Token: ".$acess_token,
		"Accept: application/json",
		"Content-Type: application/json"
	);
	
	$url = STORE_URL.$store_hash.'/v2/store';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);

	$logger->info("RedirectBigcommerce - Store API Response : ".$res);
	if(!empty($res)){
		$res = json_decode($res,true);
		if(isset($res['secure_url'])){
			
			$invoice_stmt = $conn->prepare("select * from order_details where email_id='".$email_id."' and invoice_id='".$invoice_id."' and token_validation_id='".$validation_id."'");
			$invoice_stmt->execute();
			$invoice_stmt->setFetchMode(PDO::FETCH_ASSOC);
			$invoice_result = $invoice_stmt->fetchAll();
			if(isset($invoice_result[0])) {
				$invoice_result = $invoice_result[0];
				$order_id = $invoice_result['order_id'];
				$invoice_id = $invoice_result['invoice_id'];
				$bg_customer_id = $invoice_result['bg_customer_id'];
				if($bg_customer_id > 0){

					$logger->info("Redirecting to order-confirmation.");

					header("Location:".$res['secure_url'].'/checkout/order-confirmation/'.$order_id);die();
				}else{
					$logger->info("Redirecting to custom-order-confirmation.");

					$invoice_id = base64_encode(json_encode($invoice_id,true));
					header("Location:".$res['secure_url'].'/opennode-order-confirmation?authKey='.$invoice_id);die();
				}	
			}else{
				$logger->info("Some error creating Bigcommerce Order.");
				header("Location:".$res['secure_url']."/checkout?opennodeinv=".base64_encode(json_encode($invoice_id)));die();
			}
		}
	}
}
?>