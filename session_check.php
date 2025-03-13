<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//$user='';
//$_SESSION['success_emailid']='sathyampt11@gmail.com';
if(isset($_SESSION['success_emailid']))
{
	$user=$_SESSION['success_emailid'];	
}
else
{
session_destroy();
	header('Location: index.php');
}
?>
