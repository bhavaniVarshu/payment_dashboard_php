<?php
require 'authentication.php'; // admin authentication check 

// auth check
if(isset($_SESSION['admin_id'])){
  $user_id = $_SESSION['admin_id'];
  $user_name = $_SESSION['admin_name'];
  $security_key = $_SESSION['security_key'];
  if ($user_id != NULL && $security_key != NULL) {
    header('Location: attendance-info.php');
  }
}

if(isset($_POST['login_btn'])){
 $info = $obj_admin->admin_login_check($_POST);
}

$page_name="Login";
include("include/login_header.php");

?>
<style>
	html, body{
		height:100%;
		width:100%;
		margin:unset !important;
		overflow: hidden;
	}
	.header {
		width:100%;
		background-color: #012b39;
		position: fixed; 
    top: 0;
    left: 0;
    z-index: 1000; 
    box-shadow: 0 2px 4px #012b39;
	padding: 2px 0; 
	}
	.main{
		display:flex;
		align-items:center;
		justify-content:center;
		height:100%;
		width:100%;
		height: calc(100% - 60px);
		margin:unset !important;
		flex-direction: column;
	}
	.header h3 {
    margin: 0;
    padding: 10px; 
    text-align: center;
    font-size: 24px;
    color: #fff;
  }
  .header img {
  margin-right: 10px;
  vertical-align: middle;
  width: 80px;
}
</style>
<body>
	<div class="header">
	<center><h3><img src="./assets/img/codelogo.png" width="50">
		CODE Timesheet Task Management</h3></center>
	</div>
<div class="main">

<div class="col-lg-4 col-md-6 col-sm-12">

	<div class="well rounded-0">
		<form class="form-horizontal form-custom-login" action="" method="POST">
			<div class="form-heading">
			<h2 class="text-center">Login Panel</h2>
			</div>
			
			<!-- <div class="login-gap"></div> -->
			<?php if(isset($info)){ ?>
			<h5 class="alert alert-danger"><?php echo $info; ?></h5>
			<?php } ?>
			<div class="form-group">
			<input type="text" class="form-control rounded-0" placeholder="Username" name="username" required/>
			</div>
			<div class="form-group" ng-class="{'has-error': loginForm.password.$invalid && loginForm.password.$dirty, 'has-success': loginForm.password.$valid}">
			<input type="password" class="form-control rounded-0" placeholder="Password" name="admin_password" required/>
			</div>
			<button type="submit" name="login_btn" class="btn btn-info pull-right">Login</button>
		</form>
	</div>
</div>
</div>
</body>
<?php

include("include/footer.php");

?>
