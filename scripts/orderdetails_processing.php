<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('../db-config.php');
require_once('../config.php');

$conn = getConnection();

$cols_data = array();
if(isset($_REQUEST['cols_data'])){
	$cols_data = json_decode($_REQUEST['cols_data'],true);
}
$offset = 0;
$limit = 10;
$draw = 1;
if(isset($_REQUEST['draw'])){
	$draw = $_REQUEST['draw'];
}
if(isset($_REQUEST['length']) && $_REQUEST['length'] != '' && intval($_REQUEST['length']) > 0) {
	$limit = $_REQUEST['length'];
}
if(isset($_REQUEST['start']) && $_REQUEST['start'] != '' && intval($_REQUEST['start']) > 0) {
	//$offset = ($_REQUEST['start'] - 1) * $limit;
	$offset = $_REQUEST['start'];
	//$offset = ($_REQUEST['start']/$limit)+1;
}
if(isset($_REQUEST['order'])) {
	$order = $_REQUEST['order'];
	//print_r($db_columns);exit;
	if(!empty($order)){
		$column_det = $db_columns[$order[0]['column']];
		$sorting = $order[0]['dir'];
		$sorting_val = $column_det['value'];
	}
}

$search_query = "";
if(isset($_REQUEST['searchVal']) && !empty($_REQUEST['searchVal'])){
	$search_val = $_REQUEST['searchVal'];

		
	$search_query = "AND opd.order_id LIKE '%$search_val%' or od.order_id LIKE '%$search_val%' or opd.api_response LIKE '%$search_val%'";
}

//print_r($_REQUEST);exit;
$noofrecords = 0;
$final_array = array();
$outer_array = array();

$recordsTotal = 0;
$recordsFiltered = 0;
if(isset($_REQUEST['email_id']) && isset($_REQUEST['key'])){
	$email_id = $_REQUEST['email_id'];
	$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
	$orderby = 'order by opd.id desc';
	if(!empty($sorting_val)){
		$orderby = "ORDER BY ".$sorting_val;
		if(!empty($sorting)){
			$orderby .= " ".$sorting;
		}
	}
	$recordsTotal = 0;
	$recordsFiltered = 0;
	$sql_count = "SELECT count(*) as totalCount FROM order_payment_details opd LEFT JOIN order_details od ON opd.order_id = od.invoice_id WHERE opd.email_id=? and opd.token_validation_id=?";
	$stmt = $conn->prepare($sql_count);
	$stmt->execute([$email_id,$validation_id]);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	if (count($result) > 0) {
		$result = $result[0];
		$recordsTotal = $result['totalCount'];
	}
	$sql_val_filtered = "SELECT count(*) as totalCount FROM order_payment_details opd LEFT JOIN order_details od ON opd.order_id = od.invoice_id WHERE opd.email_id=? and opd.token_validation_id=? ".$search_query;
	$stmt_filter = $conn->prepare($sql_val_filtered);
	$stmt_filter->execute([$email_id,$validation_id]);
	$stmt_filter->setFetchMode(PDO::FETCH_ASSOC);
	$result_filter = $stmt_filter->fetchAll();
	if (count($result_filter) > 0) {
		$result_filter = $result_filter[0];
		$recordsFiltered = $result_filter['totalCount'];
	}
	$sql_res = "SELECT opd.api_response,opd.id,opd.settlement_status,opd.type,opd.amount_paid,opd.email_id as email,opd.order_id as invoice_id,od.order_id,opd.status,opd.currency,opd.total_amount,opd.created_date FROM order_payment_details opd LEFT JOIN order_details od ON opd.order_id = od.invoice_id WHERE opd.email_id=? and opd.token_validation_id=? ".$search_query." ".$orderby." LIMIT ".$offset.','.$limit;
	//echo $sql_res;exit;
	$stmt_res = $conn->prepare($sql_res);
	$stmt_res->execute([$email_id,$validation_id]);
	$stmt_res->setFetchMode(PDO::FETCH_ASSOC);
	$result_final = $stmt_res->fetchAll();
	if(count($result_final) > 0){
		foreach($result_final as $k=>$values) {
			$inner_array = array();
			//print_r(json_encode($values));exit;
			if(!empty($values['invoice_id'])){
				$inner_array[] = '<input type="checkbox" class="form-check-input order_checkbox" value="'.$values['id'].'" name="chkOrgRow" />';
				foreach($cols_data as $dbk=>$dbv){
					if(isset($values[$dbv['val']])){
						if($dbv['val'] == "created_date"){
							$inner_array[] = date("Y-m-d h:i A",strtotime($values[$dbv['val']]));
						}else if($dbv['val'] == "api_response"){
							$res_val = '';
							$api_response = json_decode($values['api_response'],true);
							if(isset($api_response['id'])){
								$res_val = $api_response['id'];
							}
							$inner_array[] = $res_val;
						}else if($dbv['val'] == "amount_paid"){
							$inner_array[] = $values[$dbv['val']]." BTC";
						}else if($dbv['val'] == "status"){
							$status = '';
							if(($values['status'] == "PAID")){
								$status = '<span class="badges2">'.ucfirst(strtolower($values['status'])).'</span>';
							}else if(($values['status'] == "PROCESSING") || ($values['status'] == "UNDERPAID")){
								$status = '<span class="badges1">'.ucfirst(strtolower($values['status'])).'</span>';
							}else{
								$status = '<span class="badges">'.ucfirst(strtolower($values[$dbv['val']])).'</span>';
							}
							$inner_array[] = $status;
						}else if($dbv['val'] == "settlement_status"){
							$sstatus = '';
							if($values['type'] == "SALE"){
								$sstatus = '';
							}else{
								if($values['settlement_status'] == "CHARGE"){
									$sstatus = '<span class="badges1">'.ucfirst($values[$dbv['val']]).'</span>';
								}else{
									$sstatus = '<span class="badges">'.ucfirst($values[$dbv['val']]).'</span>';
								}
							}
							$inner_array[] = $sstatus;
						}else{
							$inner_array[] = $values[$dbv['val']];
						}
					}else{
						if($dbv['val'] == "action"){
							$actions = '';
							if($values['status'] == "UNDERPAID"){
								if($values['is_cancelled'] == 0){
									$actions .= '<a class="btn btn-line cancelOrder" data-order-id="'.$values['order_id'].'" ><button type="button" class="btn btn-outline-primary">Cancel Order</button></a>';
								}else{
									$actions .= '<button type="button" class="btn btn-outline-primary">Cancelled</button>';
								}
							}
							$inner_array[] = $actions;
						}else{
							$inner_array[] = '&nbsp;';
						}
					}
				}
			}
			if(!empty($inner_array)){
				$outer_array[] = $inner_array;
			}
			
		}
	}
}
$final_array['draw'] = $draw;
$final_array['recordsTotal'] = $recordsTotal;
$final_array['recordsFiltered'] = $recordsFiltered;
$final_array['data'] = $outer_array;
echo json_encode($final_array,true);exit;

?>
				