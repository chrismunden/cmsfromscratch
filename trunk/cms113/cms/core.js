/* 
	CORE FUNCTIONS
*/

var addEvent;
if (document.addEventListener) {
	addEvent = function(element, type, handler) {
		element.addEventListener(type, handler, null);
		if (element.href) element.href="javascript:void('');" ;
	}
}
else if (document.attachEvent) {
	addEvent = function(element, type, handler) {
		element.attachEvent("on" + type, handler);
		if (element.href) element.href="javascript:void('');" ;
	}
}
else {
	addEvent = new Function; // not supported
}
 
function getTarget(e) {
	if (window.event && window.event.srcElement)
		return window.event.srcElement ;
	if (e && e.target)
		return e.target ;
	if (!e)
		return false ;
}

function climbDom(e, tagType, containsClass) {
	if (containsClass) {
		while (
			(
				e.nodeName.toLowerCase() != tagType
				|| !classContains(e, containsClass)
			)
			&& e.nodeName.toLowerCase() != 'html'
		) e = e.parentNode ;
	}
	else {
		while (e.nodeName.toLowerCase() != tagType && e.nodeName.toLowerCase() != 'html') e = e.parentNode ;
	}
	return (e.nodeName.toLowerCase() == 'html') ? false : e ;
}

function classContains(myObject,myClassName) {
	if (!myObject.className) return false ;
	else return (myObject.className.indexOf(myClassName) == -1) ? false : true ;
}

function stripClass(myObject, classToStrip) {
	if (!myObject || !myObject.className) return false ;
	var cnPos = myObject.className.indexOf(classToStrip) ;
	if (cnPos != -1) {
		var newCN = myObject.className.replace(classToStrip, "") ;
		myObject.className = newCN ;
	}
}

function addClass(myObject, myClassName) {
	var cnPos = myObject.className.indexOf(myClassName) ;
	if (cnPos == -1) {
		myObject.className += " " + myClassName ;
	}
}

function toggleClass(el, myClassName) {
	if (classContains(el, myClassName)) {
		stripClass(el, myClassName) ;
	}
	else {
		addClass(el, myClassName) ;
	}
}

function getElementsByClassName(oElm, strTagName, strClassName) {
    var arrElements = (strTagName == "*" && document.all)? document.all : oElm.getElementsByTagName(strTagName) ;
    var arrReturnElements = new Array() ;
    strClassName = strClassName.replace(/\-/g, "\\-") ;
    var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)") ;
    var oElement ;
    for (var i=0; i<arrElements.length; i++) {
        oElement = arrElements[i] ; 
        if(oRegExp.test(oElement.className)) {
            arrReturnElements.push(oElement) ;
        }   
    }
    return (arrReturnElements) ;
}

function makeID(rawString) {
	if (!rawString.length) return '' ;
	fixedString = rawString.replace(/_/g, "USCORE") ;
	fixedString = fixedString.replace(/-/g, "DASH") ;
	fixedString = fixedString.replace(/\./g, "DOT") ;
	fixedString = fixedString.replace(/\//g, "SLASH") ;
	fixedString = fixedString.replace(/\\/g, "SLASH") ;
	return fixedString ;
}

function pathFromID(idString) {
	var fixedPath = idString ;
	fixedPath = fixedPath.replace(/USCORE/g, "_") ;
	fixedPath = fixedPath.replace(/DASH/g, "-") ;
	fixedPath = fixedPath.replace(/SLASH/g, "/") ;
	fixedPath = fixedPath.replace(/SLASH/g, "\\") ;
	fixedPath = fixedPath.replace(/DOT/g, ".") ;
	return fixedPath ;
}

function justPing() {
	alert(this.req.responseText) ;
}

function cancelBubble(e) {
	if (window.event) {
		window.event.cancelBubble = true ;
	}
	else {
		e.stopPropagation();
	}
}

function getFileExtension(fileName) {
	return fileName.substring(fileName.lastIndexOf(".")+1) ;
}

function stripFileExtension(filePath) {
	var lastDot = filePath.lastIndexOf(".") ;
	if (lastDot == -1) return filePath ;
	return filePath.substring(0, lastDot) ;
}

function getPreviewPathFromLive(pagePath) {
	if (pagePath.substring(0,3) == "../") {
		return "includes/" + pagePath.substring(3) ;
	}
	else return false ;
}
function getLivePathFromPreview(pagePath) {
	if (pagePath.substring(0,9) == "includes/") {
		return "../" + pagePath.substring(9) ;
	}
}

function getLciFolderPathFromLivePath(pagePath) {
	// Not applicable to PT LCIs!
	var previewPath = getPreviewPathFromLive(pagePath) ;
	if (previewPath) {
		return stripFileExtension(previewPath) + "_cms_files/cms_preview/" ;
	}
	else if (pagePath.indexOf("pagetemplates") == 0) {
		return stripFileExtension(pagePath) ;
	}
	else {
		return pagePath ;
	}
}

function deleteNode(optionalNodeID) {
	// Either gets ID from AJAX, or can pass in directly
	if (optionalNodeID) var response = optionalNodeID ;
	else var response = this.req.responseText ;
	if (response.indexOf("error:") == 0) {
		displayError(response.substring(6)) ;
		return false ;
	}
	else if (document.getElementById(response)) {
		document.getElementById(response).parentNode.removeChild(document.getElementById(response)) ;
	}
	else if (document.getElementById(makeID(response))) {
		document.getElementById(makeID(response)).parentNode.removeChild(document.getElementById(makeID(response))) ;
	}
	else {
		alert("No element found with ID: " + response) ;
		return false ;
	}
	var ptPos = response.indexOf(makeID("pagetemplates/")) ;
	if (ptPos == -1) return ;
	removeFromPTsList(response.substring(makeID("pagetemplates/").length)) ;
}

function displayError(messageString) {
	alert("Error returned: " + pathFromID(messageString)) ;
}

String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}
String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}

/* 
	END
	CORE FUNCTIONS
*/
