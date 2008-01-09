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
 
 
	header("Content-type: text/xml") ;
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
 	if (!isset($_GET['dir'])) exit ;
	// Strip off trailing slash if one exists
	define ("DIR", rtrim($_GET['dir'],'/')) ;
	
	$xmlr = '<xml>' ;
	$xmlr .= '<parentdir>' . DIR . '</parentdir>' ;
	
	$handle = opendir(pathFromID(DIR)) ;
	
	$arrDirs = array() ;
	$arrFiles = array() ;
	
	while (false !== ($file = readdir($handle))) {
		if (!in_array($file,$reserved_filenames) && !in_array(DIR.'/'.$file, $reserved_filenames)) {
			if (is_dir(DIR.'/'.$file)) {
				// Directory
				if ($_SESSION['loginStatus'] < 2 && DIR.'/'.$file == '../private') continue ;
				array_push($arrDirs, $file) ;
			}
			else {
				array_push($arrFiles, $file) ;
			}
		}
	}
	// Sort the 2 arrays and write contents to XML response
	asort($arrDirs) ;
	asort($arrFiles) ;
	
	foreach ($arrDirs as $arrDir) {
		if (
			(isSet($_SESSION['showNonCMSFiles']) && $_SESSION['showNonCMSFiles'] == 'yes')
			||
			(
				is_dir(getPreviewFileFromLive(DIR . '/' . $arrDir))
				&& $arrDir != '.'
				&& $arrDir != '..'
				&& False == stristr($arrDir, ".gif,,$reserved_filenames")
				)
			)
		{
			$xmlr .= "<dir>$arrDir</dir>" ;
		}
	}
	foreach ($arrFiles as $arrFile) {
		$previewFile = getPreviewFileFromLive(DIR.'/'.$arrFile) ;
		if (
			strstr($arrFile, 'phpthumb')
			|| strstr($arrFile, 'phpThumb')
			// || False === file_exists($previewFile)
			)
		{
			continue ;
		}
		
		$liveFile = DIR.'/'.$arrFile ;
		if (file_exists($liveFile)) {
			if (False === file_exists($previewFile)) {
				$filesMatch = 'liveonly' ;
			}
			else {
				$filesMatch = (md5_file($previewFile) == md5_file($liveFile)) ? 'match' : 'dontmatch' ;
			}
		}
		else if (file_exists($previewFile)) {
			$filesMatch = 'previewonly' ;
		}
		
		/* 
			Get the preview version - do the versions match?
		*/
		
		if (getFileExtension($arrFile) == "php") {
			$xmlr .= '<page><name>' ;
			$xmlr .= $arrFile ;
			$xmlr .= '</name><match>' ;
			$xmlr .= $filesMatch ;
			$xmlr .= '</match></page>' ;
		}
		else {
			$xmlr .= '<file><name>' ;
			$xmlr .= $arrFile ;
			$xmlr .= '</name><match>' ;
			$xmlr .= $filesMatch ;
			$xmlr .= '</match></file>' ;
		}
	}
	$xmlr .= "</xml>" ;
	print $xmlr ;
?>