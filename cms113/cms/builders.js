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

function buildElement(elementType, elementText, elementClassName, elementID, elementInnerHTML) {
	// buildElement(type, innerText, className, id) ;
	if (!elementType) return ;
	var newElement = document.createElement(elementType) ;
	if (elementClassName) newElement.className = elementClassName ;
	if (elementID) newElement.id = elementID ;
	if (elementText) {
		var newElementText = document.createTextNode(elementText) ;
		newElement.appendChild(newElementText) ;
	}
	else if (elementInnerHTML) {
		newElement.innerHTML = elementInnerHTML ;
	}
	return newElement ;
}

function buildTinyLinksList(listID, filesMatch, type) {
	/* 
		Builds restore / publish / expand-collapse and edit links for Pages & FIs
	*/
	// if (filesMatch) alert(filesMatch) ;
	var folderNewListClassName = "tinyLinks" ;
	if (!filesMatch || filesMatch != "FILES_MATCH") {
		folderNewListClassName += " nomatch" ;
	}
	var newActionLinksList = buildElement("ol", "", folderNewListClassName, listID) ;
	if (canDeletePages) {
		newActionLinksList.appendChild(buildDeleteLink(type)) ;
	}
	// if (isPage) newActionLinksList.appendChild(buildPublishRestoreLink("publishAll")) ;
	if (!listID.indexOf("pagetemplates") == 0 && type != "folder") {
		if (filesMatch != "liveonly") {
			newActionLinksList.appendChild(buildPublishRestoreLink("publish")) ;
		}
		newActionLinksList.appendChild(buildPublishRestoreLink("restore")) ;
	}
	if (type == "page") {
		newActionLinksList.appendChild(buildCollapseLink()) ;
	}
	if (ls > 1) {
		newActionLinksList.appendChild(buildRenameLink()) ;
	}
	return newActionLinksList ;
}

function buildRenameLink() {
	var newLI = buildElement("li", "", "renameLink") ;
	var newARename = buildElement("a", "r", "") ;
		newARename.href = "javascript:void('');" ;
		newARename.title = "Rename file" ;
		addEvent(newARename, "click", clickRenameFile, false) ;
	newLI.appendChild(newARename) ;
	return newLI ;
}

function buildDeleteLink(type) {
	var newLI = buildElement("li", "", "deleteLink") ;
	var newADelete = buildElement("a", "x", "delete") ;
		newADelete.href = "javascript:void('');"
		newADelete.title = "Delete" ;
		if (type && type == "folder") {
			addEvent(newADelete, "click", folderAction, false) ;
		}
		else {
			addEvent(newADelete, "click", clickDeleteFile, false) ;
		}
	newLI.appendChild(newADelete) ;
	return newLI ;
}

function buildCollapseLink() {
	var newLI = buildElement("li", "", "collapseLink") ;
	var newAEdit = buildElement("a", "^", "collapse") ;
		newAEdit.href = "javascript:void('');"
		newAEdit.title = translate("Hide") ;
		addEvent(newAEdit, "click", collapseFile, false) ;
	newLI.appendChild(newAEdit) ;
	return newLI ;
}

function buildPublishRestoreLink(linkType) {
	if (linkType == "restore") {
		var newLI = buildElement("li", "", "restoreLink") ;
		var newA = buildElement("a", "<", "pubrstr restore")
			newA.title = translate("Restore") ;
			newA.href = "javascript:void('');"
			addEvent(newA, "click", restoreElement, false) ;
	}
	else if (linkType == "publish") {
		var newLI = buildElement("li", "", "publishLink") ;
		var newA = buildElement("a", ">", "pubrstr publish")
			newA.title = translate("Publish") ;
			newA.href = "javascript:void('');"
			addEvent(newA, "click", publishElement, false) ;
	}
	else {
		// publishAll
		var newLI = buildElement("li", "", "publishAllLink") ;
		var newA = buildElement("a", "> all", "pubrstr publishAll")
			newA.title = "Publish this page and all child includes" ;
			newA.href = "javascript:void('');"
			addEvent(newA, "click", publishAll, false) ;
	}
	newLI.appendChild(newA) ;
	return newLI ;
}


