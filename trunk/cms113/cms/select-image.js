function setup() {
	// Go through all thumbnails and set event to show button when moused over
	myDivs = getElementsByClassName(document, "div", "thumbnail") ;
	for (iDiv=0; iDiv<myDivs.length; iDiv++) {
		addEvent(myDivs[iDiv], "mouseover", showButton, true)  ;
		addEvent(myDivs[iDiv], "mouseout", hideButton, true)  ;
		myImages = myDivs[iDiv].getElementsByTagName("img") ;
		for (iImg=0; iImg<myImages.length; iImg++) {
			addEvent(myImages[iImg], "mouseover", showButton, true)  ;
		}
		myButtons = myDivs[iDiv].getElementsByTagName("Btn") ;
		for (iBtn=0; iBtn<myButtons.length; iBtn++) {
			addEvent(myButtons[iBtn], "mouseover", showButton, true)  ;
		}
	}
}

function getButton(e) {
	var target = getTarget(e) ;
	if (!target) return false ;
	target = climbDom(target, "div") ;
	if(!target || !target.className) return false ;
	// Find button within target
	var myButton = target.getElementsByTagName("button")[0] ;
	if (!myButton) return false ;
	else return myButton ;
}

function showButton(e) {
	myButton = getButton(e) ;
	if (myButton) myButton.style.display = "inline" ;
}

function hideButton(e) {
	myButton = getButton(e) ;
	if (myButton) myButton.style.display = "none" ;
}

function selectImage(imgID) {
	window.opener.sentImage(dir + imgID, field_id) ;
	window.close() ;
}

addEvent(window, "load", setup, false) ;