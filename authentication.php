<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 30 SEP 2020
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

require 'log-autoloader.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$res = array();
$res['status'] = false;
$res['data'] = '';
$res['msg'] = '';

$logger = new Logger('Authentication');
$logger->pushHandler(new StreamHandler('var/logs/Opennode_auth_log.txt', Logger::INFO));
$logger->info("authKey: ".$_REQUEST['authKey']);

if(isset($_REQUEST['authKey'])){
	$valid = validateAuthentication($_REQUEST);
	if($valid){
		$tokenData = json_decode(base64_decode($_REQUEST['authKey']),true);
		$email_id = $tokenData['email_id'];
		$validation_id = $tokenData['key'];
		if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
			$conn = getConnection();
			$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$email_id."' and validation_id='".$validation_id."'");
			$stmt->execute([$email_id,$validation_id]);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$result = $stmt->fetchAll();
			//print_r($result[0]);exit;
			if (isset($result[0])) {
				$result = $result[0];
				$payment_option = $result['payment_option'];
				if(!empty($result['api_auth_token'])){
					$sellerdb = $result['sellerdb'];
					$acess_token = $result['acess_token'];
					$store_hash = $result['store_hash'];
					//$cartData = getCartData($email_id,$_REQUEST['cartId'],$acess_token,$store_hash);
					/*$string = base64_decode($_REQUEST['cartData']);
					$string = preg_replace("/[\r\n]+/", " ", $string);
					$json = utf8_encode($string);
					$cartData = json_decode($json,true);*/
					
					$cartAPIRes = getCartData($email_id,$_REQUEST['cartId'],$acess_token,$store_hash,$validation_id);
					if(!is_array($cartAPIRes) || (is_array($cartAPIRes) && count($cartAPIRes) == 0)) {

						$res['status'] = false;
						echo json_encode($res);
						exit;
					}

					//to use cart data from server API response to avoid manipulation from UI side
					$cartData = $cartAPIRes;	
					
					if(!empty($cartData) && isset($cartData['id'])){
						$totalAmount = $cartData['grand_total'];
						$transaction_type = "AUTH";
						if($payment_option == "CFO"){
							$transaction_type = "SALE";
							$totalAmount = $cartData['grand_total'];
						}
						$currency = $cartData['cart']['currency']['code'];
						$billingAddress = $cartData['billing_address'];
						$invoiceId = "OPENNODE".time();
						
						$isql = 'insert into order_payment_details(type,email_id,order_id,cart_id,total_amount,amount_paid,currency,status,params,token_validation_id) values(?,?,?,?,?,?,?,?,?,?)';
						$stmt= $conn->prepare($isql);
						$stmt->execute([$transaction_type, $email_id, $invoiceId,$cartData['id'],$cartData['grand_total'],"0.00",$currency,"PENDING",base64_encode(json_encode($cartData)),$validation_id]);
						$res['status'] = true;
						$url = BASE_URL."opennodePay.php?invoiceId=".base64_encode(json_encode($invoiceId));
						$res['data'] = $url;
					}
				}
			}
		}
	}
}
echo json_encode($res);exit;

function validateAuthentication($request){
	$valid = true;
	if(isset($request['authKey'])){
		
	}else{
		$valid = false;
	}
	if(isset($request['cartId'])){
		
	}else{
		$valid = false;
	}
	return $valid;
}

function getCartData($email_id,$cartId,$acess_token,$store_hash,$validation_id){
	$data = array();
	if(!empty($cartId) && !empty($email_id)){
		$conn = getConnection();
		$header = array(
				"store_hash: ".$store_hash,
				"X-Auth-Token: ".$acess_token,
				"Accept: application/json",
				"Content-Type: application/json"
			);
		$request = '';
		$url = STORE_URL.$store_hash.'/v3/checkouts/'.$cartId;
		//print_r($url);exit;
		$ch = curl_init($url); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		//curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$res = curl_exec($ch);
		curl_close($ch);
		//print_r($res);exit;
		$log_sql = 'insert into api_log(email_id,type,action,api_url,api_request,api_response,token_validation_id) values(?,?,?,?,?,?,?)';
		$stmt= $conn->prepare($log_sql);
		$stmt->execute([$email_id, "BigCommerce", "checkout",addslashes($url),addslashes($request),addslashes($res),$validation_id]);
		
		if(!empty($res)){
			$res = json_decode($res,true);
			if(isset($res['data'])){
				$data = $res['data'];
			}
		}
	}
	
	return $data;
}
?>