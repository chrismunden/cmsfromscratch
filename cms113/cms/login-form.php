<? 
/*
 * ***********************************************************************
 * Copyright © Ben Hunt 2007, 2008
 * 
 * This file is part of cmsfromscratch.

    Cmsfromscratch is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Cmsfromscratch is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Cmsfromscratch.  If not, see <http://www.gnu.org/licenses/>.
    ***********************************************************************
 */
	header('Content-type: text/html; charset=UTF-8') ;
	$sesh = @session_start() ;
	require 'pre-login.php' ;
	require '../cmsfns.php' ;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" value="text/html; charset=UTF-8" />
	<title>
		<? 
			echo translate('Please log in') . ' - ' ;
			if (isset($_SESSION['CMS_Title'])) echo stripslashes($_SESSION['CMS_Title']) ;
		?>
	</title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
</head>

<body>

<div id="topbar">
	<div id="titles">
		<div id="cmsTitle"><? echo stripslashes($_SESSION['CMS_Title']) ; ?></div>
		<div id="siteTitle"><img src="images/cms-logo.gif" alt="CMS" width="90" height="36" border="0" /></div>
	</div>
</div>
	
	<div class="login_box">
		<?
			if (isset($_GET['message'])) {
				switch ($_GET['message']) {
					case "loginwrong":
					   echo '<h1 class="errormsg">Login failed</h1>' ;
					   break;
					default:
						echo '<h1>' . translate('Please log in') . '</h1>' ;
						break ;
				}
			}
			else {
				echo '<h1>' . translate('Please log in') . '</h1>' ;
			}
		?>
		<div class="login_inner">
			<div class="login_textarea">
				<form action="login.php" method="post">
					Log in&nbsp; 
					<input type="password" name="user" id="login-field" style="width:8em;" />
					&nbsp;
					<input type="submit" value="Submit" />
				</form>
			</div>
		</div>
	</div>

</html>