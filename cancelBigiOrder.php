<?php
/**
	* Alter Client Details Page
	* Author 247Commerce
	* Date 02 APR 2021
*/
require_once('config.php');
require_once('db-config.php');
require_once('helper.php');

$output = array();
$output['status'] = false;

if(isset($_REQUEST['bc_email_id']) && isset($_REQUEST['orderId'])){
	$conn = getConnection();
	$email_id = @$_REQUEST['bc_email_id'];
	if(!empty($email_id)){
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id=?");
		$stmt->execute([$email_id]);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		
		if (count($result) > 0) {
			$result = $result[0];
			$acess_token = $result['acess_token'];
				$store_hash = $result['store_hash'];
			if(!empty($_REQUEST['orderId'])){
				$stmt_order_payment = $conn->prepare("select * from order_details where order_id=?");
				$stmt_order_payment->execute([$_REQUEST['orderId']]);
				$stmt_order_payment->setFetchMode(PDO::FETCH_ASSOC);
				$result_order_payment = $stmt_order_payment->fetchAll();
				if (isset($result_order_payment[0])) {
					$result_order_payment = $result_order_payment[0];
					$res = updateOrderStatus($result_order_payment['order_id'], $acess_token, $store_hash, $result_order_payment['email_id']);
					$check_errors = json_decode($res);
					if(isset($check_errors->errors)){
					}else{
						if(json_last_error() === 0){
							$response = json_decode($res,true);
							if(isset($response['id']) && isset($response['status_id']) && $response['status_id'] == 5){
								$sql_u = 'update order_details set is_cancelled=? where id=?';
								$stmt = $conn->prepare($sql_u);
								$stmt->execute(['1',$result_order_payment['id']]);
								$output['status'] = true;
							}
						}
					}
				}
			}
			
		}
	}
}

echo json_encode($output,true);exit;
function updateOrderStatus($bigComemrceOrderId,$acess_token,$store_hash,$email_id) {
	$conn = getConnection();
	$url_u = STORE_URL.$store_hash.'/v2/orders/'.$bigComemrceOrderId;
	$request_u = array("status_id"=>5);
	$request_u = json_encode($request_u,true);
	$header = array(
		"store_hash: ".$store_hash,
		"X-Auth-Token: ".$acess_token,
		"Accept: application/json",
		"Content-Type: application/json"
	);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_u);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request_u);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res_u = curl_exec($ch);
	curl_close($ch);
	
	$log_sql = 'insert into api_log(email_id,type,action,api_url,api_request,api_response,token_validation_id) values(?,?,?,?,?,?,?)';
	$stmt= $conn->prepare($log_sql);
	$stmt->execute([$email_id, "BigCommerce", "Update Order",addslashes($url_u),addslashes($request_u),addslashes($res_u),$token_validation_id]);

	return $res_u;
}
?>