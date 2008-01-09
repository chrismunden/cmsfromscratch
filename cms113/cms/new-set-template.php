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
	
	if ($_SESSION['loginStatus'] < 2) header('Location:index.php?message=norights') ;	
	
	if (!isset($_GET['newstname'])) {
		header('Location: set-templates.php?message=nonewstnamesupplied') ;
	}
	else {
		$newSTName = stripFileExtension($_GET['newstname']) ;
		if (is_file(STDIR . $newSTName)) {
			header('Location: set-templates.php?message=newstexists') ;
			exit ;
		}
		$newSTFileName = STDIR . $newSTName . STEXTENSION ;
		$created = @fopen($newSTFileName, 'w+') ;
		
		if ($created) {
			// Success
			chmod($newSTFileName, 0644) ;
			$newST = array(
				"cols" => array(),
				"before" => "",
				"repeat" => array(),
				"after" => "",
				"else" => ""
			) ;
			fwrite($created, serialize($newST)) ;
			fclose($created) ;
			header('Location: edit-set-template.php?file=' . $newSTName . STEXTENSION) ;
		}
		else {
			header('Location: set-templates.php?message=couldntcreatenewst') ;
		}
	}
?>