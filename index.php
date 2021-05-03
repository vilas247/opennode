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
	$stmt = $conn->prepare("select * from opennode_token_validation where email_id=? and validation_id=?");
	$stmt->execute([$email_id,$validation_id]);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetchAll();
	//print_r($result[0]);exit;
	if (isset($result[0])) {
		$result = $result[0];
		if(!empty($result['api_auth_token'])){
			header("Location:dashboard.php?bc_email_id=".@$_REQUEST['bc_email_id']."&key=".@$_REQUEST['key']);
		}
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
      <link href="css/media.css" rel="stylesheet">
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
   <body style="background:#404859;">
      <section class="login">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="text-center logo"><img src="images/logo.jpg"></div>
                  <div class="login_box">
                     <div class="login-top text-center">
                        <h1>Getting Started</h1>
                        <h6>Login to the dashboard</h6>
                     </div>
                     <form class="pt35" action="validateToken.php" method="POST" >
						<input type="hidden" name="bc_email_id" value="<?= @$_REQUEST['bc_email_id'] ?>" />
						<input type="hidden" name="key" value="<?= @$_REQUEST['key'] ?>" />
                        <div class="input-group">
                           <input type="password" name="api_auth_token" class="form-control1 pwd" placeholder="API Key" >
                           <span class="input-group-btn">
                           <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
                           </span>          
                        </div>
                        <div class="form-group mt20">
                           <button type="submit" class="btn btn-submit btn-lg btn-block">Submit</button>
                        </div>
						<div style="margin-left:50%">OR</div>
						<div class="form-group">
                           <a href="https://help.opennode.com/en/articles/2564092-how-to-set-up-opennode-on-woocommerce" target="_blank"><img src="images/button.png" /></a>
                        </div>
						<div class="form-group" style="text-align: center;">
                           <p>How can I get my <a target="_blank" href="https://help.opennode.com/en/articles/XXXXXXX-how-to-set-up-opennode-on-bigcommerce">OpenNode API Key?</a></p>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
      <script src="js/jquery.min.js"></script>
      <!-- Include all compiled plugins (below), or include individual files as needed -->
      <script src="js/bootstrap.min.js"></script>
      <script>
         $(".reveal").mousedown(function() {
            $(".pwd").replaceWith($('.pwd').clone().attr('type', 'text'));
         })
         .mouseup(function() {
         $(".pwd").replaceWith($('.pwd').clone().attr('type', 'password'));
         })
         .mouseout(function() {
         $(".pwd").replaceWith($('.pwd').clone().attr('type', 'password'));
         });
      </script>
   </body>
</html>