function buildLCI(lciFileName, parentPageID, filesMatch) {
	var newLILci = document.createElement("li") ;
		switch (getFileExtension(lciFileName)) {
			case "php" :
				var fileClass = "file page" ;
			break ;
			case "text" :
				var fileClass = "file text" ;
			break ;
			case "html" :
				var fileClass = "file html" ;
			break ;
			case "set" :
				var fileClass = "file set" ;
			break ;
		}
		newLILci.className = "lci " + fileClass ;
		// if last character isn't a slash, add one
		var goodPath = pathFromID(parentPageID) ;
		// PT LCIs should have an extra / between the path and the file name
		if (goodPath.indexOf("pagetemplates") == 0) {
			newLILci.id = makeID(stripFileExtension(goodPath) + "/" + lciFileName) ;
		}
		else {
			newLILci.id = makeID(getLciFolderPathFromLivePath(goodPath) + lciFileName) ;
		}
		newLiLciActionsListID = newLILci.id + "_actions" ;
		newLILci.appendChild(buildTinyLinksList(newLiLciActionsListID, filesMatch, "lci")) ;
		newLILciEditLink = buildElement("a", "", "", "") ;
		newLILciEditLink.innerHTML = lciFileName.replace(/ /g, "&nbsp;") ;
		newLILciEditLink.href = "javascript:void('');"
		addEvent(newLILciEditLink, "click", clickEdit, false) ;
		newLILci.appendChild(newLILciEditLink) ;
	return newLILci ;
}

function buildFolderNode(folderName, folderPrefix) {
	var newNodeID = makeID(folderPrefix + "/" + folderName) ;
	var newLI = buildElement("li", "", "dir", newNodeID) ;
		// Using innerHTML parameter?
		var newLink = buildElement("a", "", "", "", folderName) ;
		newLink.href = "javascript:void('');"
		addEvent(newLink, "click", clickFolder, false) ;
		newLI.appendChild(newLink) ;
		newLI.appendChild(buildTinyLinksList(newNodeID + "_minoractions", false, "folder")) ;
		addEvent(newLI, "mouseover", hoverMe, false) ;
		addEvent(newLI, "mouseout", unHoverMe, false) ;
	return newLI ;
}

function buildFileNode(fileName, parentElementID, filesMatch) {
	var editable = true ;
	switch (getFileExtension(fileName)) {
		case "php" :
			var fileClass = "file page" ;
		break ;
		case "text" :
			var fileClass = "file text" ;
		break ;
		case "html" :
			var fileClass = "file html" ;
		break ;
		case "set" :
			var fileClass = "file set" ;
		break ;
		default :
			var fileClass = "file" ;
			// editable = false ;
		break ;
	}
	fileClass += " " + filesMatch ;
	var newLI = buildElement("li", "", fileClass + " closed", makeID(parentElementID + "/" + fileName)) ;
	var newExpandLink = buildElement("a", fileName) ;
		newExpandLink.href = "javascript:void('');"
		newExpandLink.className = "expandFile" ;
		
/* 		if (filesMatch != "liveonly") {
			addEvent(newExpandLink, "click", clickFile, false) ;
		}
		else {
			newExpandLink.title = "File was not created in CMS. Cannot be edited." ;
		}*/
		
		addEvent(newExpandLink, "click", clickFile, false) ;
		
		newLI.appendChild(newExpandLink) ;
	return newLI ;
}

