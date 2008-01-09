<?
$sesh = @session_start() ;
// If user not already logged in, bounce to login form
if (!isset($_SESSION['loginStatus'])) {
	header ("Location:login-form.php") ;
	exit ;
}
?>