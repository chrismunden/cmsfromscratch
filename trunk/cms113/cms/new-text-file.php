<?
/*
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