<?
	
	require('../cmsfns.php') ;
	
	if (!isset($_GET['dir'])) {
		print('error:nodirectorytodelete') ;
		exit ;
	}
	$liveDir = pathFromID($_GET['dir']) ;
	$previewDir = getPreviewFileFromLive($liveDir) ;
	
	if (is_dir($liveDir)) {
		// Delete directory
		$dirWiped = wipeDir($liveDir) ;
		@rmdir($liveDir) ;
		/* if  (False === @rmdir($liveDir)) {
			print('error:Delete live copy of folder failed') ;
			exit ;
		}*/
	}
	
	if (is_dir($previewDir)) {
		// Delete directory
		$dirWiped = wipeDir($previewDir) ;
		@rmdir($previewDir) ;
		/* if  (False === @rmdir($previewDir)) {
			print('error:Delete preview copy of folder failed') ;
			exit ;
		}*/
	}
	// SUCCESS
	print($_GET['dir']) ;
?>