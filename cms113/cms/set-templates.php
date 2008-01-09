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
	* List any set templates in the /cms/set-templates/ directory.
	* Admins can create STs
	* Admins can delete STs
	
	Set templates will be in format:
	
		<<start>> // Only shown if there's *anything* else to show
		
		<<repeat 1>><<else>><<else>> // You can add as many else-options as you want, or none
		<<repeat 2>><<else>
		<<repeat 3>>
		
		<<end>
		
		<<else>> // Shown if there's nothing to show in the main bit
		
	// Else options are attempted if any of the includes in the previous block (e.g. <1>) aren't found, or are empty.
*/

	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	if ($_SESSION['loginStatus'] < 2) header('Location:index.php?message=norights') ;
	
	// Make sure STDIR exists
	if (!file_exists(STDIR)) {
		mkdir(STDIR, 0755) ;
	}
	
	// Delete file ?
	if (isset($_GET['do']) && $_GET['do'] == 'delete') {
		$ok = @unlink(realpath('settemplates/'.$_GET['file'])) ;
		if ($ok === False) {
			$message = '<p style="color:red;">Failed to delete Set Template.</p>' ;
		}
		else {
			$message = '<p>Deleted Set Template OK.</p>' ;
		}
	}
	
	$handle = opendir(STDIR) ;
	
	$itemsFound = 0 ;
	$listHTML = '' ;
	$newSetHTML = '' ;
	$jsSTList = '' ;
	while (false !== ($file = readdir($handle))) {
		if ($file != '.' && $file != '..') {
			$itemsFound++ ;
			if (strlen($jsSTList)>0) $jsSTList .= ',' ;
			$jsSTList .= '"' . stripFileExtension($file) . '"' ;
			// Build HTML for this page
			if ($itemsFound == 1) $listHTML .= '<ul class="morespace">' ;
			$listHTML .= '<li><a title="Click to edit ' . stripFileExtension($file) . '" href="edit-set-template.php?file=' . $file . '">' . stripFileExtension($file) . '</a>' ;
			// $listHTML .= '<a title="Use this option to paste in code supplied from elsewhere" class="advanced-edit" href="edit-set-template-advanced.php?file=' . $file . '">paste-in</a>' ;
			$listHTML.= '<a href="javascript:deleteST(\'' . $file .'\');" class="delete" title="Delete ' . stripFileExtension($file) . '">delete</a></li>' ;
			
			// Also build new HTML for "New Set" drop-down
			if ($file != '.' && $file != '..') {
				if (strlen($newSetHTML)) $newSetHTML .= ',' ;
				$newSetHTML .= $file ;
			}
		}
	}
	$listHTML .= '</ul>' ;
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript">
	<!--
		function deleteST(delSt) {
			if (confirm("Are you sure you want to delete " + delSt + "?"))
				location = "?do=delete&file=" + delSt ;
		}
		function addST() {
			var newSTName = prompt("Enter new Set Template name") ;
			if (newSTName) {
				for (var i=0; i<stsList.length; i++) {
					if (stsList[i] == newSTName) {
						if (!confirm("A set template already exists with that name.\nOverwrite?")) return ;
					}
				}
				location = "new-set-template.php?newstname=" + escape(newSTName) ;
			}
		}
		function updateNewSetSTDialog(numSTs) {
			top.updateNewSetSTDialog('<? echo $newSetHTML ; ?>') ;
		}
		<? echo 'var stsList=Array(' . $jsSTList . ');' ; ?>
		addEvent(window, "load", updateNewSetSTDialog, false) ;
	// -->
	</script>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<style type="text/css">
		body {
			background:none;
			margin-left:2em;
		}
		.morespace li {
			margin-bottom:.5em;
			font-size:1.1em;
		}
		.delete {
			font-size:.7em;
		}
	</style>
</head>

<body>

<?
	if (isSet($message)) echo $message ;
	echo '<h1>' . $itemsFound . ' set template' ;
	if ($itemsFound <> 1) echo 's' ;
	echo '</h1>' ;
	echo $listHTML ;
?>


<div style="margin-top:2em;">
<button type="button" onClick="addST();">New Set Template</button>
</div>

<?
	if ($itemsFound == 0) {
		echo '<br />
			<div class="inlineHelp">
			<p>
				Set templates define &quot;content sets&quot; that store complex data in a regular pattern that users can edit.
			</p>
			<p>
				For example, you may use a set to store a navigation list, consisting of some text and a link for each element.
				Or you may have news items that feature a title, an optional image, and some body text.
			</p>
			<p>
				You cannot create any actual sets until you have created at least one set template, which will dictate how to edit the set, and also how it should be displayed on your site.
			</p>
			<p><a href="http://cmsfromscratch.com/user-guide/sets/">More help on page sets &raquo;</a></p>
			</div>
		' ;
	}
?>

</body>
</html>
