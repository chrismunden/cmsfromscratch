<?
	/* 
		Expects:
			$_GET['filePath'] ;
			$_GET['newFileName'] ;
	*/
	require '../cmsfns.php' ;
	
	if (!isSet($_GET['filePath'])) {
		echo 'error:No original file name sent' ;
		exit ;
	}
	else $filePath = pathFromID($_GET['filePath']) ;
	if (!file_exists($filePath)) {
		echo 'error:Original file not found.' ;
		exit ;
	}
	
	if (!isSet($_GET['newFileName'])) {
		echo 'error:No new file name sent' ;
		exit ;
	}
	else $newFileName = $_GET['newFileName'] ;
	
	$dir = dirname($filePath) ;
	$ext = getFileExtension($filePath) ;
	
	/* 
		If it's a SET TEMPLATE ???
	*/
	
	if (isPageTemplate($filePath)) {
		// PAGE TEMPLATE --- rename it and its LCI folder
		$newFilePath = $dir . '/' . $newFileName . '.' . $ext ;
		if (False === @rename($filePath, $newFilePath)) {
			echo 'error:Failed to rename Page Template: ' . $filePath . ' >>> ' . $newFilePath ;
			exit ;
		}
		if ($ext == 'php') {
			$originalLCIFolder = stripFileExtension($filePath) ;
			$newLCIFolder = stripFileExtension($newFilePath) ;
			if (False === @rename($originalLCIFolder, $newLCIFolder)) {
				echo 'error:Failed to rename Page Template includes folder: ' . $originalLCIFolder . ' >>> ' . $newLCIFolder ;
				exit ;
			}
		}
		echo 'pt' ;
		exit ;
	}
	else if ($ext == 'php') {
		// PAGE --- rename the page and its LCI folder, and its partner in Includes
		$newFilePath = $dir . '/' . $newFileName . '.' . $ext ;
		if (False === @rename($filePath, $newFilePath)) {
			echo 'error:Failed to rename page: ' . $filePath . ' >>> ' . $newFilePath ;
			exit ;
		}
		if (False === @rename(getPreviewFileFromLive($filePath), getPreviewFileFromLive($newFilePath))) {
			echo 'error:Failed to rename includes version of page: ' . getPreviewFileFromLive($filePath) . ' >>> ' . getPreviewFileFromLive($newFilePath) ;
			exit ;
		}
		$lciRoot = getLCIRootFolderFromPagePath($filePath) ;
		$newLciRoot = getLCIRootFolderFromPagePath($newFilePath) ;
		if (False === @rename($lciRoot, $newLciRoot)) {
			echo 'error:Failed to rename page LCI folder: ' . $lciRoot . ' >>> ' . $newLciRoot ;
			exit ;
		}
		echo 'regular' ;
		exit ;
	}
	else if (isLCI($filePath)) {
		// CHILD INCLUDE --- rename it and its sibling
		// e.g. includes/page_cms_files/cms_preview/lci.ext
		$newFilePath = $dir . '/' . $newFileName . '.' . $ext ;
		if (False === @rename($filePath, $newFilePath)) {
			echo 'error:Failed to rename preview include: ' . $filePath ;
			exit ;
		}
		if (False === @rename(getLiveLCIFromPreview($filePath), getLiveLCIFromPreview($newFilePath))) {
			echo 'error:Failed to rename live include: ' . getLiveLCIFromPreview($filePath) . ' >>> ' . getLiveLCIFromPreview($newFilePath) ;
			exit ;
		}
		/*	ALSO TRY TO RENAME ANY REFERENCES TO THE LCI IN THE PARENT PAGE	*/
		$parentPagePath = getParentPageFromLCI($filePath) ;
		$parentPageSource = file_get_contents($parentPagePath) ;
		$newParentPageSource = str_replace(basename($filePath), $newFileName . '.' . $ext, $parentPageSource) ;
		@file_put_contents($parentPagePath, $newParentPageSource) ;
		
		// Success
		echo 'regular' ;
		exit ;
	}
	else if (is_dir($filePath)) {
		// DIRECTORY --- rename it and its partner in Includes
		$newDirPath = dirname($filePath) . '/' . $newFileName ;
		if (False === @rename($filePath, $newDirPath)) {
			echo 'error:Failed to rename folder: ' . $filePath . ' >>> ' . $newDirPath ;
			exit ;
		}
		$includesDirPath = getPreviewFileFromLive($filePath) ;
		$newIncludesDirPath = getPreviewFileFromLive($newDirPath) ;
		if (False === @rename($includesDirPath, $newIncludesDirPath)) {
			echo 'error:Failed to rename includes folder: ' . $includesDirPath . ' >>> ' . $newIncludesDirPath ;
			exit ;
		}
		echo 'regular' ;
		exit ;
	}
	else {
		// FREE INCLUDE --- rename it and its partner in Includes
		$newFilePath = $dir . '/' . $newFileName . '.' . $ext ;
		if (False === @rename($filePath, $newFilePath)) {
			echo 'error:Failed to rename free include: ' . $filePath . ' >>> ' . $newFilePath ;
			exit ;
		}
		
		if (False === @rename(getPreviewFileFromLive($filePath), getPreviewFileFromLive($newFilePath))) {
			echo 'error:Failed to rename includes version of free include: ' . getPreviewFileFromLive($filePath) . ' >>> ' . getPreviewFileFromLive($newFilePath) ;
			exit ;
		}
		echo 'regular' ;
		exit ;
	}
	echo 'Not handled: ' . $filePath ;
?>