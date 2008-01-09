<?php
//////////////////////////////////////////////////////////////
///  phpthumb() by James Heinrich <info@silisoftware.com>   //
//        available at http://phpthumb.sourceforge.net     ///
//////////////////////////////////////////////////////////////
///                                                         //
// See: phpthumb.changelog.txt for recent changes           //
// See: phpthumb.readme.txt for usage instructions          //
//                                                         ///
//////////////////////////////////////////////////////////////

error_reporting(E_ALL);
ini_set('display_errors', '1');
if (!@ini_get('safe_mode')) {
	set_time_limit(60);  // shouldn't take nearly this long in most cases, but with many filter and/or a slow server...
}

// this script relies on the superglobal arrays, fake it here for old PHP versions
if (phpversion() < '4.1.0') {
	$_SERVER = $HTTP_SERVER_VARS;
	$_GET    = $HTTP_GET_VARS;
}


if (file_exists('phpthumb.config.php')) {
	ob_start();
	if (include_once(dirname(__FILE__).'/phpthumb.config.php')) {
		// great
	} else {
		ob_end_flush();
		die('failed to include_once('.dirname(__FILE__).'/phpthumb.config.php) - realpath="'.realpath(dirname(__FILE__).'/phpthumb.config.php').'"');
	}
	ob_end_clean();
} elseif (file_exists('phpthumb.config.php.default')) {
	die('Please rename "phpthumb.config.php.default" to "phpthumb.config.php"');
} else {
	die('failed to include_once('.dirname(__FILE__).'/phpthumb.config.php) - realpath="'.realpath(dirname(__FILE__).'/phpthumb.config.php').'"');
}

if (!@$_SERVER['QUERY_STRING']) {
	die('$_SERVER[QUERY_STRING] is empty');
}
if (@$PHPTHUMB_CONFIG['high_security_enabled']) {
	if (!@$_GET['hash']) {
		die('ERROR: missing hash');
	}
	if (strlen($PHPTHUMB_CONFIG['high_security_password']) < 5) {
		die('ERROR: strlen($PHPTHUMB_CONFIG[high_security_password]) < 5');
	}
	if ($_GET['hash'] != md5(str_replace('&hash='.$_GET['hash'], '', $_SERVER['QUERY_STRING']).$PHPTHUMB_CONFIG['high_security_password'])) {
		die('ERROR: invalid hash');
	}
}

if (!function_exists('ImageJPEG') && !function_exists('ImagePNG') && !function_exists('ImageGIF')) {
	// base64-encoded error image in GIF format
	$ERROR_NOGD = 'R0lGODlhIAAgALMAAAAAABQUFCQkJDY2NkZGRldXV2ZmZnJycoaGhpSUlKWlpbe3t8XFxdXV1eTk5P7+/iwAAAAAIAAgAAAE/vDJSau9WILtTAACUinDNijZtAHfCojS4W5H+qxD8xibIDE9h0OwWaRWDIljJSkUJYsN4bihMB8th3IToAKs1VtYM75cyV8sZ8vygtOE5yMKmGbO4jRdICQCjHdlZzwzNW4qZSQmKDaNjhUMBX4BBAlmMywFSRWEmAI6b5gAlhNxokGhooAIK5o/pi9vEw4Lfj4OLTAUpj6IabMtCwlSFw0DCKBoFqwAB04AjI54PyZ+yY3TD0ss2YcVmN/gvpcu4TOyFivWqYJlbAHPpOntvxNAACcmGHjZzAZqzSzcq5fNjxFmAFw9iFRunD1epU6tsIPmFCAJnWYE0FURk7wJDA0MTKpEzoWAAskiAAA7';
	header('Content-Type: image/gif');
	echo base64_decode($ERROR_NOGD);
	exit;
}

// returned the fixed string if the evil "magic_quotes_gpc" setting is on
if (get_magic_quotes_gpc()) {
	$RequestVarsToStripSlashes = array('src', 'wmf', 'file', 'err', 'goto', 'down');
	foreach ($RequestVarsToStripSlashes as $key) {
		if (isset($_GET[$key])) {
			$_GET[$key] = stripslashes($_GET[$key]);
		}
	}
}

