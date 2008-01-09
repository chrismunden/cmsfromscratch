<div id="popupPrompt">

	<form id="newPage" onSubmit="return clickCreatePage();">
		<h2>New Page</h2>
		<label for="newPageName">New page name</label>
		<input type="text" style="width:40%;" id="newPageName" name="newPageName" />
		<label for="newPagePT">Page Template</label>
		<select name="newPagePT" id="newPagePT">
			<option value="">Blank page</option>
			<?
				$handle = opendir(PTSDIR) ;
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..' && getFileExtension($file) == "php") {
						echo '<option value="' . $file . '">' ;
						echo stripFileExtension($file) ;
						echo '</option>' ;
					}
				}
			?>
		</select>
		<div class="prompt_button">
			<button type="button" id="cancel_new_page" style="float:left;">Cancel</button>
			<button type="submit" id="create_page_button">Create page</button>
		</div>
		<input type="hidden" name="pagePath" id="pagePath" value="" />
	</form>
	
	<form id="newSet" onSubmit="return clickCreateSet();">
		<h2>New Content Set</h2>
		<label for="newSetName">New Content Set name</label>
		<input type="text" style="width:40%;" id="newSetName" name="newSetName" />
		<label for="newSetST">Use Set Template</label>
		
		<!-- Dynamically populated -->
		<select name="newSetST" id="newSetST">
			<?
			$handle = opendir(STDIR) ;
			while (False !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					echo '<option value="' . $file . '">' . $file . '</option>' ;
				}
			}
			?>
		</select>
		<input type="hidden" name="newSetParentPage" id="newSetParentPage" value="" />
		<input type="hidden" name="newSetFilePath" id="newSetFilePath" value="" />
		<input type="hidden" name="newSetMode" id="newSetMode" value="" />
		<div class="prompt_button">
			<button type="button" id="cancel_new_content_set" style="float:left;">Cancel</button>
			<button type="submit" id="create_set_button">Create set</button>
		</div>
	</form>
	
</div>