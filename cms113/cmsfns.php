<?
	require ('cmsdirname.php') ;
	define("PTSDIR", 'pagetemplates') ;
	define("PTEXTENSION", '.php') ;
	$SETDATATYPES = array('text','longtext','image','link','file') ;
	define("STDIR", 'settemplates/') ;
	define("SETEXTENSION", '.set') ;
	define("STEXTENSION", '.st') ;
	define("HTMLFILEEXTENSION", '.html') ;
	define("TEXTFILEEXTENSION", '.text') ;
	define("CMSIMAGESFOLDER", 'cmsimages/') ;
	define("MYDELIM", '[[[~{_!?>]]]') ;
	$reserved_filenames = Array('cache',CMSDIRNAME,'cmsimages','cmsfiles','.','..','../general.php','../cmsfns.php','../cmsdirname.php','key.php','best_method.php','phpaudit_license.text') ;

	$sesh = @session_start() ;
	/* 
		Login stuff
	*/
	if (!isset($_SESSION['settingsInitialized'])) {
		$settings = @file_get_contents("settings.text") ;
		if ($settings == False) {
			$notFound = True ;
		}
		else {
			$settingsArray = unserialize($settings) ;
			if (sizeof($settingsArray) == 0) {
				$notFound = True ;
			}
			else {
				// Something found!
				$_SESSION['CMS_Title'] = $settingsArray['cms-description'] ;
				$_SESSION['settingsInitialized'] = True ;
			}
		}
		if (isset($notFound)) {
			$_SESSION['CMS_Title'] = 'CMS' ;
			$_SESSION['Show_help_by_default'] = '0' ;
			unset($_SESSION['settingsInitialized']) ;
		}
	}
	
	function pathFromID($idString) {
		$fixedPath = $idString ;
		$fixedPath = str_replace('USCORE', '_', $fixedPath) ;
		$fixedPath = str_replace('DASH', '-', $fixedPath) ;
		$fixedPath = str_replace('SLASH', '/', $fixedPath) ;
		$fixedPath = str_replace('SLASH', '\\', $fixedPath) ;
		$fixedPath = str_replace('DOT', '.', $fixedPath) ;
		return $fixedPath ;
	}
	
	function getLiveFileFromPreview($previewPath) {
		return str_replace('/includes/', '../', $previewPath) ;
	}
	function getPreviewFileFromLive($livePath) {
		return str_replace('../', 'includes/', $livePath) ;
	}
	
	function getLCIRootFolderFromPagePath($pagePath) {
		// Given e.g. 	   "../userfolder/subfolder/pagename.php"
		// Needs to return "includes/userfolder/subfolder/pagename_cms_files/"
		// Note: Should also work without file extension
		if (!$pagePath) return false ;
		// Strip off preceding ../
		$pagePath = ltrim($pagePath, './') ;
		// Strip off filename
		$pagePath = stripFileExtension($pagePath) ;
		return 'includes/' . $pagePath . '_cms_files' ;
	}
	
	function getParentPageFromLCI($lciPath) {
		/* 
			e.g. LCI path = includes\about-us_cms_files\cms_live\lci.text
			we want includes\about-us.php
		*/
		$lciRoot = rtrim(dirname($lciPath), '/') ;
		$parentPage = str_replace('_cms_files/cms_live', '.php', $lciRoot) ;
		$parentPage = str_replace('_cms_files/cms_preview', '.php', $parentPage) ;
		return $parentPage ;
	}
	
	function isLCI($filePath) {
		return strstr($filePath, '_cms_files/cms_') ;
	}
	
	function isPageTemplate($filePath) {
		return (strpos($filePath, PTSDIR . '/') === 0) ? True : False ;
	}
	
	function getLiveLCIFromPreview($previewPath) {
		return str_replace('_cms_files/cms_preview', '_cms_files/cms_live', $previewPath) ;
	}
	
	function getFileExtension($filename) {
		return substr(strrchr(basename($filename), '.'), 1) ;
	}
	
	function matchFiles($previewFile,$liveFile) {
		if (!file_exists($liveFile)) {
			return 'PREVIEW_ONLY' ;
		}
		else if (!file_exists($previewFile)) {
			return 'LIVE_ONLY' ;
		}
		else {
			if (md5_file($previewFile) == md5_file($liveFile)) {
				// Files are identical
				return 'FILES_MATCH' ;
			}
			else {
				// Which is newer?
				if (filemtime($previewFile) < filemtime($liveFile)) {
					return 'LIVE_NEWER' ;
				}
				else {
					return 'PREVIEW_NEWER' ;
				}
			}
		}
	}
	
	function stripFileExtension($filename) {
		$dotPos = strrpos($filename,'.') ;
		if ($dotPos > 0) {
			return substr($filename,0,$dotPos) ;
		}
		else {
			return $filename ;
		}
	}
	
	function wipeDir($dir) {
		if($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				$path = $dir . '/' . $file ;
				if(is_file($path)) {
					if (!unlink($path)) {
						// Error
						echo 'Failed to delete <strong>' .  $path, '</strong><br />' ;
						return false ;
					}
				}
				else if(is_dir($path) && substr($file, 0, 1) != '.') {
					wipeDir($path) ;
					// @rmdir($path) ; This should be done by the calling script...
						// echo 'Removed dir <strong>' .  $path, '</strong><br />' ;
				}
			}
			closedir($handle) ;
			$final = rmdir($dir) ;
			return True ;
		}
		else {
			echo 'Failed to find: ' . $dir ;
			return False ;
		}
	}
	
	function simplifyContents($someHTML) {
		global $pathToRoot ;
		// Strip out path to root
		$someHTML = ereg_replace('\$pathToRoot=\'[./]*\';', '', $someHTML) ;
		// Strip out generic require call
		$someHTML = ereg_replace('(<\?[[:space:]]*include_once\(")([./]*)(general\.php"\) ; \?>)', "", $someHTML) ;
		// Convert <? puts... to <<includes>>
		return ereg_replace("(<\??)([[:space:]]*)(put)([[:space:]]*)(\()([[:space:]]*)([\"\'])([ a-zA-Z0-9_[.-.]\/]+).([a-zA-Z]{3,4})([\"\'])(\))([[:space:]]*)(;?)([[:space:]]*)(\?>)", "<< \\8.\\9 >>", $someHTML) ;
	}
	
	function unSimplifyContents($someHTML) {
		// Convert <<includes>> to <? puts...
		// Note [.-.] needed to treat "-" as a literal character!!
		return ereg_replace("(<<|&lt;&lt;)([[:space:]]*)([ a-zA-Z0-9_[.-.]\/]+).([a-zA-Z]{3,4})([[:space:]]*)(>>|&gt;&gt;)", "<? put(\"\\3.\\4\") ; ?>", $someHTML) ;
	}
	function correctGeneralInclude($someHTML, $sourceFilePath) {
		if (strpos($someHTML, "general.php") === False) {
			// Insert generic include call at beginning of document
			$numSlashes = substr_count($sourceFilePath, '/') ;
			$pathToRoot = '' ;
			for ($slsh=1; $slsh<$numSlashes; $slsh++) {
				$pathToRoot .= '../' ;
			}
			$someHTML = '<? $pathToRoot=\'' . $pathToRoot .'\'; include_once("' . $pathToRoot . 'general.php") ; ?>' . $someHTML ;
		}
		return $someHTML ;
	}
	
    function filesizeFormat($bytes, $format = '', $force = '') {
        $force = strtoupper($force);
        $defaultFormat = '%01d %s';
        if (strlen($format) == 0)
            $format = $defaultFormat;
        $bytes = max(0, (int) $bytes);
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $power = array_search($force, $units);
        if ($power === false)
            $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return sprintf($format, $bytes / pow(1024, $power), $units[$power]);
    }
	
	function makeSureFoldersExist($includesRoot) {
		$includesRoot = rtrim($includesRoot, '/') ;
		if (!file_exists($includesRoot)) {
			@mkdir($includesRoot) ;
		}
		if (!file_exists($includesRoot) . '/' . 'cms_preview') {
			@mkdir($includesRoot . '/' . 'cms_preview') ;
		}
		if (!file_exists($includesRoot) . '/' . 'cms_live') {
			@mkdir($includesRoot . '/' . 'cms_live') ;
		}
	}
	
	function tidyText($inputText) {
		$naughtyStrings = array ("%u201C", "%u201D" , "%u2013") ;
		$replaceWith = array ("\"" , "\"", "-") ;
		return str_replace($naughtyStrings, $replaceWith, $inputText) ;
	}
	
	function autop($pee, $br = 1) {
		$pee = $pee . "\n"; // just to make things a little easier, pad the end
		$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
		// Space things out a little
		$allblocks = '(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr)';
		$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
		$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
		$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
		$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
		$pee = preg_replace('!<p>([^<]+)\s*?(</(?:div|address|form)[^>]*>)!', "<p>$1</p>$2", $pee);
		$pee = preg_replace( '|<p>|', "$1<p>", $pee );
		$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
		$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
		$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
		$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
		$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
		$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
		if ($br) {
			$pee = preg_replace('/<(script|style).*?<\/\\1>/se', 'str_replace("\n", "<WPPreserveNewline />", "\\0")', $pee);
			$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
			$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
		}
		$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
		$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
		if (strpos($pee, '<pre') !== false)
			$pee = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " stripslashes('$1') .  stripslashes(clean_pre('$2'))  . '</pre>' ", $pee);
		$pee = preg_replace( "|\n</p>$|", '</p>', $pee );
		return $pee;
	}
	
	function nl2br2($inputString) {
		// Not currently used
		return str_replace(array("\r\n", "\r", "\n"), "%%linebreak%%", $inputString);
	}
	
	function translate($translate) {
		if (isSet($_SESSION['lang'])) {
			return $_SESSION['lang'][$translate] ;
		}
		else return $translate ;
	}
?>