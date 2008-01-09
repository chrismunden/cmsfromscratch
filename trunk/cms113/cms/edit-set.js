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
	To do:
		- Only admins can delete columns
*/

var myTable ;
var tableHeadRow ;
var newColPopup ;
var addColumnButton ;
var tableBody ;
var tableRows ;
var colHeads ;
var currentSelectedImage ;
var currentSelectedPageLink ;
var currentLongText ;
var currentDisplayText ;

function setup() {
	myTable = document.getElementById('setGrid') ;
	tableHeadRow = document.getElementById('tableHeadRow') ;
	newColPopup = document.getElementById('newColPopup') ;
	addColumnButton = document.getElementById('addColumnButton') ;
	tableBody = document.getElementById('tableBody') ;
	
	getRows() ;
	
	// Run commands generated in PHP to set up rows & columns
	setup2() ;
	
	getCols() ;
	// If there are no columns, prompt for new column
	if (countCols() == 0) {
		getNewColDetails() ;
	}
	ensureOneRow() ;
}

function addColHead(colName, colDataType, checkOneRow) {
	var newTH = document.createElement("th") ;
	// Also add delete link to column (admins only)
	if (adminStatus) {
		var newDelLink = document.createElement("a") ;
		newDelLink.appendChild(document.createTextNode("x")) ;
		newDelLink.className = "delete" ;
		newDelLink.href = "javascript:void('');"
		newDelLink.title = "Delete column (no undo!)" ;
		addEvent(newDelLink, "click", confirmDeleteColumn, false) ;
		newTH.appendChild(newDelLink) ;
	}
	var newText = document.createTextNode(colName) ;
	newTH.appendChild(newText) ;
	// Also put in hidden fields
	var newColNameField = document.createElement("input") ;
	newColNameField.type = "hidden" ;
	newColNameField.name = "colName[]" ;
	newColNameField.value = colName ;
	newTH.appendChild(newColNameField) ;
	var newColDataTypeField = document.createElement("input") ;
	newColDataTypeField.type = "hidden" ;
	newColDataTypeField.name = "colDataType[]" ;
	newColDataTypeField.value = colDataType ;
	newTH.appendChild(newColDataTypeField) ;
	tableHeadRow.appendChild(newTH) ;
}

function addColumn(newColName,newColType) {
	addColHead(newColName,newColType) ;
	if (countRows() > 0) {
		for (var iRow=0; iRow<tableRows.length; iRow++) {
			// alert(iRow) ;
			// *** For each row, add a TD with the appropriate inputs in
			addDataCell(tableRows[iRow], newColType, null) ;
		}
	}
	showTable() ;
	// Hide div
	newColPopup.style.display = "none" ;
	// Reset new col name input value
	document.getElementById('newColName').value = "" ;
	getCols() ;
}

function confirmDeleteColumn(e) {
	if (confirm("Are you sure you want to delete this column?\nThere is no undo.")) deleteColumn(e) ;
}
function deleteColumn(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	// Get clicked TH
	var targetColHead = climbDom(target,"th") ;
	// Find position of TH in columns
	var alltableHeadRow = tableHeadRow.getElementsByTagName("th") ;
	var thIndex = -1 ;
	for (eachTH in alltableHeadRow) {
		if (alltableHeadRow[eachTH] === targetColHead) {
			thIndex = eachTH ;
		}
	}
	if (thIndex == -1) return false ; // i.e. No match found
	// Delete appropriate TD from each row
	for (iRow=0; iRow<tableRows.length; iRow++) {
		if (tableRows[iRow].getElementsByTagName("td")[thIndex]) {
			tableRows[iRow].removeChild(tableRows[iRow].getElementsByTagName("td")[thIndex]) ;
		}
	}
	// And delete the TH in the head
	tableHeadRow.removeChild(tableHeadRow.getElementsByTagName("th")[thIndex]) ;
	getCols() ;
	// If there are no columns, hide the table and the "add Rows" button
	if (countCols() == 0) {
		hideTable() ;
	}
}

