<?
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	define ("REALCMSIMGDIR", realpath(CMSIMAGESFOLDER)) ;
	
	if (isSet($_GET['dir'])) {
		$dir = $_GET['dir'] ;
	}
	else {
		$dir = CMSIMAGESFOLDER ;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Select Image - <? echo stripslashes($_SESSION['CMS_Title']) ; ?></title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript" language="javascript" src="select-image.js"></script>
	<script type="text/javascript">
	<!--
		function launchWin(url) {
			opening=window.open(url,"imgPreview","width=600,height=520,toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,directories=no,status=no") ;
		}
	<?
		if (isSet($_GET['field_id'])) {
			echo 'var field_id = "' . $_GET['field_id'] . '" ;' ;
		}
		else {
			echo 'var field_id = "" ; ' . "\n" ;
		}
		echo 'var dir = "' . $dir . '";' ;
	?>
	// -->
	</script>
	<style type="text/css">
		h1 {
			font-size:1.5em;
		}
		body {
			padding:1em;
		}
	</style>
</head>

<body>


<h1>Select Image</h1>

<?
	/* 
		Should pass in dir, if blank corresponds to "cmsimages/"
		If present, should be "cmsimages/foldername/"
	*/
	
	$folders = explode('/', $dir) ;
	$numLevels = sizeof($folders) ;
	
	$link = '' ;
	
	echo '<div class="crumbNav">' ;
	for ($fldr=0; $fldr<$numLevels-1; $fldr++) {
		$link .= $folders[$fldr] ;
		$link .= '/' ;
		
		if ($folders[$fldr] == "cmsimages") $folders[$fldr] = "Home" ;
		if ($fldr < $numLevels-2) {
			echo '<a class="dir" title="Back to ' . $folders[$fldr] . '" href="?dir=' . $link . '">' ;
		}
		
		echo ' ' . $folders[$fldr] ;
		
		if ($fldr < $numLevels-2) {
			echo '</a>' ;
		}
		echo ' /' ;
	}
	echo '</div>' ;
	
	
	function tidyUp($fileName) {
		return str_replace(
			array('.','-','_'),
			array('dot','hyphen','underscore'),
			$fileName) ;
	}
	
	$handle = opendir($dir) ;
	$foldersHTML = '' ;
	while (false !== ($file = readdir($handle))) {
		if ($file == '..' || $file == '.') continue ;
		if (strlen($foldersHTML) == 0) $foldersHTML = '<ul style="margin: 1em 0 .5em 3em;">' ;
		$fileID = tidyUp($file) ;
		if (is_dir($dir . '/' . $file))	{
			$foldersHTML .= "\n<li style=\"list-style:disc;\">" ;
			$foldersHTML .= '<a href="?dir=' . $dir .$file . '/" title="Browse ' . $file . '">' ;
			$foldersHTML .= $file ;
			$foldersHTML .= '</a></li>' ;
		}
	}
	if (strlen($foldersHTML)) $foldersHTML .= '</ul>' ;
	echo '<ul class="bigLink">' . $foldersHTML . '</ul>' ;
	
	if (!file_exists('../phpthumb.php')) {
		echo '<h2 class="errormsg">Error: Thumbnail files are not present</h2>' ;
	}
	
	$thumb_max_width = 80 ;
	$thumb_max_height = 80 ;
	
	$handle = opendir($dir) ;
	if ($handle !== False) {
		$imgcount = 0 ;
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && !is_dir("CMSIMGDIR/$file") && stristr('gif,jpg,jpeg,png', getFileExtension($file))) {
				$imgcount++ ;
				echo '<div class="thumbnail">' ;
				echo '<div class="thumbnailInner" style="height:' . $thumb_max_height . 'px;" id="' . $file . '">' ;
				echo '<a href="javascript:void(\'\');" onClick="selectImage(\'' . $file . '\');" title="Select ' . $file . ' - Click to insert">' ;
				/* 
					Weird hack below. (../../)
				*/
				// BROKEN: http://cmsfromscratch.com/phpthumb.php?w=80&h=80&src=../../cmsimages/call_include.gif
				// WORKS:: http://cmsfromscratch.com/phpthumb.php?w=120&h=120&src=/home/cmsfroms/public_html/cms/cmsimages/call_include.gif
				echo "<img src=\"../phpthumb.php?w=$thumb_max_width&h=$thumb_max_height&src=" . realpath($dir) . "/$file\" />" ;
				// echo '<img src="../phpthumb.php?w='.$thumb_max_width.'&h='.$thumb_max_height.'&src=../../'. $dir . $file.'" />' ;
				echo '</a>' ;
				echo '<button type="button" class="selectButton" title="Click to view ' . $file . '" onClick="launchWin(\'cmsimages/' . $file . '\');"><img src="images/mag-glass.gif" alt="" width="13" height="12" border="0" style="border:0 none;"></button>' ;
				echo '</div></div>' ;
			}
		}
		closedir($handle) ;
	}
	else {
		echo '<h2 class="error">Could not open images folder.</h2>' ;
	}
	if ($imgcount == 0) {
		echo '<h2>No images found</h2>' ;
	}
?>

</body>
</html>
