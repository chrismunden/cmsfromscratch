<?
/*
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
	// where to store the local license key
	$storage_path = 'key.php' ;
	$passkey_storage_path = 'phpaudit_license.text' ;
	// where to store the best method to user first file
	$best_method='best_method.php';
	
	if (!function_exists('file_get_contents')) {
		function file_get_contents($path) {
			return implode('', file($path)) ;
		}
	}
	
	if (isset($_POST['licensekey'])) {
		$licenseKeyHandle = fopen($passkey_storage_path, 'w') ;
		fwrite($licenseKeyHandle, $_POST['licensekey']) ;
		fclose($licenseKeyHandle);
		$license = $_POST['licensekey'] ;
	}
	else if (!file_exists($passkey_storage_path)) {
		include 'inc-enter-license-form.php' ;
		exit ;
	}
	else {
		$license = file_get_contents($passkey_storage_path) ;
	}

echo "<h1>License</h1>". $license ;
	
$method = (file_exists($best_method)) ? file_get_contents($best_method) : 'phpaudit_exec_socket' ;

echo "<h1>Best method</h1>". $method ;

// Shouldn't have to change anything below this line --------------------------------------------------------

// list of all remote methods available
$methods = array('phpaudit_exec_socket', 'phpaudit_exec_curl', 'file_get_contents');

// the API fingerprint
$api_fingerprint='4bc053669c37d01ca0226523fb64081c'; 

// path to your order profile
$server='http://softwarefromscratch.com/orders/';

// path to the RPC server
$RPC='http://softwarefromscratch.com/phpaudit/rpc.php';

// Check the local key first
$returned=licensing::parse_xml(licensing::validate_local_key());

echo "<h1>Returned['status']</h1>". $returned['status'] ;

exit ;

/* echo $license, '<br />' ;
var_dump ($returned) ; exit;*/

// process the local key 
if ($returned['status']=='grab_new_key' || $returned['status']=='expired') 
	{
	// go remote to get licensing data
	$returned = licensing::parse_xml(licensing::go_remote($method, $server, $license));
	
	// var_dump ($returned) ; exit;
	
	// remote failed, set $returned to invalid
	// if (empty($returned)) { $returned['status']="invalid"; }
	// if (empty($returned)) { die('this is empty'); $returned['status']="invalid"; }  

	// we got a good response from the remote. We now need to grab a new
	// local license key and store it somewhere.
	if ($returned['status']=='active'||$returned['status']=='reissued') 
		{
		// grab a remote license key and write it to the correct place
		licensing::go_remote_api($RPC, $api_fingerprint, $license);

		$returned = licensing::parse_xml(licensing::validate_local_key());
		}
	}

exit ;
// Process the final status of the license after trying:
// 
// 1. local key first
// 2. going remote for a new key
// 3. getting a new key from the API
// 
// Just to note, #1 will happen every page refresh and #2/#3 will happen only 12 times a year.
if ($returned['status']!='active'&&$returned['status']!='reissued') 
	{
	// failed, set $returned to invalid
	if (empty($returned)) { $returned['status']="invalid"; }

	$errors=false;
	if ($returned['status']=="suspended") 
		{
		$errors='This license has been suspended.'; 
		}
	else if ($returned['status']=="pending") 
		{ 
		$errors='This license is pending admin approval.'; 
		}
	else if ($returned['status']=="expired") 
		{ 
		$errors='This license is expired.'; 
		}
/* 	else if ($returned['status']=='active'
		&&strcmp(md5('a4db21d8cedbfc53253c75763d6801fc'.$GLOBALS['token']), $returned['access_token'])!=0)
		{
		$errors='This license has an invalid checksum.'; 
		}*/
	else { $errors='This license appears to be invalid.'; }
	}

unset($server, $data, $parser, $values, $tags, $token);
?>

<?PHP 
// handle errors if you have any, if not, the license is active
if (isset($errors)) { die($errors); }
?>

