function tidyFileName(fileName) {
	fileName = fileName.replace(/\?|>|<|:|\\|\//g, "") ;
	return fileName ;
}

function getXMLElementContent(xmlElement) {
	if (!xmlElement) return false ;
	return (xmlElement.text != undefined) ? xmlElement.text : xmlElement.textContent ;
}

function getSingleXMLTagContent(xmlDoc, tagName) {
	return getXMLElementContent(xmlDoc.getElementsByTagName(tagName)[0]) ;
}

var dataTypes = new Array('Page', 'Text include','HTML include','Content Set include') ;

/* 
	Generic functions
*/

function baseName(inPath) {
	var splitArr = splitPath(inPath) ;
	return splitArr["file"] ;
}

function splitPath(inPath) {
	var lastSlashPos = inPath.lastIndexOf("/") ;
	var path = inPath.substring(0, lastSlashPos) ;
	var file = inPath.substring(lastSlashPos + 1) ;
	var returnArray = new Object ;
		returnArray["path"] = path ;
		returnArray["file"] = file ;
	return(returnArray) ;
}

function splitResponse(ajaxResponse) {
	var delimPos = ajaxResponse.indexOf(myDelim) ;
	var elementID = ajaxResponse.substring(0, delimPos) ;
	var contents = ajaxResponse.substring(delimPos + myDelim.length) ;
	var returnArray = new Object ;
		returnArray["elementID"] = elementID ;
		returnArray["contents"] = contents ;
	return(returnArray) ;
}

function getParentFolder(target) {
	if (!target) return false ;
	var parentUL = climbDom(target, "ul").parentNode.id ;
	return (parentUL) ? parentUL : false ;
}

function hideOtherFolders(el) {
	/* 
		el = reference to a DOM node in the tree that we want to keep (along with all its ancestors)
		The function should remove all other ULs in the tree that aren't ancestors of the passed node
		i.e. their IDs don't form part of the passed node's ID!
	*/
	var rootObject = document.getElementById('DOTDOT') ;
	if (!el || !el.id) return ;
	// Hide other subfolders
	var siblingLIs = el.parentNode.getElementsByTagName('li') ;
	for (var i=0; i<siblingLIs.length; i++) {
		if (siblingLIs[i].id && siblingLIs[i].id != el.id) {
			stripClass(siblingLIs[i], "open") ;
			addClass(siblingLIs[i], "closed") ;
		}
	}
}

function showDetailsForm(wotForm, parentElement, parentPage, isLCI) {
	var myForm = document.getElementById(wotForm) ;
	if (!myForm) return ;
	myForm.style.display = "block" ;
	document.getElementById("popupPrompt").style.display = "block" ;
	document.getElementById("popupPrompt").style.zIndex = "100000" ;
	switch(wotForm) {
		case "newPage" :
			document.getElementById("pagePath").value = parentElement ;
			document.getElementById("newPageName").value = "" ;
			// Next line prevents IE display bug
			document.getElementById(wotForm).getElementsByTagName("label")[0].focus() ;
			document.getElementById("newSet").style.display = "none" ;
		break ;
		case "newSet" :
			document.getElementById('newSetFilePath').value = parentElement ;
			document.getElementById('newSetName').value = "" ;
			
			document.getElementById("newSetMode").value = "" ;
			if (isLCI && isLCI.length) {
				document.getElementById("newSetMode").value = isLCI ;
			}
			
			if (parentPage) {
				document.getElementById("newSetParentPage").value = parentPage ;
			}
			else {
				document.getElementById('newSetParentPage').value = "" ;
			}
			document.getElementById(wotForm).getElementsByTagName("label")[0].focus() ;
			// document.getElementById('newSetName').focus() ;
			document.getElementById("newPage").style.display = "none" ;
		break ;
	}
}

function debug(debugString) {
	alert(debugString) ;
}

function unExpand(e) {
	/* 
		Currently not being used.
	*/
	var target = getTarget(e) ;
	if (!target) return ;
	// Exceptions to prevent unExpand from firing when normal things are clicked!
	if (
		target != document.getElementsByTagName('html')[0] &&
		target != document.getElementsByTagName('body')[0] &&
		!classContains(climbDom(target, "li"), "newLlist") &&
		!classContains(target, "folderList")
	) {
		return ;
	}
	var openElements = getElementsByClassName(document.getElementById('DOTDOT'), "li", "open") ;
	for (var i=0; i<openElements.length; i++) {
		stripClass(openElements[i], "open") ;
		addClass(openElements[i], "closed") ;
	}
}

function expandFolderActionsList(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var myList = climbDom(target, "li").getElementsByTagName('ol')[0] ;
}


function getPageSize(){
	/* 
		Nicked from Lightbox code.. 
		Remove if incorporating Lightbox later on..
	*/
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}

function showSettings() {
	document.getElementById('settingsEditor').style.display = "block" ;
	if (document.getElementById('loginMessage')) {
		document.getElementById('loginMessage').parentNode.removeChild(document.getElementById('loginMessage')) ;
	}
}
function hideSettings() {
	document.getElementById('settingsEditor').style.display = "none" ;
	unGreyOut() ;
}
function showFiles() {
	document.getElementById('filesEditor').style.display = "block" ;
}
function hideFiles() {
	document.getElementById('filesEditor').style.display = "none" ;
	unGreyOut() ;
}
function showImages() {
	document.getElementById('imagesEditor').style.display = "block" ;
}
function hideImages() {
	document.getElementById('imagesEditor').style.display = "none" ;
	unGreyOut() ;
}

function showSetTemplates() {
	document.getElementById('setTemplateEditor').style.display = "block" ;
	document.getElementById('setTemplateEditor').getElementsByTagName('iframe')[0].src = "set-templates.php" ;
}

function hideDetailsForm() {
	document.getElementById('popupPrompt').style.display = "none" ;
}

function initialiseSTCount() {
	var mySelect = document.getElementById("newSetST") ;
	var myOptions = mySelect.getElementsByTagName("option") ;
	if (myOptions.length) {
		stripClass(document.getElementsByTagName("body")[0], "noSetTemplates") ;
	}
	else {
		addClass(document.getElementsByTagName("body")[0], "noSetTemplates") ;
	}
}

function updateNewSetSTDialog(stsList) {
	var mySelect = document.getElementById("newSetST") ;
	var myOptions = mySelect.getElementsByTagName("option") ;
	for (var i=0; i<myOptions.length; i++) {
		mySelect.removeChild(myOptions[i]) ;
	}
	if (stsList && stsList.length > 0) {
		stripClass(document.getElementsByTagName("body")[0], "noSetTemplates") ;
		if (stsList) {
			var stsList = stsList.split(",") ;
			for (var i=0; i<stsList.length; i++) {
				var newOption = buildElement("option", stsList[i]) ;
					newOption.value = stsList[i] ;
				document.getElementById("newSetST").appendChild(newOption) ;
			}
		}
	}
	else {
		addClass(document.getElementsByTagName("body")[0], "noSetTemplates") ;
	}
}

function initialiseFileBrowser() {
	var initialDirBrowse = new net.ContentLoader("read-dir.php?dir=../", renderDirContents) ;
}

function initialisePTsList() {
	var initialDirBrowse = new net.ContentLoader("read-dir.php?dir=pagetemplates", initialisePTContents) ;
}

function initialiseZIndex() {
	var curZ = 150 ;
	var myTabs = getTabs() ;
	for (var i=0; i<myTabs.length; i++) {
		myTabs[i].style.zIndex = curZ ;
		curZ-=5 ;
		addEvent(myTabs[i], "click", clickTab, false) ;
	}
	var curZ = 150 ;
	var myPanels = getPanels() ;
	for (var i=0; i<myPanels.length; i++) {
		myPanels[i].style.zIndex = curZ ;
		curZ-=5 ;
	}
}

function getTabs() {
	return document.getElementById("tabs").getElementsByTagName("li") ;
}
function getPanels() {
	return getElementsByClassName(document, "div", "panel") ;
}

function shuffleTabsAndPanels(selectedTab) {
	var selectedTabRoot ;
	// SelectedTab may be ID of a tab, but if it isn't a string, it's an event, so pull up the clicked LI...
	if (typeof(selectedTab) != "string") {
		var target = getTarget(selectedTab) ;
		if (!target) return ;
		selectedTab = climbDom(target, "li").id ;
	}
	if (selectedTab.indexOf("tab__") == 0) {
		selectedTabRoot = selectedTab.substring(5) ;
	}
	else if (selectedTab.indexOf("panel__") == 0) {
		selectedTabRoot = selectedTab.substring(7) ;
	}
	else selectedTabRoot = selectedTab ;
	if (classContains(document.getElementById(selectedTab), "newtab") && classContains(document.getElementById(selectedTab), "on")) {
		if (confirm("Close tab?")) {
			destroyTabAndPanel(selectedTabRoot) ;
			return ;
		}
	}
	// Pared down to root
	var tabName = "tab__" + selectedTabRoot ;
	if (document.getElementById(tabName)) {
		var maxZ = 0 ;
		var myZ = document.getElementById(tabName).style.zIndex ;
		var myTabs = getTabs() ;
		for (var i=0; i<myTabs.length; i++) {
			tabZ = myTabs[i].style.zIndex ;
			if (tabZ > maxZ) maxZ = tabZ ;
			if (tabZ > myZ) {
				myTabs[i].style.zIndex-- ;
			}
			stripClass(myTabs[i], "on") ;
		}
		document.getElementById(tabName).style.zIndex = maxZ ;
		addClass(document.getElementById(tabName), "on") ;
	}
	
	var panelName = "panel__" + selectedTabRoot ;
	if (document.getElementById(panelName)) {
		var maxZ = 0 ;
		var myZ = document.getElementById(panelName).style.zIndex ;
		var myPanels = getPanels() ;
		for (var i=0; i<myPanels.length; i++) {
			PanelZ = myPanels[i].style.zIndex ;
			if (PanelZ > maxZ) maxZ = PanelZ ;
			if (PanelZ > myZ) {
				myPanels[i].style.zIndex-- ;
				myPanels[i].style.display = "none" ;
			}
		}
		document.getElementById(panelName).style.zIndex = maxZ ;
		document.getElementById(panelName).style.display = "block" ;
	}
}

function destroyTabAndPanel(elID) {
	if (typeof(elID) != "string") {
		elID = climbDom(elID, "div", "panel").id.substring(7) ;
	}
	removeTabOrPanel("tab__" + elID) ;
	removeTabOrPanel("panel__" + elID) ;
	activateTopTab() ;
}

function removeTabOrPanel(elID) {
	if (document.getElementById(elID)) {
		document.getElementById(elID).parentNode.removeChild(document.getElementById(elID)) ;
		removeTabOrPanel(elID) ;
	}
	else {
		return ;
	}
}

function activateTopTab() {
	var topTab = -1 ;
	var topZ = -1 ;
	var myTabs = getTabs() ;
	for (var i=0; i<myTabs.length; i++) {
		if (myTabs[i].style.zIndex > topZ) {
			topZ = myTabs[i].style.zIndex ;
			topTab = i ;
		}
	}
	if (topTab != -1) {
		addClass(myTabs[topTab], "on") ;
	}
	// Same for panels
	topPZ = -1 ;
	var topPanel = -1 ;
	var myPanels = getPanels() ;
	for (var i=0; i<myPanels.length; i++) {
		if (myPanels[i].style.zIndex > topPZ) {
			topPZ = myPanels[i].style.zIndex ;
			topPanel = i ;
		}
	}
	if (topPanel != -1) {
		myPanels[topPanel].style.display = "block" ;
	}
}

function hoverMe(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var myLI = climbDom(target, "li") ;
	if (!myLI) return ;
	addClass(myLI, "hovering") ;
}
function unHoverMe(e) {
	var target = getTarget(e) ;
	if (!target) return ;
	var myLI = climbDom(target, "li") ;
	if (!myLI) return ;
	stripClass(myLI, "hovering") ;
}

function initialiseCSTs() {
	// Count # of CSTs, and either add/strip class on #DOTDOT
}

function windowHeight() {
	var y;
	if (self.innerHeight) // all except Explorer
	{
		y = self.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
		// Explorer 6 Strict Mode
	{
		y = document.documentElement.clientHeight;
	}
	else if (document.body) // other Explorers
	{
		y = document.body.clientHeight;
	}
	return y ;
}

function stretchToFit(myEl) {
	var y = windowHeight() ;
	var taTop = findObjectTop(myEl) ;
	if (!parseInt(y) || !parseInt(taTop)) return ;
	myEl.style.height = parseInt(y) - parseInt(taTop) - 50 + "px" ;
}

function findObjectTop(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curtop += obj.offsetTop
		}
	}
	return curtop ;
}

function checkError(response) {
	if (response.indexOf("error:") == 0 || response.indexOf("debug:") == 0) {
		alert(pathFromID(response)) ;
		return false ;
	}
	return true ;
}

/* 
	Startup function
*/
window.onload = function() {
	initialiseFileBrowser() ;
	addEvent(document.getElementById('popupPrompt'), "click", cancelBubble, false) ;
	addEvent(document.getElementsByTagName('html')[0], "click", hideDetailsForm, false) ;
	addEvent(document.getElementById('cancel_new_page'), "click", hideDetailsForm, false) ;
	addEvent(document.getElementById('cancel_new_content_set'), "click", hideDetailsForm, false) ;
	initialisePTsList() ;
	initialiseCSTs() ;
	initialiseZIndex() ;
	if (document.getElementById('login-field')) document.getElementById('login-field').focus() ;
	initialiseSTCount() ;
	stretchToFit(document.getElementById("panel__images")) ;
	stretchToFit(document.getElementById("panel__settings")) ;
	stretchToFit(document.getElementById("panel__setTemplates")) ;
	stretchToFit(document.getElementById("panel__files")) ;
}