<?php
/**
	* Token Validation Page
	* Author 247Commerce
	* Date 30 MAR 2021
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('config.php');
require_once('db-config.php');
require_once('helper.php');
//print_r($_REQUEST);
if(isset($_REQUEST['api_auth_token'])){
	$conn = getConnection();
	$email_id = $_REQUEST['bc_email_id'];
	$key = @$_REQUEST['key'];
	if(!empty($email_id) && !empty($key)){
		$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id=? and validation_id=?");
		$stmt->execute([$email_id,$validation_id]);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		//print_r($result[0]);exit;
		if (isset($result[0])) {
			$result = $result[0];
			if(!empty($_REQUEST['api_auth_token'])){
				$sellerdb = $result['sellerdb'];
				$data = createFolder($sellerdb,$email_id,$validation_id);
				$sql = 'update opennode_token_validation set api_auth_token=? where email_id=? and validation_id=?';
				//echo $sql;exit;
				$stmt = $conn->prepare($sql);
				$stmt->execute([$_REQUEST['api_auth_token'],$email_id,$validation_id]);
				header("Location:dashboard.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
			}else{
				header("Location:index.php?error=1&bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
			}
		}else{
			header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
		}
	}else{
		header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
	}
}else{
	header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
}

/* creating folder Based on Seller */
function createFolder($sellerdb,$email_id,$validation_id){
	$conn = getConnection();
	$css_prop_default = '.openode-btn{
	width: 100%;
	#background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 87.5%);
	border: 1px solid #DDDDDD;
	box-sizing: border-box;
	padding: 12px 27.5px 12px 57px;
}
.openode-btn.mobile{
	display: none;
}
@media screen and (max-width: 550px) {
	.openode-btn {
		padding: 12px 27.5px 12px 49px;
	}
	.openode-btn img:first-child{
		width: 110px;
	}
		.openode-btn img:nth-child(2){
		width: 68px;
	}
	   .openode-btn.mobile{
		   display: block;
	}
		.openode-btn.desktop{
			 display: none;
	}
}
@media screen and (max-width: 350px) {
	.openode-btn img:first-child {
		width: 90px;
		margin-top: 3px;
	}
}';
	$tokenData = array("email_id"=>$email_id,"key"=>$validation_id);
	if(!empty($sellerdb)){
		$image_url = BASE_URL.'uploads/default.png';
		$folderPath = './'.$sellerdb;
		$filecontent = '$("head").append("<script src=\"'.BASE_URL.'js/247opennodeloader.js\" ></script>");';
		$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.BASE_URL.'css/247opennodeloader.css\" />");';
		
		$filecontent .= '$("body").append("<style>'.preg_replace("/[\r\n]*/","",$css_prop_default).'</style>");';
		
		$filecontent .= '$(document).ready(function() {
	var stIntId = setInterval(function() {
		if($(".checkout-step--payment").length > 0) {
			if($("#247opennodepayment").length == 0){
				$(".checkout-step--payment .checkout-view-header").after(\'<div id="247opennodepayment" class="checkout-form" style="padding:1px"><div id="247OpennodeErr" style="color:red"></div><form id="opennodepaymentForm"><input type="hidden" id="247opennodekey" value="'.base64_encode(json_encode($tokenData)).'" ><button class="openode-btn desktop" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay.png" style="float:right"></button><button class="openode-btn mobile" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay-mini.png" style="float:right"></button></form></div>\');
				loadOpennodeStatus();
				clearInterval(stIntId);
			}
		}
	}, 1000);
	$("body").on("click","#opennodepaymentForm",function(e){
		e.preventDefault();
		var text = "Please wait...";
		var current_effect = "bounce";
		var key = $("body #247opennodekey").val();
		$("#247opennodepayment").waitMe({
			effect: current_effect,
			text: text,
			bg: "rgba(255,255,255,0.7)",
			color: "#000",
			maxSize: "",
			waitTime: -1,
			source: "'.BASE_URL.'images/img.svg",
			textPos: "vertical",
			fontSize: "",
			onClose: function(el) {}
		});
		var checkDownlProd = false;
		$.ajax({
			type: "GET",
			dataType: "json",
			url: "/api/storefront/cart",
			success: function (res) {
				if(res.length > 0){
					if(res[0]["id"] != undefined){
						var cartId = res[0]["id"];
						var cartCheck = res[0]["lineItems"];
						checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
						if(cartId != ""){
							$.ajax({
								type: "GET",
								dataType: "json",
								url: "/api/storefront/checkouts/"+cartId,
								success: function (cartres) {
									var billingAddress = "";
									var consignments = "";
									var bstatus = 0;
									var sstatus = 0;
									if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
										billingAddress = cartres.billingAddress;
										bstatus = opencodebillingAddressValdation(billingAddress);
									}
									if(checkDownlProd){
										if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
											consignments = cartres.consignments;
											sstatus = opencodeshippingAddressValdation(consignments);
										}
									}
									if(bstatus ==0 && sstatus == 0 && parseFloat(cartres.grandTotal)>0){
										$.ajax({
											type: "POST",
											dataType: "json",
											crossDomain: true,
											url: "'.BASE_URL.'authentication.php",
											dataType: "json",
											data:{"authKey":key,"cartId":cartId},
											success: function (res) {
												$("#247opennodepayment").waitMe("hide");
												if(res.status){
													var data = res.data;
													window.location.href=data;
												}
											},error: function(){
												$("#247opennodepayment").waitMe("hide");
											}
										});
									}else{
										alert("Please Select Billing Address and Shipping Address");
										$("#247opennodepayment").waitMe("hide");
									}
								},error: function(){
									$("#247opennodepayment").waitMe("hide");
								}
							});
						}
					}
				}
			},error: function(){
				$("#opennodepaymentForm").waitMe("hide");
			}
		});
		
	});
});
function opencodebillingAddressValdation(billingAddress){
	var errorCount = 0;
	if(typeof(billingAddress.firstName) != "undefined" && billingAddress.firstName !== null && billingAddress.firstName !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.lastName) != "undefined" && billingAddress.lastName !== null && billingAddress.lastName !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.address1) != "undefined" && billingAddress.address1 !== null && billingAddress.address1 !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.email) != "undefined" && billingAddress.email !== null && billingAddress.email !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.city) != "undefined" && billingAddress.city !== null && billingAddress.city !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.postalCode) != "undefined" && billingAddress.postalCode !== null && billingAddress.postalCode !== "") {
		
	}else{
		errorCount++;
	}
	if(typeof(billingAddress.country) != "undefined" && billingAddress.country !== null && billingAddress.country !== "") {
		
	}else{
		errorCount++;
	}
	
	return errorCount;
}

