<?
	require '../cmsfns.php' ;
 	require 'check-login.php' ;
	
	if (!isset($_GET['pagepath'])) {
		print('error:No new page name passed.') ;
		exit ;
	}
	else {
		$newLivePagePath = pathFromID($_GET['pagepath'] . '.php') ;
		$newPreviewPagePath = getPreviewFileFromLive($newLivePagePath) ;
		$pathToPage = dirname($newLivePagePath) . '/' ;
	}
	
	if (isset($_GET['pt']) && strlen($_GET['pt'])) {
		// Fetch contents of Page Template
		$pt = $_GET['pt'] ;
		if (getFileExtension($pt) != 'php') $pt .= '.php' ;
		$ptContents = @file_get_contents(PTSDIR . '/' . pathFromID($pt)) ;
		if (False === $ptContents) {
			echo 'error: Could not find PT ' . pathFromID($pt) ;
			exit ;
		}
	}
	else {
		$ptContents = '' ;
		$pt = False ;
	}
	
	// Check page doesn't already exist
	if (is_file($newLivePagePath)) {
		print('error: Page already exists: ' . $newLivePagePath) ;
		exit ;
	}
	
	// Create local page-includes folders
	$includesDirectoryRoot = getLCIRootFolderFromPagePath($newLivePagePath) ;
	$previewIncludesDirectory = $includesDirectoryRoot . '/cms_preview/' ;
	$liveIncludesDirectory = $includesDirectoryRoot . '/cms_live/' ;
	
	if (!is_dir($includesDirectoryRoot)) {
		if (mkdir($includesDirectoryRoot, 0755) === False) {
			print('error:Couldn\'t create Root Includes folder: ' . $includesDirectoryRoot) ;
			exit ;
		}
		else chmod($includesDirectoryRoot, 0755) ;
	}
	if (!is_dir($previewIncludesDirectory)) {
		if (mkdir($previewIncludesDirectory, 0755) === False) {
			print('error:Couldn\'t create Preview Includes folder: ' . $previewIncludesDirectory) ;
			exit ;
		}
		else chmod($previewIncludesDirectory, 0755) ;
	}
	if (!is_dir($liveIncludesDirectory)) {
		if (mkdir($liveIncludesDirectory, 0755) === False) {
			print('error:Couldn\'t create Live Includes folder: ' . $liveIncludesDirectory) ;
			exit ;
		}
		else chmod($liveIncludesDirectory, 0755) ;
	}
		
	// Put in the right path to cms/general.php
	// 2nd parameter below is source file path, i.e. relative path from web root to new page...
	
	$ptContents = correctGeneralInclude($ptContents, $pathToPage) ;
	
	// Replace any << includes >> with appropriate put() function calls...
	$ptContents = unSimplifyContents($ptContents) ;
	
	
	// Read filenames of any PT include files that exist into array
	$ptLocalIncludes = PTSDIR . '/' ;
	if ($pt) $ptLocalIncludes .= pathFromID(stripFileExtension($pt)) ;
	
	// Builds an array of any files that exist in "pagetemplates/ptname"
	$ptIncludeFilesArray = Array() ;
	if (is_dir($ptLocalIncludes)) {
		$ptLocalIncludeHandle = opendir($ptLocalIncludes) ;
		while (false !== ($ptIncludeFile = readdir($ptLocalIncludeHandle))) {
			if ($ptIncludeFile != '.' && $ptIncludeFile != '..' && !is_dir($ptLocalIncludes . '/' . $ptIncludeFile) ) {
				array_push($ptIncludeFilesArray,$ptIncludeFile);
			}
		}
		closedir($ptLocalIncludeHandle) ;
	}
	
	// Parse PT for any references to child includes (i.e. <<includes>>)
	$nextIncludeEnd = 0 ;
	
	/* 
		Note: this would be better as a regex that returns its values into an array
		Because it seems to look for a 4-character file extension too, so won't work for ".set"!!
	*/
	
	while (($nextIncludeStart = strpos($ptContents, 'put("', $nextIncludeEnd)) !== False) {
		// Loop through all the references to includes in the PT contents
		$nextIncludeEnd = strpos($ptContents, '")', $nextIncludeStart + 5) ;
		
		if (!$nextIncludeEnd) break ;
		
		// Actually, may work for sets.. but maybe not if they're setname.set>> (with no space)!!
		$includeText = trim(substr($ptContents, $nextIncludeStart + 5, $nextIncludeEnd - $nextIncludeStart - 5)) ;
		// echo $includeText, '<br />' ;
		
		// Skip any references to free includes...
		if (strpos($includeText, '/')) continue ;
		
		// If a correponding include exists in PT's local folder, copy it over to the new page's LFs
		$fileFound = array_search($includeText, $ptIncludeFilesArray) ;
		$destinationFile1 = $previewIncludesDirectory . $includeText ;
		$destinationFile2 = $liveIncludesDirectory . $includeText ;
		
		if ($fileFound) {
			// i.e. We've found a reference that also exists as an actual child include of the page template
			$sourceFile=  PTSDIR . '/' . stripFileExtension($pt) . '/' . $includeText ;
			
			if (!file_exists($sourceFile)) {
				echo 'error: PT include source file does not exist: ' . $sourceFile ;
				exit ;
			}
			if (!copy($sourceFile, $destinationFile1)) {
				echo 'error: Failed to copy PT include file: ' . $sourceFile ;
				exit ;
			}
			if (!copy($sourceFile, $destinationFile2)) {
				echo 'error: Failed to copy PT include file: ' . $sourceFile ;
				exit ;
			}
			// Remove the reference from the array
			unset ($ptIncludeFilesArray[$fileFound]);
		}
	}
	
	// Now, go through the array to find any files that haven't been referenced in the PT, and copy them over to both locations
	if ($pt) {
		for ($filesLeft=0; $filesLeft<sizeOf($ptIncludeFilesArray); $filesLeft++) {
			if (!strlen($ptIncludeFilesArray[$filesLeft])) continue ;
			$sourceFile =  PTSDIR . '/' . pathFromId(stripFileExtension($pt) . '/' . $ptIncludeFilesArray[$filesLeft]) ;

			$copiedPreview = @copy($sourceFile, $previewIncludesDirectory . $ptIncludeFilesArray[$filesLeft]) ;
			if (False === $copiedPreview) {
				print('error: Failed to create new include: ' . $previewIncludesDirectory . '<<' . $ptIncludeFilesArray[$filesLeft] . '>>') ;
				exit ;
			}
			$copiedLive = @copy($sourceFile, $liveIncludesDirectory . $ptIncludeFilesArray[$filesLeft]) ;
			if (False === $copiedLive) {
				print('error: Failed to create new include: ' . $liveIncludesDirectory . '<<' . $ptIncludeFilesArray[$filesLeft] . '>>') ;
				exit ;
			}
		}
	}
	
	// Create the actual page file...
	// Duplicate! $ptContents = correctGeneralInclude($ptContents, $pathToPage) ;
	$newLiveFileHandle = fopen($newLivePagePath, 'w') ;
	if (False === fwrite($newLiveFileHandle, $ptContents)) {
		fclose($newLiveFileHandle) ;
		print('error:message=couldntcreatenewpage') ;
		exit ;
	}
	chmod($newLivePagePath, 0644) ;
	fclose($newLiveFileHandle) ;
	
	// Create the preview copy
	$newPreviewFileHandle = fopen($newPreviewPagePath, 'w') ;
	if (False === fwrite($newPreviewFileHandle, $ptContents)) {
		fclose($newPreviewFileHandle) ;
		print('error:message=couldntcreatenewpage') ;
		exit ;
	}
	chmod($newPreviewPagePath, 0644) ;
	fclose($newPreviewFileHandle) ;
	
	/* 
		Return path of new Page to AJAX function.....
	*/
	if (!isSet($_GET['suppress_output'])) print($newLivePagePath) ;
?>
