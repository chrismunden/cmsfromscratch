function makePTFromPage(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var newPTName = prompt("New Page Template name.") ;
	if (!newPTName || newPTName.length==0) return ;
	newPTName = tidyFileName(newPTName) ;
	var myPageID = climbDom(climbDom(target, "ol"), "li").id ;
	var caller = new net.ContentLoader("make-pt-from-page.php", madePTFromPage, null, "POST", "pagePath=" + myPageID + "&newPTName=" + newPTName) ;
}

function clickCreatePage() {
	if (!document.getElementById('newPageName')) return false ;
	var newPageName = tidyFileName(document.getElementById('newPageName').value) ;
	if (newPageName.length < 1) return false ;
	if (!document.getElementById('newPagePT')) return false ;
	var newPT = document.getElementById('newPagePT') ;
	var newPTVal = newPT.getElementsByTagName('option')[newPT.selectedIndex].value ;
	var newPagePathPrefix = document.getElementById('pagePath').value ;	
	var callerString = "new-page.php?pagepath=" + makeID(newPagePathPrefix + "/" + stripFileExtension(newPageName)) ;
	if (newPTVal.length) callerString += "&pt=" + makeID(stripFileExtension(newPTVal)) ;
	var caller = new net.ContentLoader(callerString, insertPage) ;
	hideDetailsForm() ;
	return false ; 
	cancelBubble(e) ;
	return false ;
}

function clickCreateSet() {
	if (!document.getElementById('newSetName')) return false ;
	var newSetName = document.getElementById('newSetName').value ;
	if (newSetName.length < 1) return false ;
	var newSetST = document.getElementById("newSetST") ;
	var newSetSTVal = newSetST.getElementsByTagName("option")[newSetST.selectedIndex].value ;
	var newSetFilePath = stripFileExtension(document.getElementById("newSetFilePath").value) ;
	var newSetParentPage = document.getElementById('newSetParentPage').value ;
	var newSetMode = document.getElementById('newSetMode').value ;
	if (newSetMode == "childinclude") {
		params = newSetName ;
		params += "&template=" + newSetSTVal ;
		params += "&parentpage=" + newSetParentPage ;
		params += "&newfilepath=" + newSetName ;
		var caller = new net.ContentLoader("new-set.php", insertLCI, null, "POST", params) ;
	}
	else {
		params = "newfilepath=" + newSetFilePath + "/" + newSetName ;
		params += "&template=" + newSetSTVal ;
		params += "&newfilepath=" + newSetFilePath + "/" + newSetName ;
		var caller = new net.ContentLoader("new-set.php", insertFreeInclude, null, "POST", params) ;
	}
	
	hideDetailsForm() ;
	// cancelBubble() ;
	return false ;
}

function clickNewPageTemplate() {
	var newPTName = prompt("New Page Template name") ;
	if (newPTName && newPTName.length) {
		// Call PHP script to create the new PT and its LCI folder
		newPTName = tidyFileName(newPTName) ;
		var newPTCall = new net.ContentLoader("new-page-template.php?newptname=" + newPTName, insertPage) ;
	}
}

function clickDeleteFile(e) {
	/* 
		Climb to LI
		If it doesn't have an ID, it's just a page label LI, so climb again to find the Page to delete
		If it does have an ID, it's probably a page LCI.
	*/
	var target = getTarget(e) ;
	if (!target) return false ;
	target = climbDom(target, "li") ;
	if(!target.id) {
		target = climbDom(target.parentNode, "li") ;
	}
	if (true === confirm("Are you sure you want to delete " + pathFromID(target.id) + "?\nThis cannot be undone.")) {
		var deleteFile = new net.ContentLoader("delete-file.php?file=" + target.id, deleteNode) ;
	}
}

function clickRenameFile(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	target = climbDom(target, "li") ;
	if(!target.id) {
		target = climbDom(target.parentNode, "li") ;
	}
	var newFileName = prompt("New file name") ;
	if (!newFileName || !newFileName.length) return ;	
	newFileName = tidyFileName(newFileName) ;
	var newTextFileCall = new net.ContentLoader("rename-file.php?filePath=" + target.id + "&newFileName=" + newFileName, fileRenamed) ;
}

