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
	
	if (!isset($_GET['dir'])) {
		print('error:nonewfoldername') ;
		exit ;
	}
	
	$newFolderPath = pathFromID($_GET['dir']) ;
	$created = @mkdir($newFolderPath, 0755) ;
	chmod($newFolderPath, 0755) ;
	
	if (False === $created)  {
		print('error: Couldn\'t create folder [' . $_GET['dir'] . ']') ;
		exit ;
	}
	
	// Any new folders created will also be created in /cms/includes/
	$createdPreview = @mkdir(getPreviewFileFromLive($newFolderPath), 0755) ;
	chmod(getPreviewFileFromLive($newFolderPath), 0755) ;
	if (False === $createdPreview) {
		print('error: Failed to create child folder [' . getPreviewFileFromLive($newFolderPath) . ']') ;
		exit ;
	}
	else {
		print($_GET['dir']) ;
	}
?>