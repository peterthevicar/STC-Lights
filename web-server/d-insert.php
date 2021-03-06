<?php
// Set up error handler and err function for logging errors
include "s-error-handler.php";

//
//------------------ Code to insert new display spec -----------------//
//
// Read the header information (a json with the spec for the new display)
if ($_POST == null) $_POST = ['json' => '{"hd":["Rainbow","Peter","90bfbf8c",1542244227,1542800758,10,2],"co":["#ff0000","#ffff00","#00ff00","#00ffff","#0000ff","#ff00ff"],"gr":["1","1","0"],"se":["4","2","2","2","2"],"fa":["0","1","3"],"sk":["2",8.3],"st":["0","#062af9","1","3","2"],"fl":["1","#000000","#ffffff","2","3","0"],"me":["1"]}'];
//~ err('DEBUG:insert:10 POST[json]='.$_POST['json']);
$new_disp = json_decode(strip_tags($_POST['json']), true);
//~ err('DEBUG:insert:12 POST='.json_encode($_POST));
//~ err('DEBUG:insert:13 new='.json_encode($new_disp));
// Hash the plain text password for comparison - very basic security
$new_disp['hd'][2] = substr(hash("md5",$new_disp['hd'][2]),4,8);

// Get an exclusive lock on json-displays
$fn = 'j-displays.json';
$waiting = true;
$duplicate = false; // Assume it's not already in there
for ($i=1; $waiting and $i<=3; $i++) { // try 3 times for exclusive access to the file
	$fp = fopen($fn, "c+"); // try to open file but don't truncate
	if ($fp) {
		if (flock($fp, LOCK_EX)) {
			//
			//-------------- EXCLUSIVE LOCKED json-displays -----------
			//
			$disps = json_decode(file_get_contents($fn), true);
			$new_id = null;
			// See if it's a duplicate - nothing clever as we're holding a lock, 
			// but foil a simple bot
			foreach ($disps as $id => $spec) {
				//~ err('DEBUG:insert:33:spec='.json_encode($spec['hd']).' new='.json_encode($new_disp['hd']));
				// See if the name is already in the list
				if ($spec['hd'][0] == $new_disp['hd'][0]) {
					// Names are the same, see if it's a valid request for modifying an existing display
					if ($spec['hd'][1] == $new_disp['hd'][1] // Creator matches
					and $spec['hd'][2] == $new_disp['hd'][2] // Password matches
					and $spec['hd'][3] != $new_disp['hd'][3]) { // Create date should change else it's automated
						$new_id = $id;
						$new_disp['hd'][6]++; // Increment the version number
					}
					else {
						// It's just a duplicate so ignore it
						$duplicate = true;
					}
					break;
				}
			}
			if (!$duplicate) {
				if ($new_id == null) $new_id = "id".(count($disps)+1); // doesn't matter if some are missed
				$disps[$new_id] = $new_disp;
				//~ err("DEBUG:insert:56 disps=".json_encode($disps));
				file_put_contents($fn, json_encode($disps));
			}
			flock($fp, LOCK_UN);
			fclose($fp);
			//
			//-------------- UNLOCKED -----------
			//
			$waiting = false;
		}
		else fclose($fp);
	}
	if ($waiting) sleep(rand(0, 2));
}
if ($waiting) {
	$msg = '{"err":1,"id":"","msg":"Couldn\'t open displays database"}';
}
elseif ($duplicate) {
	$msg = '{"err":2,"id":"'.$new_id.'","msg":"Display \"'.$new_disp['hd'][0].'\" is already in the list. (Or your password didn\'t match)"}';
}
else// Added the new display to the json file, so now let the user know
	$msg = '{"err":0,"id":"'.$new_id.'","msg":"\"'.$new_disp['hd'][0].'\" has been added to the list of displays (or modified)"}';
echo $msg;
//~ TODO: work out how to respond after errors etc
//~ Complete file writing code
//~ Thorough parameter checking before accepting
//~ Check for very similar or identical displays
?>
