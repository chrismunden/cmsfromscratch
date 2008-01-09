<?
	/* 
		Expects:
			$_POST['textcontent']
			$_POST['sourcefile']
			and possibly
			$_POST['dontrefresh'] (for Save & Continue with .text only)
		
		Takes the filename (LIVE for FREE INCLUDES, but PREVIEW for LCIs!) and new file content for TEXT or HTML files
		tries to save the preview copy of a file
		If it succeeds, tests whether the live and preview files match, returning
			<xml>
				<filesaved>
					<id>ID</id>
					<smatch>MATCH_CODE</smatch>
				</filesaved>
			</xml>
	*/
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	/* echo "debug: " . $_POST['textcontent'] ; exit ;*/
	
	/* 
	echo "debug: " ;
	var_dump($_POST) ;
	exit ;
	*/
	
	// Check we have the bits
	if (!isset($_POST['textcontent'])) {
		print('error:No content supplied.') ;
		exit ;
	}
	else if (!isset($_POST['sourcefile'])) {
		print('error:No source text file name supplied.') ;
		exit ;
	}
	else {
		$sourceFile = pathFromID($_POST['sourcefile']) ;
		$fileContent = $_POST['textcontent'] ;
	}
	
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : false ;
	if (isPageTemplate($sourceFile)) $mode = 'pagetemplate' ;
	elseif (getFileExtension($sourceFile) == "php") $mode = 'page' ;
	
	/* 
		If a child include, save file to pv location, and save live copy if live not present or empty
		If a page, just save to pv location, and save live copy if not present or empty, PLUS correct the general-include call (for the LIVE version!)
		If a free include, save to pv location, and save live copy if not present or empty
		If a Page Template, simply save to PTs folder 
	*/
	switch ($mode) {
		case "ptchildinclude" :
			// ????????????????
			$previewFile = pathFromID($_POST['sourcefile']) ;
			$liveFile = False ;
		break ;
		case "pagetemplate" :
			// ????????????????
			$previewFile = pathFromID($_POST['sourcefile']) ;
			$liveFile = False ;
		break ;
		default :
			if (isLCI(pathFromID($_POST['sourcefile']))) {
				// We have an LCI
				$previewFile = pathFromID($_POST['sourcefile']) ;
				$liveFile = getLiveLCIFromPreview(pathFromID($previewFile)) ;
			}
			else {
				$liveFile = pathFromID($_POST['sourcefile']) ;
				$previewFile = getPreviewFileFromLive($liveFile) ;
			}
		break ;
	}
	/* 
	DEBUGGING
	echo $previewFile, "\n\n", $liveFile ;
	exit ;
	*/
		
	
	// Correct path to include for general, for Pages only
	if ($mode == "page") {
		$fileContent = correctGeneralInclude(stripslashes($fileContent), $previewFile) ;
		$fileContent = unSimplifyContents($fileContent, $previewFile) ;
	}
	else {
		$fileContent = stripslashes($fileContent) ;
	}
	
	// Open handle to Preview file
    if (False === ($fileHandle = @fopen($previewFile, 'w'))) {
		print('error:Could not open preview file - ' . $previewFile) ;
        exit;
    }
	chmod($previewFile, 0644) ;
	// Try to save Preview copy
	if (False === @fwrite($fileHandle, $fileContent)) {
		fclose($fileHandle) ;
		print('error:Could not save preview file - ' . $previewFile) ;
		exit ;
    }
	fclose($fileHandle);
	
	
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
	
	header("Content-type: text/xml") ;
	$xmlr = '<xml>' ;
		if (isSet($_POST['dontrefresh'])) $xmlr .= '<dontrefresh>True</dontrefresh>' ;
		$xmlr .= '<id>' ;
			$xmlr .= $_POST['sourcefile'] ;
		$xmlr .= '</id>' ;
		if (file_exists($previewFile) && file_exists($liveFile)) {
			$xmlr .= '<match>' ;
				$xmlr .= (md5_file($previewFile) == md5_file($liveFile)) ? '1' : '0' ;
			$xmlr .= '</match>' ;
		}
	$xmlr .= '</xml>' ;
	print($xmlr) ;
	exit ;
?>