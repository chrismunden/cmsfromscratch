<?php
/*
 * ***********************************************************************
 * Copyright � Ben Hunt 2007, 2008
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
?>
 
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CMS: Enter licence key</title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
</head>
<body>
	<div id="topbar">
		<div id="titles">
	<div id="titles">
		<div id="cmsTitle">Enter licence key</div>
		<div id="siteTitle"><img src="images/cms-logo.gif" alt="CMS" width="90" height="36" border="0" /></div>
	</div>
		</div>
	</div>
	<div style="text-align:center;">
		<?
			if (isset($_GET['message'])) {
				switch ($_GET['message']) {
					case "loginwrong":
					   echo '<h1 class="errormsg">Login failed</h1>' ;
					   break;
					default:
						echo '<h1>Please enter your licence key</h1>' ;
						break ;
				}
			}
			else {
				echo '<h1>Please enter your licence key</h1>' ;
			}
		?>
		<p>
			You should have received your licence key when you registered for a free trial,<br />or purchased a full licence, from <a href="http://www.softwarefromscratch.com/orders/">www.softwarefromscratch.com/orders</a>.
		</p>
		<div style="position:relative;">
			<div style="position:absolute; top:10px; left:50%; margin-left:-15em;">
				<div style="width:30em; background:#ddd; border:1px solid; border-color:#ddd #bbb #aaa #ccc;">
				<form action="login-form.php" method="post" style="margin:1em;">
				licence key &nbsp; 
				<input type="text" name="licensekey" id="licensekey" style="width:16em;" />
				&nbsp;
				<input type="submit" value="Submit" />
				</form>
				</div>
			</div>
		</div>
	</div>
</html>