<?PHP
/**
* Licensing Class.
* 
* @author Andy Rockwell <andy@solidphp.com>
*/
class licensing
	{
	/**
	* Write the local license key to somewhere.
	* 
	* @param string $local_key		The local key data to write.
	* @return You choose.
	*/
	function store_local_key($local_key)
		{
		// write the local key string to a file in the path of your choosing.

		// I'll provide an example for testing
		$fp=fopen($GLOBALS['storage_path'], 'w');
		fwrite($fp, $local_key);
		fclose($fp);
		}

	/**
	* Get the local key from where you stored it.
	* 
	* @return string The local license key.
	*/
	function get_stored_local_key()	{
		// get the local key from where you stored it
		// I'll provide an example for testing
		return file_get_contents($GLOBALS['storage_path']);
		}

	/**
	* Write the best remote licensing method
	* 
	* @param string $method Either phpaudit_exec_socket, phpaudit_exec_curl or file_get_contents
	* @return You choose.
	*/
	function write_best_method($method)
		{
		// write the best method string to a file in the path of your choosing.

		// I'll provide an example for testing
		$fp=fopen($GLOBALS['best_method'], 'w');
		fwrite($fp, $method);
		fclose($fp);
		}

	/**
	* Get the best remote licensing method previously saved
	* 
	* @return string The saved or default remote call method.
	*/
	function get_best_method()
		{
		// get the contents of the best method file

		// I'll provide an example for testing
		$method=file_get_contents($GLOBALS['best_method']);

		$method=(strlen(trim($method))==0)?$GLOBALS['methods'][0]:$method;

		if (in_array($method, $GLOBALS['methods']))
			{
			return $method;
			}

		return 'phpaudit_exec_socket';
		}

	/**
	* Validate a local license key
	* 
	* @return array The results of validation.
	*/
	function validate_local_key()
		{
		// get the local key and parse it into an array
		$raw_array=licensing::parse_local_key();
	
		if (!@is_array($raw_array)||$raw_array===false)
			{
			return "<verify status='grab_new_key' message='The local license key was empty.' />";
			}
	
		if ($raw_array[9]&&@strcmp(@md5("a4db21d8cedbfc53253c75763d6801fc".$raw_array[9]), $raw_array[10])!=0)
			{
			return "<verify status='invalid' message='The custom variables were tampered with.' />";
			}
	
		if (@strcmp(@md5("a4db21d8cedbfc53253c75763d6801fc".$raw_array[1]), $raw_array[2])!=0)
			{
			return "<verify status='invalid' message='The local license key checksum failed.' ".$raw_array[9]." />";
			}
	
		if ($raw_array[1]<time()&&$raw_array[1]!="never")
			{
				echo '<h1>Seems to have expired.</h1>' .
					  $raw_array[1] . ' is less than ' . time() . '<br />'
				;
				var_dump($raw_array) ;
			return "<verify status='expired' message='Fetching a new local key.' ".$raw_array[9]." />";
			}
	
		
		$directory_array=@explode(",", $raw_array[3]);
		$valid_dir=licensing::path_translated();
		$valid_dir=@md5("a4db21d8cedbfc53253c75763d6801fc".$valid_dir);
		if (!@in_array($valid_dir, $directory_array))
			{
			return "<verify status='invalid' message='The file path did not match what was expected.' ".$raw_array[9]." />";
			}
	
		$host_array=@explode(",", $raw_array[4]);
		if (!@in_array(@md5("a4db21d8cedbfc53253c75763d6801fc".$_SERVER['HTTP_HOST']), $host_array))
			{
			return "<verify status='invalid' message='The hostname did not match was was expected.' ".$raw_array[9]." />";
			}
	
		$ip_array=@explode(",", $raw_array[5]);
		$server_addr=licensing::server_addr();
		$ip_check=@in_array(@md5("a4db21d8cedbfc53253c75763d6801fc".$server_addr), $ip_array);
		if (!$ip_check)
			{
			$server_addr=substr($server_addr, 0, strrpos($server_addr, '.'));
			$ip_check=@in_array(@md5("a4db21d8cedbfc53253c75763d6801fc".$server_addr), $ip_array);
			}
	
		if (!$ip_check)
			{
			$server_addr=substr($server_addr, 0, strrpos($server_addr, '.'));
			$ip_check=@in_array(@md5("a4db21d8cedbfc53253c75763d6801fc".$server_addr), $ip_array);
			}
	
		if (!$ip_check)
			{
			return "<verify status='invalid_key' message='The IP address did not match what was expected.' ".$raw_array[9]." />";
			}
	
		return "<verify status='active' message='The license key is valid.' ".$raw_array[9]." />";
		}

	/**
	* Parse the XML
	* 
	* @return array The results the parsed XML.
	*/
	function parse_xml($data)
		{		
		$parser=@xml_parser_create('');
		@xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		@xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		@xml_parse_into_struct($parser, $data, $values, $tags);
		@xml_parser_free($parser);
		
		return $values[0]['attributes'];
		}

	/**
	* Parse the XML
	* 
	* @return array The results the parsed XML.
	*/
	function get_key()
		{
		// get the local license key
		$data=licensing::get_stored_local_key();
		if (!$data) {  return false; }
	
		// parse out what we don't need
		$buffer=@str_replace("<?PHP", "", $data);
		$buffer=@str_replace("?>", "", $buffer);
		$buffer=@str_replace("/*--", "", $buffer);
		$buffer=@str_replace("--*/", "", $buffer);
	
		return @str_replace("\n", "", $buffer);
		}
	
	/**
	* Parse the cleaned local key string into an array
	* 
	* @return array The results the parsed local key.
	*/
	function parse_local_key()
		{
		$raw_data=@base64_decode(licensing::get_key()); 
		$raw_array=@explode("|", $raw_data);
		if (@is_array($raw_array)&&@count($raw_array)<8) {  return false; }
	
		return $raw_array;
		}
	
	/**
	* Make a token to be used with DNS Spoof Protection
	* 
	* @return array The token string.
	*/
	function make_token() { return md5('a4db21d8cedbfc53253c75763d6801fc'.time()); }
	
	/**
	* Go remote to validate the license using cURL
	* 
	* @param array $array The connection string.
	* @return array The XML results of the request.
	*/
	function phpaudit_exec_curl($array)
		{
		$array=@explode("?", $array);
	
		$link=curl_init();
		curl_setopt($link, CURLOPT_URL, $array[0]);
		curl_setopt($link, CURLOPT_POSTFIELDS, $array[1]);
		curl_setopt($link, CURLOPT_VERBOSE, 0);
		curl_setopt($link, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($link, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($link, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($link, CURLOPT_MAXREDIRS, 6);
		curl_setopt($link, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($link, CURLOPT_TIMEOUT, 15); // 60
		$results=curl_exec($link);
		if (curl_errno($link)>0)
			{
			curl_close($link);
			return false;
			}
		curl_close($link);
	
		if (@strpos($results, "verify")===false) { return false; }
	
		return $results;
		}
	
	/**
	* Go remote to validate the license using fsockopen()
	* 
	* @param string $http_host		ex. phpaudit.com
	* @param string $http_dir		ex. /admin
	* @param string $http_file		ex. /validate_internal.php
	* @param string $querystring	The licensing access data to pass in for validation.
	* @return array The XML results of the request.
	*/
	function phpaudit_exec_socket($http_host, $http_dir, $http_file, $querystring)
		{
		$fp=@fsockopen($http_host, 80, $errno, $errstr, 10); // was 5
		if (!$fp) { return false; }
	
		// build the headers to use
		$header="POST {$http_dir}{$http_file} HTTP/1.0\r\n";
		$header.="Host: {$http_host}\r\n";
		$header.="Content-type: application/x-www-form-urlencoded\r\n";
		$header.="User-Agent: PHPAudit v2 (http://www.phpaudit.com)\r\n";
		$header.="Content-length: ".@strlen($querystring)."\r\n";
		$header.="Connection: close\r\n\r\n";
		$header.=$querystring;

		// handle the session
		$data=false;
		if (@function_exists('stream_set_timeout')) { stream_set_timeout($fp, 20); }
		@fputs($fp, $header);

		if (@function_exists('socket_get_status')) { $status=@socket_get_status($fp); } 
		else { $status=true; }

		while (!@feof($fp)&&$status) 
			{
			$data.=@fgets($fp, 1024);

			if (@function_exists('socket_get_status')) { $status=@socket_get_status($fp); } 
			else 
				{
				if (@feof($fp)==true) { $status=false; } 
				else { $status=true; }
				}
			}

		@fclose ($fp);

		// uncomment to debug the return
		// echo "<textarea rows='100' cols='100'>".$data."</textarea>"; die;

		// we had a bad header response
		if (!strpos($data, '200')) { return false; }
		
		// the response was empty, something went wrong
		if (!$data) { return false; }

		// separate the header from the validation XML
		$data=@explode("\r\n\r\n", $data, 2);

		// no validation XML was returned!
		if (!$data[1]) { return false; }

		// We have something returned, but it's not what is expected
		if (@strpos($data[1], 'verify')===false) { return false; }

		// return the XML validation string
		return $data[1];
		}
	
	/**
	* Get the directory path
	* 
	* @return string The directory path.
	*/
	function path_translated()
		{
		if (isset($_SERVER['PATH_TRANSLATED']))
			{
			return @substr($_SERVER['PATH_TRANSLATED'], 0, @strrpos($_SERVER['PATH_TRANSLATED'], "/"));
			}
	
		if (isset($_SERVER['SCRIPT_FILENAME']))
			{
			$local_path=substr($_SERVER['SCRIPT_FILENAME'], 0, @strrpos($_SERVER['SCRIPT_FILENAME'], "/"));
			if (!$local_path) 
				{
				$local_path=@substr($_SERVER['SCRIPT_FILENAME'], 0, @strrpos($_SERVER['SCRIPT_FILENAME'], "\\"));
				}
	
			return trim($local_path);
			}
	
		return @substr($_SERVER['ORIG_PATH_TRANSLATED'], 0, @strrpos($_SERVER['ORIG_PATH_TRANSLATED'], "\\"));
		}
	
	/**
	* Get the IP address
	* 
	* @return string The IP address.
	*/
	function server_addr()
		{
		return (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'] ;
		}

	/**
	* Make a remote call to the licensing server.
	* 
	* @param string $method	The licensing method to use.
	* @param string $server	The server URL to use
	* @param string $license	The license key to validate.
	* @return string The XML validation string.
	*/
	function go_remote($method, $server, $license)
		{
		$methods=$GLOBALS['methods'];

		// if we have a previously stored license method, use that first
		if ($method)
			{
			unset($methods[$method]);
			$methods[]=$method;
			$methods=array_reverse($methods);
			}
		
		// build a querystring of the licensing data
		$query_string="license={$license}";
		$query_string.="&access_directory=".licensing::path_translated();
		$query_string.="&access_ip=".licensing::server_addr();
		$query_string.="&access_host={$_SERVER['HTTP_HOST']}";
/* 		$query_string.='&access_token=';
		$query_string.=$token=licensing::make_token();*/
		
		// loop all licensing methods and break on the first that returns $data
		$data=false;
		foreach($methods as $license_method) 
			{
			// break the $server string into parts
			$sinfo=@parse_url($server);
	
			// try fsockopen()
			if ($license_method=='phpaudit_exec_socket'&&!$data)
				{
				$data=@licensing::phpaudit_exec_socket($sinfo['host'], $sinfo['path'], '/validate_internal.php', $query_string);
				}
	
			// try cURL
			if ($license_method=='phpaudit_exec_curl'&&!$data)
				{
				$data=@licensing::phpaudit_exec_curl("{$server}/validate_internal.php?{$query_string}");
				}
	
			// try using the fopen() wrappers
			if ($license_method=='file_get_contents'&&!$data)
				{
				$data=@file_get_contents("{$server}validate_internal.php?{$query_string}");
				}
	
			// we have data, break out of the loop
			if ($data) 
				{ 
				// write the method which was successful first
				licensing::write_best_method($license_method);
				break; 
				}
			}

		return $data; // the licensing data
		}

	/**
	* Make a remote call to the licensing server.
	* 
	* @param string $RPC				The URL to the admin rpc.php file.
	* @param string $api_fingerprint	The API fingerprint to use.
	* @param string $license			The license key to validate.
	* @return string The local key string.
	*/
	function go_remote_api($RPC, $api_fingerprint, $license)
		{
		require_once dirname(__FILE__) . '/XMLRPC.class.php'; 
 
		$api = new IXR_Client($RPC);  

		$keydata=array('api_key' => $api_fingerprint, 'license_key' => $license);  
			
		$api->query('license.get_local_key', $keydata); 
	
		$local_key=$api->getResponse();

		// write the local key
		licensing::store_local_key($local_key);

		return $local_key;
		}
	}
?>










<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Untitled</title>
</head>

<body>

<h1>SUCCESS</h1>

</body>
</html>
