<?
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	if ($_SESSION['loginStatus'] < 2) header('Location:index.php?message=norights') ;	
	
	if (!isset($_GET['newstname'])) {
		header('Location: set-templates.php?message=nonewstnamesupplied') ;
	}
	else {
		$newSTName = stripFileExtension($_GET['newstname']) ;
		if (is_file(STDIR . $newSTName)) {
			header('Location: set-templates.php?message=newstexists') ;
			exit ;
		}
		$newSTFileName = STDIR . $newSTName . STEXTENSION ;
		$created = @fopen($newSTFileName, 'w+') ;
		
		if ($created) {
			// Success
			chmod($newSTFileName, 0644) ;
			$newST = array(
				"cols" => array(),
				"before" => "",
				"repeat" => array(),
				"after" => "",
				"else" => ""
			) ;
			fwrite($created, serialize($newST)) ;
			fclose($created) ;
			header('Location: edit-set-template.php?file=' . $newSTName . STEXTENSION) ;
		}
		else {
			header('Location: set-templates.php?message=couldntcreatenewst') ;
		}
	}
?>