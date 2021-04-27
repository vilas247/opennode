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
$logger = new Logger('OPENNODE Failed Order');
$logger->pushHandler(new StreamHandler('var/logs/opennode_failure.txt', Logger::ERROR));
$logger->info("authKey: ".$_REQUEST['authKey']);

if(isset($_REQUEST['authKey'])){
	$tokenData = json_decode(base64_decode($_REQUEST['authKey']),true);
	$email_id = $tokenData['email_id'];
	$invoice_id = $tokenData['invoice_id'];
	if(filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
		$conn = getConnection();
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$email_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		if(isset($result[0])) {
			$result = $result[0];
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
			if(!empty($res)){
				$res = json_decode($res,true);
				if(isset($res['secure_url'])){
					header("Location:".$res['secure_url']."/checkout?inv=".base64_encode(json_encode($invoice_id)));die();
				}
			}
		}
	}
}
?>