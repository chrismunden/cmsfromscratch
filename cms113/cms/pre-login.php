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
 
 
	$sesh = @session_start() ;
	$settings = False ;
	// Try to read settings.text in ../../, else try ./
	
	if (file_exists('../../settings.text')) {
		$settingsPath = '../../settings.text' ;
		$settings = @file_get_contents($settingsPath) ;
	}
	if (False === $settings) {
		if (file_exists('settings.text')) {
			$settingsPath = 'settings.text' ;
			$settings = @file_get_contents($settingsPath) ;
		}
		if (False === $settings) {
			// try to create in ../../
			$useDefaultSettings = True ;
			$createdSafely = @fopen('../../settings.text', 'w') ;
			if ($createdSafely) {
				$settingsPath = '../../settings.text' ;
			}
			// Also try to set file permissions on /cms/includes/ and ../ ?
			@chmod("includes/", 0755) ;
			@chmod("../", 0755) ;
		}
		if (False === $createdSafely) {
			// try to create in ./
			$settingsPath = 'settings.text' ;
			$createdLessSafely = @fopen('settings.text', 'w') ;
			$useDefaultSettings = True ;
		}
	}
	if ($settings) {
		$settingsArray = unserialize($settings) ;
		if (sizeof($settingsArray) == 0) {
			$useDefaultSettings = True ;
		}
		else {
			// Something found!
			$_SESSION['CMS_Title'] = $settingsArray['cms-description'] ;
			$_SESSION['settingsInitialized'] = True ;
			switch ($settingsArray['client-language']) {
				case "German" :
					$_SESSION['lang'] = Array (
						"Please log in"   => "Bitte anmelden",
						"Log in"   => "Anmelden",
						"Submit"   => "Absenden",
						"Login failed"   => "Anmeldung fehlgeschlagen",
						"Delete"   => "L&ouml;schen",
						"Rename file"   => "Datei umbenennen",
						"Publish"   => "Publizieren",
						"Restore"   => "Wiederherstellen",
						"Hide"   => "Verbergen",
						"Preview page"   => "Seitenvorschau",
						"New page"   => "Neue Seite",
						"New Page"   => "Neue Seite",
						"Log out"   => "Abmelden",
						"Detailed list view"   => "Detaillierte Listenansicht",
						"Thumbnails view"   => "Vorschaubildansicht",
						"Thumb"   => "Vorschaubild",
						"Filename"   => "Dateiname",
						"Size"   => "Gr&ouml;&szlig;e",
						"(Delete)"   => "L&ouml;schen",
						"Upload"   => "Hochladen",
						"New folder"   => "Neuer Ordner",
						"Browse"   => "Durchsuchen",
						"Upload new images"   => "Neue Bilder hochladen",
						"Upload new files" => "Neue Dateien hochladen",
						"No images found"   => "Keine Bilder gefunden",
						"File" => "Datei",
						"Are you sure you want to delete ..."   => "Soll wirklich gelöscht werden? ",
						"... uploaded OK"   => "erfolgreich hochgeladen",
						"No files found"   => "Keine Dateien gefunden",
						"Save &amp; Close" => "Speichern &amp; schlie&szlig;en" ,
						"Save &amp; continue editing" => "Speichern &amp; Bearbeitung fortsetzen",
						"Last edited" => "Letzte &Auml;nderung",
						"Cancel" => "Abbrechen",
						"Images" => "Bilder",
						"Files" => "Dateien"
					) ;
				case "Spanish" :
					$_SESSION['lang'] = Array (
						"Please log in"   => "Por favor entrar",
						"Log in"   => "Entrar",
						"Submit"   => "Enviar",
						"Login failed"   => "Error al entrar",
						"Delete"   => "Eliminar",
						"Rename file"   => "Cambiar el nombre del documento",
						"Publish"   => "Publicar",
						"Restore"   => "Restaurar",
						"Hide"   => "Ocultar",
						"Preview page"   => "Vista previa de la p&aacute;gina",
						"New page"   => "Nueva p&aacute;gina",
						"New Page"   => "Nueva p&aacute;gina",
						"Log out"   => "Salir",
						"Detailed list view"   => "Vista detallada de la lista",
						"Thumbnails view"   => "Vista de las miniaturas",
						"Thumb"   => "Miniatura",
						"Filename"   => "Nombre del documento",
						"Size"   => "Tama&ntilde;o",
						"(Delete)"   => "Eliminar",
						"Upload"   => "Subir",
						"New folder"   => "Nueva carpeta",
						"Browse"   => "Explorar",
						"Upload new images"   => "Subir nuevos im&aacute;genes",
						"Upload new files" => "Subir nuevos documentos",
						"No images found"   => "No hay im&aacute;genes",
						"File" => "Documento",
						"Are you sure you want to delete ..."   => "&iquest;Est&aacute; seguro que desea eliminar",
						"... uploaded OK"   => "fue subido exitosamente",
						"No files found"   => "No hay documentos",
						"Save &amp; Close" => "Guardar &amp; cerrar" ,
						"Save &amp; continue editing" => "Guardar & seguir editando",
						"Last edited" => "&Uacute;ltima modificaci&oacute;n",
						"Cancel" => "Cancelar",
						"Images" => "Im&aacute;genes",
						"Files" => "Documentos"
					) ;
				case "Slovak" :
					$_SESSION['lang'] = Array (
						"Please log in"   => "Prihl&aacute;ste sa pros&iacute;m",
						"Log in"   => "Prihl&aacute;si&#357;",
						"Submit"   => "Ododsla&#357;",
						"Login failed"   => "Prihl&aacute;senie ne&uacute;spe&#353;n&eacute;",
						"Delete"   => "Vymaza&#357;",
						"Rename file"   => "Premenuj s&uacute;bor",
						"Publish"   => "Zverejni&#357;",
						"Restore"   => "Obnovi&#357;",
						"Hide"   => "Schova&#357;",
						"Preview page"   => "N&aacute;h&#357;ad str&aacute;nky",
						"New page"   => "Nov&aacute; strana",
						"New Page"   => "Nov&aacute; strana",
						"Log out"   => "Odhl&aacute;si&#357;",
						"Detailed list view"   => "Zobrazi&#357; detailn&yacute; zoznam",
						"Thumbnails view"   => "Zoznam z&aacute;lo&#382;iek",
						"Thumb"   => "Otla&#357;ok",
						"Filename"   => "N&aacute;zov s&uacute;boru",
						"Size"   => "Ve&#357;kos&#357;",
						"(Delete)"   => "Vymaza&#357;",
						"Upload"   => "Nahra&#357; na server",
						"New folder"   => "Nov&yacute; adres&aacute;r",
						"Browse"   => "Otvori&#357;",
						"Upload new images"   => "Nahraj nov&eacute; obr&aacute;zky",
						"Upload new files" => "Nahraj nov&eacute; s&uacute;bory",
						"No images found"   => "Nena&#353;li sa &#382;iadne obr&aacute;zky",
						"File" => "S&uacute;bor",
						"Are you sure you want to delete ..."   => "Naozaj chcete vymaza&#357; ",
						"... uploaded OK"   => "ulo&#382;en&yacute;",
						"No files found"   => "&#381;iadne s&uacute;bory nen&aacute;jden&eacute;",
						"Save &amp; Close" => "Skon&#353;it &amp; Vlo&#382;it" ,
						"Save &amp; continue editing" => "Vlo&#382;it",
						"Last edited" => "Posledn&aactute; zmene",
						"Cancel" => "Storno",
						"Images" => "Obr&aacute;zky",
						"Files" => "Nen&aacute;jden&eacute;"
					) ;
				break ;
			}
		}
	}
	if (isset($useDefaultSettings)) {
		$_SESSION['CMS_Title'] = 'Go to Settings to put your Client or Site name here' ;
		$_SESSION['Show_help_by_default'] = '0' ;
		unset($_SESSION['settingsInitialized']) ;
	}
?>