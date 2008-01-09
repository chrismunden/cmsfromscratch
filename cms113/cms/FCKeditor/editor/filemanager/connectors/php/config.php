<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the File Manager Connector for PHP.
 */

global $Config ;

// SECURITY: You must explicitelly enable this "connector". (Set it to "true").
// WARNING: don't just set "ConfigIsEnabled = true", you must be sure that only 
//		authenticated users can access this file or use some kind of session checking.
$Config['Enabled'] = true ;

$path = $_SERVER["REQUEST_URI"] ;
$relativePathFromWebServerRoot =  substr($path, 0, strpos($path, "/", 1) );
// Coming out as /CMS, why???



// Path to user files relative to the document root.
// This is what is inserted into the HTML markup
$Config['UserFilesPath'] = urldecode(rtrim(str_replace('cms/FCKeditor/editor/filemanager/connectors/php', '', dirname($_SERVER['SCRIPT_NAME'])), '/')) ;
if ($Config['UserFilesPath'] == '') $Config['UserFilesPath'] = '/' ;

// Fill the following value it you prefer to specify the absolute path for the user files directory. Useful if you are using a virtual directory, symbolic link or alias. Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
// BH note: This is used for browsing the server.. should equate to the real path of the folder where /cms/ is installed
$Config['UserFilesAbsolutePath'] = realpath('../../../../../../') ;

// Due to security issues with Apache modules, it is reccomended to leave the following setting enabled.
$Config['ForceSingleExtension'] = true ;
// Perform additional checks for image files
// if set to true, validate image size (using getimagesize)
$Config['SecureImageUploads'] = true;
// What the user can do with this connector
$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFolders', 'GetFoldersAndFiles', 'CreateFolder') ;
// Allowed Resource Types
$Config['ConfigAllowedTypes'] = array('File', 'Image', 'Flash', 'Media') ;
// For security, HTML is allowed in the first Kb of data for files having the following extensions only.
$Config['HtmlExtensions'] = array("html", "htm", "xml", "xsd", "txt", "js") ;

$Config['AllowedExtensions']['File']	= array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'php', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip') ;
$Config['DeniedExtensions']['File']		= array() ;
$Config['FileTypesPath']['File']		= $Config['UserFilesPath'] ;
$Config['FileTypesAbsolutePath']['File']= $Config['UserFilesAbsolutePath'] ;
$Config['QuickUploadPath']['File']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['File']= $Config['UserFilesAbsolutePath'] ;

$Config['AllowedExtensions']['Image']	= array('bmp','gif','jpeg','jpg','png','psd','tif','tiff') ;
$Config['DeniedExtensions']['Image']	= array() ;
$Config['FileTypesPath']['Image']		= $Config['UserFilesPath']. '/cms/cmsimages/' ;
$Config['FileTypesAbsolutePath']['Image']= $Config['UserFilesAbsolutePath'].'/cms/cmsimages/' ; // This is correct, do not change
$Config['QuickUploadPath']['Image']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Image']= $Config['UserFilesAbsolutePath'] ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;
$Config['FileTypesPath']['Flash']		= $Config['UserFilesPath'] ;
$Config['FileTypesAbsolutePath']['Flash']= $Config['UserFilesAbsolutePath'] ;
$Config['QuickUploadPath']['Flash']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Flash']= $Config['UserFilesAbsolutePath'] ;

$Config['AllowedExtensions']['Media']	= array('aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm', 'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv') ;
$Config['DeniedExtensions']['Media']	= array() ;
$Config['FileTypesPath']['Media']		= $Config['UserFilesPath'] ;
$Config['FileTypesAbsolutePath']['Media']= $Config['UserFilesAbsolutePath'] ;
$Config['QuickUploadPath']['Media']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['Media']= $Config['UserFilesAbsolutePath'] ;

?>
