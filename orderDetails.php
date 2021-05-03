<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<?php
/**
	* Feed List Page
	* Author 247Commerce
	* Date 30 MAR 2021
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
      <!-- font-awesome css-->
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
      <link rel="preconnect" href="https://fonts.gstatic.com">
      <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	  <link rel="stylesheet" type="text/css" href="css/datatable/jquery.dataTables.min.css">
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
               <div class="col-md-12">
                  <div class="row order-details">
                     <div class="col-md-6">
                        <h3>Order Details</h3>
                     </div>
                     <div class="col-md-6 col-6 text-right">
						<a href="dashboard.php?bc_email_id=<?= $_REQUEST['bc_email_id']."&key=".@$_REQUEST['key'] ?>">
							<h5><i class="fas fa-arrow-left"></i> Back To Dashboard</h5>
						</a>
					 </div>
                  </div>
               </div>
            </div>
			<div class="row">
               <div class="col-md-12">
                  <div id="no-more-tables" class="table-responsive">
					<div class="col-md-12">
						<div class="row ">
							<div class="col-xl-11 col-12">
								<input type="email" class="form-control1" id="exampleInputEmail1" placeholder="Search Order Id">
							</div>
						</div>
					</div>
                     <table class="table" id="orderdetails_dashboard" >
                        <thead class="cf">
                           <tr id="table_columns" >
                              <th><input type="checkbox" class="form-check-input" id="exampleCheck1"></th>
                              <th>Order ID</th>
                              <th class="numeric">Order Status</th>
                              <th class="numeric">Source Currency</th>
                              <th class="numeric">total</th>
                              <th class="numeric">amount paid</th>
                              <th class="numeric">Order Created Date</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr id="table_data_rows">
                             
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
				  <h5 class="modal-title" id="exampleModalLongTitle"><span><img src="images/trash-purple.svg" style="margin-top: -5px;"></span> <span class="purple">Cancel Order</span>  </h5>
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				  </button>
				</div>
				<div class="modal-body" id="modalContent">
				  Are you sure you want to cancel Order <strong id="cancelOrderId"></strong>?.
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-order" id="cancelConfirm" data-dismiss="modal">No</button>
				  <button type="button" class="btn btn-order" id="deleteConfirm">Yes</button>
				</div>
			  </div>
			</div>
		</div>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="js/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
	  <script type="text/javascript" charset="utf8" src="js/datatable/jquery.dataTables.min.js"></script>
	  <script type="text/javascript" charset="utf8" src="js/datatable/datatable-responsive.js"></script>
      <script src="js/order-details.js"></script>
		<style>
			.paging_simple_numbers{
				display: flex;
			}
			#orderdetails_dashboard_wrapper .top{
				margin-left: 10px;
			}
			.paginate_button.current {
				color: #f4ca1a !important;
				border: none;
				background: #1c1835 !important;
			}
			.form-control1{
				border: 1px solid #555;
			}
		</style>
		<script>
			var app_base_url = "<?= BASE_URL ?>";
			var email_id = "<?= $_REQUEST['bc_email_id'] ?>";
			var key = "<?= $_REQUEST['key'] ?>";
			$(document).ready(function(){
				X247OrderDetails.main_data('scripts/orderdetails_processing.php?email_id='+email_id+'&key='+key,'orderdetails_dashboard');
			});
			$('body').on('click','.cancelOrder',function(e){
				e.preventDefault();
				var orderId = $(this).attr('data-order-id');
				$('body #cancelOrderId').text(orderId);
				$('#cancelOrderId').attr('data-order-id',orderId);
				$('#exampleModalCenter').modal('show');
			});
			$('body').on('click','#deleteConfirm',function(e){
				var orderId = $('#cancelOrderId').attr('data-order-id');
				$('#cancelOrderId').attr('data-order-id','');
				$('#exampleModalCenter').modal('hide');
				if(parseInt(orderId) > 0){
					$.ajax({
						type: 'POST',
						url: app_base_url + "cancelBigiOrder.php?bc_email_id="+email_id,
						async: true,
						cache: true,
						data: {'orderId':orderId},
						dataType: 'json',
						success: function (res) {
							if(res.status){
								var table = $('#orderdetails_dashboard').DataTable();
								table.draw(false);
								alert('Order cancelled successfully in BigCommerce');
							}else{
								alert('Error cancelling the order in BigCommerce');
							}
						}
					});
				}else{
					alert("Invalid OrderID");
				}
			});
         
		</script>
   </body>
</html>