function folderAction(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var parentLI = climbDom(target,"li") ;
	if (!parentLI) return ;
	cancelBubble(e) ;
	
	var parentFolder = getParentFolder(target) ;
	var localLI = climbDom(climbDom(target, "ol"), "li").id ;
	
	// May need to strip out on/over classes, just to leave action/type class..
	stripClass(parentLI, "hovering") ;
	var myClass = parentLI.className.trim() ;

	switch (myClass) {
		case "new_folder" :
			var newFolderName = prompt("New folder name") ;
			if (!newFolderName || !newFolderName.length) return ;
			newFolderName = tidyFileName(newFolderName) ;
			var newFolderCall = new net.ContentLoader("new-folder.php?dir=" + parentFolder + "/" + newFolderName, insertDir) ;
		break ;
		case "deleteLink" :
			if (confirm("Are you sure you want to delete the folder:\n" + pathFromID(localLI) + "?\nThere is no undo!")) {
				var killFolderCall = new net.ContentLoader("delete-folder.php?dir=" + localLI, deleteNode) ;
			}
		break ;
		case "new_text_include" :
			var newFileName = prompt("New text file name") ;
			if (newFileName && newFileName.length) {
				newFileName = tidyFileName(newFileName) ;
				if (getFileExtension(newFileName) == newFileName) {
					newFileName += ".text" ;
				}
				else if (getFileExtension(newFileName) != "text" && !confirm("Would you like to use the extension ." + getFileExtension(newFileName) + " instead of .text?\nClick cancel to use .text, or OK to use the file extension you entered.")) {
					newFileName = stripFileExtension(newFileName) + ".text" ;
				}
				var newTextFileCall = new net.ContentLoader("new-text-file.php?mode=freeinclude&newfilepath=" + makeID(parentFolder + "/" + newFileName), insertFreeInclude) ;
			}
		break ;
		case "new_html_include" :
			var newFileName = prompt("New HTML file name") ;
			if (newFileName && newFileName.length) {
				newFileName = tidyFileName(newFileName) ;
				var newTextFileCall = new net.ContentLoader("new-text-file.php?mode=freeinclude&newfilepath=" + makeID(parentFolder + "/" + stripFileExtension(newFileName) + ".html"), insertFreeInclude) ;
			}
			else return ;
		break ;
		case "new_content_set_include" :
			if (!checkSTsCount()) return ;
			showDetailsForm("newSet", parentFolder) ;
		break ;
		case "new_page" :
			showDetailsForm("newPage", parentFolder) ;
		break ;
		case "rename_file" :
			var newFileName = prompt("New file name") ;
			if (!newFileName || !newFileName.length) break ;
			newFileName = tidyFileName(newFileName) ;
			var newTextFileCall = new net.ContentLoader("rename-file.php?filePath=" + localLI + "&newFileName=" + newFileName, fileRenamed) ;
		break ;
		default:
			alert("Not handled: " + parentLI.className) ;
		break ;
	}
}

function checkSTsCount() {
	if (document.getElementById("newSetST").getElementsByTagName("option").length < 1) {
		alert("No Set Templates exist yet.\nYou must create a Set Template before you can create a Set.") ;
		return false ;
	}
	return true ;
}

function pageChildAction(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var parentLI = climbDom(target,"li") ;
	if (!parentLI) return ;
	cancelBubble(e) ;
	var objectPage = climbDom(parentLI.parentNode, "li", "file") ;
	var myClass = parentLI.className.toLowerCase().replace(/ ?(hovering ?)/g , "") ;
	
	switch (myClass) {
		case "new_text_include" :
			var newFileName = prompt("New text file name") ;
			var newTextFileCall = new net.ContentLoader("new-text-file.php?mode=childinclude&parentpage=" + pathFromID(objectPage.id) + "&newfilepath=" + stripFileExtension(newFileName) + ".text", insertLCI) ;
		break ;
		case "new_html_include" :
			var newFileName = prompt("New HTML file name") ;
			if (!newFileName || !newFileName.length) return ;
			newFileName = tidyFileName(newFileName) ;
			var newTextFileCall = new net.ContentLoader("new-text-file.php?mode=childinclude&parentpage=" + pathFromID(objectPage.id) + "&newfilepath=" + stripFileExtension(newFileName) + ".html", insertLCI) ;
		break ;
		case "new_content_set_include" :
			// Need to set parent page in hidden field
			if (!checkSTsCount()) return ;
			showDetailsForm("newSet", objectPage.id, objectPage.id, "childinclude") ;
		break ;	
		default :
			alert(parentLI.className.toLowerCase()) ;
		break ;
	}
	/* 
		Should pass through the full path to the LIVE file, e.g. includes/page_cms_files/cms_preview/filename.text ???
	*/
}

function restoreElement(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var parentLI = climbDom(climbDom(target.parentNode, "ol"), "li") ;
	var ajaxCall = new net.ContentLoader("publish.php?action=restore&file=" + pathFromID(parentLI.id), filesMatch) ;
}

function publishElement(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var parentLI = climbDom(climbDom(target.parentNode, "ol"), "li") ;
	var ajaxCall = new net.ContentLoader("publish.php?action=publish&file=" + pathFromID(parentLI.id), filesMatch) ;
}

function saveAndContinueTextEditor(buttonClicked) {
	saveTextEditor(buttonClicked, "true") ;
}

function saveTextEditor(buttonClicked, dontRefresh) {
	/* 
		Read contents of plain text editor, save to file...
	*/
	// url,onload,onerror,method,params,contentType
	var parentDiv = climbDom(buttonClicked, "div", "panel") ;
	var textEditorContents = parentDiv.getElementsByTagName('textarea')[0].value ;
	var sourceFile = parentDiv.id.substring(7) ; 
	// var textEditorContents = document.getElementById('textEditorTextarea').value ;
	var ajaxParams = "sourcefile=" + sourceFile ;
		ajaxParams += "&textcontent=" + encodeURIComponent(textEditorContents) ;
		if (dontRefresh) ajaxParams += "&dontrefresh=true" ;
	var ajaxCall = new net.ContentLoader("save-text.php", fileSaved, null, "POST", ajaxParams) ;
}

