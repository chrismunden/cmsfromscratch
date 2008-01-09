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
 
 
	Header("content-type: application/x-javascript") ;
	$sesh = @session_start() ;
	
	if (isSet($_SESSION['lang'])) {
		$langArray = $_SESSION['lang'] ;
		// Loop through the array and write a correponding JS array..
		echo 'var translateArray = new Object;' . "\n" ;
		while (list($key, $val) = each($langArray)) {
			echo 'translateArray["' . $key . '"] = "' . html_entity_decode($val) . '" ;' . "\n" ;
		}
	}
?>

function translate(textToTranslate) {
	if (typeof(window['translateArray']) == "undefined") return textToTranslate ;
	else if (!translateArray[textToTranslate]) return textToTranslate ;
	else return fixUTF(translateArray[textToTranslate]) ;
}

function fixUTF(data) {
	data = data.replace(/&#(\d+);/g,
		function(wholematch, parenmatch1) {
			return String.fromCharCode(+parenmatch1);
		});
	return data ;
}