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
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	if (!isset($_GET['newptname'])) {
		header('Location: page-templates.php?message=nonewptnamesupplied') ;
	}
	else {
		if (is_file(PTSDIR . $_GET['newptname'])) {
			print("error:A page template of that name already exists") ;
			exit ;
		}
		$newFilename = PTSDIR . '/' . stripFileExtension($_GET['newptname']) . PTEXTENSION ;
		$created = @fopen($newFilename, 'a') ;
		if ($created) {
			chmod($newFilename, 0644) ;
			// Also try to create the PT's local folder
			$madeDir = @mkdir(PTSDIR . '/' . $_GET['newptname']) ;
			if (!$madeDir) {
				// Couldn't make PT's local folder
				print("error:Failed to create new local includes folder for Page Template") ;
			}
			else {
				// Success
				chmod(PTSDIR . '/' . $_GET['newptname'], 0755) ;
				print($newFilename) ;
			}
		}
		else {
			print("error:Failed to create new Page Template") ;
		}
	}
?>