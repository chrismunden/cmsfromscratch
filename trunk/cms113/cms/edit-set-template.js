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
function setup() {
	// Attach event to delete button
	deleteButtonEvent(document) ;
	checkDeleteButtons() ;
	// Attach event to 'add' block
	addRowButtonEvent(document) ;
	addColDeleteEvents() ;
	addShowHideParamsEvent() ;
	showHideParams() ;
}

function addShowHideParamsEvent() {
	var allFields = document.getElementById("colsTable").getElementsByTagName("input") ;
	for (eachField in allFields) {
		if (allFields[eachField].name == "colTypes[]") {
			addEvent(allFields[eachField], "change", showHideParams, false) ;
		}
	}
}

function showHideParams() {
	var allFields = document.getElementById("colsTable").getElementsByTagName("input") ;
	for (eachField in allFields) {
		if (allFields[eachField].name == "colTypes[]") {
			var parentRow = climbDom(allFields[eachField], "tr") ;
			var thisRowFields = parentRow.getElementsByTagName("input") ;
			for (eachSibling in thisRowFields) {
				if (thisRowFields[eachSibling].name == "param1[]" || thisRowFields[eachSibling].name == "param2[]") {
					thisRowFields[eachSibling].type = (allFields[eachField].value == "thumbnail") ? "text" : "hidden" ;
				}
			}
			var thisRowSpans = parentRow.getElementsByTagName("span") ;
			for (eachSibling in thisRowSpans) {
				if (thisRowSpans[eachSibling].style) {
					thisRowSpans[eachSibling].style.display = (allFields[eachField].value == "thumbnail") ? "inline" : "none" ;
				}
			}
		}
	}
}

function checkDeleteButtons() {
	// If there's only one repeated row, hide first delete button
	if (countRepeatedRows() == 1) {
		hideFirstDeleteButton() ;
	}
}

function deleteButtonEvent(element) {
	var allButtons = element.getElementsByTagName("button") ;
	for (eachBtn in allButtons) {
		if (allButtons[eachBtn].firstChild && allButtons[eachBtn].firstChild.nodeValue == "Delete") {
			addEvent(allButtons[eachBtn], "click", clickDelete, false) ;
		}
	}
}

function addRowButtonEvent(element) {
	var allButtons = element.getElementsByTagName("button") ;
	for (eachBtn in allButtons) {
		if (allButtons[eachBtn].firstChild && allButtons[eachBtn].firstChild.nodeValue == "Add repeated block") {
			addEvent(allButtons[eachBtn], "click", clickAdd, false) ;
		}
	}
}

function hideFirstDeleteButton() {
	var allButtons = document.getElementsByTagName("button") ;
	for (eachBtn in allButtons) {
		if (allButtons[eachBtn].firstChild && allButtons[eachBtn].firstChild.nodeValue == "Delete") {
			allButtons[eachBtn].style.display = "none" ;
			return true ;
		}
	}
}

function showDeleteButtons() {
	var allButtons = document.getElementsByTagName("button") ;
	for (eachBtn in allButtons) {
		if (allButtons[eachBtn].firstChild && allButtons[eachBtn].firstChild.nodeValue == "Delete") {
			allButtons[eachBtn].style.display = "inline" ;
		}
	}
}

function countRepeatedRows() {
	var myRows = document.getElementById('stTable').getElementsByTagName("tr") ;
	var rowCount = 0 ;
	for (eachRow in myRows) {
		if (classContains(myRows[eachRow], "repeated")) {
			rowCount++ ;
		}
	}
	return rowCount ;
}

function clickDelete(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	var killTR = climbDom(target, "tr") ;
	var parent = killTR.parentNode ;
	parent.removeChild(killTR) ;
	checkDeleteButtons() ;
}

function getFirstRepeatedRow() {
	var allRows = document.getElementById('stTable').getElementsByTagName("tr") ;
	for (eachRow in allRows) {
		if (classContains(allRows[eachRow], "repeated")) {
			return allRows[eachRow] ;
		}
	}
}

function clickAdd(e) {
	// Take a copy of the first 'repeated' row
	if (!document.getElementById('stTable').getElementsByTagName("tr")) return false ;
	var firstRow = getFirstRepeatedRow() ;
	var secondRow = firstRow.cloneNode(true) ;
	// Strip out any values of any textareas
	var newTAs = secondRow.getElementsByTagName("textarea") ;
	for (eachTA in newTAs) {
		newTAs[eachTA].value = "" ;
	}
	// Attach events to buttons
	deleteButtonEvent(secondRow) ;
	addRowButtonEvent(secondRow)
	// And append the new row to the end of the repeated rows
	document.getElementById('tableBody').insertBefore(secondRow, document.getElementById('afterRow')) ;
	showDeleteButtons() ;
}

function addColumn() {
	// Add a new row to the body
	// Has 2 TDs, each with a text input, colNames[] and colTypes[]
	// Get values for inputs from inputs in the foot
	var newColRow = document.createElement("tr") ;
	var newColTD1 = document.createElement("td") ;
		var newInput1 = document.createElement("input") ;
			newInput1.type = "text" ;
			newInput1.name = "colNames[]" ;
			newInput1.value = document.getElementById('newColName').value ;
		newColTD1.appendChild(newInput1) ;
	var newColTD2 = document.createElement("td") ;
	
		var newInput2 = document.createElement("select") ;
			newInput2.name = "colTypes[]" ;
		for (var i=0; i<setDataTypes.length; i++) {
			var newOption = document.createElement("option") ;
				newOption.value = setDataTypes[i] ;
				var newOptionText = document.createTextNode(setDataTypes[i]) ;
				newOption.appendChild(newOptionText) ;
				if (document.getElementById('newColType').value == setDataTypes[i]) {
					newOption.selected = "selected" ;
				}
			newInput2.appendChild(newOption) ;
		}
	
		/* var newInput2 = document.createElement("input") ;
			newInput2.type = "text" ;
			newInput2.name = "colTypes[]" ;
			newInput2.value = document.getElementById('newColType').value ;
		*/	
		newColTD2.appendChild(newInput2) ;
	var newColTD3 = document.createElement("th") ;
		var newDelLink = document.createElement("a") ;
			newDelLink.href="javascript:void('');" ;
			newDelLink.className = "delete" ;
			newDelLink.innerHTML = "x" ;
			addEvent(newDelLink, "click", deleteCol, false) ;
		newColTD3.appendChild(newDelLink) ;
	newColRow.appendChild(newColTD1) ;
	newColRow.appendChild(newColTD2) ;
	newColRow.appendChild(newColTD3) ;
	document.getElementById('colsListBody').appendChild(newColRow) ;
	// Reset the text input in the foot
	document.getElementById('newColName').value = "" ;
}

function deleteCol(e) {
	var target = getTarget(e) ;
	// if (!target) return false ;
	var wotRow = climbDom(target, "tr") ;
	// if (!wotRow) return false ;
	wotRow.parentNode.removeChild(wotRow) ;
}

function addColDeleteEvents() {
	var allLinks = document.getElementById('colsListBody').getElementsByTagName("a") ;
	for (var eachLink=0; eachLink < allLinks.length; eachLink++) {
		addEvent(allLinks[eachLink], "click", deleteCol, false) ;
	}
}

addEvent(window, "load", setup, false) ;