function opencodeshippingAddressValdation(shippingAddress){
	var errorCount = 0;
	if(shippingAddress.length > 0){
		if(typeof(shippingAddress[0].shippingAddress) != "undefined" && shippingAddress[0].shippingAddress !== null && shippingAddress[0].shippingAddress !== "") {
			shippingAddress = shippingAddress[0].shippingAddress;
			if(typeof(shippingAddress.firstName) != "undefined" && shippingAddress.firstName !== null && shippingAddress.firstName !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.lastName) != "undefined" && shippingAddress.lastName !== null && shippingAddress.lastName !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.address1) != "undefined" && shippingAddress.address1 !== null && shippingAddress.address1 !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.city) != "undefined" && shippingAddress.city !== null && shippingAddress.city !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.postalCode) != "undefined" && shippingAddress.postalCode !== null && shippingAddress.postalCode !== "") {
				
			}else{
				errorCount++;
			}
			if(typeof(shippingAddress.country) != "undefined" && shippingAddress.country !== null && shippingAddress.country !== "") {
				
			}else{
				errorCount++;
			}
		}
	}else{
		errorCount++;
	}
	return errorCount;
}
function checkOnlyDownloadableProducts(cartData){
	var status = false;
	if(cartData != ""){
		if(cartData.physicalItems.length > 0 || cartData.customItems.length > 0){
			status = true;
		}
		else{
			if(cartData.digitalItems.length > 0){
				status = false;
			}
		}
	}
	return status;
}
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split("&"),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split("=");

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};
function loadOpennodeStatus(){
	var key = getUrlParameter("opennodeinv");
	if(key != "undefined" && key != ""){
		$.ajax({
			type: "POST",
			dataType: "json",
			crossDomain: true,
			url: "'.BASE_URL.'getPaymentStatus.php",
			dataType: "json",
			data:{"authKey":key},
			success: function (res) {
				if(res.status){
					$("body #247OpennodeErr").text(res.msg);
				}
			}
		});
	}
}
';
		$filename = 'custom_script.js';
		$res = saveFile($filename,$filecontent,$folderPath);
	}
}
?>