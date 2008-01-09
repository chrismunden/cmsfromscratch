<?
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	$messages = '' ;
	
	// Switch modes between list & thumbnails
	define ("CMSIMGDIR", 'cmsimages/') ;
	define ("REALCMSIMGDIR", realpath('cmsimages/')) ;
	if (isSet($_POST['dir'])) {
		$dir = $_POST['dir'] ;
	}
	else if (isSet($_GET['dir'])) {
		$dir = $_GET['dir'] ;
	}
	else {
		$dir = CMSIMGDIR ;
	}
	
	if (isset($_GET['mode'])) {
		switch ($_GET['mode']) {
			case 'list':
				$_SESSION['imageDetailMode'] = True ;
			break ;
			default :
				unset($_SESSION['imageDetailMode']) ;
			break ;
		}
	}
	$detailmode = isset($_SESSION['imageDetailMode']) ? True : False ;
	
	// Delete image ?
	if (isset($_GET['do'])) {
		if ($_GET['do'] == 'delete') {
			$ok = unlink(realpath($dir).'/'.$_GET['file']) ;
			$messages .= '<div class="message ok"><strong>' . $_GET['file'] . '</strong> deleted</div>' ;
			header("Location: images.php?message=$messages&dir=" . $dir) ;
		}
		else if ($_GET['do'] == 'newfolder') {
			$created = @mkdir(urldecode($_GET['dir']) . $_GET['newfoldername'], 0755) ;
		}
		else if ($_GET['do'] == 'deletefolder') {
			if (wipeDir($_GET['killdir'])) {
				$messages .= '<div class="message ok">Folder <strong>' . $_GET['killdir'] . '</strong> deleted</div>' ;
			}
		}
	}
	
	// Upload new images ?
	if ($_FILES) {
		// Loop through all files, and attempt to upload to file directory
		$messages = '' ;
		foreach ($_FILES as $newfile) {
			if (strlen($newfile['name'])) {
				$uploadfile = realpath($dir) . '/' . basename($newfile['name']);
				if (move_uploaded_file($newfile['tmp_name'], $uploadfile)) {
					chmod($uploadfile, 0744) ;
					$messages .= '<div class="message ok"><strong>' . $newfile['name'] . '</strong> uploaded OK</div>' ;
				}
				else {
				   $messages .= '<div class="message error"><strong>' . $newfile['name'] . '</strong> upload failed</div>' ;
				}
			}
		}
		$messages = rawurlencode($messages) ;
		header("Location: images.php?message=$messages&dir=" . $dir) ;
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Images</title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript">
	<!--
		function delete_file(dl_file) {
			if (confirm("Are you sure you want to delete " + dl_file + "?"))
				location = "?dir=<? echo $dir ; ?>&do=delete&file=" + dl_file ;
		}
		function launchWin(url) {
			opening=window.open(url,"imgPreview","width=600,height=520,toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,directories=no,status=no") ;
		}
		function newFolder() {
			var newFolderName = prompt("New folder name") ;
			if (newFolderName.length) location = "?do=newfolder&dir=<? echo urlencode($dir) ; ?>&newfoldername=" + escape(newFolderName) ;
		}
		function delete_folder(dl_file) {
			if (confirm("Are you sure you want to delete " + dl_file + "?"))
				location = "?do=deletefolder&killdir=" + dl_file ;
		}
	// -->
	</script>
	<style type="text/css">
		.thumbnail {
			width:125px;
		}
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

<form method="post" enctype="multipart/form-data" action="images.php" style="float:right;">
	<input type="hidden" name="dir" value="<? echo $dir ; ?>" />
	<fieldset>
		<legend>Upload new images</legend>
		<div style="margin:.5em 0;"><input type="file" name="new_file1" /></div>
		<div style="margin:.5em 0;"><input type="file" name="new_file2" /></div>
		<div style="margin:.5em 0;"><input type="file" name="new_file3" />&nbsp;&nbsp;<input type="submit" value=" Upload " /></div>
	</fieldset>
</form>

<?
	
	$folders = explode('/', $dir) ;
	$numLevels = sizeof($folders) ;
	$link = '' ;
	
	echo '<h1>' ;
	for ($fldr=0; $fldr<$numLevels-1; $fldr++) {
		$link .= $folders[$fldr] ;
		$link .= '/' ;
		
		if ($folders[$fldr] == "cmsimages") $folders[$fldr] = "Images" ;
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
	
	echo '<div style="margin-top:1em;">' ;
	if ($detailmode == False) {
		echo '<a href="?dir=' . $dir . '&mode=list"> &laquo; Detailed list view</a>' ;
	}
	else {
		echo '<a href="?dir=' . $dir . '&mode=normal"> &laquo; Thumbnails view</a>' ;
	}
	echo '</div>' ;
	
	if (isset($_GET['message'])) {
		echo '<div class="messages">' ;
		echo stripslashes($_GET['message']) ;
		echo '</div><div class="clear-all">&nbsp;</div>' ;
	}
	
	
	if (!file_exists('../phpthumb.php')) {
		echo '<h2 class="errormsg">Error: Thumbnail files are not present</h2>' ;
	}
	$thumb_max_width = 120 ;
	$thumb_max_height = 120 ;
	$detail_max_width = 35 ;
	$detail_max_height = 25 ;
	
	$handle = opendir($dir) ;
	
	
	$foldersHTML = '' ;
	$filesHTML = '' ;
	$numFolders = 0 ;	
	
	if ($handle !== False) {
		$imgcount = 0 ;
		while (False !== ($file = readdir($handle))) {
			if ($file == '..' || $file == '.') continue ;
			
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
			
			else if ($file != '.' && $file != '..' && stristr('gif,jpg,jpeg,png', getFileExtension($file))) {
				$imgcount++ ;
				if ($detailmode && $imgcount == 1) {
					// Start of detail table
					$filesHTML .= '<table border="0" cellpadding="0" cellspacing="0" class="imagedetails">
								<thead>
									<tr>
										<th>Thumb</th>
										<th>Filename</th>
										<th>Size</th>
										<th>Delete</th>
									</tr>
								</thead>
								<tbody>
					' ;
				}
				if ($detailmode) {
					// Detail mode
					$filesHTML .= "
						<tr>
							<td>
								<a href=\"javascript:void('');\" title=\"" . $file . " - Click to view\" onClick=\"launchWin('" . $dir . $file . "');\">
									<img src=\"../phpthumb.php?w=$detail_max_width&h=$detail_max_height&src=" . realpath($dir) . "/$file\" align=\"absmiddle\" />
								</a>
							</td>
							<td>
								<a href=\"javascript:void('');\" title=\"" . $file . " - Click to view\" onClick=\"launchWin('" . $dir . $file . "');\">
								$file
								</a>
							</td>
							<td class=\"numeric\">" . filesizeFormat(filesize($dir.$file)) . "</td>
							<td><a href=\"javascript:delete_file('$file');\" class=\"\">Delete</a></div></td>
						</tr>
					" ;
				}
				else {
					// Thumbnail mode
					$filesHTML .= '<div class="thumbnail">' ;
					$filesHTML .= '<div class="thumbnailInner" style="height:' . $thumb_max_height . 'px;">' ;
					$filesHTML .= "<a href=\"javascript:void('');\" title=\"" . $file . " - Click to view\" onClick=\"launchWin('" . $dir . $file . "');\">" ;
					$filesHTML .= '<img src="../phpthumb.php?w='.$thumb_max_width.'&h='.$thumb_max_height.'&src=' . realpath($dir) . '/'.$file.'" />' ;
					$filesHTML .= '</a>' ;
					$filesHTML .= '</div>' ;
					$filesHTML .= '<div class="delete"><a title="Delete ' . $file . '" href="javascript:delete_file(\'' . $file . '\');">Delete</a></div>' ;
					$filesHTML .= '</div>' ;
				}
			}
		}
		closedir($handle) ;
	}
	
	
	if ($numFolders > 0) {
		echo '<h2>' . $numFolders ;
		echo ($numFolders == 1) ? ' folder' : ' folders' ;
		echo '</h2>' ;
	}
	echo '<ul class="browse mini">' . $foldersHTML . '</ul>' ;
	
	if ($imgcount == 0) {
		echo '<h2>No images found</h2>' ;
	}
	else {
		echo '<h2 style="clear:right;">' . $imgcount ;
		echo ($imgcount == 1) ? ' image' : ' images' ;
		echo '</h2>' ;
	}
	
	echo $filesHTML ;
	if ($detailmode) {
		// End of detail table
		echo '</tbody></table>' ;
	}
?>

<div class="clear-all">&nbsp;</div>
<br />
<button type="button" onclick="newFolder();">New folder</button>

</body>
</html>