function clickSaveHTML(sourceFile, textContent) {
	var params = "sourcefile=" + makeID(sourceFile) + "&textcontent=" + encodeURIComponent(textContent) ;
	var ajaxCall = new net.ContentLoader("save-text.php", fileSaved, null, "POST", params) ;
}

function editText(elID) {
	// Call PHP script to return file contents to js fn
	var ajaxCall = new net.ContentLoader("edit-text.php?file=" + makeID(elID), renderTextEditor) ;
}

function editHTML(elID) {
	// Create a new tab and a new panel with iframe
	var htmlEditorTemplate = document.getElementById("templateHtmlEditor").innerHTML ;
	var newPanelID = "panel__" + makeID(elID) ;
	var newHTMLEditor = buildElement("div", "", "panel", newPanelID, htmlEditorTemplate) ;
	document.getElementById("pageBody").appendChild(newHTMLEditor) ;
	document.getElementById(newPanelID).getElementsByTagName("iframe")[0].src = "edit-html.php?file=" + makeID(elID) ;
	var splitPathArray = splitPath(pathFromID(elID)) ;
	buildTab(splitPathArray["file"], makeID(elID)) ;
	shuffleTabsAndPanels(newPanelID) ;
}

function editSet(elID) {
	// Create a new tab and a new panel with iframe
	var setEditorTemplate = document.getElementById("templateSetEditor").innerHTML ;
	var newPanelID = "panel__" + makeID(elID) ;
	var newSetEditor = buildElement("div", "", "panel", newPanelID, setEditorTemplate) ;
	document.getElementById("pageBody").appendChild(newSetEditor) ;
	document.getElementById(newPanelID).getElementsByTagName("iframe")[0].src = "edit-set.php?file=" + makeID(elID) ;
	var splitPathArray = splitPath(pathFromID(elID)) ;
	buildTab(splitPathArray["file"], makeID(elID)) ;
	shuffleTabsAndPanels(newPanelID) ;
}


function clickFolder(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	target = climbDom(target, "li") ;
	// If the folder already has a child UL, hide its child UL instead of creating one!
	var childULs = target.getElementsByTagName("ul") ;
	if (childULs.length > 0) {
		toggleClass(target, "open") ;
		toggleClass(target, "closed") ;
		hideOtherFolders(target) ;
	}
	else {
		hideOtherFolders(target) ;
		addClass(target, "waiting") ;
		var subDirBrowse = new net.ContentLoader("read-dir.php?dir=" + pathFromID(target.id), renderDirContents) ;
	}
	cancelBubble(e) ;
	target.blur() ;
}


function collapseFile(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	var parentLI = climbDom(target, "li", "file") ;
	if (classContains(parentLI, "open")) {
		hideOtherFolders(parentLI) ;
		toggleClass(parentLI, "open") ;
		toggleClass(parentLI, "closed") ;
	}
}

function clickFile(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	var parentLI = climbDom(target, "li", "file") ;
	if (!classContains(parentLI, "known")) {
		hideOtherFolders(parentLI) ;
		addClass(parentLI, "waiting") ;
		var subDirBrowse = new net.ContentLoader("get-file-details.php?file=" + pathFromID(parentLI.id), renderFileProperties) ;
	}
	else if (
			classContains(parentLI, "open")
			&&
			(ls > 1 || getFileExtension(pathFromID(parentLI.id)) != "php")
		) {
		clickEdit(e) ;
	}
	else {
		hideOtherFolders(parentLI) ;
		toggleClass(parentLI, "open") ;
		toggleClass(parentLI, "closed") ;
		// showChildLists
		var childLIs = parentLI.getElementsByTagName('li') ;
		if (classContains(parentLI, "open")) {
			for (cli=0; cli<childLIs.length; cli++) {
				stripClass(childLIs[cli], "closed") ;
			}
		}
	}
	cancelBubble(e) ;
	target.blur() ;
}

function clickEdit(e) {
	// User has just clicked an element li to edit it.
	// Prevent normal editor user from editing Pages...
	var target = getTarget(e) ;
	if (!target) return false ;
	var liClicked = pathFromID(climbDom(target, "li").id) ;
	if (!liClicked) return false ;
	cancelBubble(e) ;
	switch(getFileExtension(liClicked)) {
		case "set" :
			editSet(liClicked) ;
		break ;
		case "html" :
			editHTML(liClicked) ;
		break ;
		default :
			editText(liClicked) ;
		break ;
	}
}

function clickTab(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	target.blur() ;
	var myLiId = climbDom(target, "li").id ;
	shuffleTabsAndPanels(myLiId) ;
}