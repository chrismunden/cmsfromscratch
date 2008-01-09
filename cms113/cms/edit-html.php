<?
	/* 
		Expects:
			$_GET['file']
	*/
	
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	$fileName = getPreviewFileFromLive(pathFromID($_GET['file'])) ;
	$fileContents = @file_get_contents($fileName) ;
	if ($fileContents === False) {
		echo '
			<html><head><script type="text/javascript">
			<!--
				alert("Could not load HTML file!") ;
			// -->
			</script></head></html>
		' ;
		exit ;
	}
	
	
	// Tidy up text
	$writeContent = ereg_replace ( "[[:space:]]+", ' ', $fileContents) ;
	// $writeContent = str_replace ('"', '\\"', $writeContent) ;
	// $writeContent = str_replace ('</textarea>', '<&#47;textarea>', $writeContent) ;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<script type="text/javascript" language="javascript" src="net.js"></script>
	<script type="text/javascript">
	<!--
		function saveHTML() {
			for (var i=0; i<window.frames.length; i++) {
				if (window.frames[i].FCK) {
					window.frames[i].FCK.UpdateLinkedField();
				}
			}
			// Pass sourcefile & textcontent
			top.clickSaveHTML("<? echo $_GET['file'] ; ?>", document.getElementById("textcontent").value) ;
		}
		function banish() {
			top.destroyTabAndPanel("<? echo $_GET['file'] ; ?>") ;
		}
		var saveHover = new Image() ;
		saveHover.src = "images/save-icon-glow.jpg" ;
	// -->
	</script>
	<style type="text/css">
		body {
			margin:0;
			padding:0;
		}
		#saveHTMLButton, #close_button {
			position:absolute;
		}
		#saveHTMLButton {
			top:1em;
			right:3em;
			width:8em;
		}
		#close_button {
			top:3.25em;
			right:4.5em;
			width:5em;
		}
	</style>
</head>

<body style="margin:0; padding:0;">
<form action="save-text.php" method="post" style="margin:0; padding:0;" onSubmit="return false;">
	<input type="hidden" id="sourcefile" name="sourcefile" value="<? echo pathFromID($_GET['file']); ?>" />
<?
	include("FCKeditor/fckeditor.php") ;
	$oFCKeditor = new FCKeditor('textcontent') ;
	// $oFCKeditor->BasePath = '/cms/06/1_1_3/cms/FCKeditor/' ;
	$oFCKeditor->BasePath = 'FCKeditor/' ;
	$oFCKeditor->Value = $writeContent ;
	$oFCKeditor->Height = '100%' ;
	$oFCKeditor->Create() ;
?>
</form>
<button onclick="saveHTML();" id="saveHTMLButton">Save</button>
<button onclick="banish();" id="close_button">Close</button>
<!-- <button onclick="top.saveTextEditor(this, true);" id="save_and_continue_button">Save &amp; continue editing</button> -->
</body></html>
