<?
	define("STDIR", 'settemplates') ;
	$handle = opendir(STDIR) ;
	while (false !== ($file = readdir($handle))) {
		if ($file != '.' && $file != '..') {
			echo '<option value="' . $file . '">' . stripFileExtension($file) . '</option>' ;
		}
	}
?>	