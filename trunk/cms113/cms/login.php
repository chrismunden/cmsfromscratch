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
 
 
/* 
	>> form(user)
	- Checks credentials against credentials stored in file
	- If correct, sets session variables accordingly
	- If not, bounce back to login form with appropriate message
*/

include ('pre-login.php') ;

// Read Designer and Editor logins from Settings
if ($settings == False) {
	$login = 3 ;
	$settingsArray = Array() ;
}
else {
	$settingsArray = unserialize($settings) ;
	if ($settingsArray['design-mode-login']=='') {
		$login = 3 ;
	}
	else if (
		array_key_exists('design-mode-login', $settingsArray)
		&&
		(md5($_POST['user']) == $settingsArray['design-mode-login'])
	)
	{
		$login = 2 ;
	}
	else if (
		array_key_exists('editor-mode-login', $settingsArray)
		&&
		(md5($_POST['user']) == $settingsArray['editor-mode-login'])
	)
	{
		$login = 1 ;
	}
/* 	else if (
		array_key_exists('simple-mode-login', $settingsArray)
		&&
		(md5($_POST['user']) == $settingsArray['simple-mode-login'])
	)
	{
		$login = 0 ;
	}*/
	else {
		$login = -1 ;
	}
}

// echo "Login = " . $login ; exit ;

	/* 
		$login key
			3 = No settings, assume designer
			2 = Known designer
			1 = General editor login
			0 = Simple edit login (FUTURE??)
		   -1 = Invalid login
	*/

if (isSet($settingsArray['show-non-cms-files']) && $settingsArray['show-non-cms-files'] == 'yes') {
	$_SESSION['showNonCMSFiles'] = 'yes' ;
}
else $_SESSION['showNonCMSFiles'] = 'no' ;

if (isSet($settingsArray['editors-can-create-pages']) && $settingsArray['editors-can-create-pages'] == 'yes') {
	$_SESSION['editorsCanCreatePages'] = 'yes' ;
}
else $_SESSION['editorsCanCreatePages'] = 'no' ;

if (isSet($settingsArray['editors-can-delete-pages']) && $settingsArray['editors-can-delete-pages'] == 'yes') {
	$_SESSION['editorsCanDeletePages'] = 'yes' ;
}
else $_SESSION['editorsCanDeletePages'] = 'no' ;

$_SESSION['lineColour'] = ( isSet($settingsArray['top-bar-line-colour']) && strlen($settingsArray['top-bar-line-colour']) ) ? $settingsArray['top-bar-line-colour'] : '#fc6;' ;


if ($login == 2) {
	$_SESSION['loginStatus'] = 2 ;
	header("Location: index.php");
	exit ;
}
else if ($login == 3) {
	$_SESSION['loginStatus'] = 2 ;
	header("Location: index.php?message=dosettingsnow");
	exit ;
}
else if ($login == 0) {
	$_SESSION['loginStatus'] = 0 ;
	header("Location: ../index.php?mode=browse");
	exit ;
}
else if ($login == 1) {
	$_SESSION['loginStatus'] = -1 ;
	if (isSet($settingsArray['all-users-can-add-pages']) && $settingsArray['all-users-can-add-pages'] == 'yes') {
		$_SESSION['canAddPages'] = 'yes' ;
	}
	header("Location: index.php");
	exit ;
}
else {
	header("Location: login-form.php?message=loginwrong");
	exit ;
}
 ?>