<?
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	if (!isset($_GET['newptname'])) {
		header('Location: page-templates.php?message=nonewptnamesupplied') ;
	}
	else {
		if (is_file(PTSDIR . $_GET['newptname'])) {
			print("error:A page template of that name already exists") ;
			exit ;
		}
		$newFilename = PTSDIR . '/' . stripFileExtension($_GET['newptname']) . PTEXTENSION ;
		$created = @fopen($newFilename, 'a') ;
		if ($created) {
			chmod($newFilename, 0644) ;
			// Also try to create the PT's local folder
			$madeDir = @mkdir(PTSDIR . '/' . $_GET['newptname']) ;
			if (!$madeDir) {
				// Couldn't make PT's local folder
				print("error:Failed to create new local includes folder for Page Template") ;
			}
			else {
				// Success
				chmod(PTSDIR . '/' . $_GET['newptname'], 0755) ;
				print($newFilename) ;
			}
		}
		else {
			print("error:Failed to create new Page Template") ;
		}
	}
?>