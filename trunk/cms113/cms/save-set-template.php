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
	
	if (!isSet($_POST['sourcefile'])) {
		header("Location: set-templates.php?message=nosettemplatesourcefile") ;
	}
	
	$stFileName = STDIR . '/' . $_POST['sourcefile'] ;
	$stFile = @fopen($stFileName, 'w+') ;
	if (False === $stFile) {
		header("Location: set-templates.php?message=couldntreadst") ; exit; 
	}
	chmod($stFileName, 0644) ;
	
	if (isSet($_POST['st-advanced'])) {
		// Just save the raw content to the file and return
		$ok = @fwrite($stFile, $_POST['st-advanced']) ;
		fclose($stFile) ;
		if (False === $ok) {
			header("Location: set-templates.php?message=writestfailed") ;
			exit ;
		}
		else {
			header("Location: set-templates.php?message=writestok") ;
			exit ;
		}
	}
	
	
	// Loop through column definitions
	$colArray = array() ;
	$colCount = 0 ;
	
	if (isSet($_POST['colNames'])) {
		$colNames = $_POST['colNames'] ;
	}
	if (isSet($_POST['colTypes'])) {
		$colTypes = $_POST['colTypes'] ;
	}
	if (isSet($_POST['param1'])) {
		$param1 = $_POST['param1'] ;
	}
	if (isSet($_POST['param2'])) {
		$param2 = $_POST['param2'] ;
	}
	
	if (isSet($colNames)) {
		for ($col=0; $col<sizeOf($colNames); $col++) {
			array_push($colArray, array($colNames[$col], $colTypes[$col], isset($param1[$col]) ? $param1[$col] : "", isset($param2[$col]) ? $param2[$col] : "") ) ;
		}
	}
	
	// Loop through any repeated_N blocks...
	$rptdArray = array() ;
	$rptdCount = 0 ;
	$rptdAll = $_POST['repeated'] ;
	$rptdLine = array() ;
	for ($ri=0; $ri < sizeOf($rptdAll); $ri++) {
		array_push($rptdLine, $rptdAll[$ri]) ;
		if ($ri % 3 == 2) {
			array_push($rptdArray, $rptdLine) ;
			// Start a new line-array
			$rptdLine = array() ;
		}
	}
	
	$readST = array(
		"cols" => $colArray,
		"before" => $_POST['before'],
		"after" => $_POST['after'],
		"repeated" => $rptdArray,
		"else" => $_POST['else']
	) ;
	
	$ok = @fwrite($stFile, serialize($readST)) ;
	fclose($stFile) ;
	if (False === $ok) {
		header("Location: set-templates.php?message=Failed to save Set Template.") ;
	}
	else {
		header("Location: set-templates.php?message=Set Template created OK.") ;
	}
	
?>