// instantiate a new phpthumb() object
ob_start();
if (!include_once(dirname(__FILE__).'/phpthumb.class.php')) {
	ob_end_flush();
	die('failed to include_once("'.realpath(dirname(__FILE__).'/phpthumb.class.php').'")');
}
ob_end_clean();
$phpthumb = new phpthumb();

if (@$_GET['src'] && isset($_GET['md5s']) && empty($_GET['md5s'])) {
	if (eregi('^(f|ht)tp[s]?://', $_GET['src'])) {
		if ($fp_source = @fopen($_GET['src'], 'rb')) {
			$filedata = '';
			while (true) {
				$buffer = fread($fp_source, 16384);
				if (strlen($buffer) == 0) {
					break;
				}
				$filedata .= $buffer;
			}
			fclose($fp_source);
			$md5s = md5($filedata);
		}
	} else {
		$SourceFilename = $phpthumb->ResolveFilenameToAbsolute($_GET['src']);
		if (is_readable($SourceFilename)) {
			$md5s = phpthumb_functions::md5_file_safe($SourceFilename);
		} else {
			$phpthumb->ErrorImage('ERROR: "'.$SourceFilename.'" cannot be read');
		}
	}
	if (@$_SERVER['HTTP_REFERER']) {
		$phpthumb->ErrorImage('&md5s='.$md5s);
	} else {
		die('&md5s='.$md5s);
	}
}

foreach ($PHPTHUMB_CONFIG as $key => $value) {
	$keyname = 'config_'.$key;
	$phpthumb->$keyname = $value;
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '1') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

$parsed_url_referer = parse_url(@$_SERVER['HTTP_REFERER']);
if ($phpthumb->config_nooffsitelink_require_refer && !in_array(@$parsed_url_referer['host'], $phpthumb->config_nohotlink_valid_domains)) {
	$phpthumb->ErrorImage('config_nooffsitelink_require_refer enabled and '.(@$parsed_url_referer['host'] ? '"'.$parsed_url_referer['host'].'" is not an allowed referer' : 'no HTTP_REFERER exists'));
}
$parsed_url_src = parse_url(@$_GET['src']);
if ($phpthumb->config_nohotlink_enabled && $phpthumb->config_nohotlink_erase_image && eregi('^(f|ht)tp[s]?://', @$_GET['src']) && !in_array(@$parsed_url_src['host'], $phpthumb->config_nohotlink_valid_domains)) {
	$phpthumb->ErrorImage($phpthumb->config_nohotlink_text_message);
}


////////////////////////////////////////////////////////////////
// You may want to pull data from a database rather than a physical file
// If so, uncomment the following $SQLquery line (modified to suit your database)
// Note: this must be the actual binary data of the image, not a URL or filename
// see http://www.billy-corgan.com/blog/archive/000143.php for a brief tutorial on this section

//$SQLquery = 'SELECT `picture` FROM `products` WHERE (`id` = \''.mysql_escape_string(@$_GET['id']).'\')';
if (@$SQLquery) {

	// change this information to match your server
	$hostname = 'localhost';
	$username = 'username';
	$password = 'password';
	$database = 'database';
	if ($cid = @mysql_connect($hostname, $username, $password)) {
		if (@mysql_select_db($database, $cid)) {
			if ($result = @mysql_query($SQLquery, $cid)) {
				if ($row = @mysql_fetch_array($result)) {

					mysql_free_result($result);
					mysql_close($cid);
					$phpthumb->setSourceData($row[0]);
					unset($row);

				} else {
					mysql_free_result($result);
					mysql_close($cid);
					$phpthumb->ErrorImage('no matching data in database.');
					//$phpthumb->ErrorImage('no matching data in database. MySQL said: "'.mysql_error($cid).'"');
				}
			} else {
				mysql_close($cid);
				$phpthumb->ErrorImage('Error in MySQL query: "'.mysql_error($cid).'"');
			}
		} else {
			mysql_close($cid);
			$phpthumb->ErrorImage('cannot select MySQL database: "'.mysql_error($cid).'"');
		}
	} else {
		$phpthumb->ErrorImage('cannot connect to MySQL server');
	}
	unset($_GET['id']);
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '2') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

