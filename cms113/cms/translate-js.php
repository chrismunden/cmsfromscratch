<?
	Header("content-type: application/x-javascript") ;
	$sesh = @session_start() ;
	
	if (isSet($_SESSION['lang'])) {
		$langArray = $_SESSION['lang'] ;
		// Loop through the array and write a correponding JS array..
		echo 'var translateArray = new Object;' . "\n" ;
		while (list($key, $val) = each($langArray)) {
			echo 'translateArray["' . $key . '"] = "' . html_entity_decode($val) . '" ;' . "\n" ;
		}
	}
?>

function translate(textToTranslate) {
	if (typeof(window['translateArray']) == "undefined") return textToTranslate ;
	else if (!translateArray[textToTranslate]) return textToTranslate ;
	else return fixUTF(translateArray[textToTranslate]) ;
}

function fixUTF(data) {
	data = data.replace(/&#(\d+);/g,
		function(wholematch, parenmatch1) {
			return String.fromCharCode(+parenmatch1);
		});
	return data ;
}