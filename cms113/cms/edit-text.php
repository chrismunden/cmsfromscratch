<?
	require ('../cmsfns.php') ;
	/* 
		Expects:
			$_GET['file'] == the path to the file to edit
	*/	
	if (!isset($_GET['file'])) {
		print('error: No filename supplied.') ;
		exit ;
	}
	// Create preview text file if doesn't exist
	$previewFilePath = getPreviewFileFromLive(pathFromID($_GET['file'])) ;
	if (False === file_exists($previewFilePath)) {
		copy (pathFromID($_GET['file']), $previewFilePath) ;
	}
	if (False === ($fileContents = @file_get_contents($previewFilePath))) {
		print('error: Couldn\'t read file ' . $_GET['file']) ;
		exit ;
	}
	else {
		print($_GET['file'] . MYDELIM . simplifyContents(tidyText($fileContents))) ;
	}
?>