function addRow(isBlank) {
	var newTD, colDataType, colName ;
	var newRow = document.createElement("tr") ;
	if (isBlank == true) {
		// For each column, create a new TD with the appropriate inputs in
		var numCols = countCols() ;
		for (var iCol=0; iCol<numCols; iCol++) {
			colDataType = colHeads[iCol].getElementsByTagName("input")[1].value ;
			addDataCell(newRow, colDataType, "") ;
		}
	}
	var newUpCell = document.createElement("th") ;
	var newUpLink = document.createElement("a") ;
		newUpLink.className = "up" ;
		newUpLink.href = "javascript:void('');"
		newUpLink.innerHTML = '<img src="images/arrow_up.gif" class="uparrow" alt="Move row up" width="25" height="14" border="0" />' ;
		newUpLink.title = "Move row up" ;
		addEvent(newUpLink, "click", moveRowUp, false) ;
	newUpCell.appendChild(newUpLink) ;
	newRow.appendChild(newUpCell) ;
	
	var newDownCell = document.createElement("th") ;
	var newDownLink = document.createElement("a") ;
		newDownLink.className = "down" ;
		newDownLink.href = "javascript:void('');"
		newDownLink.innerHTML = '<img src="images/arrow_down.gif" class="downarrow" alt="Move row down" width="25" height="14" border="0" />' ;
		newDownLink.title = "Move row down" ;
		addEvent(newDownLink, "click", moveRowDown, false) ;
	newDownCell.appendChild(newDownLink) ;
	newRow.appendChild(newDownCell) ;
	
	var newDelCell = document.createElement("th") ;
		newDelCell.className = "setRowControls" ;
	var newDelLink = document.createElement("a") ;
		newDelLink.className = "delete" ;
		newDelLink.style.float = "right;" ;
		newDelLink.href = "javascript:void('');"
		newDelLink.innerHTML = "x" ;
		newDelLink.title = "Delete row (no undo!)" ;
		addEvent(newDelLink, "click", confirmDeleteRow, false) ;
	newDelCell.appendChild(newDelLink) ;
	newRow.appendChild(newDelCell) ;
	
	tableBody.appendChild(newRow) ;
	getRows() ;
	return newRow ;
}

function addRowManual(isBlank) {
	addRow(isBlank) ;
	setRowButtons() ;
}

