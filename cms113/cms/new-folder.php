<?
	require ('../cmsfns.php') ;
	
	if (!isset($_GET['dir'])) {
		print('error:nonewfoldername') ;
		exit ;
	}
	
	$newFolderPath = pathFromID($_GET['dir']) ;
	$created = @mkdir($newFolderPath, 0755) ;
	chmod($newFolderPath, 0755) ;
	
	if (False === $created)  {
		print('error: Couldn\'t create folder [' . $_GET['dir'] . ']') ;
		exit ;
	}
	
	// Any new folders created will also be created in /cms/includes/
	$createdPreview = @mkdir(getPreviewFileFromLive($newFolderPath), 0755) ;
	chmod(getPreviewFileFromLive($newFolderPath), 0755) ;
	if (False === $createdPreview) {
		print('error: Failed to create child folder [' . getPreviewFileFromLive($newFolderPath) . ']') ;
		exit ;
	}
	else {
		print($_GET['dir']) ;
	}
?>