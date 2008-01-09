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
	/* 
		Expects:
			$_GET['file']  =  Path to preview version of file
	*/
	require 'check-login.php' ;
	require ('../cmsfns.php') ;
	
	if (isset($_GET['file'])) {
		$liveFile = pathFromID($_GET['file']) ;
		$file = getPreviewFileFromLive($liveFile) ;
		
		if (False === file_exists($file)) {
			copy ($liveFile, $file) ;
		}
		
		// Read in file contents
		$fileContents = @file_get_contents($file) ;
		if ($fileContents === False) {
			header('error.php?msg=couldntreadsetfilecontents&detail=' . $file) ;
			exit ;
		}
	}
	else {
		header('error.php?msg=couldntreadsetfile&detail=' . $_GET['file']) ;
		exit ;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript" language="javascript" src="edit-set.js"></script>
	<script type="text/javascript">
	<!--
		function submitForm() {
			if (!document.getElementsByTagName) return false ;
			myForm = document.getElementsByTagName('form')[0] ;
			if (myForm) myForm.submit() ;
		}
		function banish() {
			top.destroyTabAndPanel("<? echo $_GET['file'] ; ?>") ;
		}
		<?
			// Convert file contents to array?
			$inputArray = unserialize($fileContents) ;
			if (!is_array($inputArray)) {
				// Create blank array
				$inputArray = array() ;
			}
			
			// Loop through array items, and create JS calls to populate form
			reset ($inputArray) ;
			if (sizeOf($inputArray) > 1) {
				$numCols = sizeOf($inputArray[2]) ;
				$numRows = sizeOf($inputArray) ;
				// Make calls to addColHead for each item in row 1
				
				echo 'function setup2() {' ;
				
				for ($header=0; $header<$numCols; $header++) {
					echo 'addColumn("' . $inputArray[2][$header][0] . '","' . $inputArray[2][$header][1] . '") ;' . "\n" ;
				}
				for ($line=3; $line<$numRows; $line++) {
					// Add a row
					echo "var newRow = addRow(false);\n" ;
					for ($col=0; $col<$numCols; $col++) {
						// Add td
						echo 'addDataCell(newRow, "' . $inputArray[2][$col][1] . '","' . nl2br2(stripSlashes($inputArray[$line][$col])) . '") ;' . "\n" ;
					}
				}
				
				echo 'setRowButtons() ;' ;
				
				echo '}' ;
			}
			if ($_SESSION['loginStatus'] > 1) {
				echo 'var adminStatus = true ;' ;
			}
			else {
				echo 'var adminStatus = false ;' ;
			}
		?>
	// -->
	</script>
	<style type="text/css">
		body {
			background:#fff;
			margin-left:2em;
		}
	</style>
</head>
<body>

<form action="save-set.php" method="post" id="setForm">

	<div>
		<button type="button" onclick="banish();"><? echo translate('Cancel') ; ?></button>
		<button type="button" onclick="submitForm();" style="width:10em; margin-left:3em;"><? echo translate('Save &amp; Close') ; ?></button>
	</div>

<h1>Edit set: <? echo stripFileExtension(basename(pathFromID($file))) ;  ?></h1>

<table border="0" cellpadding="0" cellspacing="0" id="setGrid" style="display:none;">
	<thead>
		<tr id="tableHeadRow"></tr>
	</thead>
	<tbody id="tableBody">
	</tbody>
</table>

<button type="button" onClick="addRowManual(true);" id="addRowsButton">Add Row</button>
<?
	if ($_SESSION['loginStatus'] > 1) {
		echo '<button title="Add column" id="addColumnButton" type="button" onClick="getNewColDetails();">Add column</button>' ;
	}
?>

<div style="margin-top:1em;">
<?
	echo '<input type="hidden" name="sourceFile" value="' . $_GET['file'] . '" />' ;
	echo '<input type="hidden" name="setTemplate" value="' . $inputArray[0] . '" />' ;
	echo '<input type="hidden" name="formattingTemplate" value="' . $inputArray[1] . '" />' ;
	
	
	
	if ($_SESSION['loginStatus'] > 1) {
		// Show rendering set as drop-down
		$handle = opendir(STDIR) ;
		
		$itemsFound = 0 ;
		$newSetHTML = '' ;
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$itemsFound++ ;
				if ($itemsFound == 1) echo  '<strong>Format using</strong>&nbsp;<select name="formattingTemplate">' ;
				echo  '<option value="' . $file . '"' ;
				if ($file == $inputArray[1]) echo ' selected="selected"' ;
				echo '>' ;
				echo stripFileExtension($file) ;
				if ($file == $inputArray[0]) echo ' (default)' ;
				echo '</option>' ;
			}
		}
		echo  '</select><br /><br />' ;
	}
	
?>
</div>
</form>

<div class="popup" id="newColPopup">
	<form id="newCol" onSubmit="return submitNewColDetails();">
		<div style="margin:.5em;">
			<label for="newColName">New column name</label><br />
			<input type="text" name="newColName" id="newColName" />
		</div>
		
		<div style="margin:.5em;">
			<label for="newColType">Data type</label><br />
			<select name="newColType" id="newColType">
				<option value="text">Text (short)</option>
				<option value="longtext">Long text</option>
				<option value="image">Image</option>
				<option value="link">Link</option>
			</select>
		</div>
		<div style="float:left;"><button type="button" onClick="cancelNewColDetails();">Cancel</button></div>
		<div style="text-align:center; margin:.5em;"><button type="button" onClick="submitNewColDetails();">Add column</button></div>
	</form>
</div>

<div class="popup" id="editLongTextPopup" style="width:80%; margin-left:-40%; z-index:200; margin-top:-5%;">
	<form id="editLongText" onSubmit="return saveLongText();">
		<textarea id="longTextArea" style="width:100%; height:30em; margin-bottom:1em;"></textarea>
		<div style="text-align:center;">
			<button type="button" onClick="hideLongTextEditor();">Cancel</button>
			&nbsp; &nbsp; 
			<button type="button" onClick="saveLongText();">Save text &raquo;</button>
		</div>
	</form>
</div>

</body>
</html>