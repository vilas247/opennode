<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<?php
/**
	* Initial Page
	* Author 247Commerce
	* Date 30 MAR 2021
*/
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('config.php');
require_once('db-config.php');

/*creating DB connection */
$conn = getConnection();

/* check zoovu token is validated or not 
	If already Verified redirect to Home Page
*/
if(isset($_REQUEST['bc_email_id']) && isset($_REQUEST['key'])){
	$email_id = $_REQUEST['bc_email_id'];
	$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
	$stmt = $conn->prepare("select * from opennode_token_validation where email_id='".$email_id."' and validation_id='".$validation_id."'");
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	//print_r($result[0]);exit;
	if (isset($result[0])) {
		$result = $result[0];
		if(empty($result['api_auth_token'])){
			header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
		}
	}else{
		header("Location:index.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
	}
}


?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
      <title>Opennode</title>
      <!-- Bootstrap -->
      <link href="css/bootstrap.css" rel="stylesheet">
      <link href="css/main.css" rel="stylesheet">
      <link rel="stylesheet" href="css/jtoggler.styles.css">
      <link href="css/media.css" rel="stylesheet">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
      <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->
   </head>
	<body style="background:#f9fafc;">
		<section class="seeting_top">
			<div class="container">
				<div class="row">
				   <div class="col-md-6"><img src="images/logo-inner.jpg"></div>
				   <div class="col-md-6 st-right">
					  <a href="customButton.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".$_REQUEST['key'] ?>" ><button type="button" class="btn cp-btn">Custom Payment Button</button></a>
					  <a href="orderDetails.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".$_REQUEST['key'] ?>" ><button type="button" class="btn order-btn">Order Details</button></a>
				   </div>
				</div>
			</div>
		</section>
		<section class="seeting-sec">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<form action="updateCustomButton.php" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="bc_email_id" value="<?= @$_REQUEST['bc_email_id'] ?>" />
						<input type="hidden" name="key" value="<?= @$_REQUEST['key'] ?>" />
						<div class="panel panel-primary">
							<div class="panel-heading">
								<div class="row order-details1">
								   <div class="col-md-6">
									  <h3>Custom Payment Button</h3>
								   </div>
									<div class="col-md-6 col-4 text-right">
										<a href="dashboard.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".$_REQUEST['key'] ?>">
											<h5><i class="fas fa-arrow-left"></i> Back To Dashboard</h5>
										</a>
									</div>
								</div>
							</div>
							<?php
								$container_id = '.checkout-step--payment .checkout-view-header';
								$css_prop = '.openode-btn{
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
								$html_code = '<button class="openode-btn desktop" type="submit"><img src="#imageSrc" style="float:left"><img src="#imageSrc1" style="float:right"></button><button class="openode-btn mobile" type="submit"><img src="#imageSrc" style="float:left"><img src="#imageSrc2" style="float:right"></button>';
								
								$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
								$stmt_c = $conn->prepare("select * from custom_opennodepay_button where email_id='".$_REQUEST['bc_email_id']."' and token_validation_id='".$validation_id."'");
								$stmt_c->execute();
								$stmt_c->setFetchMode(PDO::FETCH_ASSOC);
								$result_c = $stmt_c->fetchAll();
								if(count($result_c) > 0){
									$result_c = $result_c[0];
								}else{
									$result_c['container_id'] = $container_id;
									$result_c['css_prop'] = $css_prop;
									$result_c['html_code'] = $html_code;
								}
								//print_r($result_c);exit;
								$enable = '';
								$is_image_enabled = '';
								if(isset($result_c['is_enabled']) && $result_c['is_enabled'] == "1"){
									$enable = "checked";
								}
								if(isset($result_c['is_image_enabled']) && $result_c['is_image_enabled'] == "1"){
									$is_image_enabled = "checked";
								}
								
							?>
							<div class="panel-body custom-payment-btn">
								<div class="row">
									<div class="col-md-6">
										<h2>Container Id / Css</h2>
										<textarea class="form-control2" name="container_id" placeholder=".checkout-step--payment .checkout-view-header" id="exampleFormControlTextarea1" id="container_id" rows="5"><?= @$result_c['container_id'] ?></textarea>
									</div>
									<div class="col-md-6">
										<div class="row">
											<div class="col-md-6">
												<h2>Css Properties</h2>
											</div>
											<div class="col-md-6 text-right" style="display:none;" ><input type="checkbox" style="display:none;" name="is_enabled" <?= $enable ?> class="jtoggler"></div>
											<div class="col-md-12">
													<textarea class="form-control2" id="css_prop" name="css_prop" placeholder=".openode-btn{
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
}" rows="5"><?= @$result_c['css_prop'] ?></textarea>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<h2>Html Code</h2>
										<textarea class="form-control2" name="html_code" id="html_code" placeholder='<button class="openode-btn desktop" type="submit"><img src="#imageSrc" style="float:left"><img src="#imageSrc1" style="float:right"></button><button class="openode-btn mobile" type="submit"><img src="#imageSrc" style="float:left"><img src="#imageSrc2" style="float:right"></button>' id="exampleFormControlTextarea1" rows="5"><?= @$result_c['html_code'] ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="text-right"><button type="button" id="resetCustom" class="btn order-btn">Reset</button>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn order-btn">Update</button></div>
						</form>
					</div>
				</div>
			</div>
		</section>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>
		<script src="js/jtoggler.js"></script>
		<script>
			$(document).ready(() => {
				$('.jtoggler').jtoggler();
				$(document).on('jt:toggled', function(event, target) {
					console.log(event, target);
					console.info($(target).prop('checked'))
				});
				$(document).on('jt:toggled:multi', function (event, target) {
					console.log(event, target);
					console.info($(target).parent().index())
				});
			});
			var id = '<?= $container_id ?>';
			var css = '<?= base64_encode($css_prop) ?>';
			var html_code = '<?= $html_code ?>';
			$('body').on('click','#resetCustom',function(){
				$('body #container_id').val(id);
				$('body #css_prop').val(window.atob(css));
				$('body #html_code').val(html_code);
			});
		</script>
	</body>
</html>