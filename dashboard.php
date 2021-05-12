<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<?php
/**
	* Feed List Page
	* Author 247Commerce
	* Date 22 FEB 2021
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once('db-config.php');
require_once('config.php');

$conn = getConnection();
if(isset($_REQUEST['bc_email_id']) && isset($_REQUEST['key'])){
	$email_id = $_REQUEST['bc_email_id'];
	$validation_id = json_decode(base64_decode($_REQUEST['key']),true);
	$stmt = $conn->prepare("select * from opennode_token_validation where email_id=? and validation_id=?");
	$stmt->execute([$email_id,$validation_id]);
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
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	  <link rel="stylesheet" href="css/toaster/toaster.css">
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
                  <a href="customButton.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".@$_REQUEST['key'] ?>" ><button type="button" class="btn cp-btn">Custom Payment Button</button></a>
                  <a href="orderDetails.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".@$_REQUEST['key'] ?>" ><button type="button" class="btn order-btn">Order Details</button></a>
               </div>
            </div>
         </div>
      </section>
      <section class="seeting-sec">
         <div class="container">
            <div class="row">
				<?php 
					$payment_option = 'CFO';
					/* getting feed data from table */
					$con = getConnection();
					$email_id = @$_REQUEST['bc_email_id'];
					$stmt = $conn->prepare("select * from opennode_token_validation where email_id=? and validation_id=?");
					$stmt->execute([$email_id,$validation_id]);
					$stmt->setFetchMode(PDO::FETCH_ASSOC);
					$result_token = $stmt->fetchAll();
					
					if (count($result_token) > 0) {
						foreach($result_token as $k=>$v){
							$payment_option = $v['payment_option'];
							$enabled = false;
							if($v['is_enable'] == 1){
								$enabled = true;
							}
					?>
				<div class="col-md-12">
					<div class="panel panel-primary">
						<div class="panel-heading">Settings</div>
						<div class="panel-body">
							<div class="row">
							   <div class="col-md-3">
								  <h6>BigCommerce Email</h6>
								  <h5><?= $v['email_id'] ?></h5>
							   </div>
							   <div class="col-md-8">
								  <h6>API Key</h6>
								  <h5><?= '*********************************'.substr ($v['api_auth_token'], -4) ?></h5>
							   </div>
							   <div class="col-md-1">
								  <h6>Action</h6>
								  <input type="checkbox" id="actionChange" class="jtoggler" <?= ($enabled)?'checked':'' ?> value="<?= ($enabled)?'1':'0' ?>" >
							   </div>
							</div>
						</div>
					</div>
					<!--<div class="text-right"><button type="button" class="btn order-btn">Update</button></div>-->
               </div>
			   <?php
					}
				}
				?>
               <div class="col-md-12">
                  <h3>Order Details <img src="images/refresh.svg" id="refreshButton" style="height:3%;width:3%"></h3>
                  <div id="no-more-tables" class="table-responsive">
                     <table class="table">
                        <thead class="cf">
                           <tr>
                              <th>OpenNode ID</th>
                              <th>Order ID</th>
                              <th class="numeric">Payment type</th>
                              <th class="numeric">Order Status</th>
                              <th class="numeric">Currency</th>
                              <th class="numeric">total</th>
                              <!--<th class="numeric">amount paid</th>-->
                              <th class="numeric">Created date</th>
                           </tr>
                        </thead>
                        <tbody>
							<?php
								$sql_res = "SELECT opd.api_response,opd.id,opd.settlement_status,opd.type,opd.amount_paid,opd.email_id as email,opd.order_id as invoice_id,od.order_id,opd.status,opd.currency,opd.total_amount,opd.created_date FROM order_payment_details opd LEFT JOIN order_details od ON opd.order_id = od.invoice_id WHERE opd.email_id=? and opd.token_validation_id=? order by opd.id desc LIMIT 0,15";
								$stmt_res = $conn->prepare($sql_res);
								$stmt_res->execute([$_REQUEST['bc_email_id'],$validation_id]);
								$stmt_res->setFetchMode(PDO::FETCH_ASSOC);
								$result_final = $stmt_res->fetchAll();
								if(count($result_final) > 0){
									foreach($result_final as $k=>$values) {
							?>
									<tr>
										<td>
											<?php
												$res_val = '';
												$api_response = json_decode(str_replace("\\","",$values['api_response']),true);
												if(isset($api_response['id'])){
													$res_val = $api_response['id'];
												}
												echo $res_val;
											?>
										</td>
										<td><?= $values['order_id'] ?></td>
										<td>
											<?= $values['type'] ?>
										</td>
										<td>
											<?php
												$status = '';
												if(($values['status'] == "PAID")){
													$status = '<span class="badges2">'.ucfirst(strtolower($values['status'])).'</span>';
												}else if(($values['status'] == "PROCESSING") || ($values['status'] == "UNDERPAID")){
													$status = '<span class="badges1">'.ucfirst(strtolower($values['status'])).'</span>';
												}else{
													$status = '<span class="badges">'.ucfirst(strtolower($values['status'])).'</span>';
												}
											?>
											<?= $status ?>
										</td>
										<td>
											<?= $values['currency'] ?>
										</td>
										<td>
											<?= $values['total_amount'] ?>
										</td>
										<!--<td>
											<?= $values['amount_paid']." BTC" ?>
										</td>-->
										<td><?= date("Y-m-d h:i A",strtotime($values['created_date'])) ?></td>
									</tr>
							<?php
									}
								}else{
									echo '<tr class="odd"><td valign="top" colspan="10" class="dataTables_empty">No data available in table</td></tr>';
								}
							?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  <!-- Modal -->
		<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
			  <div class="modal-content">
				<div class="modal-header">
				  <h5 class="modal-title" id="exampleModalLongTitle"><span><img src="images/trash-purple.svg" style="margin-top: -5px;"></span> <span class="purple">Disable OPENNODE</span>  </h5>
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
				</div>
				<div class="modal-body" id="modalContent">
				  Are you sure you want to disable <strong>OPENNODE?</strong>.
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-order" id="cancelConfirm" data-dismiss="modal">Cancel</button>
				  <button type="button" class="btn btn-order" id="deleteConfirm">Disable</button>
				</div>
			  </div>
			</div>
		</div>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="js/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <script src="js/jtoggler.js"></script>
	  <script type="text/javascript" charset="utf8" src="js/toaster/jquery.toaster.js"></script>
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
      </script>
	  <script type="text/javascript">
		var bc_email_id = "<?= @$_REQUEST['bc_email_id'] ?>";
		var key = "<?= @$_REQUEST['key'] ?>";
		$(document).ready(function() {
			$('body').on('change','#actionChange',function(){
				var val = $(this).val();
				if(val == "0"){
					var url = 'enable.php?bc_email_id='+bc_email_id+'&key='+key;
					window.location.href = url;
				}else{
					$('body #exampleModalCenter').modal('show');
				}
			});
			$('body').on('click','#deleteConfirm',function(e){
				var url = 'disable.php?bc_email_id='+bc_email_id+'&key='+key;
				window.location.href = url;
			});
			$('body').on('click','#cancelConfirm,.close',function(e){
				$('body #exampleModalCenter').modal('hide');
				$('#actionChange').trigger('click');
			});
		});
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
		$(document).ready(function(){
			var enabled = getUrlParameter('enabled');
			if(enabled){
				$.toaster({ priority : "success", title : "Success", message : "OPENNODE Payments enabled for your Store" });
			}
			var disabled = getUrlParameter('disabled');
			if(disabled){
				$.toaster({ priority : "success", title : "Success", message : "OPENNODE Payments disabled for your Store" });
			}
			$('body').on('click','#refreshButton',function(){
				var url = 'dashboard.php?bc_email_id='+bc_email_id+'&key='+key;
				window.location.href = url;
			});
		});
      </script>
   </body>
</html>