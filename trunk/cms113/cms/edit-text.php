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
	require ('../cmsfns.php') ;
	/* 
		Expects:
			$_GET['file'] == the path to the file to edit
	*/	
	if (!isset($_GET['file'])) {
		print('error: No filename supplied.') ;
		exit ;
	}
	// Create preview text file if doesn't exist
	$previewFilePath = getPreviewFileFromLive(pathFromID($_GET['file'])) ;
	if (False === file_exists($previewFilePath)) {
		copy (pathFromID($_GET['file']), $previewFilePath) ;
	}
	if (False === ($fileContents = @file_get_contents($previewFilePath))) {
		print('error: Couldn\'t read file ' . $_GET['file']) ;
		exit ;
	}
	else {
		print($_GET['file'] . MYDELIM . simplifyContents(tidyText($fileContents))) ;
	}
?>