$allowedGETparameters = array('src', 'new', 'w', 'h', 'f', 'q', 'sx', 'sy', 'sw', 'sh', 'zc', 'bc', 'bg', 'bgt', 'fltr', 'file', 'goto', 'err', 'xto', 'ra', 'ar', 'aoe', 'far', 'iar', 'maxb', 'down', 'phpthumbDebug', 'hash', 'md5s');
foreach ($_GET as $key => $value) {
	if (in_array($key, $allowedGETparameters)) {
		$phpthumb->$key = $value;
	} else {
		$phpthumb->ErrorImage('Forbidden parameter: '.$key);
	}
}

if (!empty($PHPTHUMB_DEFAULTS)) {
	foreach ($PHPTHUMB_DEFAULTS as $key => $value) {
		if ($PHPTHUMB_DEFAULTS_GETSTRINGOVERRIDE || !isset($_GET[$key])) {
			$phpthumb->$key = $value;
		}
	}
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '3') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////


// check to see if file can be output from source with no processing or caching
$CanPassThroughDirectly = true;
if (!empty($phpthumb->rawImageData)) {
	// data from SQL, should be fine
} elseif (!@is_file(@$_GET['src']) || !@is_readable(@$_GET['src'])) {
	$CanPassThroughDirectly = false;
}
foreach ($_GET as $key => $value) {
	switch ($key) {
		case 'src':
			// allowed
			break;

		default:
			// all other parameters will cause some processing,
			// therefore cannot pass through original image unmodified
			$CanPassThroughDirectly = false;
			$UnAllowedGET[] = $key;
			break;
	}
}
if (!empty($UnAllowedGET)) {
	$phpthumb->DebugMessage('Cannot pass through directly because $_GET['.implode(';', array_unique($UnAllowedGET)).'] are set', __FILE__, __LINE__);
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '4') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

function SendSaveAsFileHeaderIfNeeded() {
	global $phpthumb;
	if (@$_GET['down']) {
		$downloadfilename = ereg_replace('[/\\:\*\?"<>|]', '_', $_GET['down']);
		if (phpthumb_functions::version_compare_replacement(phpversion(), '4.1.0', '>=')) {
			$downloadfilename = trim($downloadfilename, '.');
		}
		if (@$downloadfilename) {
			$phpthumb->DebugMessage('SendSaveAsFileHeaderIfNeeded() sending header: Content-Disposition: attachment; filename="'.$downloadfilename.'"', __FILE__, __LINE__);
			header('Content-Disposition: attachment; filename="'.$downloadfilename.'"');
			return true;
		}
	}
	$phpthumb->DebugMessage('SendSaveAsFileHeaderIfNeeded() sending header: Content-Disposition: inline', __FILE__, __LINE__);
	header('Content-Disposition: inline');
	return true;
}

while ($CanPassThroughDirectly && $phpthumb->src) {
	// no parameters set, passthru
	$SourceFilename = $phpthumb->ResolveFilenameToAbsolute($phpthumb->src);

	if (!GetImageSize($SourceFilename)) {

		// security -- prevent passing through of non-image files
		$phpthumb->DebugMessage('GetImageSize('.$SourceFilename.') failed (invalid image?)', __FILE__, __LINE__);

	} else if (@$_GET['phpthumbDebug']) {

		$phpthumb->DebugMessage('Would have passed "'.$SourceFilename.'" through directly, but skipping due to phpthumbDebug', __FILE__, __LINE__);

	} else {

		// security checks
		if ($GetImageSize = @GetImageSize($SourceFilename)) {
			$ImageCreateFunctions = array(1=>'ImageCreateFromGIF', 2=>'ImageCreateFromJPEG', 3=>'ImageCreateFromPNG');
			if (@$ImageCreateFunctions[$GetImageSize[2]]) {
				$theFunction = $ImageCreateFunctions[$GetImageSize[2]];
				if (function_exists($theFunction) && ($dummyImage = @$theFunction($SourceFilename))) {
					// great
					unset($dummyImage);
				} else {
					$phpthumb->DebugMessage('Not passing "'.$SourceFilename.'" through directly because '.$theFunction.'() failed', __FILE__, __LINE__);
					break;
				}
			} else {
				$phpthumb->DebugMessage('Not passing "'.$SourceFilename.'" through directly because GetImageSize() returned unhandled image type "'.$GetImageSize[2].'"', __FILE__, __LINE__);
				break;
			}
		} else {
			$phpthumb->DebugMessage('Not passing "'.$SourceFilename.'" through directly because GetImageSize() failed', __FILE__, __LINE__);
			break;
		}
		SendSaveAsFileHeaderIfNeeded();
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', @filemtime($SourceFilename)).' GMT');
		if ($getimagesize = @GetImageSize($SourceFilename)) {
			header('Content-Type: '.phpthumb_functions::ImageTypeToMIMEtype($getimagesize[2]));
		}
		@readfile($SourceFilename);
		exit;

	}
	break;
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '5') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

