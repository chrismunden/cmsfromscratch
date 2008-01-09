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
	require '../cmsfns.php' ;
	$messages = '' ;
	
	// Switch modes between list & thumbnails
	define ("CMSFILESDIR", 'cmsfiles/') ;
	define ("REALFILESDIR", realpath('cmsfiles/')) ;
	if (isSet($_POST['dir'])) {
		$dir = $_POST['dir'] ;
	}
	else if (isSet($_GET['dir'])) {
		$dir = $_GET['dir'] ;
	}
	else {
		$dir = CMSFILESDIR ;
	}
	
	if (isset($_GET['do'])) {
		if ($_GET['do'] == 'delete') {
			$ok = unlink(realpath($dir).'/'.$_GET['file']) ;
			$messages .= '<div class="message ok"><strong>' . $_GET['file'] . '</strong> deleted</div>' ;
			header("Location: files.php?message=$messages&dir=" . $dir) ;
		}
		else if ($_GET['do'] == 'newfolder') {
			$newDirPath = urldecode($_GET['dir']) . $_GET['newfoldername'] ;
			$created = @mkdir($newDirPath, 0755) ;
		}
		else if ($_GET['do'] == 'deletefolder') {
			if (wipeDir($_GET['killdir'])) {
				$messages .= '<div class="message ok">Folder <strong>' . $_GET['killdir'] . '</strong> deleted</div>' ;
			}
		}
	}
	
	// Upload new files ?
	if ($_FILES) {
		// Loop through all files, and attempt to upload to file directory
		$messages = '' ;
		foreach ($_FILES as $newfile) {
			if (strlen($newfile['name'])) {
				$uploadfile = realpath($dir) . '/' . basename($newfile['name']);
				if (move_uploaded_file($newfile['tmp_name'], $uploadfile)) {
				   $messages .= '<div class="message ok"><strong>' . $newfile['name'] . '</strong> uploaded OK</div>' ;
				}
				else {
				   $messages .= '<div class="message error"><strong>' . $newfile['name'] . '</strong> upload failed</div>' ;
				}
			}
		}
	}
	
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Downloadable Files</title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript">
	<!--
		function delete_file(dl_file) {
			if (confirm("Are you sure you want to delete " + dl_file + "?"))
				location = "?do=delete&file=" + dl_file ;
		}
		function delete_folder(dl_file) {
			if (confirm("Are you sure you want to delete " + dl_file + "?"))
				location = "?do=deletefolder&killdir=" + dl_file ;
		}
		function newFolder() {
			var newFolderName = prompt("New folder name") ;
			if (newFolderName.length) location = "?do=newfolder&dir=<? echo urlencode($dir) ; ?>&newfoldername=" + escape(newFolderName) ;
		}
	// -->
	</script>
	<style type="text/css">
		body {
			background:none;
			margin-left:2em;
		}
		h2 {
			margin:1.5em 0 0;
		}
	</style>
</head>

<body>

<?
	$folders = explode('/', $dir) ;
	$numLevels = sizeof($folders) ;
	$link = '' ;
	echo '<h1>' ;
	for ($fldr=0; $fldr<$numLevels-1; $fldr++) {
		$link .= $folders[$fldr] ;
		$link .= '/' ;
		
		if ($folders[$fldr] == CMSFILESDIR) $folders[$fldr] = "Files" ;
		if ($fldr < $numLevels-2) {
			echo '<a class="dir" title="Back to ' . $folders[$fldr] . '" href="?dir=' . $link . '">' ;
		}
		
		echo ' ' . $folders[$fldr] ;
		
		if ($fldr < $numLevels-2) {
			echo '</a>' ;
		}
		echo ' /' ;
	}
	echo '</h1>' ;


	if (isset($messages)) echo "<div class=\"messages\">$messages</div>" ;
?>

