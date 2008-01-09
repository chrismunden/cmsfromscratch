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
	
	if (!isset($_GET['dir'])) {
		print('error:nodirectorytodelete') ;
		exit ;
	}
	$liveDir = pathFromID($_GET['dir']) ;
	$previewDir = getPreviewFileFromLive($liveDir) ;
	
	if (is_dir($liveDir)) {
		// Delete directory
		$dirWiped = wipeDir($liveDir) ;
		@rmdir($liveDir) ;
		/* if  (False === @rmdir($liveDir)) {
			print('error:Delete live copy of folder failed') ;
			exit ;
		}*/
	}
	
	if (is_dir($previewDir)) {
		// Delete directory
		$dirWiped = wipeDir($previewDir) ;
		@rmdir($previewDir) ;
		/* if  (False === @rmdir($previewDir)) {
			print('error:Delete preview copy of folder failed') ;
			exit ;
		}*/
	}
	// SUCCESS
	print($_GET['dir']) ;
?>