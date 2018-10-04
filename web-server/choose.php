<?php
//
//Format of json-displays:
//
//::= <display details>*
//<display details> ::= <id> <name> <creator> <created> <used> <uses>
//    <id>, <uses> ::= <int>
//    <name>, <creator> ::= <str>
//    <created>, <used> ::= <timestamp>
//
//
//Format of json-disp-specs:
//
//  <display specs> ::= <id> <gradient> <segment> <fading> <sparkle> <spot>
//    <gradient> ::= <colour list> <repeats> <blend> <bar on> <bar off>
//  <segment> := <num segments> <motion> <duration> <reverse>
//    <num segments> ::= <int 0-8>
//    <motion> ::= <int LEFT, RIGHT, R2L1, STOP>
//    <duration> ::= <float 0+>
//    <reverse> ::= <int REPEAT, REVERSE>
//  <fading> ::= <duration> <blend> <fade min> <fade max>
//    <blend> ::= <int STEP, SMOOTH>
//    <fade min>, <fade max> ::== <int 0-255>
//  <sparkle> ::== <sparks per thousand> <duration>
//    <sparks per thousand> ::= <int 0-1000>
//  <spot> ::= <size> <colour> <motion> <duration> <reverse>
//    <size> ::= <int 0-32>

// Read in the json-displays file
$disps=json_decode(file_get_contents("json-displays.json"), true);

// This funtion outputs all the elements for a labelled drop-down (select)
function select_input($select_label, $select_name, array $option) {
    global $disps;
    // Header with label
    echo "<p>$select_label: <select name=\"$select_name\" id=\"$select_name\">\n";
    // Put in all the options
    foreach ($option as $option_value => $option_text) {
        // $sel will be set to 'selected' for the selected option
        $sel=($disps[$select_name]==$option_value)?' selected':'';
        echo "  <option value=\"{$option_value}\"{$sel}>{$option_text}</option>\n";
    }
    // Finish off the select
    echo "</select></p>\n";
}

//file_put_contents("form.json", json_encode(array('var1'=>'val1', 'var2'=>'val2')));
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
	th {color:blue; text-align:left}
	#curSel, .curSel {color:white; background-color:blue; font-size:100%} /* Selected row in table */
	.header {position: sticky; top: 0; background-color:white} /* will scroll to top of page then stop */
	.footer {position: sticky; bottom: 0; width: 100%} /* stays at foot of page with text scrolling behind */
	button {font-size:100%}
	.hidden {display: none}
	/* 
	@media (min-width: 858px) {
    html {
        font-size: 1em;
    }
    */
</style>
<script>
// https://www.w3schools.com/howto/howto_js_sort_table.asp
function sortTable(tid,n,n_headers,type,dir) {
  var table, rows, switching, i, x, y, shouldSwitch, switchcount = 0;
  table = document.getElementById(tid);
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first n_headers, which contains table headers): */
    for (i = n_headers; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /* Check if the two rows should switch place,
      based on the direction, asc=1 or desc=0: */
      if (dir == 1) {
		var xv = (type==0?x.innerHTML.toLowerCase():Number(x.innerHTML));
		var yv = (type==0?y.innerHTML.toLowerCase():Number(y.innerHTML));
        if (xv > yv) {
          // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      } else if (dir == 0) {
		var xv = (type==0?x.innerHTML.toLowerCase():Number(x.innerHTML));
		var yv = (type==0?y.innerHTML.toLowerCase():Number(y.innerHTML));
        if (xv < yv) {
         // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      // Each time a switch is done, increase this count by 1:
      switchcount ++;
    }
  }
}
function unselRow() {
	var cursel;
	cursel = document.getElementById("curSel")
	if (cursel) {
		cursel.id="";
		cursel.parentNode.removeChild(cursel.nextSibling);
	}
}
function selRow(thisrow) {
	var cursel;
	// un-select current one
	unselRow();
	// Select new one
	thisrow.id="curSel";
	var newtr = document.createElement("tr");
	newtr.classList.add("curSel");
	var newtd = document.createElement("td");
	newtd.colSpan = 6;
	var newbut = document.createElement("button");
	newbut.innerText = "DISPLAY";
	newbut.onclick = function f() {doProcess(1);};
	newtd.appendChild(newbut);
	newbut = document.createElement("button");
	newbut.innerText = "EDIT";
	newbut.onclick = function f() {doProcess(2);};
	//newbut.style="margin-left:1em";
	newtd.appendChild(newbut);
	newtr.appendChild(newtd);
	thisrow.parentNode.insertBefore(newtr, thisrow.nextSibling);
	//thisrow.firstChild.appendChild(newdiv);
	// For double click to select, this works but needs onclick to be 
	// reset when id is reset above: 
	//thisrow.onclick=function secondClick(){doDisplay();};
}
function sortCol(params) {
	//Need to remove se class='hidden'lection as it adds a row we don't want to sort
	var paramv = params.split(",");
	unselRow();
	sortTable('ta',paramv[0],1,paramv[1],paramv[2]);
}
function doProcess(action) {
	var cursel;
	cursel = document.getElementById("curSel");
	if (cursel) {
		if (action == 1) alert("DISPLAY "+cursel.children[0].innerText)
		else location.href = 'edit.php?'+cursel.lastChild.innerText;
	}
}
</script>
</head>
<body>
	<div>
		Welcome to the St.Thomas Christmas Lights controller. There are
		currently <?php echo count($disps);?> different displays to choose
		from. Enjoy!
	</div>
	<div>
		<p class="header">
		  Sort:
		  <select onchange="sortCol(this.value)" autocomplete="off">
			  <option value="0,0,1">Display Name</option>
			  <option value="1,0,1">Creator</option>
			  <option value="2,1,0">Newest first</option>
			  <option value="3,1,0">Most recently used</option>
			  <option value="4,1,0" selected>Most popular</option>
		   </select>
		</p>
		<table id="ta">
			<tr>
				<th>Display name</th>
				<th>Created by</th>
			</tr>
			<?php foreach ($disps as $disp_id => $disp) {
				echo "<tr onclick='selRow(this)'>";
				echo   "<td>",$disp[0],"</td>\n";
				echo   "<td>",$disp[1],"</td>\n";
				echo   "<td class='hidden'>",$disp[2],"</td>\n";
				echo   "<td class='hidden'>",$disp[3],"</td>\n";
				echo   "<td class='hidden'>",$disp[4],"</td>\n";
				echo   "<td class='hidden'>",$disp_id,"</td>";
				echo "</tr>";
			}
			?>
		</table>
		<script>sortCol("4,1,0");</script>
	</div>
	<div class="footer">
		Footer text 
	</div>

</body>
</html>