function buildNewList(isPartOfFolder, showDeleteDir, folderNewListID, showMakePT) {
	/* 
		Consider applying new-folder and delete-folder as part of the same loop??
		Just need including in a list
	*/
	var newFolderActionsList = buildElement("ol", "", "newList", folderNewListID) ;
	var inPTs = (folderNewListID.indexOf("pagetemplates") == 0) ? true : false ;
	for (iDT=0; iDT<dataTypes.length; iDT++) {
		// Skip "new page" if a child of a page..
		if (dataTypes[iDT].toLowerCase() == "page"){
			if (!isPartOfFolder || !canCreatePages) continue ;
		}
		else if (ls < 2) break ;
		var newOption = buildElement("li") ;
			var newClassName = "new_" + dataTypes[iDT] ;
			newClassName = newClassName.replace(/\s/g, "_") ;
			newClassName = newClassName.toLowerCase() ;
			newOption.className = newClassName ;
			var newOptionA = buildElement("a", translate("New " + dataTypes[iDT])) ;
				newOptionA.href = "javascript:void('');"
				newOptionA.title = translate("New " + dataTypes[iDT]) ;
				if (isPartOfFolder) {
					addEvent(newOptionA, "click", folderAction, false) ;
				}
				else {
					addEvent(newOptionA, "click", pageChildAction, false) ;
				}
			newOption.appendChild(newOptionA) ;
		newFolderActionsList.appendChild(newOption) ;
	}
	if (isPartOfFolder) {
		if (ls > 1) {
			// New folder link
			var newOption = buildElement("li", "", "new_folder") ;
				var newOptionA = buildElement("a", "New Folder") ;
					newOptionA.href = "javascript:void('');"
					newOptionA.title = "Create new folder" ;
					addEvent(newOptionA, "click", folderAction, false) ;
				newOption.appendChild(newOptionA) ;
			newFolderActionsList.appendChild(newOption) ;
	
			// If part of folder, include wrapped in a <li></li>
			var newLINew = buildElement("li", "", "folderNewList") ;
			addEvent(newLINew, "click", expandFolderActionsList, false) ;
			// Attach
			newLINew.appendChild(newFolderActionsList) ;
			// Add clearer
			var newClearer = buildElement("div", "", "clearer")	;
			newLINew.appendChild(newClearer) ;
			return newLINew ;
		}
	}
	else if (!inPTs) {
		if (showMakePT && ls > 1) {
			// Make PT from this page link
			var newOption2 = buildElement("li", "", "make_pt_from_page") ;
				var newOption2A = buildElement("a", "Make page template") ;
					newOption2A.href = "javascript:void('');"
					newOption2A.title = "Make page template from this page" ;
					addEvent(newOption2A, "click", makePTFromPage, false) ;
				newOption2.appendChild(newOption2A) ;
			newFolderActionsList.appendChild(newOption2) ;
		}
		// Preview Page link
		var newOption = buildElement("li", "", "preview_page") ;
			var newOptionA = buildElement("a", "Preview page") ;
			var pagePath = folderNewListID.substr(0, folderNewListID.indexOf("_actions")) ;
				newOptionA.href = pathFromID(pagePath) + "?mode=preview" ;
				newOptionA.target = "_blank" ;
				newOptionA.title = "Preview page" ;
			newOption.appendChild(newOptionA) ;
		newFolderActionsList.appendChild(newOption) ;
	}
	return newFolderActionsList ;
}

function buildTab(tabText, newID) {
	// If tab already exists with new ID, don't create a new one, just focus to the existing one
	if (document.getElementById("tab__" + newID)) {
		shuffleTabsAndPanels("tab__" + newID) ;
		return ;
	}
	if (tabText.length > 20) {
		tabText = tabText.substring(0, 12) + "..." + getFileExtension(tabText) ;
	}
	var newLI = buildElement("li", "", "newtab", "tab__" + newID) ;
	var newLink = buildElement("a", tabText) ;
		newLink.href = "javascript:void('');"
	addEvent(newLink, "click", shuffleTabsAndPanels, false) ;
	newLI.appendChild(newLink) ;
	document.getElementById("tabs").appendChild(newLI) ;
	shuffleTabsAndPanels(newLI) ;
}