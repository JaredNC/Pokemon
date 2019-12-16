<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=team">Team Manager</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div class="tcg_body">
Pokemon start<br><br>';
if(isset($_GET['do']) && $_GET['do'] == 'mass') {
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
		`poke_indv`.`userid`=$userid
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
				    <th>SELL</th>
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
		    <td><a href=/pokemon.php?section=sell&do=sellm&pokemon=' . $resultLoop["c_id"] . '>SELL</a></td>
		</tr>';
	}
	$str .= '</tbody> 
		</table>
	</div>';
	
	$str .= '<br>Total Pokemon:' . $counter . '<br>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'sellm') {
	// ############ GET VARIABLES ############
	//'g' means it's POST data
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	
	
    
    if($exists['Exists'] == true) {
        // ############ Remove pokemon ############
		$db->query_write("UPDATE 
			`poke_indv` 
		SET  
			`userid` = 15
		WHERE 
			`indvid` = " . $pokemon);
			
		// ############ Check other decks for removed pokemon ############
		$qry = "SELECT 
			`poke_deck`.`decklist`,
			`poke_deck`.`deckid`
		FROM 
			`poke_deck`
		WHERE  
			`poke_deck`.`userid` = " . $userid . " 
		ORDER BY 
			`poke_deck`.`deckid` ASC";
		
		$result3 = $db->query_read($qry);
		while ($resultLoop = $db->fetch_array($result3)) {
			$yourdecks = explode(',',$resultLoop["decklist"]);
			if(in_array($pokemon,$yourdecks)) {
				echo '<div>Found pokemon from team ' . $resultLoop["deckid"] . '! Deleting the team...</div>';
				$db->query_write("UPDATE 
					`poke_deck` 
				SET 
					`userid` = 15
				WHERE 
					`deckid` = " . $resultLoop["deckid"]);
				$db->query_write("UPDATE 
					`user` 
				SET 
					`poke_team` = 0
				WHERE 
					`userid` = " . $userid);
			}
		}
		
		// ############ Give pengos ############
		$db->query_write("UPDATE `user` SET ucash = ucash+25 WHERE userid = " . $userid);
		echo "Your pokemon have been successfully processed. Enjoy your pengos!";
    } else {
        echo 'bad';
    }
} else if(isset($_GET['do']) && $_GET['do'] == 'sell') {
	// ############ GET VARIABLES ############
	//'g' means it's POST data
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	$qry = "SELECT 
		`poke_deck`.`decklist` AS 'd_list'
	FROM 
		`poke_deck`
	WHERE  
		`poke_deck`.`deckid`=$deck 
		AND userid = $userid";
	$result = $db->query_first($qry);
	
	$d_list = explode(',',$result["d_list"]);
	
	$error = (count($d_list) == 5) ? false : true;
    
    if(!$error) {
        // ############ Remove pokemon ############
		$db->query_write("UPDATE 
			`poke_indv` 
		SET  
			`userid` = 15
		WHERE 
			`indvid` IN(" . $result["d_list"] . ")");
		
		// ############ Remove deck ############
		$db->query_write("UPDATE 
			`poke_deck` 
		SET 
			`userid` = 15
		WHERE 
			`deckid` = " . $deck);
			
		// ############ Check other decks for removed pokemon ############
		$qry = "SELECT 
			`poke_deck`.`decklist`,
			`poke_deck`.`deckid`
		FROM 
			`poke_deck`
		WHERE  
			`poke_deck`.`userid` = " . $userid . " 
		ORDER BY 
			`poke_deck`.`deckid` ASC";
		
		$result3 = $db->query_read($qry);
		while ($resultLoop = $db->fetch_array($result3)) {
			$yourdecks = explode(',',$resultLoop["decklist"]);
			if(array_intersect($yourdecks,$d_list)) {
				echo '<div>Found pokemon from team ' . $resultLoop["deckid"] . '! Deleting the team...</div>';
				$db->query_write("UPDATE 
					`poke_deck` 
				SET 
					`userid` = 15
				WHERE 
					`deckid` = " . $resultLoop["deckid"]);
				$db->query_write("UPDATE 
					`user` 
				SET 
					`poke_team` = 0
				WHERE 
					`userid` = " . $userid);
			}
		}
		
		// ############ Give candy ############
		$db->query_write("INSERT INTO 
			`poke_items` 
			(itemid, userid, purchase_date)
		VALUES 
			(6," . $userid . "," . time() . ")");
		echo "Your pokemon have been successfully processed. Enjoy your rare candy!";
    } else {
        echo 'bad';
    }
} else if(isset($_GET['do']) && $_GET['do'] == 'sell2') {
	// ############ GET VARIABLES ############
	//'g' means it's POST data
	$vbulletin->input->clean_array_gpc('g', array(
		'deck' => TYPE_INT
	));
	$deck = clean_number($vbulletin->GPC['deck'],5000);
	
	$qry = "SELECT 
		`poke_deck`.`decklist` AS 'd_list'
	FROM 
		`poke_deck`
	WHERE  
		`poke_deck`.`deckid`=$deck 
		AND userid = $userid";
	$result = $db->query_first($qry);
	
	$d_list = explode(',',$result["d_list"]);
	
	$error = (count($d_list) == 6) ? false : true;
    
    if(!$error) {
        // ############ Remove pokemon ############
		$db->query_write("UPDATE 
			`poke_indv` 
		SET  
			`userid` = 15
		WHERE 
			`indvid` IN(" . $result["d_list"] . ")");
		
		// ############ Remove deck ############
		$db->query_write("UPDATE 
			`poke_deck` 
		SET 
			`userid` = 15
		WHERE 
			`deckid` = " . $deck);
			
		// ############ Check other decks for removed pokemon ############
		$qry = "SELECT 
			`poke_deck`.`decklist`,
			`poke_deck`.`deckid`
		FROM 
			`poke_deck`
		WHERE  
			`poke_deck`.`userid` = " . $userid . " 
		ORDER BY 
			`poke_deck`.`deckid` ASC";
		
		$result3 = $db->query_read($qry);
		while ($resultLoop = $db->fetch_array($result3)) {
			$yourdecks = explode(',',$resultLoop["decklist"]);
			if(array_intersect($yourdecks,$d_list)) {
				echo '<div>Found pokemon from team ' . $resultLoop["deckid"] . '! Deleting the team...</div>';
				$db->query_write("UPDATE 
					`poke_deck` 
				SET 
					`userid` = 15
				WHERE 
					`deckid` = " . $resultLoop["deckid"]);
				$db->query_write("UPDATE 
					`user` 
				SET 
					`poke_team` = 0
				WHERE 
					`userid` = " . $userid);
			}
		}
		
		// ############ Give pokeballs ############
		$db->query_write("UPDATE 
				`user` 
			SET 
				`pokeballs` = `pokeballs` + 2
			WHERE 
				`userid` = " . $userid);
		echo "Your pokemon have been successfully processed. Enjoy your Poke Balls!";
    } else {
        echo 'bad';
    }
} else {
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
		if($count == 5) {
    		$request = '<a href="pokemon.php?section=team&do=view&deck=' . $resultLoop["d_id"] . '">' . $resultLoop["d_name"] . '</a> - <a href="pokemon.php?section=sell&do=sell&deck=' . $resultLoop["d_id"] . '">Sell Pokemon for Candy</a>';
    		$str .= '<div class="tradelistactive">
    			' . $request . '
    		</div>';
		}
		if($count == 6) {
    		$request = '<a href="pokemon.php?section=team&do=view&deck=' . $resultLoop["d_id"] . '">' . $resultLoop["d_name"] . '</a> - <a href="pokemon.php?section=sell&do=sell2&deck=' . $resultLoop["d_id"] . '">Sell Pokemon for Balls</a>';
    		$str .= '<div class="tradelistactive">
    			' . $request . '
    		</div>';
		}
	}
	echo $str;
}
} else {
echo "Nothing to see here.";
}
echo '</div>';