<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=pokemon">Pokemon</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo 'Pokemon start<br><br>';

if(isset($_GET['do']) && $_GET['do'] == 'view' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_mon WHERE monid = $pokemon) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result = $db->query_first("SELECT
			`poke_mon`.`monname` AS 'c_name'
		FROM
			`poke_mon`
		WHERE
			`poke_mon`.`monid` = $pokemon");
		$pokemonname = $result["c_name"];
		$str .= '<h1 class="ownedcard">' . $pokemonname . '</h1><br>
		<img src="pokemon/images/monimages/600px-' . str_pad($pokemon , 3 , "0" , STR_PAD_LEFT) . $pokemonname . '.png" />';
		$str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=owned&pokemon=' . $pokemon . '">View users who own this pokemon.</a></h2></div>';
	} else {
		$str .= 'That pokemon does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'view2' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result = $db->query_first("SELECT
			`poke_mon`.`monname` AS 'c_name',
			`poke_mon`.`monid` AS 'c_monid',
			`poke_mon`.`evolution` AS 'c_evo',
			`poke_indv`.`userid` AS 'c_owner',
			`poke_indv`.`nick` AS 'c_nick',
			`poke_indv`.`shiny` AS 'c_shiny',
			`poke_indv`.`level` AS 'c_level',
			`poke_indv`.`exp` AS 'c_exp',
			`poke_indv`.`friend` AS 'c_friend',
			`poke_indv`.`gender` AS 'c_gender',
			`user`.`username` AS 'c_ownername',
			`poke_indv`.`indv_item_id` AS 'c_itemid',
			`poke_indv`.`mom_id` AS 'c_momid'
		FROM
		    `poke_indv`
    		LEFT JOIN
    			`poke_mon`
    		ON
    		    `poke_indv`.`monid` = `poke_mon`.`monid`
    		INNER JOIN
    			`user`
    		ON
    		    `poke_indv`.`userid` = `user`.`userid`
		WHERE
			`poke_indv`.`indvid` = $pokemon");
		$pokemonname = $result["c_name"];
		//$nick = ($result["c_nick"] == '') ? $pokemonname : $result["c_nick"];
		$shine = ($result["c_shiny"] == 1) ? 'shiny' : '';
		$shine2 = ($result["c_shiny"] == 1) ? 'S' : '';
		if($result["c_itemid"] == 0) {
		    $itemstr = 'NA';
		    if($userid == $result["c_owner"]) {
		        $itemstr .= ' (<a href="/pokemon.php?section=pokemon&do=equip&pokemon=' . $pokemon . '">Equip an Item</a>)';
		    }
		} else {
		    $item_qry2 = $db->query_first("SELECT * FROM poke_item_master WHERE itemid = " . $result["c_itemid"]);
		    $itemstr = $item_qry2['name'];
		    if($userid == $result["c_owner"]) {
		        $itemstr .= ' (<a href="/pokemon.php?section=pokemon&do=transact&action=remove&pokemon=' . $pokemon . '">Remove Item</a>)';
		    }
		}
		
		if($result["c_nick"] != '') {
		    $nick = $result["c_nick"];
		    $title = '<font size="+2">' . $nick . '</font> (' . $pokemonname . ')';
		} else {
		    $nick = $pokemonname;
		    $title = '<font size="+2">' . $pokemonname . '</font>';
		}
		
		if($result["c_momid"] != 0) {
		    $momqry = $db->query_first("
    		    SELECT
    		        `poke_mon`.`monname` AS 'name',
    		        `poke_indv`.`nick` AS 'nick'
                FROM
                    `poke_indv`
                INNER JOIN
                    `poke_mon`
                ON
                    `poke_indv`.`monid` = `poke_mon`.`monid`
                WHERE
                    `poke_indv`.`indvid` = " . $result["c_momid"]);
            $momstr = ($result["c_gender"] == 'M') ? 'Son of ' : 'Daughter of ';
            $mom_nick = ($momqry["nick"] != '') ? $momqry["nick"] : $momqry["name"];
            $momstr .= '<a href=/pokemon.php?section=pokemon&do=view2&pokemon=' . $result["c_momid"] . '>' . $mom_nick . '</a><br>';
		} else {
		    $momstr = '';
		}
		if($result["c_shiny"] == 1) {
		    $shiny_link = $db->query_first("
    		    SELECT
    		        `link`
                FROM
                    `poke_shiny_link`
                WHERE
                    `indvid` = " . $pokemon . "
                ORDER BY 
                    linkid DESC");
		    $link = ($shiny_link['link'] == '') ? 'pokemon/images/monimages/S600px-' . str_pad($result["c_monid"] , 3 , "0" , STR_PAD_LEFT) . $pokemonname . '.png' : $shiny_link['link'];
            $img = '<img src="' . $link . '" />';
        } else {
            $img = '<img src="pokemon/images/monimages/600px-' . str_pad($result["c_monid"] , 3 , "0" , STR_PAD_LEFT) . $pokemonname . '.png" />';
        }
		$str .= '<h1 class="ownedcard2' . $shine . '">' . $title . '</h1>' . $momstr . '
		<h2>Owned by ' . $result["c_ownername"] . '</h2><br>
		' . $img;
		$clvl = next_lvl($result["c_level"]);
		$plvl = next_lvl($result["c_level"]-1);
		$cexp = $result["c_exp"] - $plvl;
		
		//Friendship
		if($result["c_friend"] < 100) {
		    $friend = 'does not like you.';
		} else if($result["c_friend"] < 200) {
		    $friend = 'is ok with you.';
		} else if($result["c_friend"] < 300) {
		    $friend = 'kind of likes you.';
		} else if($result["c_friend"] < 400) {
		    $friend = 'really likes you.';
		} else {
		    $friend = 'loves you!';
		}
		
		//Check if items
		$itemqry = "SELECT 
    		count(`poke_items`.`indv_item_id`) AS 'count', 
    		`poke_items`.`itemid` AS 'c_itemid', 
    		`poke_item_master`.`name` AS 'c_name'
    	FROM 
    		`poke_items`
    		INNER JOIN (`poke_item_master`)
    			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
    	WHERE  
    		`poke_items`.`userid`=$userid
    		AND `poke_items`.`use_date`=0
    	GROUP BY
        	`poke_items`.`itemid`
        ORDER BY 
    		`poke_items`.`itemid` ASC";
    	$itemresult = $db->query_read($itemqry);
    	while ($resultLoop = $db->fetch_array($itemresult)) {
    	    $item[$resultLoop['c_itemid']] = $resultLoop['count'];
    	    $item_name[$resultLoop['c_itemid']] = $resultLoop['c_name'];
    	}
    	
		//Check Evo Possibility
		if ($result["c_monid"] == 133) {
		    if($item[4] > 0) {
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=4">Evolve this Pokemon with Water Stone</a></h2></div>';
		    }
		    if($item[5] > 0) {
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=5">Evolve this Pokemon with Thunder Stone</a></h2></div>';
		    }
		    if($item[2] > 0) {
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=2">Evolve this Pokemon with Fire Stone</a></h2></div>';
		    }
		    if($result["c_friend"] >= 400) {
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=998">Evolve this Pokemon to Espeon</a></h2></div>';
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=999">Evolve this Pokemon to Umbreon</a></h2></div>';
		    }
		} else if ($result["c_monid"] == 236 && $result["c_level"] >= 20) {
		    $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=997">Evolve this Pokemon to Hitmonlee</a></h2></div>';
	        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=998">Evolve this Pokemon to Hitmonchan</a></h2></div>';
	        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=999">Evolve this Pokemon to Hitmontop</a></h2></div>';
		} else if ($result["c_monid"] == 44) {
		    if($item[1] > 0) {
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=1">Evolve this Pokemon with Leaf Stone</a></h2></div>';
		    }
		    if($item[9] > 0) {
		        // HERE ADD SUN STONE
		        $eevee .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '&method=9">Evolve this Pokemon with Sun Stone</a></h2></div>';
		    }
		} else if($result["c_evo"] == 1) {
    		$specresult = $db->query_first("SELECT
    			`evo_monid`,
    			`method`
    		FROM
    		    `poke_spec_evo`
    		WHERE
    			`monid` = " . $result["c_monid"]);
    		$method = $specresult["method"];
    		if($method == 0 || $method == '') {
    		    $can_evo = false;
    		} else {
                $can_evo = ($item[$method] > 0) ? true : false;
		    }
		} else if($result["c_evo"] == 2) {
		    $can_evo = ($result["c_friend"] >= 400) ? true : false;
    	} else if ($result["c_evo"] != 0) {
		    $can_evo = ($result["c_level"] < $result["c_evo"]) ? false : true;
		}
		$gender = ($result["c_gender"] == 'M') ? 'Male' : 'Female';
		$str .= '<div class="card_owners">
		    ' . $shiny . '
		    <font size="+2">Level: ' . $result["c_level"] . '</font><br>
		    Next level: ' . $cexp . '/' . ($clvl-$plvl) . ' exp
		    <br>
		    Holding: ' . $itemstr . '
		    <br>
		    ' . $gender . '<br>
		</div>';
		if($userid == $result["c_owner"]) {
            $str .= '<div class="card_owners">' . $nick . ' ' . $friend . '</div>';
        }
        if($userid == $result["c_owner"] && $result["c_shiny"] == 1) {
            $str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=shiny&pokemon=' . $pokemon . '">Update shiny image.</a></h2></div>';
        }
		$str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=owned&pokemon=' . $result["c_monid"] . '">View users who own the same pokemon.</a></h2></div>';
		if($userid == $result["c_owner"]) {
		    $str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=nick&pokemon=' . $pokemon . '">Rename this Pokemon</a></h2></div>';
		    if($item[6] > 0) {
		        $str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=candy&pokemon=' . $pokemon . '">Feed this Pokemon a Rare Candy</a></h2></div>';
		    }
		    if($can_evo) {
		        $str .= '<div class="tradelistactive"><h2><a href="pokemon.php?section=pokemon&do=transact&action=evo&pokemon=' . $pokemon . '">Evolve this Pokemon</a></h2></div>';
		    }
		    $str .= $eevee;
		    $str .= '<div class="tradelistinactive"><h2><a href="pokemon.php?section=pokemon&do=release&pokemon=' . $pokemon . '">Release this Pokemon</a></h2></div>';
		}
	} else {
		$str .= 'That pokemon does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'equip' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
    // ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
	    //Check if items
		$itemqry = "SELECT 
    		count(`poke_items`.`indv_item_id`) AS 'count', 
    		`poke_items`.`itemid` AS 'c_itemid', 
    		`poke_item_master`.`name` AS 'c_name'
    	FROM 
    		`poke_items`
    		INNER JOIN (`poke_item_master`)
    			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
    	WHERE  
    		`poke_items`.`userid`=$userid
    		AND `poke_items`.`use_date`=0
    	GROUP BY
        	`poke_items`.`itemid`
        ORDER BY 
    		`poke_items`.`itemid` ASC";
    	$itemresult = $db->query_read($itemqry);
    	while ($resultLoop = $db->fetch_array($itemresult)) {
    	    $item[$resultLoop['c_itemid']] = $resultLoop['count'];
    	    $item_name[$resultLoop['c_itemid']] = $resultLoop['c_name'];
    	    $items_opt .= '<option value="' . $resultLoop['c_itemid'] . '">' . $resultLoop['c_name'] . '</option>';
    	}
    	
    	$str = '<div class="tcg_body"><h1><u>EQUIP ITEM:</u></h1><br>
            <form class=buy action="/pokemon.php?section=pokemon&do=transact&action=equip" method="post">
                ID of Pokemon:<br>
                <input name="poke" type="text" maxlength="5" value="' . $pokemon . '" readonly><br><br>
            	Item:<br>
                <select name="item">
                    ' . $items_opt . '
                </select>
            	<br><input type="submit" value="Equip Item" />
        	</form>
        </div>
        ';
	} else {
		$str .= 'That pokemon is not yours!<br><br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'nick' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result = $db->query_first("SELECT
			`poke_mon`.`monname` AS 'c_name',
			`poke_mon`.`monid` AS 'c_monid',
			`poke_indv`.`userid` AS 'c_owner',
			`poke_indv`.`nick` AS 'c_nick',
			`poke_indv`.`shiny` AS 'c_shiny',
			`poke_indv`.`level` AS 'c_level',
			`user`.`username` AS 'c_ownername'
			
		FROM
		    `poke_indv`
    		INNER JOIN
    			`poke_mon`
    		ON
    		    `poke_indv`.`monid` = `poke_mon`.`monid`
    		INNER JOIN
    			`user`
    		ON
    		    `poke_indv`.`userid` = `user`.`userid`
		WHERE
			`poke_indv`.`indvid` = $pokemon");
		$pokemonname = $result["c_name"];
		$nick = ($result["c_nick"] == '') ? $pokemonname : $result["c_nick"];
		
		$str .= '<div class=tcg_body><form class=buy action="pokemon.php?section=pokemon&do=transact&action=nick" method="post">';
	
		$str .= 'Nickname:<br><input class=title name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,15);" 
		onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,15);" maxlength="15" value="' . $nick . '"><br>
		<font size="1">(Maximum characters: 15)<br>
		You have <input readonly type="text" name="countdown" size="3" value="15"> characters left.</font><br>';
		
		$str .= '<input type="hidden" name="auth" value="Ash" />
		<input type="hidden" name="pokemon" value="' . $pokemon . '" />
		<br><input type="submit" value="Change Nickname" />
		</form></div>';
	} else {
		$str .= 'That pokemon is not yours!<br><br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'shiny' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
    // ############ CLEAN VARIABLES ############
    $vbulletin->input->clean_array_gpc('g', array(
        'pokemon' => TYPE_INT
    ));
    $pokemon = clean_number($vbulletin->GPC['pokemon'],20000);

    // ############ CHECK IF pokemon EXISTS ############
    $exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid AND shiny = 1) AS 'Exists'");

    // ############ MAIN CODE ############
    if($exists['Exists'] == true) {
        $str .= '<div class=tcg_body><form class=buy action="pokemon.php?section=pokemon&do=transact&action=shiny" method="post">';

        $str .= 'URL:<br><input class=title name="limitedtextfield" type="text" onKeyDown="limitText(this.form.limitedtextfield,this.form.countdown,100);" 
		onKeyUp="limitText(this.form.limitedtextfield,this.form.countdown,100);" maxlength="100"><br>
		<font size="1">(Maximum characters: 100)<br>
		You have <input readonly type="text" name="countdown" size="3" value="100"> characters left.</font><br>';

        $str .= '<input type="hidden" name="auth" value="Ash" />
		<input type="hidden" name="pokemon" value="' . $pokemon . '" />
		<br><input type="submit" value="Change Shiny Image" />
		</form></div>';
    } else {
        $str .= 'That pokemon is not yours!<br><br>';
    }
    echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'release' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result = $db->query_first("SELECT
			`poke_mon`.`monname` AS 'c_name',
			`poke_mon`.`monid` AS 'c_monid',
			`poke_indv`.`userid` AS 'c_owner',
			`poke_indv`.`nick` AS 'c_nick'
		FROM
		    `poke_indv`
    		INNER JOIN
    			`poke_mon`
    		ON
    		    `poke_indv`.`monid` = `poke_mon`.`monid`
    		INNER JOIN
    			`user`
    		ON
    		    `poke_indv`.`userid` = `user`.`userid`
		WHERE
			`poke_indv`.`indvid` = $pokemon");
		$pokemonname = $result["c_name"];
		$nick = ($result["c_nick"] == '') ? $pokemonname : $result["c_nick"];
		
		$str .= '<div class=tcg_body><form class=buy action="pokemon.php?section=pokemon&do=transact&action=release" method="post">';
	
		$str .= 'Are you sure you wish to release ' . $nick . '?<br>
		<input type="hidden" name="auth" value="Ash" />
		<input type="hidden" name="pokemon" value="' . $pokemon . '" />
		<br><input type="submit" value="Release Pokemon" />
		</form></div>';
	} else {
		$str .= 'That pokemon is not yours!<br><br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'admin' && ($usergroup == 6 || $usergroup == 29)){
    if(isset($_GET['action']) && $_GET['action'] == 'give') {
        // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'note' => TYPE_NOHTML,
	   		'poke' => TYPE_INT,
	   		'user' => TYPE_INT
		));
		$poke = clean_number($vbulletin->GPC['poke'],999);
        $user = clean_number($vbulletin->GPC['user'],9999);
        $note = $db->escape_string($vbulletin->GPC['note']);
        $mqry = $db->query_first("SELECT * FROM `poke_mon` WHERE monid = " . $poke);
        $uqry = $db->query_first("SELECT username FROM `user` WHERE userid = " . $user);
        
        $error = false;
        if($vbulletin->GPC['auth'] != 'Kaos') {
            $error = true;
        }
        if($mqry['monname'] == '') {
            $error = true;
        }
        if($uqry['username'] == '') {
            $error = true;
        }
        
        if(!$error) {
            $shiny = (mt_rand(0,500) == 2) ? 1 : 0;
            if($shiny == 1) {
                $shiny1 = '[g=yellow]SHINY[/g] [highlight] ';
                $shiny2 = ' [/highlight]';
            }
            $gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' a ' . $shiny1 . '[url="https://forums.novociv.org/pokemon.php?section=pokemon&do=view&pokemon=' . $poke . '"]' . $mqry['monname'] . '[/url]' . $shiny2 . '. 
            	    
        	[img]https://forums.novociv.org/pokemon/images/monimages/600px-' . str_pad($poke , 3 , "0" , STR_PAD_LEFT) . $mqry['monname'] . '.png[/img]
        	
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "poke_indv 
        			(monid, userid, shiny, catch_date, gender) 
        		VALUES 
        			(" . $poke . ", " . $user . ", " . $shiny . ", " . time() . ", '" . $gender . "')");
        	// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   'Pokemon Given to you by Admin'; 
				$pmtext     = $a;
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$user'"));
				
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
            echo '<div class=tcg_body>Success! ' . $mqry['monname'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else {
            echo 'Error: ' . $mqry['monname'] . ' ' . $uqry['username'] . ' ' . $username;
        }
        
        
    } else if(isset($_GET['action']) && $_GET['action'] == 'givei') {
        // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'note' => TYPE_NOHTML,
	   		'poke' => TYPE_INT,
	   		'amount' => TYPE_INT,
	   		'user' => TYPE_INT
		));
		$poke = clean_number($vbulletin->GPC['poke'],999);
        $amount = clean_number($vbulletin->GPC['amount'],999);
        $user = clean_number($vbulletin->GPC['user'],9999);
        $note = $db->escape_string($vbulletin->GPC['note']);
        $mqry = $db->query_first("SELECT * FROM `poke_item_master` WHERE itemid = " . $poke);
        $uqry = $db->query_first("SELECT username FROM `user` WHERE userid = " . $user);
        
        $error = false;
        if($vbulletin->GPC['auth'] != 'Kaos') {
            $error = true;
        }
        if($mqry['name'] == '' && $poke != 999) {
            $error = true;
        }
        if($uqry['username'] == '') {
            $error = true;
        }
        if($amount <= 0 || $amount > 25) {
            $error = true;
        }
        
        if(!$error && $poke != 999) {
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' ' . $amount . ' ' . $mqry['name'] . '. 
            	    
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            
            for($i=0;$i<$amount;$i++) {
			    $vals[] = '(' . $poke . ',' . $user . ',' . time() . ')';
			}
			$valstr = implode(',',$vals);
			
			$db->query_write("INSERT INTO 
				`poke_items` 
				(itemid, userid, purchase_date)
			VALUES 
				" . $valstr);
			// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   'Item Given to you by Admin'; 
				$pmtext     = $a;
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$user'"));
				
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
            echo '<div class=tcg_body>Success! ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else if(!$error && $poke == 999) {
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' ' . $amount . ' pokeballs. 
            	    
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            
            $db->query_write("UPDATE 
				`user` 
			SET 
				pokeballs = pokeballs+" . $amount . " 
			WHERE userid = " . $user);
            echo '<div class=tcg_body>Success! ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else {
            echo 'Error: ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username;
        }
        
        
    } else {
        $str .= '
        <div class=tcg_body>
            <form class=buy action="pokemon.php?section=pokemon&do=admin&action=give" method="post">
                ID of Pokemon:<br>
                <input name="poke" type="text" maxlength="5"><br><br>
            	ID of User:<br>
                <input name="user" type="text" maxlength="5"><br><br>
            	Note:<br>
                <input name="note" type="text" maxlength="100"><br>
            	<input type="hidden" name="auth" value="Kaos" />
            	<br><input type="submit" value="Give Pokemon" />
        	</form>
        </div>
        ';
        $str .= '
        <div class=tcg_body>
            <form class=buy action="pokemon.php?section=pokemon&do=admin&action=givei" method="post">
                ID of Item:<br>
                <input name="poke" type="text" maxlength="5"><br><br>
                Amount of Item:<br>
            	<input name="amount" type="text" maxlength="5"><br><br>
            	ID of User:<br>
                <input name="user" type="text" maxlength="5"><br><br>
            	Note:<br>
                <input name="note" type="text" maxlength="100"><br>
            	<input type="hidden" name="auth" value="Kaos" />
            	<br><input type="submit" value="Give Item" />
        	</form>
        </div>
        ';
        echo $str;
    }
} else if(isset($_GET['do']) && $_GET['do'] == 'owned' && isset($_GET['pokemon']) && $_GET['pokemon'] <= 20000){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'pokemon' => TYPE_INT
	));
	$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
	
	// ############ CHECK IF pokemon EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_mon WHERE monid = $pokemon) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$result = $db->query_read("SELECT
			`poke_indv`.`userid` AS 'c_user',
			`user`.`username` AS 'c_username',
			count(*) as 'c_num'
		FROM
			`poke_indv`
			INNER JOIN (`user`)
			ON (`poke_indv`.`userid` = `user`.`userid`)
		WHERE
			`poke_indv`.`monid` = $pokemon
		GROUP BY
			`poke_indv`.`userid`");
		$str .= '<div class="card_owners">
			<table class="tablesorter">
				<thead> 
					<tr>
						<th>Username</th>
						<th>Count</th>
					</tr>
				</thead>
				<tbody>';
		while ($resultLoop = $db->fetch_array($result)) {
			$owner = '<a href="member.php?' . $resultLoop["c_user"] . '">' . $resultLoop["c_username"] . '</a>';
			$str .= '
					<tr>
					    <td>' . $owner . '</td>
					    <td><a href="pokemon.php?section=home&do=list&user=' . $resultLoop["c_user"] . '">' . $resultLoop["c_num"] . '</a></td>
					</tr>';
		}
		$str .= '
				</tbody> 
			</table>
		</div>';
	} else {
		$str .= 'That pokemon does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'transact') {
    if(isset($_GET['action']) && $_GET['action'] == 'nick') {
        // ############ POST VARIABLES ############
        //'p' means it's POST data
        $vbulletin->input->clean_array_gpc('p', array(
            'auth' => TYPE_NOHTML,
            'limitedtextfield' => TYPE_NOHTML,
            'pokemon' => TYPE_INT
        ));

        $pokemon = clean_number($vbulletin->GPC['pokemon'], 20000);
        $title = $vbulletin->db->escape_string($vbulletin->GPC['limitedtextfield']);
        $error = false;

        // ############ CHECK IF pokemon EXISTS ############
        $exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
        if ($exists['Exists'] == false) {
            $error = true;
        }
        if (strlen($title) > 15) {
            $error = true;
        }
        if ($vbulletin->GPC['auth'] != 'Ash') {
            $error = true;
        }

        if (!$error) {
            $db->query_write("UPDATE 
				`poke_indv` 
			SET 
				nick = '" . $title . "' 
			WHERE 
				indvid = " . $pokemon);
            echo 'Changing Nickname...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
        } else {
            echo 'Something went wrong.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
        }
    } else if(isset($_GET['action']) && $_GET['action'] == 'shiny') {
        // ############ POST VARIABLES ############
        //'p' means it's POST data
        $vbulletin->input->clean_array_gpc('p', array(
            'auth' => TYPE_NOHTML,
            'limitedtextfield' => TYPE_NOHTML,
            'pokemon' => TYPE_INT
        ));

        $error = false;

        $pokemon = clean_number($vbulletin->GPC['pokemon'],20000);

        $fileurl = $vbulletin->db->escape_string($vbulletin->GPC['limitedtextfield']);
        $size = getimagesize($fileurl);
        list($width, $height) = getimagesize($fileurl);
        if($size == 0) { $error = true; $estr .= 'not an image<br>'; }
        if($width > 600) { $error = true; $estr .= 'width too big<br>'; }
        if($height > 600) { $error = true; $estr .= 'height too big<br>'; }
//        if($width/$height != 1) { $error = true; $estr .= 'error code 148<br>'; }

        // ############ CHECK IF pokemon EXISTS ############
        $exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid AND shiny = 1) AS 'Exists'");
        if($exists['Exists'] == false) {
            $error = true;
            $estr .= 'Bad pokemon<br>';
        }
        if(strlen($fileurl)>100) { $error = true; $estr .= 'url too long<br>'; }
        if($vbulletin->GPC['auth'] != 'Ash') { $error = true; $estr .= 'bad auth<br>'; }

        if(!$error) {
            $db->query_write("INSERT INTO 
				`poke_shiny_link` 
			SET 
				link = '" . $fileurl . "', 
				indvid = " . $pokemon);
            echo 'Changing Shiny Image...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
        } else {
            echo 'Something went wrong.<br>' . $estr;
        }
    } else if(isset($_GET['action']) && $_GET['action'] == 'release') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
			'pokemon' => TYPE_INT
		));
		
		$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
		$error = false;
	
    	// ############ CHECK IF pokemon EXISTS ############
    	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	    if($exists['Exists'] == false) {
	        $error = true;
	    }
		if($vbulletin->GPC['auth'] != 'Ash') { $error = true; }
		
		if(!$error) {
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				userid = 15 
			WHERE 
				indvid = " . $pokemon);
			echo 'Releasing Pokemon...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else {
			echo 'Something went wrong.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'candy') {
	    // ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'pokemon' => TYPE_INT
		));
		
		$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
		$error = false;
	
    	// ############ CHECK IF pokemon EXISTS ############
    	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	    if($exists['Exists'] == false) {
	        $error = true;
	    }
	    
	    // ############ CHECK IF candy EXISTS ############
    	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_items WHERE itemid = 6 AND userid = $userid AND use_date = 0) AS 'Exists'");
	    if($exists['Exists'] == false) {
	        $error = true;
	    }
		
		if(!$error) {
			$result = $db->query_first("SELECT
    			`poke_indv`.`level` AS 'c_level'
    		FROM
    		    `poke_indv`
    		WHERE
    			`poke_indv`.`indvid` = $pokemon");
    		
            $lvl = $result['c_level']; 
            $next_lvl = round((($lvl+1)*($lvl+1))/2,0); 
			
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				exp = " . $next_lvl . ",
				level = level+1
			WHERE 
				indvid = " . $pokemon);
			
			$db->query_write("UPDATE 
				`poke_items` 
			SET 
				use_date = " . time() . "
			WHERE 
				itemid = 6
				AND use_date = 0
				AND userid = " . $userid . "
			LIMIT 1");
			
			echo 'Feeding...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
		} else {
			echo 'Something went wrong.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'remove') {
	    // ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'pokemon' => TYPE_INT
		));
		
		$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
		$error = false;
	
    	// ############ CHECK IF pokemon EXISTS ############
    	$exists1 = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid AND indv_item_id <> 0) AS 'Exists'");
	    if($exists1['Exists'] == false) {
	        $error = true;
	    }
	    
		if(!$error) {
		    $result = $db->query_first("SELECT * FROM poke_indv WHERE indvid = " . $pokemon);
		    $db->query_write("UPDATE 
				`poke_indv` 
			SET 
				indv_item_id = 0
			WHERE 
				indvid = " . $pokemon);
			
			$db->query_write("INSERT INTO 
				`poke_items` 
				(itemid, userid, purchase_date)
			VALUES 
				(" . $result['indv_item_id'] . "," . $userid . "," . time() . ")");
			
			echo 'Removing...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'equip') {
	    // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'poke' => TYPE_INT,
			'item' => TYPE_INT
		));
		
		$pokemon = clean_number($vbulletin->GPC['poke'],20000);
		$item = clean_number($vbulletin->GPC['item'],2000);
		$error = false;
	
    	// ############ CHECK IF pokemon EXISTS ############
    	$exists1 = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	    if($exists1['Exists'] == false) {
	        $error = true;
	    }
	    
	    // ############ CHECK IF item EXISTS ############
    	$exists2 = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_items WHERE itemid = $item AND userid = $userid AND use_date = 0) AS 'Exists'");
	    if($exists2['Exists'] == false) {
	        $error = true;
	    }
		
		if(!$error) {
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				indv_item_id = " . $item . "
			WHERE 
				indvid = " . $pokemon);
			
			$db->query_write("UPDATE 
				`poke_items` 
			SET 
				use_date = " . time() . "
			WHERE 
				itemid = " . $item . "
				AND use_date = 0
				AND userid = " . $userid . "
			LIMIT 1");
			
			echo 'Equip...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
		} else {
		    echo $item;
		    /*
			echo 'Something went wrong.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
			*/
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'evo') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'pokemon' => TYPE_INT,
			'method' => TYPE_INT
		));
		
		$pokemon = clean_number($vbulletin->GPC['pokemon'],20000);
		$method = clean_number($vbulletin->GPC['method'],1000);
		$error = false;
	
    	// ############ CHECK IF pokemon EXISTS ############
    	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
	    if($exists['Exists'] == false) {
	        $error = true;
	    }
		$result = $db->query_first("SELECT
			`poke_mon`.`monid` AS 'c_monid',
			`poke_mon`.`evolution` AS 'c_evo',
			`poke_indv`.`userid` AS 'c_owner',
			`poke_indv`.`friend` AS 'c_friend',
			`poke_indv`.`level` AS 'c_level'
		FROM
		    `poke_indv`
    		INNER JOIN
    			`poke_mon`
    		ON
    		    `poke_indv`.`monid` = `poke_mon`.`monid`
		WHERE
			`poke_indv`.`indvid` = $pokemon");
		
		//Check Evo Possibility
		if($result["c_monid"] == 133) {
		    //Check if items
    		$itemqry = "SELECT 
        		count(`poke_items`.`indv_item_id`) AS 'count', 
        		`poke_items`.`itemid` AS 'c_itemid', 
        		`poke_item_master`.`name` AS 'c_name'
        	FROM 
        		`poke_items`
        		INNER JOIN (`poke_item_master`)
        			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
        	WHERE  
        		`poke_items`.`userid`=$userid
        		AND `poke_items`.`use_date`=0
        	GROUP BY
            	`poke_items`.`itemid`
            ORDER BY 
        		`poke_items`.`itemid` ASC";
        	$itemresult = $db->query_read($itemqry);
        	while ($resultLoop = $db->fetch_array($itemresult)) {
        	    $item[$resultLoop['c_itemid']] = $resultLoop['count'];
        	}
		    if($method == 5 && $item[$method] > 0) {
		        $new_mon = 135;
                $itembool = true;
		    } else if($method == 4 && $item[$method] > 0) {
		        $new_mon = 134;
                $itembool = true;
		    } else if($method == 2 && $item[$method] > 0) {
		        $new_mon = 136;
                $itembool = true;
		    } else if($method == 998 && $result["c_friend"] >= 400) {
		        $new_mon = 196;
                $itembool = false;
		    } else if($method == 999 && $result["c_friend"] >= 400) {
		        $new_mon = 197;
                $itembool = false;
		    } else {
		        $error = true;
		    }
		} else if($result["c_monid"] == 44) {
		    //Check if items
    		$itemqry = "SELECT 
        		count(`poke_items`.`indv_item_id`) AS 'count', 
        		`poke_items`.`itemid` AS 'c_itemid', 
        		`poke_item_master`.`name` AS 'c_name'
        	FROM 
        		`poke_items`
        		INNER JOIN (`poke_item_master`)
        			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
        	WHERE  
        		`poke_items`.`userid`=$userid
        		AND `poke_items`.`use_date`=0
        	GROUP BY
            	`poke_items`.`itemid`
            ORDER BY 
        		`poke_items`.`itemid` ASC";
        	$itemresult = $db->query_read($itemqry);
        	while ($resultLoop = $db->fetch_array($itemresult)) {
        	    $item[$resultLoop['c_itemid']] = $resultLoop['count'];
        	}
		    if($method == 1 && $item[$method] > 0) {
		        $new_mon = 45;
                $itembool = true;
		    } else if($method == 9 && $item[$method] > 0) {
		        // HERE MUST PUT SUN STONE
		        $new_mon = 182;
                $itembool = true;
		    } else {
		        $error = true;
		    }
		} else if($result["c_monid"] == 236) {
		    if($method == 997) {
		        $new_mon = 106;
		    } else if($method == 998) {
		        $new_mon = 107;
		    } else if($method == 999) {
		        $new_mon = 237;
		    } else {
		        $error = true;
		    }
		    $error = ($result["c_level"] < $result["c_evo"]) ? true : false;
		    $itembool = false;
		} else if($result["c_monid"] == 238) {
		    $new_mon = 124;
		    $error = ($result["c_level"] < $result["c_evo"]) ? true : false;
		    $itembool = false;
		} else if($result["c_monid"] == 239) {
		    $new_mon = 125;
		    $error = ($result["c_level"] < $result["c_evo"]) ? true : false;
		    $itembool = false;
		} else if($result["c_monid"] == 240) {
		    $new_mon = 126;
		    $error = ($result["c_level"] < $result["c_evo"]) ? true : false;
		    $itembool = false;
		} else if($result["c_evo"] == 1) {
		    //Check if items
    		$itemqry = "SELECT 
        		count(`poke_items`.`indv_item_id`) AS 'count', 
        		`poke_items`.`itemid` AS 'c_itemid', 
        		`poke_item_master`.`name` AS 'c_name'
        	FROM 
        		`poke_items`
        		INNER JOIN (`poke_item_master`)
        			ON (`poke_items`.`itemid` = `poke_item_master`.`itemid`)
        	WHERE  
        		`poke_items`.`userid`=$userid
        		AND `poke_items`.`use_date`=0
        	GROUP BY
            	`poke_items`.`itemid`
            ORDER BY 
        		`poke_items`.`itemid` ASC";
        	$itemresult = $db->query_read($itemqry);
        	while ($resultLoop = $db->fetch_array($itemresult)) {
        	    $item[$resultLoop['c_itemid']] = $resultLoop['count'];
        	}
		    
		    $specresult = $db->query_first("SELECT
    			`evo_monid`,
    			`method`
    		FROM
    		    `poke_spec_evo`
    		WHERE
    			`monid` = " . $result["c_monid"]);
    		$method = $specresult["method"];
    		if($method == 0 || $method == '') {
    		    $error = true;
    		} else {
                $error = ($item[$method] > 0) ? false : true;
                $new_mon = $specresult["evo_monid"];
                $itembool = true;
		    }
		} else if($result["c_evo"] == 2) {
		    $error = ($result["c_friend"] < 400) ? true : false;
		    $specresult = $db->query_first("SELECT
    			`evo_monid`
    		FROM
    		    `poke_evo`
    		WHERE
    			`monid` = " . $result["c_monid"]);
    		$new_mon = $specresult["evo_monid"];
    		$itembool = false;
		} else if ($result["c_evo"] != 0) {
		    $error = ($result["c_level"] < $result["c_evo"]) ? true : false;
		    $new_mon = $result["c_monid"] + 1;
		    $itembool = false;
		} else {
		    $error = true;
		}
		if(!$error) {
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				monid = " . $new_mon . " 
			WHERE 
				indvid = " . $pokemon);
			
			//CHECK IF YOU WILL DELETE DECK
			// ############ QUERY VARIABLES ############
			$qry = "SELECT 
				`poke_deck`.`decklist`,
				`poke_deck`.`deckid`
			FROM 
				`poke_deck`
			WHERE  
				`poke_deck`.`userid` = " . $userid . " 
			ORDER BY 
				`poke_deck`.`deckid` ASC";
				
			$result8 = $db->query_read($qry);
			while ($resultLoop = $db->fetch_array($result8)) {
				$yourdeck = $resultLoop["deckid"];
				$yourdecks = explode(',',$resultLoop["decklist"]);
				if(array_intersect($yourdecks,array($pokemon))) {
					$mids = array();
					$cardlist = $resultLoop["decklist"];
        			$qrylist = implode("','",$yourdecks);
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
        			$db->query_write("UPDATE 
        				`poke_deck`
        			SET
        				`mon_ids` = '" . $monlist . "'
        			WHERE
        				`deckid` = " . $yourdeck);
				}
			}
			
            if($itembool) {
    			$db->query_write("UPDATE 
    				`poke_items` 
    			SET 
    				use_date = " . time() . "
    			WHERE 
    				itemid = " . $method . "
    				AND use_date = 0
    				AND userid = " . $userid . "
    			LIMIT 1");
            }
			echo 'Evolving...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=pokemon&do=view2&pokemon=' . $pokemon . '"
			//-->
			</script>';
		} else {
			echo 'Something went wrong.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	}
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