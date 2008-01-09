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
 
 
	require 'check-login.php' ;
	if ($_SESSION['loginStatus'] < 2) header('Location:index.php?message=norights') ;
	
	if (isset($_POST['settingsform'])) {
	
/* 		if (dirname($_SERVER['HTTP_HOST']) !== dirname($_SERVER['SCRIPT_NAME'])) {
			header("Location:settings.php") ;
		}*/
		
		$sesh = @session_start() ;
		
		// $_POST should actually contain all our fields & values
		if (file_exists('../../settings.text')) {
			$fp = fopen('../../settings.text', 'w+') ;
		}
		else {
			$fp = fopen('settings.text', 'w+') ;
		}
		
		if (strlen($_POST['new-editor-mode-login']) > 0) {
			$_POST['editor-mode-login'] = md5($_POST['new-editor-mode-login']) ;
		}
		if (strlen($_POST['new-design-mode-login']) > 0) {
			$_POST['design-mode-login'] = md5($_POST['new-design-mode-login']) ;
		}
		$_POST['new-design-mode-login'] = null ;
		$_POST['new-editor-mode-login'] = null ;
		
		fwrite($fp, serialize($_POST)) ;
		fclose($fp); 
		unset($_SESSION['settingsInitialized']) ;
		$message = 'settingssaved' ;
	}
	
	$clientLanguages = Array("English","German","Slovak","Spanish") ;
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Edit settings</title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<style type="text/css">
		.faint {
			font-size:.85em;
			color:#666;
			font-style:italic;
			padding-left:.9em;
		}
		body {
			background:none;
		}
	</style>
</head>

<body>
<h1>CMS Settings</h1>

<?

	if (isSet($message)) {
		 if ($message == 'settingssaved') {
			echo '<p style="text-align:center;font-weight:bold;">Settings saved OK<br /><a href="logout.php" target="_top">Log out</a> and back in to apply</p>' ;
		}
	}
	include ('pre-login.php') ;
?>

<form action="settings.php" method="post">
	<input type="hidden" name="settingsform" />
	
	
<table class="invisible">
	<tbody>
		<tr>
			<td>Header title</td>
			<td>
				<input type="text" name="cms-description" value="<?
					if (isSet($settingsArray) && array_key_exists('cms-description', $settingsArray)) {
						echo stripslashes($settingsArray['cms-description']) ;
					}
				?>" />
			</td>
		</tr>
		<tr>
			<td>Show files not created in CMS</td>
			<td>
				<?
					if (isSet($settingsArray) && array_key_exists('show-non-cms-files', $settingsArray)) {
						$showNonCMSFiles = $settingsArray['show-non-cms-files'] ;
					}
					else {
						$showNonCMSFiles = 'no' ;
					}
				?>
				<input type="radio" name="show-non-cms-files" id="show-non-cms-files-yes" value="yes" <? if ($showNonCMSFiles == 'yes') echo 'checked="checked"' ; ?> /> <label for="show-non-cms-files-yes">Yes</label>&nbsp; 
				<input type="radio" name="show-non-cms-files" id="show-non-cms-files-no" value="no" <? if ($showNonCMSFiles != 'yes') echo 'checked="checked"' ; ?> /> <label for="show-non-cms-files-no">No</label>
			</td>
		</tr>
		<tr>
			<td>New <strong>Designer</strong> mode login</td>
			<td><input type="text" name="new-design-mode-login" value="" /></td>
		</tr>
		<tr>
			<td>New <strong>Client</strong> mode login</td>
			<td><input type="text" name="new-editor-mode-login" value="" /></td>
		</tr>
		<tr>
			<td>Client language</td>
			<td>
				<select name="client-language">
					<?
						for ($i=0; $i<sizeOf($clientLanguages); $i++) {
							echo '<option value="' . $clientLanguages[$i] . '"';
							if (isset($settingsArray)) {
								if (array_key_exists('client-language', $settingsArray) && $settingsArray['client-language'] == $clientLanguages[$i]) {
									echo ' selected="selected"' ;
								}
							}
							echo '>' . $clientLanguages[$i] . '</option>' ;
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Editors can create pages</td>
			<td>
				<?
					if (isSet($settingsArray) && array_key_exists('editors-can-create-pages', $settingsArray)) {
						$editorsCanCreatePages = $settingsArray['editors-can-create-pages'] ;
					}
					else {
						$editorsCanCreatePages = 'no' ;
					}
				?>
				<input type="radio" name="editors-can-create-pages" id="editors-can-create-pages-yes" value="yes" <? if ($editorsCanCreatePages == 'yes') echo 'checked="checked"' ; ?> /> <label for="editors-can-create-pages-yes">Yes</label>&nbsp; 
				<input type="radio" name="editors-can-create-pages" id="editors-can-create-pages-no" value="no" <? if ($editorsCanCreatePages != 'yes') echo 'checked="checked"' ; ?> /> <label for="editors-can-create-pages-no">No</label>
			</td>
		</tr>
		<tr>
			<td>Editors can delete pages</td>
			<td>
				<?
					if (isSet($settingsArray) && array_key_exists('editors-can-delete-pages', $settingsArray)) {
						$editorsCanDeletePages = $settingsArray['editors-can-delete-pages'] ;
					}
					else {
						$editorsCanDeletePages = 'no' ;
					}
				?>
				<input type="radio" name="editors-can-delete-pages" id="editors-can-delete-pages-yes" value="yes" <? if ($editorsCanDeletePages == 'yes') echo 'checked="checked"' ; ?> /> <label for="editors-can-delete-pages-yes">Yes</label>&nbsp; 
				<input type="radio" name="editors-can-delete-pages" id="editors-can-delete-pages-no" value="no" <? if ($editorsCanDeletePages != 'yes') echo 'checked="checked"' ; ?> /> <label for="editors-can-delete-pages-no">No</label>
			</td>
		</tr>
	</tbody>
</table>
			
<input type="submit" value="Save changes" style="margin-top:1em;" />
			
		<input type="hidden" name="design-mode-login" value="<?
				if (isSet($settingsArray) && array_key_exists('design-mode-login', $settingsArray)) {
					echo stripslashes($settingsArray['design-mode-login']) ;
				}
			?>" />
		<input type="hidden" name="editor-mode-login" value="<?
				if (isSet($settingsArray) && array_key_exists('editor-mode-login', $settingsArray)) {
					echo stripslashes($settingsArray['editor-mode-login']) ;
				}
			?>" />

</form>