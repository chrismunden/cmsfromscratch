<?
	require 'check-login.php' ;
	require 'inc-file-settings.php' ;
	require '../cmsfns.php' ;
	
	if (isset($_GET['dir'])) {
		// Strip off trailing slash if one exists
		define ("DIR", rtrim($_GET['dir'],'/')) ;
	}
	else {
		define ("DIR", '..') ;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Browse content - <? echo stripslashes($_SESSION['CMS_Title']) ; ?></title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript">
	<!--
		function selectPage(wotPage) {
			window.opener.sentLink(wotPage) ;
			window.close() ;
		}
	// -->
	</script>
	<style type="text/css">
		h1 {
			font-size:1.5em;
		}
	</style>
</head>

<body class="">
<h1>Select page:</h1><div class="crumbNav">
	<?
		// Break down current location, linking to each folder..
		$folders = explode('/',DIR) ;
		$levels = sizeof($folders) ;
		$link = '' ;
		for ($fldr=0; $fldr<$levels; $fldr++) {
			$link .= $folders[$fldr] ;
			$link .= '/' ;
			if ($fldr < $levels-1) {
				echo '<a href="?dir=' . $link . '">' ;
			}
			if ($folders[$fldr] == "..") $folders[$fldr] = "Home" ;
			echo ' ' . $folders[$fldr] ;
			if ($fldr < $levels-1) {
				echo '</a>' ;
			}
			echo ' /' ;
		}
	?>
</div>


<?
	$handle = opendir(DIR) ;
	$itemsFound = 0 ;
	$outputHTML = '' ;
	$outputDirs = '' ;
	$outputFiles = '' ;
	while (false !== ($file = readdir($handle))) {
		$previewFile = getPreviewFileFromLive(DIR.'/'.$file) ;
		if (strstr($file, 'phpthumb') || strstr($file, 'phpthumb') || !file_exists($previewFile)) {
			continue ;
		}
		if (!in_array($file,$reserved_filenames)) {
			$itemsFound++ ;
			if (is_dir(DIR.'/'.$file)) {
				// Directory
				$outputDirs .= "\n<li class=\"dir\">" ;
				$outputDirs .= '<a href="?dir=' . DIR .'/'. $file . '" title="Browse ' . $file . '">' ;
				$outputDirs .= $file ;
				$outputDirs .= '</a></li>' ;
			}
			else if (getFileExtension($file) == 'html' || getFileExtension($file) == 'php') {
				// File
				$outputFiles .= "\n" . '<li class="file"><a href="javascript:selectPage(\'' . DIR . '/' . $file . '\');" >' ;
				$outputFiles .= $file ;
				$outputFiles .= '</a>' ;
				$outputFiles .= "\n</li>" ;
			}
		}
	}
	closedir($handle) ;
	
	if ($itemsFound > 0) {
		$outputHTML = '<div class="bigLinks"><ul>' . $outputDirs . $outputFiles . '<!-- 3 --></ul></div>' ;
	}
	else {
		$outputHTML .= '<div><span class="info">No files or folders found</span></div>' ;
	}
	
	echo $outputHTML ;
?>



</body>
</html>
