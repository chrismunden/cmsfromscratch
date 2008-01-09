<?
	$sesh = @session_start();
	unset($_SESSION['loginStatus']);
	unset($_SESSION['settingsInitialized']) ;
	unset($_SESSION['lang']) ;
	header("Location: index.php");
?>