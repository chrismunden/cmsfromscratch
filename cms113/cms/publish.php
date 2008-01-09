<?
	require '../cmsfns.php' ;	
	require 'check-login.php' ;
	
	/* 
		If filepath starts with ../, it's a free include or a page
		Otherwise, it's an LCI.
	*/
	
	// debug	echo 'error: ' . $_GET['file'] ; exit ;
	
	
	if (substr($_GET['file'], 0, 3) == "../") {
		/* 
			PAGES AND FREE INCLUDES
			Expects:
				$_GET['file'] = The name of the single file to publish/restore.
				$_GET['action'] = Either "publish" or "restore"
		*/
		
		if (!isset($_GET['action'])) {
			print('error: publishnoactionset') ;
		}
		elseif (!isset($_GET['file'])) {
			print('error: publishnofileset') ;
		}
		
		$liveFile = pathFromID($_GET['file']) ;
		$previewFile = getPreviewFileFromLive($liveFile) ;
		
		// if (substr($_GET['action'], 0, 7) == 'publish') {
			// i.e. works for "publish" and "publishall"
		if ($_GET['action'] == 'publish' && file_exists($previewFile)) {
			if (False === copy($previewFile, $liveFile)) {
				print ('error: Failed to publish ' . $_GET['file']) ;
			}
		}
		else {
			if (False === copy($liveFile, $previewFile)) {
				print ('error: Failed to restore ' . $_GET['file']) ;
			}
		}
		// Success
		print($_GET['file']) ;
		exit ;
	}
	
	else {
		
		/* 
			CHILD INCLUDES (1 or more)
			Expects:
				 -	$_GET['action']  as "publish" or "restore"
				 -	$_GET['file']    = relative path to 1 or more PREVIEW INCLUDE file(s) (i.e. includes/path/blah.ext)
		*/
		
		if (!isset($_GET['action'])) {
			print('error: No action') ;
			exit ;
		}
		elseif (!isset($_GET['file'])) {
			print('error: No file set') ;
			exit ;
		}
		
		$previewFile = pathFromID($_GET['file']) ;
		$liveFile = getLiveLCIFromPreview($previewFile) ;
		
		if ($_GET['action'] == 'publish' && file_exists($previewFile)) {
			// PUBLISH
			if (False === @copy($previewFile, $liveFile)) {
				print('error: Publishing failed - ' . $previewFile . ' -> ' . $liveFile) ;
				exit ;
			}
		}
		else {
			// RESTORE
			if (False === @copy($liveFile, $previewFile)) {
				print('error: Restoring failed - ' . $liveFile . ' -> ' . $previewFile) ;
				exit ;
			}
		}
		// SUCCESS
		print($_GET['file']) ;
		exit ;
	}
?>