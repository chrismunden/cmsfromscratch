/* 
	Functions for handling AJAX responses
*/

function fileRenamed() {
	switch (this.req.responseText) {
		case "regular" :
			// Destroy & rebuild ".."
			var dotDot = document.getElementById('DOTDOT') ;
			var dotDotKids = dotDot.childNodes ;
			for (var i=0; i<dotDotKids.length; i++) {
				dotDot.removeChild(dotDotKids[i]) ;
			}
			initialiseFileBrowser() ;
		break ;
		case "pt" :
			// Destroy & rebuild "pagetemplates"
			var pts = document.getElementById('pagetemplates') ;
			var ptsKids = pts.childNodes ;
			for (var i=0; i<ptsKids.length; i++) {
				pts.removeChild(ptsKids[i]) ;
			}
			initialisePTsList() ;
		break ;
		default :
			alert("Error:\n" + this.req.responseText) ;
		break ;
	}
}

function fileSaved() {
	if (this.req.responseText.indexOf("error:") == 0 || this.req.responseText.indexOf("debug:") == 0) {
		displayError(this.req.responseText) ;
		return ;
	}
	/* Otherwise, we should have an XML document of the form:
		<xml>
			<filesaved>
				<id>ELEMENT_ID</id>
				<smatch>MATCH_CODE</smatch>  (i.e. 1 or 0)
			</filesaved>
		</xml>
	*/
	var xmlr = this.req.responseXML ;
	// Save & continue...
	
	var savedElementID = makeID(getXMLElementContent(xmlr.getElementsByTagName('id')[0])) ;
	
	if (getSingleXMLTagContent(this.req.responseXML, "dontrefresh")) {
		alert("Saved.") ;
		return ;
	}
	if (!document.getElementById(savedElementID)) {
		if (savedElementID.length > 0) {
			alert("Element of ID: " + savedElementID + " doesn't exist..") ;
		}
		else alert("Saved.") ;
		return ;
	}
	else {
		destroyTabAndPanel(savedElementID) ;
		var myFilesMatch = getXMLElementContent(xmlr.getElementsByTagName('match')[0]) ;
		if (myFilesMatch == "1") {
			filesMatch(makeID(savedElementID)) ;
		}
		else {
			filesMatch(makeID(savedElementID), "dontmatch") ;
		}
	}
}

function filesMatch(filePath, dontMatch) {
	if (!filePath) var filePath = makeID(this.req.responseText) ;
	var filePath = makeID(filePath) ;
	if (filePath.indexOf("error:") == 0 || filePath.indexOf("debug:") == 0) {
		displayError(pathFromID(filePath)) ;
		return ;
	}
	if (!document.getElementById(filePath)) {
		alert("Element of ID: " + filePath + " doesn't exist..") ;
		return ;
	}	
	var actionsLinks = document.getElementById(filePath).getElementsByTagName('ol')[0] ;
	if (dontMatch) {
		addClass(actionsLinks, "nomatch") ;
	}
	else {
		stripClass(actionsLinks, "nomatch") ;
	}
}

function pagePublishedAll() {
	var filePath = makeID(this.req.responseText) ;
	if (filePath.indexOf("error:") == 0) {
		displayError(filePath.substring(6)) ;
	}
	if (!document.getElementById(filePath)) {
		alert("Element of ID: " + filePath + " doesn't exist..") ;
		return ;
	}
	var fileUL = document.getElementById(filePath) ;
	// ********************************* ???? redundant now ????
}

function insertPage(forceNewPagePath) {
	if (!checkError(this.req.responseText)) return false ;
	// returns e.g. ../np2.php
	if (forceNewPagePath) {
		var newPagePath = forceNewPagePath ;
	}
	else {
		var newPagePath = this.req.responseText ;
	}
	var arrSplitPath = splitPath(newPagePath) ;
	// debug(arrSplitPath["path"] + " /// " + arrSplitPath["file"]) ;
	var parentID = makeID(arrSplitPath["path"]) ;
	// Added true, as new pages should, by definition, match
	var newLI = buildFileNode(arrSplitPath["file"], parentID, true) ;
	// Then needs inserting into correct list...
	if (!document.getElementById(parentID)) {
		if (parentID.length) {
			alert(pathFromID(parentID) + " not found!") ;
		}
		else {
			alert(pathFromID(newPagePath) + " not found!") ;
		}
		return false ;
	}
	else {
		insertFileNode(newLI, parentID) ;
	}
	if (parentID == "pagetemplates") {
		addToPTsList(arrSplitPath["file"]) ;
	}
}

