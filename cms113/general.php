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
	require ('cmsfns.php') ;
	global $mode ;
	global $whereWeAre ;
	global $pathToRoot ;
	$pathToRoot2 = (!strlen($pathToRoot)) ? '.' : $pathToRoot ;
	
	if (isSet($_GET['mode'])) {
		$mode = $_GET['mode'] ;
	}
	else {
		$mode = 'live' ;
	}
	
	$rootDir = realpath($pathToRoot2) ;
	$thisDir = realpath('.') ;
	if ($rootDir != $thisDir) {
		$whereWeAre = substr($thisDir, strlen($rootDir)+1) . '/' ;
	}
	else {
		$whereWeAre = '' ;
	}
	
	function put($putWhat, $justReturnValue = False, $historyInc = array()) {
		global $mode, $pathToRoot, $pathToRoot2, $whereWeAre ;
		$pageName = basename($_SERVER["SCRIPT_NAME"]) ;
		
		if (strpos($putWhat, '/')) {
			$putWhat = str_replace('SITEROOT/', '', $putWhat) ;
			
			$includeFileName = str_replace(array('<<','>>'), '', $putWhat) ;
			// FREE INCLUDE
			if ($mode == "preview") {
				$includeFilePath = $pathToRoot . CMSDIRNAME . '/includes/' . $includeFileName ;
			}
			else {
				$includeFilePath = $pathToRoot . $includeFileName ;
			}
		}
		else {
			// CHILD INCLUDE
			if ($mode == 'preview') {
				$includeFilePath = $pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . stripFileExtension(basename($_SERVER["SCRIPT_NAME"])) . '_cms_files/cms_preview/' . $putWhat ;
			}
			else {
				$includeFilePath = $pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . stripFileExtension(basename($_SERVER["SCRIPT_NAME"])) . '_cms_files/cms_live/' . $putWhat ;
			}
		}
		
		// Prevent circular includes!
		if (in_array($includeFilePath, $historyInc)) {
			echo '<!-- Warning: Loop caused in includes! -->' ;
			return false ;
		}
		else {
			array_push($historyInc, $includeFilePath) ;
		}
		
		/* 
			If the file doesn't exist, try looking in the page's own directory (i.e. remove the /page_cms_files/cms_live|preview/ bit...
		*/
		if (!file_exists($includeFilePath)) {
			$alternativePath = $pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . basename($includeFilePath) ;
			if (file_exists($alternativePath)) {
				$includeFilePath = $alternativePath ;
			}
		}
		
		$fileContents = @file_get_contents($includeFilePath) ;
		if ($fileContents !== False) {
		
			switch (getFileExtension($includeFilePath)) {
			
				case "text" :
					$nestResult = nestIncludes($fileContents, $historyInc) ;
					$fileContents = $nestResult[0] ;
					$historyInc = $nestResult[1] ;
					if (!$justReturnValue) echo $fileContents ;
					else return $fileContents ;
				break ;
				
				case "html" :
					$nestResult = nestIncludes($fileContents, $historyInc) ;
					$fileContents = $nestResult[0] ;
					$historyInc = $nestResult[1] ;
					$fileContents = str_replace('"cmsimages/', '"' . CMSDIRNAME . '/cmsimages/', $fileContents) ; // May be redundant now?
					// $fileContents = str_replace('href="/' , 'href="' . $pathToRoot2 . '/', $fileContents) ; REDUNDANT
					$fileContents = convertSmartQuotes($fileContents) ;
					if (!$justReturnValue) echo $fileContents ;
					else return $fileContents ;
				break ;
				
				case "set" :
					if (!$justReturnValue) {
						renderSet($fileContents, False, $historyInc) ;
					}
					else {
						return renderSet($fileContents, True, $historyInc) ;
					}
				break ;
				
				case "phpx" :
			        ob_start();
				        include $includeFilePath ;
				        $contents = ob_get_contents() ;
			        ob_end_clean() ;
			        echo $contents ;
				break ;
			}
		}
		else if ($mode == 'preview') {
			echo '<!-- Not found: ' . $includeFilePath . ' -->' ;
		}
	}
	
	function nestIncludes($fileContents, $historyInc) {
		// May be able to tidy up the code around including...
		$repeatIt = True ;
		while ($repeatIt == True) {
			$nextPutItem = getNextPut($fileContents) ;
			if (isSet($nextPutItem) && sizeOf($nextPutItem)>1) {
				// Replace the result of putting the new thing in place of the put() call
				$newPutResult = put("$nextPutItem[3].$nextPutItem[4]", True, $historyInc) ;
				// echo $newPutResult ;
				$fileContents = str_replace($nextPutItem[0], $newPutResult, $fileContents) ;
				array_push($historyInc, $nextPutItem[0]) ;
			}
			else {
				$repeatIt = False ;
			}
		}
		return array($fileContents, $historyInc) ;
	}
	
	function getNextPut($someHTML) {
		if (eregi("(<<|&lt;&lt;)([[:space:]]*)([-a-zA-Z0-9_\/]+).([a-zA-Z]{3,4})([[:space:]]*)(>>|&gt;&gt;)", $someHTML, $regs))
		return $regs ;
	}
	
	function convertSmartQuotes($string) { 
	    $search = array(chr(145), 
	                    chr(146), 
	                    chr(147), 
	                    chr(148), 
	                    chr(151)); 
	    $replace = array("'", 
	                     "'", 
	                     '"', 
	                     '"', 
	                     '-'); 
	    return str_replace($search, $replace, $string); 
	}	
	
	function renderSet($setContentsFileContents, $justReturnValue = False, $historyInc) {
		global $pathToRoot ;
		// Turn the string back into an array
		$setContents = unserialize($setContentsFileContents) ;
		$STPath = $pathToRoot . CMSDIRNAME . '/' . STDIR . $setContents[1] ;
		if (!file_exists($STPath)) return ;
		$templateContents = unserialize(file_get_contents($STPath)) ;
		
		// Item [2] now lists the column names and data types.. We use this to map the data to the right names.
		$columns = $setContents[2] ;
		$numCols = sizeOf($columns) ;
		$setContentsOutput = '' ;
		$contentsToShow = False ;
		
		// For each line in the Set
		reset($setContents) ;
		// var_dump($setContents) ;
		
		
		for ($item=3; $item<sizeOf($setContents); $item++) {
			// For each major repeated block
			for ($repeatedBlock=0; $repeatedBlock<sizeOf($templateContents['repeated']); $repeatedBlock++) {
				
				// Then for each of the 3 alternatives in the block
				for ($alternativeOption=0; $alternativeOption<sizeOf($templateContents['repeated'][$repeatedBlock]); $alternativeOption++) {
				
					$thisBlockStillOK = True ;
					
					$thisRepeatBlock = $templateContents['repeated'][$repeatedBlock][$alternativeOption] ;
					
					// Parse out the required column names in each repeat block
					$thisBlockRequiredCols = array() ;
					
					// !!! BUG here: Messes up adjacent includes!!
					// Need to be more specific about permitted characters, as currently needs a space between or runs all chars together
					// Note added regex assertions to exclude [[ ... ]]
					// Added - to allow dashes, and \s to include spaces in column names
					preg_match_all('/(?<!\[)\[\s*?([\w-\s]+)\s*?\](?!\])/', $thisRepeatBlock, $requiredColumnsFound, PREG_SET_ORDER) ;
					
					for ($rptItem=0; $rptItem<sizeOf($requiredColumnsFound); $rptItem++) {
						// $matches[$rptItem][1] returns the name of the required colum
						array_push($thisBlockRequiredCols,  trim($requiredColumnsFound[$rptItem][1]) ) ;
					}
					
					/* var_dump($thisBlockRequiredCols) ;*/
					
					// For each required item
					for ($rqCol=0; $rqCol<sizeOf($thisBlockRequiredCols); $rqCol++) {
						$rqdColName = $thisBlockRequiredCols[$rqCol] ;
						// Get the position of the relevant column
						for ($iCol=0; $iCol<$numCols; $iCol++) {
							if ($columns[$iCol][0] == $rqdColName) {
								$rqdColPos = $iCol ;
								$isImage = ($columns[$iCol][1] == 'image') ? True : False ;
								$isLink = ($columns[$iCol][1] == 'link') ? True : False ;
								$isFile = ($columns[$iCol][1] == 'file') ? True : False ;
								$isLongtext = ($columns[$iCol][1] == 'longtext') ? True : False ;
							}
						}
						if (!isSet($isImage)) continue ;
						// Get value from corresponding position in Set line
						// If value exists, we're good
						$setContentsValue = @$setContents[$item][$rqdColPos] ;
						if ($isImage) {
							$setContentsValue = $pathToRoot . $setContentsValue ;
						}
						else if ($isLink && strpos($setContentsValue, '/') !== 0 && strpos($setContentsValue, 'http') !== 0) {
							$setContentsValue = $pathToRoot.$setContentsValue ;
						}
						else if ($isFile) {
							$setContentsValue = $pathToRoot . '/cmsfiles/' . $setContentsValue ;
						}
						
						if (!$setContentsValue || !strlen($setContentsValue)) {
							$thisBlockStillOK = False ;
						}
						else {
							
							/* 
								Where the parameter fields are in the relevant array!!!
								The values we want are in $templateContents[n][2] and $templateContents[n][3] ;
							*/
							/* 
							NO THUMBNAILS YET...
							if ($columns[$rqCol][1] == 'thumbnail') {
								$param1 = False ;
								$param2 = False ;
								for ($tcItems=0; $tcItems<sizeOf($templateContents["cols"]); $tcItems++) {
									if ($templateContents["cols"][$tcItems][0] == $rqdColName) {
										$param1 = $templateContents["cols"][$tcItems][2] ;
										$param2 = $templateContents["cols"][$tcItems][3] ;
									}
								}
								$setContentsValue = 'cms/phpthumb.php?src=' . REALIMGDIR . '/' . $setContents[$item][$rqdColPos] ;
								if ($param1) {
									$setContentsValue .= '&w=' . $param1 ;
								}
								if ($param1) {
									$setContentsValue .= '&h=' . $param2 ;
								}
							}
							*/
							
							// Change to regex?? Need to accommodate any number of spaces
							$thisRepeatBlock = str_replace('['. $rqdColName.']', $setContentsValue, stripslashes($thisRepeatBlock)) ;
							$thisRepeatBlock = str_replace('[ '. $rqdColName.' ]', $setContentsValue, $thisRepeatBlock) ;
							$thisRepeatBlock = str_replace('[[', '[', $thisRepeatBlock) ;
							$thisRepeatBlock = str_replace(']]', ']', $thisRepeatBlock) ;
							if ($isLongtext) $thisRepeatBlock = autop($thisRepeatBlock) ;
						}
						
					}
					if ($thisBlockStillOK) {
						$thisRepeatBlockArray = nestIncludes($thisRepeatBlock, $historyInc) ;
						$setContentsOutput .= $thisRepeatBlockArray[0] . "\n" ;
						$contentsToShow = True ;
						break ;
					}
				}
			}
		}
		
		if ($contentsToShow) {
			$beforeValArray = nestIncludes(stripslashes($templateContents['before']), $historyInc) ;
			$outputHTML  = $beforeValArray[0] . "\n" ;
			$outputHTML .= stripslashes(stripslashes($setContentsOutput)) ;
			$afterValArray = nestIncludes(stripslashes($templateContents['after']), $historyInc) ;
			$outputHTML .= $afterValArray[0] . "\n" ;
		}
		else {
			$outputHTML = stripslashes($templateContents['else']) ;
		}
		if ($justReturnValue) return $outputHTML ;
		else echo $outputHTML ;
	}
	
	if ($mode == "preview") {
		if (!file_exists($pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . basename($_SERVER["SCRIPT_NAME"]))) {
			echo "<strong>Error. No such file:</strong> " . $pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . basename($_SERVER["SCRIPT_NAME"]) ;
			exit ;
		}
		include($pathToRoot . CMSDIRNAME . '/includes/' . $whereWeAre . basename($_SERVER["SCRIPT_NAME"])) ;
		exit ;
	}
?>