function addDataCell(targetRow, dataType, fieldValue) {
	if (!targetRow) return false ;
	if (!fieldValue) fieldValue = "" ;
	// Create new TD, insert appropriate inputs depending on dataType, and attach to row
	newTD = document.createElement("td") ;
	switch (dataType) {
		case "text" :
			var newTextInput = document.createElement("input") ;
				newTextInput.type = "text" ;
				newTextInput.name = "val[]" ;
				newTextInput.value = fieldValue ;
				newTextInput.style.width = "20em;" ;
			newTD.appendChild(newTextInput) ;
		break ;
		case "longtext" :
			var newHiddenInput = document.createElement("input") ;
				newHiddenInput.type = "hidden" ;
				newHiddenInput.name = "val[]" ;
				newHiddenInput.value = fieldValue ;
			newTD.appendChild(newHiddenInput) ;
			var newTextInput = document.createElement("input") ;
				newTextInput.type = "text" ;
				newTextInput.name = "" ;
				newTextInput.value = stripLineBreaks(fieldValue, ' ') ;
				newTextInput.style.width = "15em;" ;
				newTextInput.readonly = "readonly" ;
				addEvent(newTextInput, "click", launchLongTextEditor, false) ;
			newTD.appendChild(newTextInput) ;
			/* var newLTButton = document.createElement("button") ;
				newLTButton.innerHTML = "&nbsp;Edit&nbsp;" ;
				newLTButton.type = "button" ;
				addEvent(newLTButton, "click", launchLongTextEditor, false) ;
			newTD.appendChild(newLTButton) ;*/
		break ;
		case "image" :
			var newHiddenInput = document.createElement("input") ;
				newHiddenInput.type = "hidden" ;
				newHiddenInput.name = "val[]" ;
				newHiddenInput.value = fieldValue ;
			newTD.appendChild(newHiddenInput) ;
			var newBlankImage = document.createElement("img") ;
				if (fieldValue.length > 0) {
					newBlankImage.src = fieldValue ;
					newBlankImage.title = fieldValue ;
				}
				else {
					newBlankImage.src = "images/t.gif" ;
				}
				newBlankImage.className = "setImage" ;
				addEvent(newBlankImage, "click", selectImageEditor, false) ;
			newTD.appendChild(newBlankImage) ;
		break ;
		case "thumbnail" :
			var newHiddenInput = document.createElement("input") ;
				newHiddenInput.type = "hidden" ;
				newHiddenInput.name = "val[]" ;
				newHiddenInput.value = fieldValue ;
			newTD.appendChild(newHiddenInput) ;
			var newBlankThumb = document.createElement("img") ;
				if (fieldValue.length > 0) {
					newBlankThumb.src = "cmsimages/" + fieldValue ;
					newBlankThumb.title = fieldValue ;
				}
				else {
					newBlankThumb.src = "images/t.gif" ;
				}
				newBlankThumb.alt = "Blank" ;
				newBlankThumb.className = "setImage" ;
				addEvent(newBlankThumb, "click", selectImageEditor, false) ;
			newTD.appendChild(newBlankThumb) ;
		break ;
		case "link" :
			var newTextInput = document.createElement("input") ;
				newTextInput.type = "text" ;
				newTextInput.name = "val[]" ;
				newTextInput.value = fieldValue ;
			newTD.appendChild(newTextInput) ;
			var newSelectLinkButton = document.createElement("button") ;
				newSelectLinkButton.type = "button" ;
				newSelectLinkButton.innerHTML = "..." ;
				newSelectLinkButton.style.margin = "0 .5em;" ;
				addEvent(newSelectLinkButton, "click", selectLink, false) ;
			newTD.appendChild(newSelectLinkButton) ;
		break ;
		case "file" :
			var newTextInput = document.createElement("input") ;
				newTextInput.type = "text" ;
				newTextInput.name = "val[]" ;
				newTextInput.value = fieldValue ;
			newTD.appendChild(newTextInput) ;
			var newSelectLinkButton = document.createElement("button") ;
				newSelectLinkButton.type = "button" ;
				newSelectLinkButton.innerHTML = "..." ;
				newSelectLinkButton.style.margin = "0 .5em;" ;
				addEvent(newSelectLinkButton, "click", selectFile, false) ;
			newTD.appendChild(newSelectLinkButton) ;
		break ;
	}
	var deleteRowCell = targetRow.getElementsByTagName("th")[0] ;
	targetRow.insertBefore(newTD, deleteRowCell) ;
}


function launchWin(url,w,h,type,winName) {   // false type is stripped down, true is normal toolbar window
	if(type==false) {
		opening=window.open(url,winName,"width="+w+",height="+h+",toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,directories=no,status=no");
	}
       else {
		opening=window.open(url,winName,"width="+w+",height="+h+",toolbar=yes,menubar=yes,scrollbars=yes,resizable=yes,location=yes,directories=yes,status=yes");
	}
}

function confirmDeleteRow(e) {
	if (confirm("Are you sure you want to delete this row?\nThere is no undo.")) deleteRow(e) ;
}
function deleteRow(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	var myRow = climbDom(target, "tr") ;
	if (!myRow) return false ;
	myRow.parentNode.removeChild(myRow) ;
	setRowButtons() ;
}

function getNewColDetails() {
	// Show div...
	newColPopup.style.display = "block" ;
}

function cancelNewColDetails() {
	newColPopup.style.display = "none" ;
}

function submitNewColDetails() {
	addColumn(document.getElementById('newColName').value, document.getElementById('newColType').value) ;
	return false ;
}

function showTable() {
	myTable.style.display = "block" ;
	document.getElementById('addRowsButton').style.display = "inline" ;
}
function hideTable() {
	myTable.style.display = "none" ;
	document.getElementById('addRowsButton').style.display = "none" ;
}

function getRows() {
	tableRows = tableBody.getElementsByTagName("tr") ;
}

function getCols() {
	colHeads = tableHeadRow.getElementsByTagName("th") ;
}

function countCols() {
	return colHeads.length ;
}

function countRows() {
	return tableRows.length ;
}

function ensureOneRow() {
	// If there are no rows, create one blank row
	if (countRows() == 0) {
		addRow(true) ;
	}
}

