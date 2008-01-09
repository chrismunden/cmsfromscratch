<?
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	// Takes the filename (LIVE for FREE INCLUDES, but PREVIEW for LCIs!) and new file content for TEXT or HTML files
	
	if (!isSet($_POST['sourceFile'])) {
		header('Location:error.php?message=nosetfile&detail=' . $_POST['sourceFile']) ;
		exit ;
	}
	
	$colNames = $_POST['colName'] ;
	$colDataTypes = $_POST['colDataType'] ;
	$vals = $_POST['val'] ;
	$colCount = sizeOf($colNames) ;
	
	$setArray = array() ;
	$setArray[0] = $_POST['setTemplate'] ;
	$setArray[1] = $_POST['formattingTemplate'] ;
	for ($i=0; $i<$colCount; $i++) {
		$setArray[2][$i] = array($colNames[$i], $colDataTypes[$i]) ;
	}
	
	// Loop through all the actual values
	$rowCount = 2 ;
	$valCount = 0 ;
	for ($val=0; $val<sizeOf($vals); $val++) {
		if ($val % $colCount == 0) {
			$rowCount++ ;
		}
		$setArray[$rowCount][$val % $colCount] = str_replace('%%linebreak%%', "\n", addslashes($vals[$val])) ;
	}
	$fileContent = serialize($setArray) ;
	
	
	
	
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : false ;
	if (substr($_POST['sourceFile'], -4, 4) == ".php") $mode = 'page' ;
	
	/* 
		If a child include, save file to pv location, and save live copy if live not present or empty
		If a page, just save to pv location, and save live copy if not present or empty, PLUS correct the general-include call (for the LIVE version!)
		If a free include, save to pv location, and save live copy if not present or empty
		If a Page Template, simply save to PTs folder 
	*/
	switch ($mode) {
		case "ptchildinclude" :
			// ????????????????
			$previewFile = $_POST['sourceFile'] ;
			$liveFile = False ;
		break ;
		case "pagetemplate" :
			// ????????????????
			$previewFile = PTSDIR . $_POST['sourceFile'] ;
			$liveFile = False ;
		break ;
		default :
			if (isLCI(pathFromID($_POST['sourceFile']))) {
				// We have an LCI
				$previewFile = pathFromID($_POST['sourceFile']) ;
				$liveFile = getLiveLCIFromPreview(pathFromID($previewFile)) ;
			}
			else {
				$liveFile = pathFromID($_POST['sourceFile']) ;
				$previewFile = getPreviewFileFromLive($liveFile) ;
			}
		break ;
	}
	
	
	// Open handle to Preview file
    if (False === ($fileHandle = @fopen($previewFile, 'w'))) {
		print('error:Could not open preview file - ' . $previewFile) ;
        exit;
    }
	// Try to save Preview copy
	if (False === @fwrite($fileHandle, $fileContent)) {
		fclose($fileHandle) ;
		print('error:Could not save preview file - ' . $previewFile) ;
		exit ;
    }
	fclose($fileHandle);
	chmod($previewFile, 0644) ;
	
	
	// If the live file is zero-length, save to Live also.
	if (
		$liveFile &&
		(!is_file($liveFile) || @filesize($liveFile) === 0)
	) {
		$saveLive = fopen($liveFile, 'w') ;
		if (False === @fwrite($saveLive, $fileContent)) {
			print('error:Could not save preview file') ;
			fclose($saveLive) ;
			exit ;
		}
		fclose($saveLive) ;
		chmod($liveFile, 0644) ;
	}
	
	$filesMatch = (md5_file($previewFile) == md5_file($liveFile)) ? '1' : '0' ;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Set saved</title>
</head>
<body>
<script type="text/javascript">
<!--
	<? echo 'top.filesMatch("' . $_POST['sourceFile'] . '", "' . $filesMatch .'" ) ;' ; ?>
	top.destroyTabAndPanel("<? echo $_POST['sourceFile'] ; ?>") ;
// -->
</script>
</body>
</html>