function addToPTsList(ptName) {
	/* 
		<select name="newPagePT" id="newPagePT">
			<option value="">Blank page</option>
		</select>
	*/
	var mySel = document.getElementById('newPagePT') ;
	// Go through options and make sure it doesn't already exist
	var myOptions = mySel.getElementsByTagName('option') ;
	for (var i=0; i<myOptions.length; i++) {
		if (myOptions[i].value == ptName) {
			return ;
		}
	}
	// None found, so insert
	var newOption = buildElement("option", stripFileExtension(ptName)) ;
		newOption.value = ptName ;
	mySel.appendChild(newOption) ;
}

function removeFromPTsList(ptName) {
	ptName = pathFromID(ptName) ;
	var mySel = document.getElementById('newPagePT') ;
	// Go through options and make sure it doesn't already exist
	var myOptions = mySel.getElementsByTagName('option') ;
	for (var i=0; i<myOptions.length; i++) {
		if (myOptions[i].value == ptName) {
			mySel.removeChild(myOptions[i]) ;
			return ;
		}
	}
}

function insertDir() {
	/* 
		Inserts a new Directory node (li)
	*/
	var newFolderPath = this.req.responseText ;
	if (!newFolderPath || newFolderPath.length < 1) {
		// Throw an error ;
		alert("Error creating new folder.") ;
		return ;
	}
	else if (newFolderPath.toLowerCase().indexOf("error") == 0) {
		alert("Could not create new folder.\nPlease check the permissions in the web root folder and in /cms/includes/.") ;
		return ;
	}
	// alert(newFolderPath) ;
	var arrSplitPath = splitPath(newFolderPath) ;
	var prefix = arrSplitPath["path"] ;
	var folderName = arrSplitPath["file"] ;	
	var newDirLi = buildFolderNode(folderName, prefix) ;
	var parentUL = document.getElementById(prefix).getElementsByTagName('ul')[0] ;
	var parentUlLis = parentUL.childNodes ;
	/* 
		Insert at end of list of Directories
	*/
	for (var pulli=0; pulli<parentUlLis.length; pulli++) {
		if (!classContains(parentUlLis[pulli], "dir")) {
			parentUL.insertBefore(newDirLi, parentUlLis[pulli]) ;
			return ;
		}
	}
	/* if (classContains(parentUL.getElementsByTagName('li')[0], "blank")) {
		parentUL.removeChild(parentUL.getElementsByTagName('li')[0]) ;
	}*/
	/* 
		Else.. should surely put at first position in list ***
	*/
	parentUL.appendChild(newDirLi) ;
}

function insertFreeInclude() {
	if (this.req.responseText.indexOf("error:") == 0 || this.req.responseText.indexOf("debug:") == 0) {
		displayError(this.req.responseText) ;
		return ;
	}
	var arrSplitPath = splitPath(this.req.responseText) ;
	var parentElementID = makeID(arrSplitPath["path"]) ;
	var newFileLi = buildFileNode(arrSplitPath["file"], parentElementID, true) ;
	insertFileNode(newFileLi, parentElementID) ;
}

function insertFileNode(newNode, parentElementID) {
	if (!document.getElementById(parentElementID)) {
		alert("No element with ID: " + parentElementID) ;
		return ;
	}
	var parentNode = document.getElementById(parentElementID) ;
	var parentUL = parentNode.getElementsByTagName('ul')[0] ;
	var parentUlLis = parentUL.childNodes ;
	// Insert before last li
	if (parentUlLis.length > 0) {
		parentUL.insertBefore(newNode, parentUlLis[parentUlLis.length-1]) ;
	}
	else {
		parentUL.appendChild(newNode) ;
	}
	/* if (classContains(parentUL.getElementsByTagName('li')[0], "blank")) {
		parentUL.removeChild(parentUL.getElementsByTagName('li')[0]) ;
	}*/
}

