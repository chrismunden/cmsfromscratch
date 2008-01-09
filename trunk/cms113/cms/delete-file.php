<?
	@require_once('../cmsfns.php') ;
	$killFile2 = False ;
	/* 
		Expects:
			-	$_GET['file']
		
		Deletes
		 - Free includes (both live and preview copies)
		 - Pages (both live and preview copies, including LCI folders)
		 - LCIs (deletes both Preview and Live versions)
	*/
	
	if (!isset($_GET['file'])) {
		print('error:nodeletefilesupplied') ;
	}
	else {
		$killFile = pathFromID($_GET['file']) ;
	}
	
	// Checks
	if (!file_exists($killFile)) {
		print('error:filedoesnotexist - ' . $_GET['file']) ;
		exit ;
	}
	
	// Delete primary File
	if  (False === @unlink($killFile)) {
		print('error:deletefilefailed - ' . $killFile) ;
		exit ;
	}
	
	// DEBUG print ('error:' . $killFile); exit ;
	
	if (strstr($killFile, '_cms_files/')) {
		// We have a LCI. Get the live file from preview
		// Note: Use a generic function for this...!!!
		$killFile2 = str_replace('_cms_files/cms_preview/', '_cms_files/cms_live/', $killFile) ;
	}
	else if (isPageTemplate($killFile)) {
		// PT name is strip slash, add ".php"
		$killFile2 = false ;
		$includesDir = stripFileExtension($killFile) . '/' ;
	}
 	else {
		// Must be a page or Free Include, so find the corresponding Live file??? = $killFile2
		if (strstr($killFile, '../')) {
			$killFile2 = getPreviewFileFromLive($killFile) ;
		}
		if (getFileExtension($killFile) == "php") {
			// File is a PAGE, try to delete its local folders too
			$includesDir = stripFileExtension(getPreviewFileFromLive($killFile)) . '_cms_files' ;
			clearstatcache() ;
		}
	}
	
	if (isSet($includesDir) && is_dir($includesDir)) {
		if (wipeDir($includesDir)) {
			$includesDirWiped = @rmdir($includesDir) ;
			/* if (!$includesDirWiped) {
				print('error:couldntwipepageincludesfolder') ;
				exit ;
			}*/
		}
	}
	
	/* 
		Delete the corresponding Page/FI
	*/
	if  ($killFile2 && False === @unlink($killFile2)) {
		print('error:deletefile2failed') ;
	}
	
	// OK. Let the View know delete worked.
	if (!isSet($_GET['suppress_output'])) {
		print($_GET['file']) ;
		exit ;
	}
?>