<?/*
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
	require 'check-login.php' ;
	require '../cmsfns.php' ;
	
	$customTabHTML = '' ;
	$customPanelHTML = '' ;
	if (file_exists('extra-tabs.php')) {
		include_once('extra-tabs.php') ;
	}
	function addCustomTab($tabLabel, $tabSourceScript) {
		global $customTabHTML, $customPanelHTML ;
		$newTabLabel = str_replace(array('.','-', ' '), '_', $tabLabel) ;
		$customPanelHTML .= '
		<div id="panel__' . $newTabLabel . '" class="panel hidden">
			<div class="pushdown"></div>
			<iframe id="' . $newTabLabel . 'Iframe" name="' . $newTabLabel . 'Iframe" src="' . $tabSourceScript . '" frameborder="0"></iframe>
		</div>
		' ;
		$customTabHTML .= '<li id="tab__' . $newTabLabel . '"><a href="javascript:void(\'\');">' . $tabLabel . '</a></li>' ;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>CMS <? 
		if (isset($_SESSION['CMS_Title'])) {
			echo ' - ' . stripslashes($_SESSION['CMS_Title']) ;
		}
	?> </title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<link href="popup-prompt.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" language="javascript" src="translate-js.php"></script>
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript" language="javascript" src="net.js"></script>
	<script type="text/javascript" language="javascript" src="renderers.js"></script>
	<script type="text/javascript" language="javascript" src="callers.js"></script>
	<script type="text/javascript" language="javascript" src="builders.js"></script>
	<script type="text/javascript" language="javascript" src="prototype.lite.js"></script>
	<script type="text/javascript">
	<!--
	var ls = "<? echo $_SESSION['loginStatus'] ; ?>" ;
	var showAliens = <? echo (isSet($_SESSION['showNonCMSFiles']) && $_SESSION['showNonCMSFiles'] == 'yes') ? 'true' : 'false' ; ?> ;
	var canCreatePages = <? echo ($_SESSION['loginStatus']>1 || (isSet($_SESSION['editorsCanCreatePages']) && $_SESSION['editorsCanCreatePages'] == 'yes')) ? 'true' : 'false' ; ?> ;
	var canDeletePages = <? echo ($_SESSION['loginStatus']>1 || (isSet($_SESSION['editorsCanDeletePages']) && $_SESSION['editorsCanDeletePages'] == 'yes')) ? 'true' : 'false' ; ?> ;
	var myDelim = "<? echo MYDELIM ; ?>" ;
	// -->
	</script>
	<script type="text/javascript" language="javascript" src="smcms.js"></script>
</head>

<body id="pageBody" <? if ($_SESSION['loginStatus'] < 2) echo ' class="logged-in-as-client"' ; ?>>

<!-- <noscript><h1>Warning: JavaScript is not enabled! Nothing is going to work.</h1></noscript> -->
<div id="topbar">
	<div id="titles">
		<div id="cmsTitle"><? echo stripslashes($_SESSION['CMS_Title']) ; ?></div>
		<div id="siteTitle"><img src="images/cms-logo.gif" alt="CMS" width="90" height="36" border="0" /></div>
	</div>
	<div id="minorLinks">
		<?
			echo '<a href="http://cmsfromscratch.com/donate.php" target="_blank">Donate</a>' ;
			if ($_SESSION['loginStatus'] > 1) {
				echo '<a href="http://cmsfromscratch.com/user-guide/" target="_blank">Online help</a>' ;
			}
		?>
		<a href="logout.php"><? echo translate('Log out') ; ?></a>
	</div>
	<ul id="tabs">
		<li id="tab__browse" class="on"><a href="javascript:void('');"><? echo translate('Browse') ; ?></a></li>
		<li id="tab__images"><a href="javascript:void('');"><? echo translate('Images') ; ?></a></li>
		<li id="tab__files"><a href="javascript:void('');"><? echo translate('Files') ; ?></a></li>
		<? if ($_SESSION['loginStatus'] > 1) {
			echo '
				<li id="tab__setTemplates"><a href="javascript:void(\'\');">Set&nbsp;Templates</a></li>
				<li id="tab__pageTemplates"><a href="javascript:void(\'\');">Page&nbsp;Templates</a></li>
				<li id="tab__settings"><a href="javascript:void(\'\');">Settings</a></li>
			' ;
			}
			if (strlen($customTabHTML)) echo $customTabHTML ;
		?>
	</ul>
</div>

<?
	if (isset($_GET['message'])) {
		if ($_GET['message'] == 'dosettingsnow') {
			echo '<div id="loginMessage">Please select Settings now to set your Designer login!</div>' ;
		}
	}
?>
<div id="panel__browse" class="panel">
	<div class="pushdown"></div>
	<div class="bigLinks">
		<div id="DOTDOT"></div>
	</div>
</div>

<?
	if (strlen($customPanelHTML)) echo $customPanelHTML ;
?>

<? if ($_SESSION['loginStatus'] > 1) {
echo '
<div id="panel__settings" class="panel hidden">
	<div class="pushdown"></div>
	<iframe id="settingsIframe" name="settingsIframe" src="settings.php" frameborder="0"></iframe>
</div>
	
<div id="panel__setTemplates" class="panel hidden">
	<div class="pushdown"></div>
	<iframe id="setTemplatesIframe" name="setTemplatesIframe" src="set-templates.php" frameborder="0"></iframe>
</div>'
; } ?>
	
<div id="panel__images" class="panel hidden">
	<div class="pushdown"></div>
	<iframe id="imagesIframe" name="imagesIframe" src="images.php" frameborder="0"></iframe>
</div>

<div id="panel__files" class="panel hidden">
	<div class="pushdown"></div>
	<iframe id="filesIframe" name="filesIframe" src="files.php" frameborder="0"></iframe>
</div>


<? if ($_SESSION['loginStatus'] > 1) {
echo '
<div id="panel__pageTemplates" class="panel">
	<div class="pushdown"></div>
	<div style="margin:50px 0 0 100px;">
	<h1>Page templates</h1>
	<div class="bigLinks">
		<div id="pagetemplates"></div>
		<div style="margin-top:1em;">
			<button type="button" onclick="clickNewPageTemplate();">New Page Template</button>
		</div>
	</div>
	</div>
	<br />
	<div class="indent">
		<div class="inlineHelp">
		<p>
			Use Page templates to define structure and includes for pages of various formats (e.g. &lsquo;Article&rsquo; / &lsquo;Product&rsquo; / &lsquo;Section index&rsquo;).
		</p>
		<p>
			If you allow your users to create new pages, they can select a template to use.
		</p>
		<p>
			New pages based on templates automatically get:
		</p>
		<ul>
			<li>the page\'s source code, including any &lt;&lt; include &gt;&gt; calls</li>
			<li>any includes that belong to the page template, with contents</li>
		</ul>
		<p><a href="http://cmsfromscratch.com/help/page-templates/">More help on page templates &raquo;</a></p>
		</div>
	</div>
</div>'
; } ?>


<div id="templateSetEditor" class="hidden">
	<div class="pushdown"></div>
	<iframe id="setEditorIframe" name="setEditorIframe" src="" frameborder="0"></iframe>
</div>

<div id="templateHtmlEditor" class="hidden">
	<div class="pushdown"></div>
	<iframe id="htmlEditorIframe" name="htmlEditorIframe" frameborder="0"></iframe>
</div>

<div id="templateTextEditor" class="hidden" style="position:relative; border:5px solid red;">
	<div class="pushdown"></div>
	<div class="textAreaButtons">
		<button type="button" onclick="destroyTabAndPanel(this);"><? echo translate('Cancel') ; ?></button>
		<button type="button" onclick="saveTextEditor(this, true);" id="save__and_continue_text_editor"><? echo translate('Save &amp; continue editing') ; ?></button>
		<button type="button" onclick="saveTextEditor(this, false);" style="width:10em; margin-left:3em;"><? echo translate('Save &amp; Close') ; ?></button>
	</div>
	<textarea class="fullScreen"></textarea>
</div>

<?
	// This is for the "new page/set" popups
	include('inc-popup-prompt.php') ;
?>

</body>
</html>