<form method="post" enctype="multipart/form-data" action="files.php" style="float:right;">
	<input type="hidden" name="dir" value="<? echo $dir ; ?>" />
	<fieldset>
		<legend><? echo translate('Upload new files') ; ?></legend>
		<div style="margin:.5em 0;"><input type="file" name="new_file1" /></div>
		<div style="margin:.5em 0;"><input type="file" name="new_file2" /></div>
		<div style="margin:.5em 0;"><input type="file" name="new_file3" />&nbsp;&nbsp;<input type="submit" value=" <? echo translate('Upload') ; ?> " /></div>
	</fieldset>
</form>


<?
	// Read all files in '/CMSFILES/'
	$handle = opendir($dir) ;
	
	$foldersHTML = '' ;
	$filesHTML = '' ;
	$numFolders = 0 ;
	
	$anyFiles = False ;
	while (false !== ($file = readdir($handle))) {
	
		if ($file != '.' && $file != '..') {
		
			if (is_dir($dir . '/' . $file))	{
				$numFolders ++ ;
				if (!strlen($foldersHTML)) $foldersHTML = '<ul class="bigLinks" style="margin-top:1em;">' ;
				$foldersHTML .= "\n<li class=\"dir\">" ;
				$foldersHTML .= '<a href="?dir=' . $dir .$file . '/" title="Browse ' . $file . '">' ;
				$foldersHTML .= $file ;
				$foldersHTML .= '</a>' ;
				if ($_SESSION['loginStatus'] > 1) {
					$foldersHTML .= '
						<ol class="tinyLinks">
							<li class="deleteLink"><a title="Delete" href="javascript:delete_folder(\'' . urlencode($dir . $file) . '\');" class="delete">x</a></li>
						</ol>
					' ;
				}
				$foldersHTML .= '</li>' ;
			}

			else if ($file != '.' && $file != '..') {
				if (!$anyFiles) {
					$filesHTML .= '
						<table border="0" cellpadding="0" cellspacing="0" class="bordered">
						<thead>
							<th>File</th>
							<th>Size</th>
							<th>Last edited</th>
							<th>Delete</th>
						</thead>
						<tbody>
					' ;
				}
				$anyFiles = True ;
				$filesHTML .= "\n<tr>" ;
				
					$filesHTML .= "\n\t".'<td class="type '.getFileExtension($file).'">' ;
						$filesHTML .= '<a href="' . $dir . $file . '" title="' . $file . '" target="_blank">' ;
						$filesHTML .= $file ;
						$filesHTML .= '</a>' ;
					$filesHTML .= '</td>' ;
					
					$filesHTML .= "\n\t".'<td style="text-align:right;">' ;
					$filesize = filesize($dir.$file) ;
					if ($filesize > 999)
						$filesHTML .= round($filesize/1000).' KB' ;
					else
						$filesHTML .= $filesize.' B' ;
					$filesHTML .= '</td>' ;
					
					$filesHTML .= "\n\t<td style=\"color:#444;\">" ;
						$filesHTML .= date("d M y, g:ia", filemtime($dir.$file)) ;
					$filesHTML .= '</td>' ;
					
					$filesHTML .= "\n\t<td>" ;
						$filesHTML .= '<a class="delete" href="javascript:delete_file(\'' . $file . '\');" class="delete_file">x</a>' ;
					$filesHTML .= '</td>' ;
					
				$filesHTML .= "\n</tr>\n" ;
			}
		}
	}
	closedir($handle) ;
	
	if ($numFolders > 0) {
		echo '<h2>' . $numFolders ;
		echo ($numFolders == 1) ? ' folder' : ' folders' ;
		echo '</h2>' ;
		echo '<ul class="browse mini">' . $foldersHTML . '</ul>' ;
	}
	
	if (!$anyFiles) {
		echo '<h2>No files found</h2>' ;
	}
	else {
		echo $filesHTML ;
	}
?>
</tbody>
</table>

<div class="clear-all">&nbsp;</div>
<br />
<button type="button" onclick="newFolder();">New folder</button>



</body>
</html>