// check to see if file already exists in cache, and output it with no processing if it does
$phpthumb->SetCacheFilename();
if (is_file($phpthumb->cache_filename)) {
	$parsed_url = @parse_url(@$_SERVER['HTTP_REFERER']);
	if ($phpthumb->config_nooffsitelink_enabled && @$_SERVER['HTTP_REFERER'] && !in_array(@$parsed_url['host'], $phpthumb->config_nooffsitelink_valid_domains)) {
		$phpthumb->DebugMessage('Would have used cached (image/'.$phpthumb->thumbnailFormat.') file "'.$phpthumb->cache_filename.'" (Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($phpthumb->cache_filename)).' GMT), but skipping because $_SERVER[HTTP_REFERER] ('.@$_SERVER['HTTP_REFERER'].') is not in $phpthumb->config_nooffsitelink_valid_domains ('.implode(';', $phpthumb->config_nooffsitelink_valid_domains).')', __FILE__, __LINE__);
	} elseif ($phpthumb->phpthumbDebug) {
		$phpthumb->DebugMessage('Would have used cached file, but skipping due to phpthumbDebug', __FILE__, __LINE__);
		$phpthumb->DebugMessage('* Would have sent headers (1): Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($phpthumb->cache_filename)).' GMT', __FILE__, __LINE__);
		if ($getimagesize = @GetImageSize($phpthumb->cache_filename)) {
			$phpthumb->DebugMessage('* Would have sent headers (2): Content-Type: '.phpthumb_functions::ImageTypeToMIMEtype($getimagesize[2]), __FILE__, __LINE__);
		}
		if (ereg('^'.preg_quote(str_replace($phpthumb->osslash, '/', $PHPTHUMB_CONFIG['document_root'])).'(.*)$', str_replace($phpthumb->osslash, '/', $phpthumb->cache_filename), $matches)) {
			$phpthumb->DebugMessage('* Would have sent headers (3): Location: '.dirname($matches[1]).'/'.urlencode(basename($matches[1])), __FILE__, __LINE__);
		} else {
			$phpthumb->DebugMessage('* Would have sent headers (3): readfile('.$phpthumb->cache_filename.')', __FILE__, __LINE__);
		}
	} else {
		SendSaveAsFileHeaderIfNeeded();
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($phpthumb->cache_filename)).' GMT');
		if ($getimagesize = @GetImageSize($phpthumb->cache_filename)) {
			header('Content-Type: '.phpthumb_functions::ImageTypeToMIMEtype($getimagesize[2]));
		}
		if (ereg('^'.preg_quote(str_replace($phpthumb->osslash, '/', $PHPTHUMB_CONFIG['document_root'])).'(.*)$', str_replace($phpthumb->osslash, '/', $phpthumb->cache_filename), $matches)) {
			// BEN HACKED THIS
			header('Location:cache/'.urlencode(basename($matches[1])));
			// WAS
			// header('Location: '.dirname($matches[1]).'/'.urlencode(basename($matches[1])));
		} else {
			@readfile($phpthumb->cache_filename);
		}
		exit;
	}
}
else {
	$phpthumb->DebugMessage('Cached file "'.$phpthumb->cache_filename.'" does not exist, processing as normal', __FILE__, __LINE__);
}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '6') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