function insertLCI() {
	// alert(this.req.responseText) ;
	if (this.req.responseText.indexOf("error:") == 0 || this.req.responseText.indexOf("debug:") == 0) {
		displayError(this.req.responseText + " (insertLCI)") ;
		return ;
	}
	/* 
		Returns e.g.
			DOTDOTSLASHpageDOTphp/filename.text !!!
		
		So DOTDOTSLASHpageDOTphp is the name of the parent LI,
		the new item ID is e.g. DOTDOTSLASHpageDOTphp1DOTtext
		and we're inserting the new item at the end of its first child UL
	*/
	var arrSplitPath = splitPath(this.req.responseText) ;
	// alert(arrSplitPath["path"] + " ++++ " + arrSplitPath["file"]) ;
	var parentPageID = makeID(arrSplitPath["path"]) ;
	if (parentPageID.indexOf("pagetemplates")==0) {
		// For PT LCIs, will return e.g. pagetemplates/ptname/newchild.ext
		// Need to convert this to parent ID: pagetemplates/ptname.php
		parentPageID += makeID(".php") ;
	}
	newLILci = buildLCI(arrSplitPath["file"], parentPageID, "FILES_MATCH") ;
	if (document.getElementById(parentPageID)) {
		var parentUL = document.getElementById(parentPageID).getElementsByTagName('ul')[0] ;
	}
	else {
		alert(pathFromID("Element ID: " + parentPageID + "\nnot found.")) ;
	}
	parentUL.appendChild(newLILci) ;
	/* 
		If the first such LI is the "empty" placeholder, kill it.
	*/
	/* if (classContains(parentUL.getElementsByTagName('li')[0], "blank")) {
		parentUL.removeChild(parentUL.getElementsByTagName('li')[0]) ;
	}*/
}

function renderDirContents() {
	if (!this.req.responseXML) {
		alert (this.req.responseText) ;
		return ;
	}
	var rootObject = document.getElementById('DOTDOT') ;
	var xmlr = this.req.responseXML ;
	var newDirListRaw = getXMLElementContent(xmlr.getElementsByTagName('parentdir')[0]) ;
	// debug(newDirListRaw) ;
	var parentElementID = makeID(newDirListRaw) ;
	stripClass(document.getElementById(parentElementID), "waiting") ;
	
	var newUL = document.createElement("ul") ;
		newUL.className = "folderList open" ;
	
	var dirs = xmlr.getElementsByTagName("dir") ;
	var files = xmlr.getElementsByTagName("file") ;
	var pages = xmlr.getElementsByTagName("page") ;
	
	// Add new LI to document
	var parent = document.getElementById(parentElementID) ;
	addClass(parent, "open") ;
	stripClass(parent, "closed") ;
	parent.appendChild(newUL) ;
	
	// Add folders first
	for (var idir=0; idir<dirs.length; idir++) {
		if (getXMLElementContent(dirs[idir]).toLowerCase().indexOf('x') == 0 && ls < 2) continue ;
		newUL.appendChild(buildFolderNode(getXMLElementContent(dirs[idir]), parentElementID)) ;
	}
	// Add pages
	for (var ipage=0; ipage<pages.length; ipage++) {
		var filesMatch = getXMLElementContent(pages[ipage].getElementsByTagName('match')[0]) ;
		if ( (filesMatch == "previewonly" || filesMatch == "liveonly") && !showAliens) continue ;
		if (getXMLElementContent(pages[ipage]).toLowerCase().indexOf('x') == 0 && ls < 2) continue ;
		newUL.appendChild(buildFileNode(getXMLElementContent(pages[ipage].getElementsByTagName('name')[0]), parentElementID, filesMatch)) ;
	}
	// Add files
	for (var ifile=0; ifile<files.length; ifile++) {
		var filesMatch = getXMLElementContent(files[ifile].getElementsByTagName('match')[0]) ;
		if ( (filesMatch == "previewonly" || filesMatch == "liveonly") && !showAliens) continue ;
		if (getXMLElementContent(files[ifile]).toLowerCase().indexOf('x') == 0 && ls < 2) continue ;
		newUL.appendChild(buildFileNode(getXMLElementContent(files[ifile].getElementsByTagName('name')[0]), parentElementID, filesMatch)) ;
	}
	
	// Add list for new includes/directories etc.
	// Params are isPartOfFolder, showDeleteDir, folderNewListID, showMakePT
	newUL.appendChild(buildNewList(true, parentElementID != "DOTDOT" ? true : false, parentElementID + "_actions"), false) ;
}

