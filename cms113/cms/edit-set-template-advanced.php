<?
	/* 
		Expects:
			$_GET['file']
			
		Simply allows user to see serialized ST array, and overpaste 3rd-party code
	
	*/
	
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	if ($_SESSION['loginStatus'] < 2) header('Location:index.php?message=norights') ;
	
	if (isset($_GET['file'])) {
		// Read in file contents and write to textarea
		if (!is_file((STDIR . $_GET['file']))) {
			header('Location:set-templates.php?message=stfiledoesntexist') ;
			exit ;
		}
		$stContents = file_get_contents(STDIR . $_GET['file']) ;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Edit Set template <? echo stripFileExtension($_GET['file']) ; ?></title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript">
	<!--
		function banish() {
			location = 'set-templates.php' ;
		}
	// -->
	</script>
	<script type="text/javascript" language="javascript" src="core.js"></script>
</head>
<body>

<form action="save-set-template.php" method="post">
<input type="hidden" name="sourcefile" value="<? echo $_GET['file'] ; ?>" />
<h1>Edit Set Template: <? echo stripFileExtension($_GET['file']) ; ?></h1>
<div id="inlineTextEditor">
	<div class="textareaPadder">
		<textarea id="textEditorTextarea" name="st-advanced"><? echo $stContents ; ?></textarea>
	</div>
</div>

<div style="text-align:center;">
	<button type="submit" style="width:10em;">Save</button>
	&nbsp; &nbsp; 
	<button onClick="banish();">Cancel</button>
</div>

</form>

<br />

</body>
</html>