if ($phpthumb->rawImageData) {

	// great

} elseif (!empty($_GET['new'])) {

	// generate a blank image resource of the specified size/background color/opacity
	if (($phpthumb->w <= 0) || ($phpthumb->h <= 0)) {
		$phpthumb->ErrorImage('"w" and "h" parameters required for "new"');
	}
	@list($bghexcolor, $opacity) = explode('|', $_GET['new']);
	if (!phpthumb_functions::IsHexColor($bghexcolor)) {
		$phpthumb->ErrorImage('BGcolor parameter for "new" is not valid');
	}
	$opacity = (strlen($opacity) ? $opacity : 100);
	if ($phpthumb->gdimg_source = phpthumb_functions::ImageCreateFunction($phpthumb->w, $phpthumb->h)) {
		$alpha = (100 - min(100, max(0, $opacity))) * 1.27;
		if ($alpha) {
			$phpthumb->is_alpha = true;
			ImageAlphaBlending($phpthumb->gdimg_source, false);
			ImageSaveAlpha($phpthumb->gdimg_source, true);
		}
		$new_background_color = phpthumb_functions::ImageHexColorAllocate($phpthumb->gdimg_source, $bghexcolor, false, $alpha);
		ImageFilledRectangle($phpthumb->gdimg_source, 0, 0, $phpthumb->w, $phpthumb->h, $new_background_color);
	} else {
		$phpthumb->ErrorImage('failed to create "new" image ('.$phpthumb->w.'x'.$phpthumb->h.')');
	}

} elseif (!$phpthumb->src) {

	$phpthumb->ErrorImage('Usage: '.$_SERVER['PHP_SELF'].'?src=/path/and/filename.jpg'."\n".'read Usage comments for details');

} elseif (substr(strtolower($phpthumb->src), 0, 7) == 'http://') {

	ob_start();
	$HTTPurl = strtr($phpthumb->src, array(' '=>'%20'));
	if ($fp = fopen($HTTPurl, 'rb')) {

		$rawImageData = '';
		do {
			$buffer = fread($fp, 8192);
			if (strlen($buffer) == 0) {
				break;
			}
			$rawImageData .= $buffer;
		} while (true);
		fclose($fp);
		$phpthumb->setSourceData($rawImageData, urlencode($phpthumb->src));

	} else {

		$fopen_error = strip_tags(ob_get_contents());
		ob_end_clean();
		if (ini_get('allow_url_fopen')) {
			$phpthumb->ErrorImage('cannot open "'.$HTTPurl.'" - fopen() said: "'.$fopen_error.'"');
		} else {
			$phpthumb->ErrorImage('"allow_url_fopen" disabled');
		}

	}
	ob_end_clean();

}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '7') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

$phpthumb->GenerateThumbnail();

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '8') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

if ($phpthumb->file) {

	$phpthumb->RenderToFile($phpthumb->ResolveFilenameToAbsolute($phpthumb->file));
	if ($phpthumb->goto && (substr(strtolower($phpthumb->goto), 0, strlen('http://')) == 'http://')) {
		// redirect to another URL after image has been rendered to file
		header('Location: '.$phpthumb->goto);
		exit;
	}

} else {

	if ((file_exists($phpthumb->cache_filename) && is_writable($phpthumb->cache_filename)) || is_writable(dirname($phpthumb->cache_filename))) {

		$phpthumb->CleanUpCacheDirectory();
		$phpthumb->RenderToFile($phpthumb->cache_filename);

	} else {

		$phpthumb->DebugMessage('Cannot write to $phpthumb->cache_filename ('.$phpthumb->cache_filename.') because that directory ('.dirname($phpthumb->cache_filename).') is not writable', __FILE__, __LINE__);

	}

}

////////////////////////////////////////////////////////////////
// Debug output, to try and help me diagnose problems
if (@$_GET['phpthumbDebug'] == '9') {
	$phpthumb->phpthumbDebug();
}
////////////////////////////////////////////////////////////////

$phpthumb->OutputThumbnail();

?>