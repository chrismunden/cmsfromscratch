<?
	require('../cmsfns.php') ;
	/* 
		Expects:
			$_GET['newfilepath'], which corresponds to the name of the 'live' file.
			$_GET['parentpage'], which is used for child includes (and pagetemplate LCIs)
			$_GET['mode']
			 - freeinclude
			 - childinclude
			 - ptchildinclude
	*/
	
	// DEBUG print('error: ' . $_GET['parentpage']) ; exit ;
	
	if (!isset($_GET['newfilepath']) ) {
		print('error:nonewfilenamesupplied') ;
		exit ;
	}
	
	if (isSet($_GET['parentpage']) && strpos($_GET['parentpage'], PTSDIR) === 0) {
		$mode = 'ptchildinclude' ;
	}
	else if (isset($_GET['mode'])) {
		$mode = $_GET['mode'] ;
	}
	else {
		print('error:modenotset') ;
		exit ;
	}
	
	// Replace any spaces with dashes (was underscores!)
	// $fileName = str_replace(' ', '-', $_GET['newfilepath']) ;
	
	$fileName = $_GET['newfilepath'] ;
	
			
	switch ($mode) {
		case "childinclude" :
			// Make 2 copies: in includes / PATH / PAGENAME_AS_FOLDER / cmslive and .../cmspreview
			if (!isset($_GET['parentpage'])) {
				print('error:noparentpagesupplied') ;
				exit ;
			}
			$includesRoot = getLCIRootFolderFromPagePath(pathFromID($_GET['parentpage'])) ;
			makeSureFoldersExist($includesRoot) ;
			$newPreviewFilePath = $includesRoot . '/cms_preview/' . $fileName ;
			$newLiveFilePath = getLiveLCIFromPreview($newPreviewFilePath) ;
			
			// parentpage = e.g. DOTDOTSLASHpageDOTphp
			$returnFile = $_GET['parentpage'] . '/' . $fileName ;
			
		break ;
		case "ptchildinclude" :
			// New text/html file belongs to a Page Template
			$newPreviewFilePath = stripFileExtension($_GET['parentpage']) . '/' . $fileName ;
			$newLiveFilePath = False ;
		break ;
		case "freeinclude" :
			$newPreviewFilePath = pathFromID($_GET['newfilepath']) ;
			$newLiveFilePath = getPreviewFileFromLive($newPreviewFilePath) ;
		break ;
	}
	
	if ($newPreviewFilePath) {
		if (is_file($newPreviewFilePath)) {
			print('error:newpreviewfilealreadyexists - ' . $newPreviewFilePath) ;
		}
		$createdPreview = @fopen($newPreviewFilePath, 'w') ;
		if (False === $createdPreview) {
			print('error:Could not create new Preview text file.') ;
			fclose($createdPreview) ;
		}
		chmod($newPreviewFilePath, 0644) ;
		fclose($createdPreview) ;
	}
	if ($newLiveFilePath) {
		if (is_file($newLiveFilePath)) {
			print('error:Could not create new Live text file. - ' . $newLiveFilePath) ;
		}
		$createdLive = @fopen($newLiveFilePath, 'w') ;
		if (False === $createdLive) {
			print('error:couldntcreatefile') ;
			fclose($createdLive) ;
		}
		chmod($newLiveFilePath, 0644) ;
		fclose($createdLive) ;
	}
	/* 
		SUCCESS
		Return info to insert new file!!!
	*/
	switch ($mode) {
		case "childinclude" :
			print ($returnFile) ;
			exit ;
		break ;
		default :
			// (Including free includes and pagetemplate-lcis)
			print ($newPreviewFilePath) ;
			exit ;
		break ;
	}
?>