function selectImageEditor(e) {
	currentSelectedImage = getTarget(e) ;
	if (!currentSelectedImage) return false ;
	launchWin('select-image.php',550,400,false,'selectimage') ;
}
function sentImage(someID) {
	if (!currentSelectedImage) return false ;
	currentSelectedImage.src = someID ;
	currentSelectedImage.title = someID ;
	// Set value of hidden val[] field
	var parentTD = climbDom(currentSelectedImage,"td") ;
	if (!parentTD) return false ;
	var hiddenField = parentTD.getElementsByTagName("input")[0] ;
	hiddenField.value = someID ;
	currentSelectedImage = false ;
}

function selectLink(e) {
	currentSelectedPageLink = getTarget(e) ;
	if (!currentSelectedPageLink) return false ;
	launchWin('select-page.php',550,400,false,'selectpage') ;
}
function selectFile(e) {
	currentSelectedPageLink = getTarget(e) ;
	if (!currentSelectedPageLink) return false ;
	launchWin('select-page.php?dir=cmsfiles',550,400,false,'selectfile') ;
}
function sentLink(linkID) {
	// Find input in same cell as the button that was clicked
	var myTD = climbDom(currentSelectedPageLink, "td") ;
	if (!myTD) return false ;
	// Strip off leading "../"
	if (linkID.indexOf("../") == 0) {
		linkID = linkID.substring(3) ;
	}
	myTD.getElementsByTagName("input")[0].value = linkID ;
}

function setRowButtons() {
	for (var nRow=0; nRow<tableRows.length; nRow++) {
		var rowImages = tableRows[nRow].getElementsByTagName('img') ;
		for (var iImg=0; iImg < rowImages.length; iImg++) {
			if (nRow==0 && classContains(rowImages[iImg], "uparrow")) {
				addClass(rowImages[iImg], "hidden") ;
			}
			else if (nRow == tableRows.length-1 && classContains(rowImages[iImg], "downarrow")) {
				addClass(rowImages[iImg], "hidden") ;
			}
			else if (  (classContains(rowImages[iImg], "uparrow") || classContains(rowImages[iImg], "downarrow") && classContains(rowImages[iImg], "hidden") ) ) {
				stripClass(rowImages[iImg], "hidden") ;
			}
		}
	}
}

function moveRowUp(e) {
	var target=getTarget(e) ;
	if (!target) return false ;
	var targetRow = climbDom(target, 'tr') ;
	for (var iRow=0; iRow<tableRows.length; iRow++) {
		if (tableRows[iRow] === targetRow) {
			break ;
		}
	}
	tableRows[iRow].parentNode.insertBefore(tableRows[iRow], tableRows[iRow-1]) ;
	setRowButtons()
}

function moveRowDown(e) {
	var target=getTarget(e) ;
	if (!target) return false ;
	var targetRow = climbDom(target, 'tr') ;
	for (var iRow=0; iRow<tableRows.length; iRow++) {
		if (tableRows[iRow] === targetRow) {
			break ;
		}
	}
	tableRows[iRow].parentNode.insertBefore(tableRows[iRow+1], tableRows[iRow]) ;
	setRowButtons()
}

function launchLongTextEditor(e) {
	var target=getTarget(e) ;
	if (!target) return false ;
	// Get the current value of the text..
	var parentCell = climbDom(target,"td") ;
	currentLongText = parentCell.getElementsByTagName("input")[0] ;
	currentDisplayText = parentCell.getElementsByTagName("input")[1] ;
	if (!currentLongText) return false ;
	if (currentLongText.value.length != currentDisplayText.value) {
		document.getElementById('longTextArea').innerHTML = stripLineBreaks(currentLongText.value, "\n") ;
	}
	else {
		document.getElementById('longTextArea').innerHTML = stripLineBreaks(currentDisplayText.value, "\n") ;
	}
	document.getElementById('editLongTextPopup').style.display = "block" ;
	document.getElementById('longTextArea').focus() ;
}

function hideLongTextEditor() {
	document.getElementById('editLongTextPopup').style.display = "none" ;
}

function stripLineBreaks(inputString, replaceWith) {
	return inputString.replace(/%%linebreak%%/g, replaceWith) ;
}

function saveLongText() {
	currentLongText.value = document.getElementById('longTextArea').value ;
	currentDisplayText.value = document.getElementById('longTextArea').value ;
	hideLongTextEditor() ;
}

addEvent(window, "load", setup, false) ;