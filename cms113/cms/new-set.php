<?/*
 * ***********************************************************************
 * Copyright © Ben Hunt 2007, 2008
 * 
 * This file is part of cmsfromscratch.

    Cmsfromscratch is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Cmsfromscratch is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Cmsfromscratch.  If not, see <http://www.gnu.org/licenses/>.
    ***********************************************************************
 */
	require '../cmsfns.php' ;
	/* 
		Expects:
			All cases:
				$_POST['template'] == name of set template to use
				$_POST['newfilepath'] == name of new Content Set file
					(for FIs =  e.g.  'folder/images')
					(for CIs =  e.g. 'images')
			+ Only for CIs also
				$_POST['parentpage'] e.g. "../page.php"
	*/
	
	/* 
	echo "debug:" ;
	var_dump($_POST) ;
	exit ;
	*/
	/* 
		For PT LCIs, need to strip off .php and add /
	*/
	
	if (!isset($_POST['newfilepath'])) {
		print('error:nonewptnamesupplied') ;
		exit ;
	}
	else {
		$newFilePath = pathFromID($_POST['newfilepath']) . '.set' ;
	}
	
	if (!isset($_POST['template'])) {
		print('error:No set template supplied') ;
		exit ;
	}
	
	define("DIR", dirname($_POST['newfilepath'])) ;
	
	// Read in template
	$templateArray = @unserialize(file_get_contents(STDIR.$_POST['template'])) ;
	$newArray = array() ;
	$newArray[0] = $_POST['template'] ;
	$newArray[1] = $newArray[0] ;
	$newArray[2] = $templateArray['cols'] ;
	
	// If it's going to belong to a PT, there's only one copy!
	if (isset($_POST['parentpage'])) {
		if (isPageTemplate(pathFromID($_POST['parentpage']))) {
			// strip the .php and append a /
			$newPreviewFilePath = stripFileExtension(pathFromID($_POST['parentpage'])) . '/' . $_POST['newfilepath'] . '.set' ;
			$newLiveFilePath = False ;
			$successReturnValue = $newPreviewFilePath ;
		}
		else {
			$includesRoot = getLCIRootFolderFromPagePath(pathFromID($_POST['parentpage'])) ;
			makeSureFoldersExist($includesRoot) ;
			$newPreviewFilePath = $includesRoot . '/cms_preview/' . $_POST['newfilepath'] . '.set' ;
			$newLiveFilePath = getLiveLCIFromPreview($newPreviewFilePath) ;
			$successReturnValue = $_POST['parentpage'] . '/' . $_POST['newfilepath'] . '.set' ;
			// Note: Simpler line below works in new text... 
			// $returnFile = $_GET['parentpage'] . '/' . $fileName ;
		}
	}
	else {
		// FREE INCLUDE: there are also 2 copies (i.e. parentpage not set)
		$newLiveFilePath = $newFilePath ;
		// echo "debug:$newLiveFilePath" ; exit ;
		$newPreviewFilePath = getPreviewFileFromLive($newFilePath) ;
		$successReturnValue = $newLiveFilePath ;
	}
	
	if (False !== $newPreviewFilePath && is_file($newPreviewFilePath)) {
		print('error: ' . $newPreviewFilePath . " is a file.") ;
		exit ;
	}
	
	
	// Create preview file
	$createdPreview = @fopen($newPreviewFilePath, 'a') ;
	if (False === $createdPreview) {
		print('error: Couldn\'t create new set - ' . $newPreviewFilePath) ;
		exit ;
	}
	else {
		chmod($newPreviewFilePath, 0644) ;
		fwrite($createdPreview, serialize($newArray)) ;
		fclose($createdPreview) ;
	}
	

	// Create live file?
	if (False !== $newLiveFilePath) {
		if (is_file($newLiveFilePath)) {
			print('error: Couldn\'t create new set - ' . $newLiveFilePath) ;
			exit ;
		}
		else {
			$createdLive = @copy($newPreviewFilePath, $newLiveFilePath) ;
			chmod($newLiveFilePath, 0644) ;
		}
	}
	
	// SUCCESS
	print($successReturnValue) ;
?>