<?
	echo '$_SERVER[\'SERVER_NAME\'] = ' . $_SERVER['SERVER_NAME'] ;
	echo '<br />' ;
	echo '$_SERVER[\'SCRIPT_NAME\'] = ' . $_SERVER['SCRIPT_NAME'] ;
	echo '<br />' ;
	echo 'dirname($_SERVER[\'SCRIPT_NAME\']) = ' . dirname($_SERVER['SCRIPT_NAME']) ;
	echo '<br /><h2>Step by step</h2>' ;
	
	echo 'a:' . dirname($_SERVER['SCRIPT_NAME']) ;
	echo '<br />' ;
	echo 'b:' . str_replace('cms/FCKeditor/editor/filemanager/connectors/php', '', dirname($_SERVER['SCRIPT_NAME'])) ;
	echo '<br />' ;
	echo 'c:' . rtrim(str_replace('cms/FCKeditor/editor/filemanager/connectors/php', '', dirname($_SERVER['SCRIPT_NAME'])), '/') ;
	echo '<br />' ;
	
	$path1 = urldecode(rtrim(str_replace('cms/FCKeditor/editor/filemanager/connectors/php', '', dirname($_SERVER['SCRIPT_NAME'])), '/')) ;
	echo '1 = ' . $path1 ;
	echo urldecode($path1) ;
	echo '<br />' ;
	echo '2 = ' . realpath('../../../../../') ;
	echo '<br />' ;
    echo '$_SERVER[\'PHP_SELF\'] = ' . $_SERVER['PHP_SELF'] ;
	echo '<br />' ;
    echo '$_SERVER[\'DOCUMENT_ROOT\'] = ' . $_SERVER['DOCUMENT_ROOT'] ;
	echo '<br />' ;
    echo '$_SERVER[\'SCRIPT_FILENAME\'] = ' . $_SERVER['SCRIPT_FILENAME'] ;
	echo '<br />' ;
    echo 'realpath(../../../../../../) = ' . realpath('../../../../../') ;
	echo '<br />' ;
    echo 'dirname(../../../../../../) = ' . dirname('../../../../../') ;
?>