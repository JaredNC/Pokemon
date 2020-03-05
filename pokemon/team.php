<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=team">Team Manager</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div class="tcg_body">
Pokemon start<br><br>';
if(isset($_GET['do']) && $_GET['do'] == 'input') {
	if(isset($_GET['action']) && $_GET['action'] == 'make') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'ids' => TYPE_ARRAY,
			'auth' => TYPE_NOHTML,
			'limitedtextfield' => TYPE_NOHTML,
	   		'qrytime' => TYPE_INT
		));
		$deckname = $vbulletin->db->escape_string(str_replace('|', '', $vbulletin->GPC['limitedtextfield']));
		foreach($vbulletin->GPC['ids'] as $value) {
			$ids[] = clean_number($value,20000);
		}
		sort($ids);
		$cards_owned = owned_poke($userid);
		$cards_offered = array_unique(array_map('intval', $ids));
		if(count(array_diff($cards_offered, $cards_owned))) {
			echo 'You offered pokemon that you don\'t have!';
		} else if(count($ids) < 1) {
			echo 'You don\'t have enough pokemon in this team.';
		} else if(count($ids) > 6) {
			echo 'You have too many pokemon in this team.';
		} else if(strlen($deckname) > 100 || $deckname == '') {
			echo 'Invalid team name.';
		} else if($vbulletin->GPC['auth'] == 'Deck') {
			$cardlist = implode(',',$ids);
			$qrylist = implode("','",$ids);
			$qry = "SELECT 
    			`monid`
    		FROM 
    			`poke_indv`
    		WHERE  
    			`indvid` IN('" . $qrylist . "')";
    		$result = $db->query_read($qry);
    		while ($resultLoop = $db->fetch_array($result)) {
    			$mids[] = $resultLoop["monid"];
	    	}
	    	$monlist = implode(',',$mids);
			echo 'Team Name: ' . $deckname . '<br>IDs: ' . $cardlist;
			$db->query_write("INSERT INTO 
				`poke_deck` 
				(userid, name, decklist, mon_ids)
			VALUES 
				('" . $userid . "', '" . $deckname . "', '" . $cardlist . "', '" . $monlist . "')");
			echo 'Making Team!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=team"
			//-->
			</script>';
		} else {
			echo 'Something went wrong.';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'update') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'ids' => TYPE_ARRAY,
			'auth' => TYPE_NOHTML,
			'limitedtextfield' => TYPE_NOHTML,
	   		'qrytime' => TYPE_INT,
	   		'deckid' => TYPE_INT
		));
		$deck = clean_number($vbulletin->GPC['deckid'],5000);
		// ############ CHECK IF TRADE EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck AND userid = $userid AND rental != 1) AS 'Exists'");
		
		// ############ MAIN CODE ############
		$deckname = $vbulletin->db->escape_string($vbulletin->GPC['limitedtextfield']);
		foreach($vbulletin->GPC['ids'] as $value) {
			$ids[] = clean_number($value,20000);
		}
		sort($ids);
		$cards_owned = owned_poke($userid);
		$cards_offered = array_unique(array_map('intval', $ids));
		if(count(array_diff($cards_offered, $cards_owned))) {
			echo 'You offered pokemon that you don\'t have!';
		} else if(count($ids) < 1) {
			echo 'You don\'t have enough pokemon in this deck.';
		} else if(count($ids) > 6) {
			echo 'You have too many pokemon in this deck.';
		} else if(strlen($deckname) > 100 || $deckname == '') {
			echo 'Invalid team name.';
		} else if($exists['Exists'] == false) {
			echo 'You are trying to update a team that isn\'t yours or doesn\'t exist.';
		} else if($vbulletin->GPC['auth'] == 'Deck') {
			$cardlist = implode(',',$ids);
			$qrylist = implode("','",$ids);
			$qry = "SELECT 
    			`monid`
    		FROM 
    			`poke_indv`
    		WHERE  
    			`indvid` IN('" . $qrylist . "')";
    		$result = $db->query_read($qry);
    		while ($resultLoop = $db->fetch_array($result)) {
    			$mids[] = $resultLoop["monid"];
	    	}
	    	$monlist = implode(',',$mids);
			echo 'Team Name: ' . $deckname . '<br>IDs: ' . $cardlist;
			$db->query_write("UPDATE 
				`poke_deck`
			SET
				`name` = '" . $deckname . "',
				`decklist` = '" . $cardlist . "',
				`mon_ids` = '" . $monlist . "'
			WHERE
				`deckid` = " . $deck);
			echo 'Updating Team!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=team"
			//-->
			</script>';
		} else {
			echo 'Something went wrong.';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'delete') {
		// ############ GET VARIABLES ############
		//'g' means it's POST data
		$vbulletin->input->clean_array_gpc('g', array(
			'deck' => TYPE_INT
		));
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'qrytime' => TYPE_INT
		));
		$deck = clean_number($vbulletin->GPC['deck'],5000);
		// ############ CHECK IF TRADE EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck AND userid = $userid) AS 'Exists'");
		
		// ############ MAIN CODE ############
		if($exists['Exists'] == true && $vbulletin->GPC['auth'] == 'Delete') {
			$db->query_write("UPDATE 
				`poke_deck` 
			SET 
				`userid` = 15,
				`rental` = 0
			WHERE 
				`deckid` = " . $deck);
			echo 'Deleting Team...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=team"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'active') {
		// ############ GET VARIABLES ############
		//'g' means it's POST data
		$vbulletin->input->clean_array_gpc('g', array(
			'deck' => TYPE_INT
		));
		$deck = clean_number($vbulletin->GPC['deck'],5000);
		// ############ CHECK IF TRADE EXISTS ############
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck AND userid = $userid) AS 'Exists'");
		
		// ############ MAIN CODE ############
		if($exists['Exists'] == true) {
			$db->query_write("UPDATE 
				`user` 
			SET 
				`poke_team` = " . $deck . "
			WHERE 
				`userid` = " . $userid);
			echo 'Activating Team...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=team"
			//-->
			</script>';
		} else {
		    echo 'Bad Team';
		}
	}  else {
		echo 'Error.';
	}
} else if(isset($_GET['do']) && $_GET['do'] == 'view' && isset($_GET['deck']) && $_GET['deck'] <= 5000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result1 = $db->query_first("SELECT
			`poke_deck`.`decklist` AS 'cardlist',
			`poke_deck`.`name` AS 'deckname',
			`poke_deck`.`userid` AS 'userid',
			`poke_deck`.`rental` AS 'rental',
			`user`.`username` AS 'username'
		FROM 
			`poke_deck`
			INNER JOIN (`user`)
				ON (`poke_deck`.`userid` = `user`.`userid`)
		WHERE
			`poke_deck`.`deckid` = $deck");
		
		if($result1["userid"] == 15 && $result1["rental"] != 1) {
			echo 'That team has been deleted.</div>';
			exit($footer);
		}
		$cards = explode(',',$result1["cardlist"]);
		$userlink = '<a href="member.php?' . $result1["userid"] . '">' . $result1["username"] . '</a>';
		$qrytime = time();
		$listuser = $userid;
		
		// ############ QUERY VARIABLES ############
		$qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_indv`.`level` AS 'c_level', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_mon`.`type` AS 'c_rarity',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			LEFT JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`indvid` IN(" . $result1["cardlist"] . ")
		ORDER BY 
			`poke_indv`.`monid` ASC";
		$result = $db->query_read($qry);
		//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
		$counter = 0;
		$str .= '<script type="text/javascript">
		var datam, regm, jom, $tm, containm, stokenm;
		jQuery(document).ready(function() 
		{ 
			jQuery("#myTable").tablesorter(); 
		});
		</script>';
		$str .= 'Team: "' . $result1["deckname"] . '" by ' . $userlink . '.';
		$str .= '<div class="cards_table">
			<table id="myTable" class="tablesorter"> 
				<thead>
					<tr>
					    <th>Pokemon Name</th>
					    <th>Nickname</th>
					    <th>ID</th>
					    <th>Level</th>
					    <th>Shiny</th>
					    <th>Type</th>
					</tr>
				</thead>
				<tbody id="fbody">';
		while ($resultLoop = $db->fetch_array($result)) {
			$counter++;
			$foil = ($resultLoop["c_foil"] == 0) ? 'No' : 'Shiny';
			$type = $resultLoop["c_type"];
			$level = $resultLoop["c_level"];
			$nick = ($resultLoop["c_nick"] == '') ? 'NA' : $resultLoop["c_nick"];
			$cardname = '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["c_masterid"] . '">' . $resultLoop["c_name"] . '</a>';
			$nickname = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop["c_id"] . '">' . $nick . '</a>';
			$str .= '<tr> 
			    <td>' . $cardname . '</td> 
			    <td>' . $nickname . '</td>
			    <td>' . $resultLoop["c_id"] . '</td> 
			    <td>' . $level . '</td> 
			    <td>' . $foil . '</td> 
			    <td>' . $type . '</td>
			</tr>';
		}
		$str .= '</tbody> 
			</table>
		</div>';
		
		$str .= '<br>Total Pokemon: ' . $counter . '<br>';
		
		if($userid == $result1["userid"] && $result1["rental"] != 1) {
			$str .= '<div id="cancel_form">
				<form class="deck" action="/pokemon.php?section=team&do=input&deck=' . $deck . '&action=delete" method="post">
					<input type="hidden" name="auth" value="Delete" />
					<input type="hidden" name="qrytime" value="' . $qrytime . '" />
					<input type="submit" value="Delete Team" />
				</form>
			</div>';
			$str .= '<div id="update_deck">
				<form class="deck" action="/pokemon.php?section=team&do=update&deck=' . $deck . '" method="post">
					<input type="submit" value="Update Team" />
				</form>
			</div>';
		}
		$str .= '<div id="update_deck">
			<form class="deck" action="/pokemon.php?section=team&do=export&deck=' . $deck . '" method="post">
				<input type="submit" value="Export Team" />
			</form>
		</div>';
	} else {
		$str .= 'That team does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'view_raw' && isset($_GET['deck']) && $_GET['deck'] <= 5000){
    // ############ CLEAN VARIABLES ############
    $vbulletin->input->clean_array_gpc('g', array(
        'deck' => TYPE_INT
    ));
    $deck = clean_number($vbulletin->GPC['deck'],5000);

    // ############ CHECK IF TRADE EXISTS ############
    $exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck) AS 'Exists'");

    // ############ MAIN CODE ############
    if($exists['Exists'] == true) {
        $result1 = $db->query_first("SELECT
			`poke_deck`.`decklist` AS 'cardlist',
			`poke_deck`.`name` AS 'deckname',
			`poke_deck`.`userid` AS 'userid',
			`poke_deck`.`rental` AS 'rental',
			`user`.`username` AS 'username'
		FROM 
			`poke_deck`
			INNER JOIN (`user`)
				ON (`poke_deck`.`userid` = `user`.`userid`)
		WHERE
			`poke_deck`.`deckid` = $deck");

        if($result1["userid"] == 15 && $result1["rental"] != 1) {
            echo '<div id="team_dump">0</div>
                That team has been deleted.</div>';
            exit($footer);
        }
        $cards = explode(',',$result1["cardlist"]);
        $qrytime = time();
        $listuser = $userid;

        // ############ QUERY VARIABLES ############
        $qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_indv`.`level` AS 'c_level', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`friend` AS 'c_friend',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			LEFT JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`indvid` IN(" . $result1["cardlist"] . ")
		ORDER BY `poke_indv`.`level` DESC";
        $result = $db->query_read($qry);
        //echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
        $counter = 0;
        $str .= '<div id="team_dump">';
        while ($resultLoop = $db->fetch_array($result)) {
            $counter++;
            $monid = $resultLoop["c_masterid"];
            $nick = ($resultLoop["c_nick"] == '') ? $resultLoop["c_name"] : $resultLoop["c_nick"];
            $level = $resultLoop["c_level"];
            $friend = $resultLoop["c_friend"];
            $item_id = 0;
            $type = $resultLoop["c_type"];

            $str .= '<p>' . implode(",",array($monid,$nick,$level,$friend,$item_id,$type)) . '</p>';
        }
        $str .= '</div>';

        $str .= '<div id="team_owner">' . $result1["username"] . '</div>';

        $str .= '<br>Total Pokemon: ' . $counter . '<br>';
    } else {
        $str .= '<div id="team_dump">0</div>
        That team does not exist!<br>';
    }
    echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'make1') {
	$listuser = $userid;
	
	// ############ QUERY VARIABLES ############
	if(isset($_GET['sort']) && $_GET['sort'] == 'level') {
		$qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_indv`.`level` AS 'c_level',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			INNER JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`userid`=$listuser 
		ORDER BY 
			`poke_indv`.`level` DESC,
			`poke_mon`.`monname` ASC";
	} else if(isset($_GET['sort']) && $_GET['sort'] == 'name') {
		$qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_indv`.`level` AS 'c_level',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			INNER JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`userid`=$listuser 
		ORDER BY 
			`poke_mon`.`monname` ASC";
	} else {
	    $qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_indv`.`level` AS 'c_level',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			INNER JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`userid`=$listuser 
		ORDER BY 
			`poke_mon`.`monid` ASC";
	}
	$result = $db->query_read($qry);
	//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
	$counter = 0;
	$str .= '<script type="text/javascript">
	var datam, regm, jom, $tm, containm, stokenm;
	jQuery(document).ready(function() 
	{  
		
	jQuery(\'input[type="checkbox"]\').click(function () {
	    var days = jQuery(\'input[type="checkbox"]:checked\').length;
	    if (days == 100) {
	        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', true);
	    } else {
	        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', false);
	    }
	    document.getElementById("countchecked").innerHTML=days;
	    if (days >= 40) {
	        jQuery("#make_deck").show();
	    } else {
	        jQuery("#make_deck").hide();
	    }
	})
	jQuery(\'input[name="other_1"]\').change(function () {
	    jQuery(\'input[type="checkbox"]\').prop({
	        \'checked\': false,
	            \'disabled\': false
	    });
	});
	jQuery(\'.checkbox_wrapper\').on(\'click\', function(e){ 
	var checked = jQuery(this).find(\'input[type="checkbox"]\').is(":checked");
	if(checked){
		jQuery(this).parent().addClass(\'is_selected\').removeClass(\'not_selected\');
	}else{
	        jQuery(this).parent().addClass(\'not_selected\').removeClass(\'selected\');
	}                        
	});
	});
	</script>';
	$str .= '<div class="deck_limit">Team size: <span id="countchecked">0</span>/6</div>';
	$str .= '<div><a href="pokemon.php?section=team&do=make1&sort=name">Sort by name.</a> | 
	                <a href="pokemon.php?section=team&do=make1&sort=level">Sort by level.</a> |
	                <a href="pokemon.php?section=team&do=make1&sort=id">Sort by ID.</a></div>';
	$str .= '<div class="cards_table">
	<form class="deck" action="/pokemon.php?section=team&do=input&action=make" method="post">';
	$str .= 'Team Name:<br>
	<input class="title" name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,100);" 
	onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,100);" maxlength="100"><br>
	<font size="1">(Maximum characters: 100)<br>
	You have <input readonly type="text" name="countdown" size="3" value="100"> characters left.</font><br>';
	while ($resultLoop = $db->fetch_array($result)) {
		$counter++;
		$foil = ($resultLoop["c_foil"] == 0) ? 'foil0' : 'foil1';
		$type = $resultLoop["c_type"];
		$level = $resultLoop["c_level"];
		$cardimg = '<img class="deck' . $foil . '" src="pokemon/images/monimages/600px-' . str_pad($resultLoop["c_masterid"] , 3 , "0" , STR_PAD_LEFT) . $resultLoop["c_name"] . '.png" alt="' . $resultLoop["c_nick"] . '" />';
		$str.= '
		<div class="not_selected">
			<div class="checkbox_wrapper">
				<label class="deck_card" for="cb' . $counter . '">
					' . $cardimg . '
					<div class="checkboxdiv">
						<input name="ids[]" id="cb' . $counter . '" type="checkbox" value="' . $resultLoop["c_id"] . '" />
					</div>
				</label>
			</div>
		</div>';
	}
	$str .= '<input type="hidden" name="auth" value="Deck" />
	<input type="hidden" name="qrytime" value="' . $qrytime . '" />
	<div><input type="submit" value="Make Team!" /></div>
	</form>
	</div>';
	
	$str .= '<br>Total Pokemon: ' . $counter . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'make') {
	$listuser = $userid;
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		`poke_indv`.`indvid` AS 'c_id', 
		`poke_indv`.`nick` AS 'c_nick', 
		`poke_mon`.`monname` AS 'c_name', 
		`poke_mon`.`type` AS 'c_type',
		`poke_indv`.`shiny` AS 'c_foil',
		`poke_indv`.`level` AS 'c_level',
		`poke_indv`.`monid` AS 'c_masterid'
	FROM 
		`poke_indv`
		INNER JOIN (`poke_mon`)
			ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
	WHERE  
		`poke_indv`.`userid`=$listuser 
	ORDER BY 
		`poke_mon`.`monid` ASC";
		
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
          jQuery(\'input[type="checkbox"]\').click(function () {
	    var days = jQuery(\'input[type="checkbox"]:checked\').length;
	    if (days == 100) {
	        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', true);
	    } else {
	        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', false);
	    }
	    document.getElementById("countchecked").innerHTML=days;
	    if (days >= 40) {
	        jQuery("#make_deck").show();
	    } else {
	        jQuery("#make_deck").hide();
	    }
	})
	jQuery(\'input[name="other_1"]\').change(function () {
	    jQuery(\'input[type="checkbox"]\').prop({
	        \'checked\': false,
	            \'disabled\': false
	    });
	});
	});
	</script>';
	$str .= '<div class="deck_limit">Team size: <span id="countchecked">0</span>/6</div>';
	$str .= '<div><a href="pokemon.php?section=team&do=make1&sort=id">Experimental Gallery View</a>';
	$str .= '<div class="cards_table">
	<form class="deck" action="/pokemon.php?section=team&do=input&action=make" method="post">';
	$str .= 'Team Name:<br>
	<input class="title" name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,100);" 
	onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,100);" maxlength="100"><br>
	<font size="1">(Maximum characters: 100)<br>
	You have <input readonly type="text" name="countdown" size="3" value="100"> characters left.</font><br>';
	$str .= '<div>Use this box to filter: <input id="searchInput" value="Type To Filter"></div>';
	$str .= '	<table id="myTable" class="tablesorter"> 
			<thead>
				<tr>
				    <th>ID</th>
				    <th>Pokemon Name</th>
				    <th>Nickname</th>
				    <th>Level</th>
				    <th>Type</th>
				    <th>Shiny</th>
				    <th>Eligible</th>
				    <th>Add to Team</th>
				</tr>
			</thead>
			<tbody id="fbody">';
	while ($resultLoop = $db->fetch_array($result)) {
		$counter++;
		$foil = ($resultLoop["c_foil"] == 0) ? 'No' : 'Shiny';
		$g2 = ($resultLoop["c_id"] < 3994) ? 'Gen I' : 'Gen II';
		$type = $resultLoop["c_type"];
		$level = $resultLoop["c_level"];
		$nick = ($resultLoop["c_nick"] == '') ? 'NA' : $resultLoop["c_nick"];
		$cardname = '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["c_masterid"] . '">' . $resultLoop["c_name"] . '</a>';
		$cardnick = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop["c_id"] . '">' . $nick . '</a>';
		$str .= '<tr> 
		    <td>' . $resultLoop["c_masterid"] . '</td> 
		    <td>' . $cardname . '</td> 
		    <td>' . $cardnick . '</td>
		    <td>' . $level . '</td> 
		    <td>' . $type . '</td> 
		    <td>' . $foil . '</td> 
		    <td>' . $g2 . '</td> 
		    <td><input type="checkbox" name="ids[]" value="' . $resultLoop["c_id"] . '" /></td>
		</tr>';
	}
	$str .= '</tbody> 
		</table>
	<input type="hidden" name="auth" value="Deck" />
	<input type="hidden" name="qrytime" value="' . $qrytime . '" />
	<div><input type="submit" value="Make Team!" /></div>
	</form>
	</div>';
	
	$str .= '<br>Total Pokemon: ' . $counter . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'update1' && isset($_GET['deck']) && $_GET['deck'] <= 5000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck AND userid = $userid AND rental != 1) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result1 = $db->query_first("SELECT
			`poke_deck`.`decklist` AS 'cardlist',
			`poke_deck`.`name` AS 'deckname'
		FROM 
			`poke_deck`
		WHERE
			`poke_deck`.`deckid` = $deck");
		
		$cards = explode(',',$result1["cardlist"]);
		$listuser = $userid;
		
		// ############ QUERY VARIABLES ############
		if(isset($_GET['sort']) && $_GET['sort'] == 'level') {
		$qry = "SELECT 
				`poke_indv`.`indvid` AS 'c_id', 
				`poke_indv`.`nick` AS 'c_nick', 
				`poke_mon`.`monname` AS 'c_name', 
				`poke_mon`.`type` AS 'c_type',
				`poke_indv`.`shiny` AS 'c_foil',
				`poke_indv`.`level` AS 'c_level',
				`poke_indv`.`monid` AS 'c_masterid'
			FROM 
				`poke_indv`
				INNER JOIN (`poke_mon`)
					ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
			WHERE  
				`poke_indv`.`userid`=$listuser 
			ORDER BY 
				`poke_indv`.`level` DESC,
				`poke_mon`.`monname` ASC";
		} else if(isset($_GET['sort']) && $_GET['sort'] == 'name') {
			$qry = "SELECT 
				`poke_indv`.`indvid` AS 'c_id', 
				`poke_indv`.`nick` AS 'c_nick', 
				`poke_mon`.`monname` AS 'c_name', 
				`poke_mon`.`type` AS 'c_type',
				`poke_indv`.`shiny` AS 'c_foil',
				`poke_indv`.`level` AS 'c_level',
				`poke_indv`.`monid` AS 'c_masterid'
			FROM 
				`poke_indv`
				INNER JOIN (`poke_mon`)
					ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
			WHERE  
				`poke_indv`.`userid`=$listuser 
			ORDER BY 
				`poke_mon`.`monname` ASC";
		} else {
			$qry = "SELECT 
				`poke_indv`.`indvid` AS 'c_id', 
				`poke_indv`.`nick` AS 'c_nick', 
				`poke_mon`.`monname` AS 'c_name', 
				`poke_mon`.`type` AS 'c_type',
				`poke_indv`.`shiny` AS 'c_foil',
				`poke_indv`.`level` AS 'c_level',
				`poke_indv`.`monid` AS 'c_masterid'
			FROM 
				`poke_indv`
				INNER JOIN (`poke_mon`)
					ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
			WHERE  
				`poke_indv`.`userid`=$listuser 
			ORDER BY 
				`poke_mon`.`monid` ASC";
		}
		$result = $db->query_read($qry);
		//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
		$counter = 0;
		$str .= '<script type="text/javascript">
		var datam, regm, jom, $tm, containm, stokenm;
		jQuery(document).ready(function() 
		{  
			
		jQuery(\'input[type="checkbox"]\').click(function () {
		    var days = jQuery(\'input[type="checkbox"]:checked\').length;
		    if (days == 100) {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', true);
		    } else {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', false);
		    }
		    document.getElementById("countchecked").innerHTML=days;
		    if (days >= 40) {
		        jQuery("#make_deck").show();
		    } else {
		        jQuery("#make_deck").hide();
		    }
		})
		jQuery(\'input[name="other_1"]\').change(function () {
		    jQuery(\'input[type="checkbox"]\').prop({
		        \'checked\': false,
		            \'disabled\': false
		    });
		});
		jQuery(\'.checkbox_wrapper\').on(\'click\', function(e){ 
		var checked = jQuery(this).find(\'input[type="checkbox"]\').is(":checked");
		if(checked){
			jQuery(this).parent().addClass(\'is_selected\').removeClass(\'not_selected\');
		}else{
		        jQuery(this).parent().addClass(\'not_selected\').removeClass(\'selected\');
		}                        
		});
		});
		</script>';
		$str .= '<div class="deck_limit">Deck size: <span id="countchecked">' . count($cards) . '</span>/6</div>';
		$str .= '<div>
		<a href="pokemon.php?section=team&do=update1&sort=name&deck=' . $deck . '">Sort by name.</a> | 
		<a href="pokemon.php?section=team&do=update1&sort=level&deck=' . $deck . '">Sort by level.</a> |
	    <a href="pokemon.php?section=team&do=update1&sort=id&deck=' . $deck . '">Sort by ID.</a>
		</div>';
		$str .= '<div class="cards_table">
		<form class="deck" action="/pokemon.php?section=team&do=input&action=update" method="post">';
		$str .= 'Team Name:<br>
		<input class="title" name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,100);" 
		onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,100);" maxlength="100" value="' . $result1["deckname"] . '"><br>
		<font size="1">(Maximum characters: 100)<br>
		You have <input readonly type="text" name="countdown" size="3" value="' . (100 - strlen($result1["deckname"])) . '"> characters left.</font><br>';
		while ($resultLoop = $db->fetch_array($result)) {
			$selected = (in_array($resultLoop["c_id"],$cards)) ? 'is_selected ' : 'not_selected';
			$checked = (in_array($resultLoop["c_id"],$cards)) ? 'checked' : '';
			$counter++;
			$foil = ($resultLoop["c_foil"] == 0) ? 'foil0' : 'foil1';
			$type = $resultLoop["c_type"];
			$level = $resultLoop["c_level"];
			$cardimg = '<img class="deck' . $foil . '" src="pokemon/images/monimages/600px-' . str_pad($resultLoop["c_masterid"] , 3 , "0" , STR_PAD_LEFT) . $resultLoop["c_name"] . '.png" alt="' . $resultLoop["c_nick"] . '" />';
			$str.= '
			<div class="' . $selected . '">
				<div class="checkbox_wrapper">
					<label class="deck_card" for="cb' . $counter . '">
						' . $cardimg . '
						<div class="checkboxdiv">
							<input name="ids[]" id="cb' . $counter . '" type="checkbox" value="' . $resultLoop["c_id"] . '" ' . $checked . '/>
						</div>
					</label>
				</div>
			</div>';
		}
		$str .= '<input type="hidden" name="auth" value="Deck" />
		<input type="hidden" name="deckid" value="' . $deck . '" />
		<input type="hidden" name="qrytime" value="' . $qrytime . '" />
		<div><input type="submit" value="Update Team!" /></div>
		</form>
		</div>';
		
		$str .= '<br>Total Pokemon: ' . $counter . '<br>';
	} else {
		$str .= 'That team doesn\'t exist or you don\'t own it!
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php?section=team"
		//-->
		</script>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'export' && isset($_GET['deck']) && $_GET['deck'] <= 5000){
    // ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result1 = $db->query_first("SELECT
			`poke_deck`.`decklist` AS 'cardlist',
			`poke_deck`.`name` AS 'deckname',
			`poke_deck`.`userid` AS 'userid',
			`poke_deck`.`rental` AS 'rental',
			`user`.`username` AS 'username'
		FROM 
			`poke_deck`
			INNER JOIN (`user`)
				ON (`poke_deck`.`userid` = `user`.`userid`)
		WHERE
			`poke_deck`.`deckid` = $deck");
		
		if($result1["userid"] == 15 && $result1["rental"] != 1) {
			echo 'That team has been deleted.</div>';
			exit($footer);
		}
		$cards = explode(',',$result1["cardlist"]);
		$qrytime = time();
		$listuser = $userid;
		
		// ############ QUERY VARIABLES ############
		$qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_indv`.`level` AS 'c_level', 
			`poke_indv`.`catch_date` AS `c_date`,
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_mon`.`type` AS 'c_rarity',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			LEFT JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`indvid` IN(" . $result1["cardlist"] . ")
		ORDER BY 
			`poke_indv`.`monid` ASC";
		$result = $db->query_read($qry);
		//echo "<br>Query Time Elapsed: ".(microtime(true) - $qrytime)."s<br>";
		$counter = 0;
		$str .= '<div class=tcg_body>';
		$gen1 = 0;
		while ($resultLoop = $db->fetch_array($result)) {
		    if($resultLoop['c_id'] < 3994) {
		        $gen1 = 1;
		    }
			$counter++;
			$foil = ($resultLoop["c_foil"] == 0) ? '' : 'Shiny: Yes<br>';
			$type = $resultLoop["c_type"];
			$level = $resultLoop["c_level"];
			$cardname = $resultLoop["c_name"];
			$nickname = ($resultLoop["c_nick"] == '') ? $cardname : $resultLoop["c_nick"] . ' (' . $cardname . ')';
			$str .= $nickname . '<br>
			    Ability: None<br>
			    Level: ' . $level . '<br> 
			    ' . $foil . '<br>';
		}
		$str .= '</div>';
		if($gen1 == 1) {
		    $str .= '<div class=tradelistinactive>Not Eligible for Gen 2 Challenges</div>';
		}
		$str .= '<br>Total Pokemon: ' . $counter . '<br>';
	} else {
		$str .= 'That team does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'update' && isset($_GET['deck']) && $_GET['deck'] <= 5000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_deck WHERE deckid = $deck AND userid = $userid AND rental != 1) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result1 = $db->query_first("SELECT
			`poke_deck`.`decklist` AS 'cardlist',
			`poke_deck`.`name` AS 'deckname'
		FROM 
			`poke_deck`
		WHERE
			`poke_deck`.`deckid` = $deck");
		
		$cards = explode(',',$result1["cardlist"]);
		$rarray = array(1 => 'Common', 'Uncommon', 'Rare', 'Mythic');
		$listuser = $userid;
		
		// ############ QUERY VARIABLES ############
		$qry = "SELECT 
			`poke_indv`.`indvid` AS 'c_id', 
			`poke_indv`.`nick` AS 'c_nick', 
			`poke_mon`.`monname` AS 'c_name', 
			`poke_mon`.`type` AS 'c_type',
			`poke_indv`.`shiny` AS 'c_foil',
			`poke_indv`.`level` AS 'c_level',
			`poke_indv`.`monid` AS 'c_masterid'
		FROM 
			`poke_indv`
			LEFT JOIN (`poke_mon`)
				ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
		WHERE  
			`poke_indv`.`userid`=$listuser 
		ORDER BY 
			`poke_mon`.`monid` ASC";
			
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
          jQuery(\'input[type="checkbox"]\').click(function () {
		    var days = jQuery(\'input[type="checkbox"]:checked\').length;
		    if (days == 100) {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', true);
		    } else {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', false);
		    }
		    document.getElementById("countchecked").innerHTML=days;
		    if (days >= 40) {
		        jQuery("#make_deck").show();
		    } else {
		        jQuery("#make_deck").hide();
		    }
		})
		jQuery(\'input[name="other_1"]\').change(function () {
		    jQuery(\'input[type="checkbox"]\').prop({
		        \'checked\': false,
		            \'disabled\': false
		    });
		});
		});
		</script>';
		$str .= '<div class="deck_limit">Team size: <span id="countchecked">' . count($cards) . '</span>/6</div>';
		$str .= '<div><a href="pokemon.php?section=team&do=update1&sort=id&deck=' . $deck . '">Experimental Gallery View</a>';
		$str .= '<div class="cards_table">
		<form class="deck" action="/pokemon.php?section=team&do=input&action=update" method="post">';
		$str .= 'Team Name:<br>
		<input class="title" name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,100);" 
		onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,100);" maxlength="100" value="' . $result1["deckname"] . '"><br>
		<font size="1">(Maximum characters: 100)<br>
		You have <input readonly type="text" name="countdown" size="3" value="' . (100 - strlen($result1["deckname"])) . '"> characters left.</font><br>';
		$str .= '<div>Use this box to filter: <input id="searchInput" value="Type To Filter"></div>';
	    $str .= '	<table id="myTable" class="tablesorter"> 
				<thead>
					<tr>
					    <th>ID</th>
						<th>Pokemon Name</th>
						<th>Nickname</th>
						<th>Level</th>
						<th>Type</th>
						<th>Shiny</th>
						<th>Eligible</th>
						<th>Add to Team</th>
					</tr>
				</thead>
				<tbody id="fbody">';
		while ($resultLoop = $db->fetch_array($result)) {
			$checked = (in_array($resultLoop["c_id"],$cards)) ? 'checked' : '';
			$checked2 = (in_array($resultLoop["c_id"],$cards)) ? 'T' : '';
			$counter++;
			$foil = ($resultLoop["c_foil"] == 0) ? 'No' : 'Shiny';
			$g2 = ($resultLoop["c_id"] < 3994) ? 'Gen I' : 'Gen II';
		    $type = $resultLoop["c_type"];
			$level = $resultLoop["c_level"];
			$nick = ($resultLoop["c_nick"] == '') ? 'NA' : $resultLoop["c_nick"];
			$cardname = '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $resultLoop["c_masterid"] . '">' . $resultLoop["c_name"] . '</a>';
			$cardnick = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop["c_id"] . '">' . $nick . '</a>';
			$str .= '<tr> 
			    <td>' . $resultLoop["c_masterid"] . '</td> 
				<td>' . $cardname . '</td> 
				<td>' . $cardnick . '</td>
				<td>' . $level . '</td> 
				<td>' . $type . '</td> 
				<td>' . $foil . '</td> 
				<td>' . $g2 . '</td> 
				<td><input type="checkbox" name="ids[]" value="' . $resultLoop["c_id"] . '" ' . $checked . '/>' . $checked2 . '</td>
			</tr>';
		}
		$str .= '</tbody> 
			</table>
		<input type="hidden" name="auth" value="Deck" />
		<input type="hidden" name="deckid" value="' . $deck . '" />
		<input type="hidden" name="qrytime" value="' . $qrytime . '" />
		<div><input type="submit" value="Update Team!" /></div>
		</form>
		</div>';
		
		$str .= '<br>Total Pokemon: ' . $counter . '<br>';
	} else {
		$str .= 'That team doesn\'t exist or you don\'t own it!
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php?section=team"
		//-->
		</script>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'list') {
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		`poke_deck`.`deckid` AS 'd_id', 
		`poke_deck`.`name` AS 'd_name',
		`poke_deck`.`decklist` AS 'd_list'
	FROM 
		`poke_deck`
	WHERE  
		`poke_deck`.`userid`=$userid
	ORDER BY 
		`poke_deck`.`deckid` ASC";
	$result = $db->query_read($qry);
	$str .= '<h1>Displaying your teams:</h1>';
	while ($resultLoop = $db->fetch_array($result)) {
		$count = count(explode(',',$resultLoop["d_list"]));
		$request = '<a href="pokemon.php?section=team&do=view&deck=' . $resultLoop["d_id"] . '">' . $resultLoop["d_name"] . ' (' . $count . 
		' pokemon)</a> - <a href="pokemon.php?section=team&do=input&action=active&deck=' . $resultLoop["d_id"] . '">Make Active Team</a>';
		$str .= '<div class="tradelistactive">
			' . $request . '
		</div>';
	}
	echo $str;
} else {
	$result = $db->query_first("SELECT
		count(*) AS 'count'
	FROM 
		`poke_deck`
	WHERE
		`userid` = $userid");
	$count = $result["count"];
	if($count > 0) { $viewdecks = '<h1><a class="deck" href="/pokemon.php?section=team&do=list">View Teams (' . $count . ')</a></h1>' . "\n"; }
	echo "\n" . '<div class="deck_option">' . "\n" . $viewdecks . '
	<h1><a class="deck" href="/pokemon.php?section=team&do=make">Make a Team</a></h1>' . "\n" . '
	</div>' . "\n";
}
} else {
echo "Nothing to see here.";
}
echo '</div>';