function renderFileProperties() {
	var xmlr = this.req.responseXML ;
	var fileName = getSingleXMLTagContent(xmlr, 'file') ;
	var filesMatch = getSingleXMLTagContent(xmlr, 'filestatus') ;
	stripClass(document.getElementById(makeID(fileName)), "waiting") ;
	
	var lcis = xmlr.getElementsByTagName("lci") ;
	var numLcis = lcis.length ;
	
	var parent = document.getElementById(makeID(fileName)) ;
	// Add links list to main page/file ...

	parent.appendChild(buildTinyLinksList(makeID(fileName + "_actions"), filesMatch, (getFileExtension(fileName)=="php") ? "page" : "fi" )) ;
	if (getFileExtension(fileName) == "php" || fileName.indexOf("pagetemplates") == 0) {
		var fileDetailDiv = buildElement("div", "", "fileDetail") ;
		var actionsList = buildNewList(false, false, makeID(fileName) + "_actions", true) ;
		fileDetailDiv.appendChild(actionsList) ;
		fileDetailDiv.appendChild(buildElement("hr", "", "clear-all")) ;
		var showMakePT = (fileName.indexOf("pagetemplates") == 0) ? false : true ;
		var newUL = document.createElement("ul") ;
		/* 
			Build list of LCIs
		*/
		for (var iLci=0; iLci<numLcis; iLci++) {
			var lciFileName = getXMLElementContent(lcis[iLci].getElementsByTagName("filename")[0]) ;
			var lciMatchStatus = getXMLElementContent(lcis[iLci].getElementsByTagName("filestatus")[0]) ;
			newLILci = buildLCI(lciFileName, parent.id, lciMatchStatus) ;
			newUL.appendChild(newLILci) ;
		}
		fileDetailDiv.appendChild(newUL) ;
		fileDetailDiv.appendChild(buildElement("div","","clear-all")) ;
		parent.appendChild(fileDetailDiv) ;
	}
	
	addClass(parent,"open") ;
	addClass(parent,"known") ;
	stripClass(parent,"closed") ;
}

function initialisePTContents() {
	var rootObject = document.getElementById('pagetemplatesSLASH') ;
	var xmlr = this.req.responseXML ;
	var parentElementID = getXMLElementContent(xmlr.getElementsByTagName('parentdir')[0]) ; // i.e. "pagetemplates"
	if (!document.getElementById(parentElementID)) return ;
	var newUL = document.createElement("ul") ;
		newUL.className = "folderList open" ;
	var pages = xmlr.getElementsByTagName("page") ;
	
	var parent = document.getElementById(parentElementID) ;
	addClass(parent, "open") ;
	stripClass(parent, "closed") ;
	parent.appendChild(newUL) ;
	
	// Add pages
	for (var ipage=0; ipage<pages.length; ipage++) {	
		var filesMatch = getXMLElementContent(pages[ipage].getElementsByTagName('match')[0]) ;
		var newPTName = getXMLElementContent(pages[ipage].getElementsByTagName('name')[0]) ;
		newUL.appendChild(buildFileNode(newPTName, parentElementID, filesMatch)) ;
		addToPTsList(newPTName) ;
	}
}

function madePTFromPage() {
	if (this.req.responseText.length > 0) {
		var newPTName = this.req.responseText ;
	}
	else {
		alert("Failed to make new page template from page.") ;
		return ;
	}
	addToPTsList(newPTName) ;
	insertPage('pagetemplates/' + newPTName + ".php") ;
}

function renderTextEditor() {
	var splitArr = splitResponse(this.req.responseText) ;
	var elID = splitArr["elementID"] ;
	buildTab(baseName(pathFromID(elID)), elID) ;
	// Create a new tab and a new panel with iframe
	var textEditorTemplate = document.getElementById("templateTextEditor").innerHTML ;
	var newPanelID = "panel__" + makeID(elID) ;
	var newTextEditor = buildElement("div", "", "panel", newPanelID, textEditorTemplate) ;
	document.getElementById("pageBody").appendChild(newTextEditor) ;
	document.getElementById(newPanelID).getElementsByTagName("textarea")[0].value = splitArr["contents"] ;
	stretchToFit(document.getElementById(newPanelID).getElementsByTagName("textarea")[0]) ;
	shuffleTabsAndPanels(elID) ;
}