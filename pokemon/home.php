<?php
if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=home">Pokemon Home</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div class="tcg_body">
Home start<br><br>';

if(isset($_GET['do']) && $_GET['do'] == 'list') {
	// ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'user' => TYPE_INT
	));
	$rarray = array(1 => 'Common', 'Uncommon', 'Rare', 'Mythic');
	$listuser = (isset($_GET['user']) && $_GET['user'] <= 10000) ? clean_number($vbulletin->GPC['user'],10000) : $userid;
	
	$userqry = $db->query_first("SELECT `username` FROM `user` WHERE `userid` = " . $listuser);
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		`poke_indv`.`indvid` AS 'c_id', 
		`poke_indv`.`nick` AS 'c_nick', 
		`poke_indv`.`level` AS 'c_level', 
		`poke_mon`.`monname` AS 'c_name', 
		`poke_mon`.`type` AS 'c_type',
		`poke_indv`.`shiny` AS 'c_foil',
		`poke_indv`.`gender` AS 'c_gender',
		`poke_indv`.`monid` AS 'c_masterid'
	FROM 
		`poke_indv`
		LEFT JOIN (`poke_mon`)
			ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
	WHERE  
		`poke_indv`.`userid`=$listuser 
	ORDER BY 
		`poke_mon`.`monid` ASC";
		
	$result2 = $db->query_first("SELECT 
        	count(DISTINCT `poke_indv`.`monid`) AS `count`
        FROM `poke_indv` 
        WHERE `poke_indv`.`userid`=$listuser");
		
	$result = $db->query_read($qry);
	//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
	$counter = 0;
	$str .= '<script type="text/javascript">
	var datam, regm, jom, $tm, containm, stokenm;
	jQuery(document).ready(function() 
	    { 
	        jQuery("#myTable").tablesorter(); 
          
          jQuery("#searchInput").keyup(function () {
              //current value of searchInput
              datam = this.value;
              //create a jquery object of the rows
              jom = jQuery("#fbody").find("tr");
              if (this.value == "") {
                  jom.show();
                  return;
              }
              //hide all the rows
              jom.hide();
          
              //Recusively filter the jquery object to get results.
              jom.filter(function () {
                  regm = /(\w+)|"([^"]+)"/g;
                  $tm = jQuery(this);
                  sTokenm  = null;
                  while((sTokenm = regm.exec(datam)) !== null){
                      sTokenm = sTokenm[1] === undefined ? sTokenm[2] : sTokenm[1];
                      containm = new RegExp(("(^\|[ \\\n\\\r\\\t.,\'\"\+!?-]+)" + sTokenm), "gi");
                      if (containm.test($tm.text())) return true;
                  }
                  return false;
              })
              //show the rows that match.
              .show();
          }).focus(function () {
              this.value = "";
              jQuery(this).css({
                  "color": "black"
              });
              jQuery(this).unbind(\'focus\');
          }).css({
              "color": "#C0C0C0"
          });
      }
	);
	</script>';
	$str .= 'Displaying pokemon for user: <a href="member.php?' . $listuser . '">' . $userqry["username"] . '</a>';
	$str .= '<div>Use this box to filter: <input id="searchInput" value="Type To Filter"></div>';
	$str .= '<div class="cards_table">
		<table id="myTable" class="tablesorter"> 
			<thead> 
				<tr> 
				    <th>ID</th>
				    <th>Pokemon Name</th> 
				    <th>Pokemon Nick</th> 
				    <th>Gender</th> 
				    <th>ID</th>
				    <th>Level</th>
				    <th>Type</th> 
				    <th>Shiny</th> 
				</tr> 
			</thead> 
			<tbody id="fbody">';
	while ($resultLoop = $db->fetch_array($result)) {
		$counter++;
		$foil = ($resultLoop["c_foil"] == 0) ? 'No' : 'Shiny';
		$type = $resultLoop["c_type"];
		$pokemon_name = '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["c_masterid"] . '">' . $resultLoop["c_name"] . '</a>';
		$nick = ($resultLoop["c_nick"] == '') ? 'NA' : $resultLoop["c_nick"];
		$pokemon_nick = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop["c_id"] . '">' . $nick . '</a>';
		if($resultLoop["c_masterid"] == 0) {
		    $pokemon_name = 'MissingNo';
		    $type = 'NaN';
		    $foil = 'NaN';
		}
		$str .= '<tr> 
		    <td>' . $resultLoop["c_masterid"] . '</td> 
		    <td>' . $pokemon_name . '</td> 
		    <td>' . $pokemon_nick . '</td> 
		    <td>' . $resultLoop["c_gender"] . '</td>
		    <td>' . $resultLoop["c_id"] . '</td>
		    <td>' . $resultLoop["c_level"] . '</td>
		    <td>' . $type . '</td>
		    <td>' . $foil . '</td> 
		</tr>';
	}
	$str .= '</tbody> 
		</table>
	</div>';
	
	$str .= '<br>Total Pokemon:' . $counter . '<br>
	            Unique Pokemon:' . $result2['count'] . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'slist') {
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		`poke_indv`.`indvid` AS 'c_id', 
		`poke_indv`.`nick` AS 'c_nick', 
		`poke_indv`.`level` AS 'c_level', 
		`poke_mon`.`monname` AS 'c_name', 
		`poke_mon`.`type` AS 'c_type',
		`poke_indv`.`shiny` AS 'c_foil',
		`poke_indv`.`gender` AS 'c_gender',
		`poke_indv`.`monid` AS 'c_masterid',
		`poke_indv`.`userid` AS 'c_userid'
	FROM 
		`poke_indv`
		LEFT JOIN (`poke_mon`)
			ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
	WHERE  
		`poke_indv`.`shiny`=1 
	ORDER BY 
		`poke_mon`.`monid` ASC";
		
	$result = $db->query_read($qry);
	$counter = 0;
	$str .= '<script type="text/javascript">
	var datam, regm, jom, $tm, containm, stokenm;
	jQuery(document).ready(function() 
	    { 
	        jQuery("#myTable").tablesorter(); 
          
          jQuery("#searchInput").keyup(function () {
              //current value of searchInput
              datam = this.value;
              //create a jquery object of the rows
              jom = jQuery("#fbody").find("tr");
              if (this.value == "") {
                  jom.show();
                  return;
              }
              //hide all the rows
              jom.hide();
          
              //Recusively filter the jquery object to get results.
              jom.filter(function () {
                  regm = /(\w+)|"([^"]+)"/g;
                  $tm = jQuery(this);
                  sTokenm  = null;
                  while((sTokenm = regm.exec(datam)) !== null){
                      sTokenm = sTokenm[1] === undefined ? sTokenm[2] : sTokenm[1];
                      containm = new RegExp(("(^\|[ \\\n\\\r\\\t.,\'\"\+!?-]+)" + sTokenm), "gi");
                      if (containm.test($tm.text())) return true;
                  }
                  return false;
              })
              //show the rows that match.
              .show();
          }).focus(function () {
              this.value = "";
              jQuery(this).css({
                  "color": "black"
              });
              jQuery(this).unbind(\'focus\');
          }).css({
              "color": "#C0C0C0"
          });
      }
	);
	</script>';
	$str .= 'Displaying Shiny Pokemon';
	$str .= '<div>Use this box to filter: <input id="searchInput" value="Type To Filter"></div>';
	$str .= '<div class="cards_table">
		<table id="myTable" class="tablesorter"> 
			<thead> 
				<tr> 
				    <th>ID</th>
				    <th>Pokemon Name</th> 
				    <th>Pokemon Nick</th> 
				    <th>Gender</th> 
				    <th>ID</th>
				    <th>Level</th>
				    <th>Type</th> 
				    <th>Shiny</th> 
				    <th>Userid</th> 
				</tr> 
			</thead> 
			<tbody id="fbody">';
	while ($resultLoop = $db->fetch_array($result)) {
		$counter++;
		$foil = ($resultLoop["c_foil"] == 0) ? 'No' : 'Shiny';
		$type = $resultLoop["c_type"];
		$pokemon_name = '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["c_masterid"] . '">' . $resultLoop["c_name"] . '</a>';
		$nick = ($resultLoop["c_nick"] == '') ? 'NA' : $resultLoop["c_nick"];
		$pokemon_nick = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop["c_id"] . '">' . $nick . '</a>';
		if($resultLoop["c_masterid"] == 0) {
		    $pokemon_name = 'MissingNo';
		    $type = 'NaN';
		    $foil = 'NaN';
		}
		$str .= '<tr> 
		    <td>' . $resultLoop["c_masterid"] . '</td> 
		    <td>' . $pokemon_name . '</td> 
		    <td>' . $pokemon_nick . '</td> 
		    <td>' . $resultLoop["c_gender"] . '</td>
		    <td>' . $resultLoop["c_id"] . '</td>
		    <td>' . $resultLoop["c_level"] . '</td>
		    <td>' . $type . '</td>
		    <td>' . $foil . '</td> 
		    <td><a href="/member.php?' . $resultLoop["c_userid"] . '">' . $resultLoop["c_userid"] . '</a></td> 
		</tr>';
	}
	$str .= '</tbody> 
		</table>
	</div>';
	
	$str .= '<br>Total Pokemon:' . $counter . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'ilist') {
	// ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'user' => TYPE_INT
	));
	$listuser = (isset($_GET['user']) && $_GET['user'] <= 10000) ? clean_number($vbulletin->GPC['user'],10000) : $userid;
	
	$userqry = $db->query_first("SELECT `username` FROM `user` WHERE `userid` = " . $listuser);
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		count(`poke_items`.`indv_item_id`) AS 'count', 
		`poke_items`.`itemid` AS 'c_itemid', 
		`poke_item_master`.`name` AS 'c_name'
	FROM 
		`poke_items`
		INNER JOIN (`poke_item_master`)
			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
	WHERE  
		`poke_items`.`userid`=$listuser
		AND `poke_items`.`use_date`=0
	GROUP BY
    	`poke_items`.`itemid`
    ORDER BY 
		`poke_items`.`itemid` ASC";
		
	$result = $db->query_read($qry);
	//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
	$counter = 0;
	$str .= '<script type="text/javascript">
	var datam, regm, jom, $tm, containm, stokenm;
	jQuery(document).ready(function() 
	    { 
	        jQuery("#myTable").tablesorter(); 
          
          jQuery("#searchInput").keyup(function () {
              //current value of searchInput
              datam = this.value;
              //create a jquery object of the rows
              jom = jQuery("#fbody").find("tr");
              if (this.value == "") {
                  jom.show();
                  return;
              }
              //hide all the rows
              jom.hide();
          
              //Recusively filter the jquery object to get results.
              jom.filter(function () {
                  regm = /(\w+)|"([^"]+)"/g;
                  $tm = jQuery(this);
                  sTokenm  = null;
                  while((sTokenm = regm.exec(datam)) !== null){
                      sTokenm = sTokenm[1] === undefined ? sTokenm[2] : sTokenm[1];
                      containm = new RegExp(("(^\|[ \\\n\\\r\\\t.,\'\"\+!?-]+)" + sTokenm), "gi");
                      if (containm.test($tm.text())) return true;
                  }
                  return false;
              })
              //show the rows that match.
              .show();
          }).focus(function () {
              this.value = "";
              jQuery(this).css({
                  "color": "black"
              });
              jQuery(this).unbind(\'focus\');
          }).css({
              "color": "#C0C0C0"
          });
      }
	);
	</script>';
	$str .= 'Displaying items for user: <a href="member.php?' . $listuser . '">' . $userqry["username"] . '</a>';
	$str .= '<div>Use this box to filter: <input id="searchInput" value="Type To Filter"></div>';
	$str .= '<div class="cards_table">
		<table id="myTable" class="tablesorter"> 
			<thead> 
				<tr> 
				    <th>Item</th> 
				    <th>Amount</th> 
				</tr> 
			</thead> 
			<tbody id="fbody">';
	while ($resultLoop = $db->fetch_array($result)) {
		$counter++;
		$str .= '<tr> 
		    <td>' . $resultLoop["c_name"] . '</td>
		    <td>' . $resultLoop["count"] . '</td>
		</tr>';
	}
	$str .= '</tbody> 
		</table>
	</div>';
	
	$str .= '<br>Total Items:' . $counter . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'request') {
	if(isset($_GET['action']) && $_GET['action'] == 'respond') {
		// ############ GET VARIABLES ############
		//'g' means it's POST data
		$vbulletin->input->clean_array_gpc('g', array(
			'request' => TYPE_INT,
		));
		$request = clean_number($vbulletin->GPC['request'],5000);
		
		// ############ CHECK IF REQUEST EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_request WHERE requestid = $request AND user2id = $userid AND responded = 0) AS 'Exists'");
		
		// ############ MAIN CODE ############
		if($exists['Exists'] == true) {
			$result = $db->query_first("SELECT 
				`poke_request`.`requestid` AS 'r_id', 
				`poke_request`.`user1id` AS '1_id',
				`poke_request`.`user2id` AS '2_id',
				`user1`.`username` AS '1_name', 
				`user2`.`username` AS '2_name',
				`poke_request`.`dateline` AS 'dateline'
			FROM 
				`poke_request`
			LEFT JOIN `user` AS `user1`
				ON (`poke_request`.`user1id` = `user1`.`userid`)
			LEFT JOIN `user` AS `user2`
				ON (`poke_request`.`user2id` = `user2`.`userid`)
			WHERE  
				`poke_request`.`requestid`=$request");
			$str .= '<div id="request_response">
				<a href="member.php?' . $result["1_id"] . '">' . $result["1_name"] . '</a> is requesting a Trade.
			</div>';
			$str .= '<div id="trade_form">
				<form class="trade" action="/pokemon.php?section=home&do=request&action=approve&request=' . $request . '" method="post">
					<input type="hidden" name="auth" value="Approve" />
					<input type="submit" value="Approve Trade Request" />
				</form>
			</div>';
			$str .= '<div id="trade_form">
				<form class="trade" action="/pokemon.php?section=home&do=request&action=deny&request=' . $request . '" method="post">
					<input type="hidden" name="auth" value="Deny" />
					<input type="submit" value="Deny Trade Request" />
				</form>
			</div>';
			echo $str;
		} else {
			echo 'That is not a valid request!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'approve') {
		// ############ GET VARIABLES ############
		//'g' means it's POST data
		$vbulletin->input->clean_array_gpc('g', array(
			'request' => TYPE_INT,
		));
		$request = clean_number($vbulletin->GPC['request'],5000);
		
		// ############ CHECK IF REQUEST EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_request WHERE requestid = $request AND user2id = $userid AND responded = 0) AS 'Exists'");
		
		// ############ MAIN CODE ############
		if($exists['Exists'] == true) {
			$result = $db->query_first("SELECT 
				user1id AS '1_id'
			FROM 
				poke_request
			WHERE
				requestid=$request");
			$person = $result["1_id"];
			$qry = "SELECT EXISTS(
				SELECT 1 
				FROM poke_trade 
				WHERE 
					((user1id = $userid) AND (active = 1))
					OR ((user1id = $person) AND (active = 1))
					OR ((user2id = $userid) AND (active = 1))
					OR ((user2id = $person) AND (active = 1))
					)
			AS 'Exists'";
			$exists2 = $db->query_first($qry);
			if($exists2['Exists'] == true) {
				echo 'One of the specified members are already in an active trade!
				<script type="text/javascript">
				<!--
				window.location = "/pokemon.php"
				//-->
				</script>';
			} else {
				$db->query_write("UPDATE 
					`poke_request` 
				SET 
					`responded` = 1
				WHERE 
					`requestid` = " . $request);
				$db->query_write("INSERT INTO 
					`poke_trade` 
					(user1id, user2id, lastaction)
				VALUES 
					('$person', '$userid', " . time() . ")");
				$result2 = $db->query_first("SELECT `tradeid` FROM `poke_trade` WHERE tradeid = LAST_INSERT_ID()");
				$requestid = $result2["tradeid"];
				// Setup Auto Private Message
				$pmfromid = 15; // Sexbot
				// Send Private Message
				if ($pmfromid) {
					require_once('./includes/class_dm.php'); 
					require_once('./includes/class_dm_pm.php'); 
					//pm system 
					$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
					//pm Titel / Text 
					$pmtitle    =   $vbphrase['poke_trade_request_pm_title']; 
					$pmtext     =   $vbphrase['poke_trade_request_pm_body'];
					$pmstoren = 'A Trade. The trade request was accepted. Please view the trade [url="https://forums.novociv.org/pokemon.php?section=trade&do=view&trade=' . $requestid . '"]HERE[/url]';
					$pmtext     = str_replace("[NAME]", "$pmstoren", "$pmtext");
					$pmgave = $username;
					$pmtext     = str_replace("[DONATE]", "$pmgave", "$pmtext");
					$pmfromname = 'Sexbot';           
					$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$person'"));
					
					$pmSystem->verify_message( $pmtext ); 
					$pmSystem->verify_title( $pmtitle ); 
					
					//Set the fields 
					$pmSystem->set('fromuserid', $pmfromid); 
					$pmSystem->set('fromusername', $pmfromname); 
					$pmSystem->set('title', $pmtitle); 
					$pmSystem->set('message', $pmtext); 
					$pmSystem->set('dateline', TIMENOW); 
					$pmSystem->set('iconid', 4);
						$pmSystem->set_recipients($finduser[username], $botpermissions);
					
						//Set Private Message 
					if ( $pmSystem->pre_save() === false ) 
					{ 
					 if ($pmSystem->errors) { 
					    return $pmSystem->errors; 
					}  
					} 
					else 
					{ 
					 $pmSystem->save();                
					}
				}
				echo 'Accepting Request...
				<script type="text/javascript">
				<!--
				window.location = "/pokemon.php?section=trade&do=view&trade=' . $requestid . '"
				//-->
				</script>';
			}
		} else {
			echo 'That is not a valid request!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'deny') {
		// ############ GET VARIABLES ############
		//'g' means it's POST data
		$vbulletin->input->clean_array_gpc('g', array(
			'request' => TYPE_INT,
		));
		$request = clean_number($vbulletin->GPC['request'],5000);
		
		// ############ CHECK IF REQUEST EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_request WHERE requestid = $request AND user2id = $userid AND responded = 0) AS 'Exists'");
		
		// ############ MAIN CODE ############
		if($exists['Exists'] == true) {
			$result = $db->query_first("SELECT 
				user1id AS '1_id'
			FROM 
				poke_request
			WHERE
				requestid=$request");
			$person = $result["1_id"];
			$db->query_write("UPDATE 
				`poke_request` 
			SET 
				`responded` = 1
			WHERE 
				`requestid` = " . $request);
			// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   $vbphrase['poke_trade_request_pm_title']; 
				$pmtext     =   $vbphrase['poke_trade_request_pm_body'];
				$pmstoren = 'Not trade. The trade request was denied';
				$pmtext     = str_replace("[NAME]", "$pmstoren", "$pmtext");
				$pmgave = $username;
				$pmtext     = str_replace("[DONATE]", "$pmgave", "$pmtext");
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$person'"));
				
				$pmSystem->verify_message( $pmtext ); 
				$pmSystem->verify_title( $pmtitle ); 
				
				//Set the fields 
				$pmSystem->set('fromuserid', $pmfromid); 
				$pmSystem->set('fromusername', $pmfromname); 
				$pmSystem->set('title', $pmtitle); 
				$pmSystem->set('message', $pmtext); 
				$pmSystem->set('dateline', TIMENOW); 
				$pmSystem->set('iconid', 4);
					$pmSystem->set_recipients($finduser[username], $botpermissions);
				
					//Set Private Message 
				if ( $pmSystem->pre_save() === false ) 
				{ 
				 if ($pmSystem->errors) { 
				    return $pmSystem->errors; 
				}  
				} 
				else 
				{ 
				 $pmSystem->save();                
				}
			}
			echo 'Denying Request...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else {
			echo 'That is not a valid request!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else {
		// ############ QUERY VARIABLES ############
		$qry = "SELECT 
			`poke_request`.`requestid` AS 'r_id', 
			`poke_request`.`user1id` AS '1_id',
			`poke_request`.`user2id` AS '2_id',
			`user1`.`username` AS '1_name', 
			`user2`.`username` AS '2_name',
			`poke_request`.`dateline` AS 'dateline'
		FROM 
			`poke_request`
		LEFT JOIN `user` AS `user1`
			ON (`poke_request`.`user1id` = `user1`.`userid`)
		LEFT JOIN `user` AS `user2`
			ON (`poke_request`.`user2id` = `user2`.`userid`)
		WHERE  
			`poke_request`.`user2id`=$userid
			AND `poke_request`.`responded`=0
		ORDER BY 
			`poke_request`.`dateline` ASC";
		$result = $db->query_read($qry);
		$str .= '<h1>Displaying your requests:</h1>';
		while ($resultLoop = $db->fetch_array($result)) {
			$request = '<a href="pokemon.php?section=home&do=request&action=respond&request=' . $resultLoop["r_id"] . '">Respond</a>';
			$person = '<a href="member.php?' . $resultLoop["1_id"] . '">' . $resultLoop["1_name"] . '</a> is requesting a trade.';
			$lastaction = 'Requested at ' . vbdate("F j, Y, g:i:s A",$resultLoop["dateline"]);
			
			$str .= '<div class="tradelistactive">
				' . $person . '<br>
				' . $lastaction . '<br>
				' . $request . '
			</div>';
		}
		echo $str;
	}
} else if(isset($_GET['do']) && $_GET['do'] == 'dex') {
    // ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'user' => TYPE_INT,
		'gen' => TYPE_INT
	));
	$listuser = (isset($_GET['user']) && $_GET['user'] <= 10000) ? clean_number($vbulletin->GPC['user'],10000) : $userid;
	$gen = (isset($_GET['gen']) && $_GET['gen'] <= 10) ? clean_number($vbulletin->GPC['gen'],10) : 1;
//	$dex = ($gen == 2) ? 251 : 151;
	if($gen == 3) {
	    $dex = 386;
    } else if($gen == 2) {
	    $dex = 251;
    } else {
	    $dex = 151;
    }

	$userqry = $db->query_first("SELECT `username` FROM `user` WHERE `userid` = " . $listuser);
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		*
	FROM 
		`poke_indv`
	WHERE  
		`poke_indv`.`userid`=$listuser 
	GROUP BY 
		`poke_indv`.`monid`";
		
	$result = $db->query_read($qry);
	while ($resultLoop = $db->fetch_array($result)) {
	    if($resultLoop["monid"] <= $dex){
	        $owns[$resultLoop["monid"]] = 1;
	    }
	}
    $str .= '<div class="tcg_body"><a href="pokemon.php?section=home&do=dex&gen=1">Gen 1 Pokedex</a> | 
    <a href="pokemon.php?section=home&do=dex&gen=2">Gen 2 Pokedex</a> | 
    <a href="pokemon.php?section=home&do=dex&gen=3">Gen 3 Pokedex</a>
    </div>';

    $str .= '<div class="cards_table">
		<table id="myTable" class="tablesorter"> 
			<tbody id="fbody"><tr>';
	for($i=1; $i<=$dex; $i++) {
		if($owns[$i] == 1) {
    		$str .= '<td><center><a href="pokemon.php?section=pokemon&do=view&pokemon=' . $i . '">
    		<img src="pokemon/images/img/' . $i . '.png" /></a></center></td>';
		} else {
		    $str .= '<td><center><a href="pokemon.php?section=pokemon&do=view&pokemon=' . $i . '">
    		<img src="pokemon/images/pokeball.png" /></a></center></td>';
		}
		if($i%10 == 0) { $str .= '</tr><tr>'; }
	}
	$str .= '</tr></tbody> 
		</table>
		Count: ' . count($owns) . '/' . $dex . '
	</div>';
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'evos') {
    // ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'user' => TYPE_INT,
		'gen' => TYPE_INT
	));
	$listuser = (isset($_GET['user']) && $_GET['user'] <= 10000) ? clean_number($vbulletin->GPC['user'],10000) : $userid;
	$gen = (isset($_GET['gen']) && $_GET['gen'] <= 10) ? clean_number($vbulletin->GPC['gen'],10) : 1;
	$dex = ($gen == 2) ? 251 : 151;
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		*
	FROM 
		`poke_evo`";
		
	$result = $db->query_read($qry);
	$str .= '<div class="cards_table">';
	while ($resultLoop = $db->fetch_array($result)) {
	    $str .= '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["monid"] . '">
    		    <img src="pokemon/images/img/' . $resultLoop["monid"] . '.png" />
    		</a> -> <a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["evo_monid"] . '">
    		    <img src="pokemon/images/img/' . $resultLoop["evo_monid"] . '.png" />
    		</a><br>';
	}
	$str .= '</div>';
	echo $str;
} else {
	echo '<script type="text/javascript">
	<!--
	window.location = "https://forums.novociv.org/pokemon.php"
	//-->
	</script>';
}
} else {
echo "Nothing to see here.";
}
echo '</div>';