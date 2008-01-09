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
	/* 
		Expects:
			$_GET['file']
			
		Set templates will be in format:
		
			<<start>> // Only shown if there's *anything* else to show
				<<repeat 1>><<else>><<else>> // You can add as many else-options as you want, or none
				<<repeat 2>><<else>
				<<repeat 3>>
			<<end>
			
			<<else>> // Shown if there's nothing to show in the main bit
			
		// Else options are attempted if any of the includes in the previous block (e.g. <1>) aren't found, or are empty.
		
		Will be array where 0 is start, 1 is array of repeat blocks (each multiple), 2 is end, 3 is else block.
	
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
		$set = file_get_contents(STDIR . $_GET['file']) ;
		if (strlen($set) == 0) {
			header('Location:set-templates.php?message=stfileempty') ;
			exit ;
		}
		$stArray = unserialize($set) ;
		if ($stArray === False) {
			header('Location:set-templates.php?message=couldntreadtextfile') ;
			exit ;
		}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Edit Set template <? echo stripFileExtension($_GET['file']) ; ?></title>
	<link href="styles.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript">
	<!--
		function submitForm() {
			if (!document.getElementsByTagName) return false ;
			myForm = document.getElementsByTagName('form')[0] ;
			if (myForm) myForm.submit() ;
		}
		function banish() {
			location = 'set-templates.php' ;
		}
		var setDataTypes = new Array() ;
		<?	for ($dataType=0; $dataType<sizeOf($SETDATATYPES); $dataType++) {
				echo 'setDataTypes[setDataTypes.length] = "' . $SETDATATYPES[$dataType] . '" ;' . "\n" ;
			}
		?>
	// -->
	</script>
	<script type="text/javascript" language="javascript" src="core.js"></script>
	<script type="text/javascript" language="javascript" src="edit-set-template.js"></script>
	<style type="text/css">
		body {background:none;}
		h2 {
			margin:0 0 0 1em;
		}
		fieldset {
			margin-top:0;
		}
		textarea {
			height:3em;
			width:100%;
			margin:.3em 0;
		}
		#colsTable td {
			padding:.5em .75em .5em .5em;
		}
		tr {
			vertical-align:top;
		}
		tr.repeated {
		}
		tr.repeated textarea {
			height:5em;
		}
		textarea.alternative {
			height:2em;
		}
		.hid {
			display:none;
		}
		/* 
		.param {
			width:1.7em;
			margin-left:.15em;
			text-align:right;
		}
		.paramTitle {
			margin-left:1em;
			font-size:.75em;
			color:#555;
			display:none;
		}
		*/
		#colsListBody input, #colsListBody select {
			width:7em;
		}
		#colsTable a.delete {
			margin:0;
		}
		legend button {
			margin:0 0 0 .5em;
		}
	</style>
</head>
<body>

<h1>Edit Set Template: <? echo stripFileExtension($_GET['file']) ; ?></h1>
<form action="save-set-template.php" method="post">

<div style="text-align:center;">
		<button onClick="submitForm();" style="width:10em;">Save</button>
		&nbsp; &nbsp; 
		<button onClick="banish();">Cancel</button>
</div>







<div style="float:left; width:350px;">
<h2>Columns</h2>
	<table border="0" cellpadding="0" cellspacing="0" id="colsTable" style="border:1px solid #666;">
		<thead>
			<tr>
				<th>Column name</th>
				<th>Data type</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody id="colsListBody">
			<?
				for ($col=0; $col<sizeOf($stArray["cols"]); $col++) {
					echo '<tr>' ;
					echo '<td><input type="text" name="colNames[]" value="' . $stArray["cols"][$col][0] . '" /></td>' ;
					echo '<td>' ;
						
						// echo '<input type="text" name="colTypes[]" value="' . $stArray["cols"][$col][1] . '" />' ;
						
						echo '<select name="colTypes[]" />' ;
						for ($dataType=0; $dataType<sizeOf($SETDATATYPES); $dataType++) {
							echo '<option value="' . $SETDATATYPES[$dataType] . '" ' ;
							if ($SETDATATYPES[$dataType] == $stArray["cols"][$col][1]) {
								echo 'selected="selected" ' ;
							}
							echo '>' ;
							echo $SETDATATYPES[$dataType] . '</option>' ;
						}
						echo '</select>' ;
						
/* 						echo '<span class="paramTitle">Max&nbsp;Width</span>' ;
						echo '<input type="hidden" name="param1[]" class="param" value="' . $stArray["cols"][$col][2] . '" />' ;
						echo '<span class="paramTitle">Max&nbsp;Height</span>' ;
						echo '<input type="hidden" name="param2[]" class="param" value="' . $stArray["cols"][$col][3] . '" />' ;*/
					echo '</td>' ;
					echo '<th><a href="javascript:void(\'\');" class="delete">x</a></th>' ;
				}
				echo '</tr>' ;
			?>
		</tbody>
		<tfoot>
			<tr>
				<th><input type="text" name="newColName" id="newColName" /></th>
				<th style="text-align:left;" nowrap="nowrap">
					<select name="newColType" id="newColType" style="margin-right:.5em;">
					<?
						for ($dataType=0; $dataType<sizeOf($SETDATATYPES); $dataType++) {
							echo '<option value="' . $SETDATATYPES[$dataType] . '">' . $SETDATATYPES[$dataType] . '</option>' ;
						}
					?>
					</select>
					<button type="button" onClick="addColumn();" title="Add column"> + </button>
				</th>
				<th>&nbsp;</th>
			</tr>
		</tfoot>
	
	</table>
</div>

<div style="float:left;">
<h2>Rendering rules</h2>
	<table border="0" cellpadding="0" cellspacing="0" id="stTable" class="invisible">
		<tbody id="tableBody">
			<tr>
				<td>
				<fieldset><legend>Before</legend>
				<textarea name="before"><?
					echo stripslashes($stArray["before"]) ;
				?></textarea>
				</fieldset>
				</td>
			</tr>
			<?
				if (!isSet($stArray["repeated"]) || sizeOf($stArray["repeated"]) == 0) {
					// Blank initial values
					$stArray["repeated"][0] = array("","","") ;
				}
				for ($iNum=0; $iNum<sizeOf($stArray["repeated"]); $iNum++) {
					echo '<tr class="repeated">' ;
						echo '<td><fieldset><legend>Repeated (alternatives in order of priority)</legend>' ;
						/* <button type="button" title="Delete repeated block">x</button><button type="button" title="Add repeated block">+</button> */
						echo '<textarea style="height:5em;" name="repeated[]">' , stripslashes($stArray["repeated"][$iNum][0]) , '</textarea>' ;
						echo '<textarea style="height:4em;" name="repeated[]">' , stripslashes($stArray["repeated"][$iNum][1]) , '</textarea>' ;
						echo '<textarea style="height:3em;" name="repeated[]">' , stripslashes($stArray["repeated"][$iNum][2]) , '</textarea>' ;
						echo '</td>' ;
					echo '</tr>' ;
				}
			?>
			<trid="afterRow">
				<td>
				<fieldset><legend>After</legend>
				<textarea name="after"><?
					echo stripslashes($stArray["after"]) ;
				?></textarea>
				</fieldset>
				</td>
			</tr>
			<tr>
				<td>
				<fieldset><legend>Alternative</legend>
				<textarea name="else" class="alternative"><?
					echo stripslashes($stArray["else"]) ;
				?></textarea>
				</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
</div>
	<input type="hidden" name="sourcefile" value="<? echo $_GET['file'] ; ?>" />
</form>
<br />

</body>
</html>