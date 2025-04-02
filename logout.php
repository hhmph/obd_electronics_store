<?php 
	session_start();
	unset($_SESSION['session_username']);
	unset($_SESSION['is_user']);
	session_destroy();
	header("location:login.php");
?>