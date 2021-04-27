<?php
/**
	* Alter Client Details Page
	* Author 247Commerce
	* Date 22 FEB 2021
*/
require_once('config.php');
require_once('db-config.php');
require_once('helper.php');

//print_r($_FILES);exit;
if(isset($_REQUEST['container_id'])){
	$conn = getConnection();
	$email_id = @$_REQUEST['bc_email_id'];
	$key = @$_REQUEST['key'];
	if(!empty($email_id) && !empty($key)){
		$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
		$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$email_id."' and validation_id='".$validation_id."'");
		$stmt->execute();
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		
		if (count($result) > 0) {
			$stmt_c = $conn->prepare("select * from custom_opennodepay_button where email_id='".$email_id."' and token_validation_id='".$validation_id."'");
			$stmt_c->execute();
			$stmt_c->setFetchMode(PDO::FETCH_ASSOC);
			$result_c = $stmt_c->fetchAll();
			$enable = 0;
			if(isset($_REQUEST['is_enabled']) && $_REQUEST['is_enabled'] == "on"){
				$enable = 1;
			}
			$is_image_enabled = 0;
			if(isset($_REQUEST['is_image_enabled']) && $_REQUEST['is_image_enabled'] == "on"){
				$is_image_enabled = 1;
			}
			if (count($result_c) > 0) {
				if ($_FILES['image_url']['name'] != "" && $_FILES['image_url']['error'] == 0 && $_FILES['image_url']['size'] > 0) {
					$ext = pathinfo($_FILES ['image_url'] ['name'], PATHINFO_EXTENSION);
					$target_dir = "uploads/";
					$target_file = $target_dir."opennode-".time().".".$ext;
					if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
						$iusql = 'update custom_opennodepay_button set image_url="'.$target_file.'" where email_id="'.$email_id.'" and token_validation_id="'.$validation_id.'"';
						$conn->exec($iusql);
					}
				}
				$usql = 'update custom_opennodepay_button set container_id="'.$_REQUEST['container_id'].'",css_prop="'.$_REQUEST['css_prop'].'",html_code="'.htmlentities($_REQUEST['html_code']).'",is_enabled="'.$enable.'",is_image_enabled="'.$is_image_enabled.'" where email_id="'.$email_id.'" and token_validation_id="'.$validation_id.'"';
				// execute the query
				$conn->exec($usql);
				$sellerdb = $result[0]['sellerdb'];
				alterFile($sellerdb,$email_id,$validation_id);
			}else{
				$image_url = '';
				if ($_FILES['image_url']['name'] != "" && $_FILES['image_url']['error'] == 0 && $_FILES['image_url']['size'] > 0) {
					$ext = pathinfo($_FILES ['image_url'] ['name'], PATHINFO_EXTENSION);
					$target_dir = "uploads/";
					$fileName = 
					$target_file = $target_dir."opennode-".time().".".$ext;
					if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
						$image_url = $target_file;
					}
				}
				$isql = 'insert into custom_opennodepay_button(email_id,container_id,css_prop,is_enabled,image_url,is_image_enabled,token_validation_id,html_code) values("'.$email_id.'","'.$_REQUEST['container_id'].'","'.$_REQUEST['css_prop'].'","'.$enable.'","'.$target_file.'","'.$is_image_enabled.'","'.$validation_id.'","'.htmlentities($_REQUEST['html_code']).'")';
				$stmt_i = $conn->prepare($isql);
				// execute the query
				$stmt_i->execute();
				$sellerdb = $result[0]['sellerdb'];
				alterFile($sellerdb,$email_id,$validation_id);
			}
			header("Location:customButton.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
		}else{
			header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
		}
	}else{
		header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
	}
}else{
	header("Location:customButton.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
}

/* creating tables Based on Seller */
function alterFile($sellerdb,$email_id,$validation_id){
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
		$folderPath = './'.$sellerdb;
		
		$stmt_c = $conn->prepare("select * from custom_opennodepay_button where email_id='".$email_id."' and token_validation_id='".$validation_id."'");
		$stmt_c->execute();
		$stmt_c->setFetchMode(PDO::FETCH_ASSOC);
		$result_c = $stmt_c->fetchAll();
		if (count($result_c) > 0) {
			$result_c = $result_c[0];
		}
		$enable = 0;
		$is_image_enabled = '';
		if(isset($result_c['is_enabled']) && $result_c['is_enabled'] == 1){
			$enable = 1;
		}
		if(isset($result_c['is_image_enabled']) && $result_c['is_image_enabled'] == "1"){
			$is_image_enabled = "1";
		}
		$filecontent = '$("head").append("<script src=\"'.BASE_URL.'js/247opennodeloader.js\" ></script>");';
		$filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.BASE_URL.'css/247opennodeloader.css\" />");';
		
		$media_css = '@media (max-width: 639px) {
			#opennodepaymentForm .button--large {
				font-size: 1.38462rem;
				padding: 0px;
			}
		}';
		$filecontent .= '$("body").append("<style>'.preg_replace("/[\r\n]*/","",$media_css).'</style>");';
		
		$id = $result_c['container_id'];
		$css_prop = $result_c['css_prop'];
		$image_url = BASE_URL.$result_c['image_url'];
		$html_code = html_entity_decode($result_c['html_code']);
		$btnContent = '';
		if(!empty($id)){
			if(!empty($html_code)){
				$btnContent = str_replace("#imageSrc2",BASE_URL.'uploads/pay-mini.png',$html_code);
				$btnContent = str_replace("#imageSrc1",BASE_URL.'uploads/pay.png',$btnContent);
				$btnContent = str_replace("#imageSrc",BASE_URL.'uploads/logo.png',$btnContent);
			}else{
				$btnContent = '<button class="openode-btn desktop" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay.png" style="float:right"></button><button class="openode-btn mobile" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay-mini.png" style="float:right"></button>';
			}
			$filecontent .= '$(document).ready(function() {
					var stIntId = setInterval(function() {
						if($(".checkout-step--payment").length > 0) {
							if($("#247opennodepayment").length == 0){
								$("'.$id.'").after(\'<div id="247opennodepayment" class="checkout-form" style="padding:1px"><div id="247OpennodeErr" style="color:red"></div><form id="opennodepaymentForm"><input type="hidden" id="247opennodekey" value="'.base64_encode(json_encode($tokenData)).'" >'.$btnContent.'</form></div>\');
								loadOpennodeStatus();
								clearInterval(stIntId);
							}
						}
					}, 1000);';
		}else{
			if(!empty($html_code)){
				$btnContent = str_replace("#imageSrc2",BASE_URL.'uploads/pay-mini.png',$html_code);
				$btnContent = str_replace("#imageSrc1",BASE_URL.'uploads/pay.png',$btnContent);
				$btnContent = str_replace("#imageSrc",BASE_URL.'uploads/logo.png',$btnContent);
			}else{
				$btnContent = '<button class="openode-btn desktop" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay.png" style="float:right"></button><button class="openode-btn mobile" type="submit"><img src="'.BASE_URL.'uploads/logo.png" style="float:left"><img src="'.BASE_URL.'uploads/pay-mini.png" style="float:right"></button>';
			}
			$filecontent .= '$(document).ready(function() {
				var stIntId = setInterval(function() {
				if($(".checkout-step--payment").length > 0) {
					if($("#247opennodepayment").length == 0){
						$(".checkout-step--payment .checkout-view-header").after(\'<div id="247opennodepayment" class="checkout-form" style="padding:1px"><div id="247OpennodeErr" style="color:red"></div><form id="opennodepaymentForm"><input type="hidden" id="247opennodekey" value="'.base64_encode(json_encode($tokenData)).'" >'.$btnContent.'</form></div>\');
						loadOpennodeStatus();
						clearInterval(stIntId);
					}
				}
			}, 1000);';
		}
		if(!empty($css_prop)){
			$filecontent .= '$("body").append("<style>'.preg_replace("/[\r\n]*/","",$css_prop).'</style>");';
		}else{
			$filecontent .= '$("body").append("<style>'.preg_replace("/[\r\n]*/","",$css_prop_default).'</style>");';
		}
	$filecontent .= '$("body").on("click","#opennodepaymentForm",function(e){
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
									var cartData = window.btoa(unescape(encodeURIComponent(JSON.stringify(cartres))));
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
									if(bstatus ==0 && sstatus == 0){
										$.ajax({
											type: "POST",
											dataType: "json",
											crossDomain: true,
											url: "'.BASE_URL.'authentication.php",
											dataType: "json",
											data:{"authKey":key,"cartId":cartId,cartData:cartData},
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
	if(typeof(billingAddress.stateOrProvince) != "undefined" && billingAddress.stateOrProvince !== null && billingAddress.stateOrProvince !== "") {
		
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
	if(typeof(billingAddress.phone) != "undefined" && billingAddress.phone !== null && billingAddress.phone !== "") {
		
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
			if(typeof(shippingAddress.stateOrProvince) != "undefined" && shippingAddress.stateOrProvince !== null && shippingAddress.stateOrProvince !== "") {
				
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
			if(typeof(shippingAddress.phone) != "undefined" && shippingAddress.phone !== null && shippingAddress.phone !== "") {
				
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