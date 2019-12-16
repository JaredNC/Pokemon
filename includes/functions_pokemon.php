<?php
function next_lvl($lvl){
    $lvl++;
    $lvl = $lvl*$lvl;
    $lvl = round($lvl/2,0);
    return $lvl;
}
function weighted_prob($arr) {
    $n = 1;
    $arr2 = array();
    foreach($arr as $key => $value) {
        $n1 = $n + $value;
        $arrt = array_fill($n,$value,$key);
        $arr2 = $arr2 + $arrt;
        $n = $n1;
    }
    return $arr2;
}
function grab_poke_one($card) {
	//This function takes an array of poke ids and returns all the necessary info regarding them.
	//Now let's build our query.
	global $db;
	$result = $db->query_first("SELECT 
		`poke_mon`.`monname` AS 'c_name', 
		`poke_mon`.`type` AS 'c_type',
		`poke_mon`.`monid` AS 'c_masterid'
	FROM 
		`poke_mon`
	WHERE  
		`poke_mon`.`monid` = $card 
	ORDER BY 
		`poke_mon`.`monid` ASC");
	//Now let's store the info in a nested array.
	$output["name"] = $result["c_name"];
	$output["monid"] = $result["c_masterid"];
	$output["type"] = $result["c_type"];
	
	return $output; 
}
function poke_roll($forumid) {
	$f[502] = array(
        1 => 6,
        4 => 6,
        7 => 6,
        10 => 20,
        13 => 10,
        16 => 30,
        19 => 30,
        25 => 2,
        52 => 6,
        43 => 10,
        66 => 2,
        39 => 2,
        21 => 10
        );
    $f[523] = array(
        27 => 6,
        23 => 6,
        29 => 20,
        32 => 20,
        19 => 16,
        21 => 20,
        16 => 20,
        25 => 2
        );
    $f[20] = array(
        1 => 3,
        4 => 3,
        7 => 3,
        10 => 10,
        13 => 5,
        16 => 15,
        19 => 15,
        25 => 1
        );
    $f[4] = array(

        );
    $f[542] = array(
        108 => 6,
        114 => 6,
        69 => 30,
        124 => 2,
        88 => 20
        );
    $f[538] = array(
        4 => 6,
        37 => 20,
        58 => 20,
        77 => 34,
        126 => 2
        );
    $f[580] = array(
        27 => 4,
        35 => 2,
        41 => 20,
        46 => 4,
        74 => 10,
        75 => 2,
        95 => 2
        );
    $f[581] = array(
        54 => 8,
        60 => 20,
        72 => 40,
        86 => 4,
        98 => 10,
        120 => 2,
        129 => 40
        );
    $f[582] = array(
        29 => 40,
        32 => 40,
        48 => 40,
        79 => 2,
        102 => 40,
        111 => 20,
        113 => 10,
        115 => 10,
        128 => 20,
        147 => 2
        );
    $f[527] = array(
        105 => 5,
        138 => 3,
        140 => 3,
        142 => 1
        );
    $f[584] = array(
        63 => 10,
        104 => 10,
        106 => 10,
        107 => 10,
        122 => 10,
        123 => 1,
        127 => 1,
        132 => 10,
        138 => 10,
        140 => 10,
        142 => 1
        );
    $f[592] = array(
        16 => 10,
        19 => 10,
        161 => 8,
        163 => 10,
        187 => 1,
        21 => 10,
        102 => 10,
        165 => 3,
        167 => 3,
        204 => 1,
        214 => 1,
        41 => 1,
        60 => 1,
        13 => 5,
        14 => 3,
        10 => 5,
        11 => 3,
        69 => 3,
        92 => 1,
        190 => 1
        );
    $f[593] = array(
        16 => 10,
        19 => 10,
        163 => 1,
        187 => 5,
        21 => 10,
        102 => 10,
        204 => 1,
        214 => 1,
        41 => 5,
        69 => 3,
        92 => 1,
        23 => 3,
        179 => 5,
        194 => 2,
        190 => 4,
        74 => 2,
        27 => 1,
        95 => 1,
        20 => 1,
        42 => 1,
        195 => 2,
        211 => 4
        );
    $f[594] = array(
        19 => 5,
        21 => 5,
        39 => 2,
        74 => 10,
        231 => 2,
        41 => 5,
        216 => 3,
        206 => 1,
        202 => 1
        );
    $f[595] = array(
        16 => 5,
        19 => 5,
        39 => 2,
        63 => 3,
        96 => 13,
        132 => 1,
        163 => 5,
        165 => 3,
        167 => 3,
        204 => 2,
        209 => 10
        );
    $f[585] = array(
        234 => 10,
        226 => 3,
        200 => 1,
        198 => 10,
        191 => 10,
        177 => 10,
        206 => 1
        );
    $f[586] = $f[585];
    $f[587] = $f[585];
    $f[588] = $f[585];
    $f[589] = $f[585];
    $f[590] = $f[585];
    $f[596] = array(
        12 => 3,
        15 => 3,
        16 => 1,
        29 => 1,
        32 => 1,
        37 => 3,
        39 => 1,
        46 => 5,
        48 => 5,
        54 => 1,
        58 => 3,
        63 => 3,
        69 => 1,
        92 => 1,
        96 => 1,
        123 => 2,
        127 => 2,
        132 => 1,
        163 => 5,
        165 => 10,
        167 => 10,
        185 => 3,
        191 => 10,
        193 => 2,
        204 => 2,
        209 => 10,
        234 => 5
        );
    $f[597] = array(
        17 => 3,
        19 => 1,
        20 => 1,
        52 => 5,
        81 => 5,
        83 => 3,
        128 => 2,
        164 => 2,
        209 => 1,
        241 => 3,
        12 => 1,
        15 => 1,
        165 => 3,
        167 => 3,
        204 => 2,
        72 => 10,
        73 => 2,
        226 => 2,
        170 => 5,
        222 => 5
        );
    $f[598] = array(
        19 => 1,
        20 => 1,
        21 => 1,
        22 => 1,
        23 => 1,
        24 => 1,
        41 => 1,
        42 => 1,
        56 => 5,
        179 => 3,
        180 => 1,
        183 => 3,
        118 => 7,
        119 => 1,
        190 => 3,
        214 => 1,
        241 => 3,
        66 => 5,
        67 => 1,
        74 => 3,
        75 => 1
        );
    $f[600] = array(
        17 => 1,
        20 => 1,
        48 => 1,
        49 => 1,
        23 => 1,
        83 => 3,
        161 => 2,
        162 => 1,
        164 => 3,
        179 => 3,
        180 => 1,
        203 => 8,
        129 => 5,
        204 => 1,
        190 => 3,
        214 => 1,
        60 => 2,
        61 => 1,
        69 => 1,
        70 => 1,
        108 => 1,
        114 => 1,
        223 => 3,
        41 => 1,
        42 => 1,
        124 => 3,
        220 => 3,
        225 => 3,
        215 => 3,
        207 => 5,
        227 => 2
        );
    $f[601] = array(
        201 => 10,
        177 => 2,
        194 => 2,
        195 => 1,
        235 => 1,
        218 => 1,
        228 => 3
        );
    $f[602] = array(
        24 => 1,
        42 => 1,
        61 => 1,
        84 => 3,
        85 => 1,
        77 => 2,
        78 => 1,
        114 => 1,
        215 => 4,
        217 => 4,
        232 => 4,
        60 => 2,
        61 => 1,
        55 => 1,
        75 => 1,
        95 => 1,
        126 => 2,
        246 => 5,
        67 => 1,
        195 => 4,
        200 => 4,
        247 => 2
        );
    /*
	$chances = array(
		517 => 100,
		503 => 50,
		538 => 100,
		8 => 10,
		4 => 20,
		21 => 5,
		449 => 50);
	*/
	$chances = array(
		502 => 15,
		523 => 20,
		20 => 500,
		542 => 12,
		538 => 12,
		580 => 5,
		581 => 12,
		582 => 2,
		527 => 30,
		584 => 15,
		592 => 10,
		593 => 10,
		594 => 10,
		595 => 10,
		596 => 10,
		585 => 10,
		586 => 20,
		587 => 20,
		588 => 10,
		589 => 20,
		590 => 20,
		597 => 10,
		598 => 10,
		600 => 10,
		601 => 10,
		602 => 10
		);
	$pass = (mt_rand(1,$chances[$forumid]) == 2) ? true : false;
	if($pass) {
	    $probs = weighted_prob($f[$forumid]);
        $roll = $probs[mt_rand(1,count($probs))];
	} else {
	    $roll = 0;
	}
	
	return $roll;
}

// Starting a new file based rolling system, to move spawn tables outside of the function.
function poke_roll2($forumid) {
    $lines = file("pokemon/spawns/" . $forumid . ".txt", FILE_IGNORE_NEW_LINES);
        foreach($lines as $value) {
            $a = explode(',',$value);
            $spawns[$a[0]] = $a[1];
        }

    $pass = (mt_rand(1,$spawns[0]) == 2) ? true : false;
	if($pass) {
	    unset($spawns[0]);
	    $probs = weighted_prob($spawns);
        $roll = $probs[mt_rand(1,count($probs))];
	} else {
	    $roll = 0;
	}
	
	return $roll;
}
function make_spawn_array() {
    $path    = 'pokemon/spawns/';
    $files = scandir($path);
    $files = array_diff(scandir($path), array('.', '..'));
    $forums_list = array();
    foreach($files as $forum_spawn){
        $forumid = explode('.',$forum_spawn);
        $forums_list[] = $forumid[0];
    }
    return $forums_list;
}
function poke_item_roll($forumid) {
    $f[20] = array(
        1 => 15,
        2 => 15,
        3 => 15,
        4 => 15,
        5 => 15,
        6 => 15,
        7 => 1,
        8 => 10,
        999 => 50
        );
    $f[583] = array(
        1 => 10,
        2 => 10,
        3 => 10,
        4 => 10,
        5 => 10,
        6 => 30,
        7 => 1,
        8 => 10,
        9 => 10,
        999 => 60
        );
    $f[584] = array(
        6 => 10,
        8 => 10,
        999 => 39
        );
        
    //$banned = array(0,144,145,146,150,151,243,244,245,249,250,251);
    $f[1] = array(
        0 => 1,
        144 => 10,
        145 => 10,
        146 => 10,
        150 => 5,
        151 => 5,
        243 => 10,
        244 => 10,
        245 => 10,
        249 => 5,
        250 => 5,
        251 => 5
        );
	$chances = array(
		20 => 200,
		583 => 20,
		584 => 20,
		1 => 1
		);
	$pass = (mt_rand(1,$chances[$forumid]) == 1) ? true : false;
	if($pass) {
	    $probs = weighted_prob($f[$forumid]);
        $roll = $probs[mt_rand(1,count($probs))];
	} else {
	    $roll = 0;
	}
	
	return $roll;
}
function poke_discover($userid, $username, $balls, $forumid, $threadid, $mid, $egg=0, $mom=0) {
    if($mid == 0) {
        $name = 'M&#808;e&#871;&#773;w&#836;&#771;&#778;&#876;&#836;&#835;';
    } else {
        $poke = grab_poke_one($mid);
        $name = $poke["name"];
    }
    $shiny = (mt_rand(0,500) == 2) ? true : false;
    $shiny = ($balls == 0 && $egg == 0 && mt_rand(0,50) == 2) ? true : $shiny;
    $sh1 = 0;
    if($shiny) {
        $shiny1 = '[g=yellow]SHINY[/g] [highlight] ';
        $shiny2 = ' [/highlight]';
        $sh1 = 1;
    }
    
	if($forumid == 503) {
		$aaaa=1;
	} else if($balls == 0 && $egg == 0) {
	    $a = $username . ' just encountered a ' . $shiny1 . '[url="https://forums.novociv.org/pokemon.php?section=pokemon&do=view&pokemon=' . $mid . '"]' . $name . '[/url]' . $shiny2 . ', but has no poke balls left to catch it!
	    [url="https://forums.novociv.org/buy.php?do=buypokeballs"]Buy more for next time![/url]
	    
		[img]https://forums.novociv.org/pokemon/images/monimages/600px-' . str_pad($mid , 3 , "0" , STR_PAD_LEFT) . $poke["name"] . '.png[/img]';
		global $vbulletin;
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
			(threadid, username, userid, dateline, pagetext, visible) 
		VALUES 
			(" . $threadid . ", 'Sexbot', 15, " . time() . ", '" . $a . "', 1)");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = " . $threadid);
	} else {
		$gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
        $a = ($egg==1) ? $username . ' just hatched a ' : $username . ' just encountered a ';
        $mom1 = ($mom == 0) ? '' : ', mom_id';
        $mom2 = ($mom == 0) ? '' : ', ' . $mom;
        
        $a .= $shiny1 . '[url="https://forums.novociv.org/pokemon.php?section=pokemon&do=view&pokemon=' . $mid . '"]' . $name . '[/url]' . $shiny2 . ', and caught it! They now have ' . ($balls-1) . ' poke balls left!
		    [url="https://forums.novociv.org/buy.php?do=buypokeballs"]Buy More?[/url]
		    
		[img]https://forums.novociv.org/pokemon/images/monimages/600px-' . str_pad($mid , 3 , "0" , STR_PAD_LEFT) . $poke["name"] . '.png[/img]';
		global $vbulletin;
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
			(threadid, username, userid, dateline, pagetext, visible) 
		VALUES 
			(" . $threadid . ", 'Sexbot', 15, " . time() . ", '" . $a . "', 1)");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = " . $threadid);
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET pokeballs = pokeballs-1 WHERE userid = " . $userid);
		$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "poke_indv 
			(monid, userid, shiny, catch_date, gender" . $mom1 . ") 
		VALUES 
			(" . $mid . ", " . $userid . ", " . $sh1 . ", " . time() . ", '" . $gender . "'" . $mom2 . ")");
	}

}
function poke_item_discover($userid, $username, $threadid, $itemid) {
    global $vbulletin;
	
	if($itemid == 999) {
	    $result = $vbulletin->db->query_first("SELECT 
			FLOOR( ucash + market_bank1 + gameroom_cash /15 ) AS networth 
		FROM 
			user 
		WHERE
		    userid = " . $userid);
        $penalty = floor($result['networth']/10000);
	    $number = mt_rand(1,max(2,5-$penalty));
        $item_find = ($number == 1) ? 'pokeball' : 'pokeballs';
        $a = $username . ' just found ' . $number . ' ' . $item_find . '!';
    	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
    		(threadid, username, userid, dateline, pagetext, visible) 
    	VALUES 
    		(" . $threadid . ", 'Sexbot', 15, " . time() . ", '" . $a . "', 1)");
    	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
    	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = " . $threadid);
	    $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET pokeballs = pokeballs+" . $number . " WHERE userid = " . $userid);
	} else {
	    $mqry = $vbulletin->db->query_read("SELECT * FROM `poke_item_master` WHERE 1");
        while ($resultLoop = $vbulletin->db->fetch_array($mqry)) {
            $item[$resultLoop['itemid']]['name'] = $resultLoop['name'];
            $item[$resultLoop['itemid']]['cost'] = $resultLoop['cost'];
        }
        $number = mt_rand(1,3);
        $number = ($itemid == 7) ? 1 : $number;
        $item_find = ($number == 1) ? $item[$itemid]['name'] : $item[$itemid]['name'] . 's';
        $a = $username . ' just found ' . $number . ' ' . $item_find . '!';
    	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
    		(threadid, username, userid, dateline, pagetext, visible) 
    	VALUES 
    		(" . $threadid . ", 'Sexbot', 15, " . time() . ", '" . $a . "', 1)");
    	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
    	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = " . $threadid);
    	for($i=0;$i<$number;$i++) {
		    $vals[] = '(' . $itemid. ',' . $userid . ',' . time() . ')';
		}
		$valstr = implode(',',$vals);
		
		$vbulletin->db->query_write("INSERT INTO 
			`poke_items` 
			(itemid, userid, purchase_date)
		VALUES 
			" . $valstr);
	}
	return $number;
}
function poke_gacha($machine,$userid,$username) {
	global $vbulletin;
	$probs = weighted_prob($machine);
    $roll = $probs[mt_rand(1,count($probs))];
    $poke = grab_poke_one($roll);
    $shiny = (mt_rand(0,500) == 2) ? 1 : 0;
    if($shiny == 1) {
        $shiny1 = '[g=yellow]SHINY[/g] [highlight] ';
        $shiny2 = ' [/highlight]';
    }
    $gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
    $a = $username . ' just rolled a ' . $shiny1 . '[url="https://forums.novociv.org/pokemon.php?section=pokemon&do=view&pokemon=' . $roll . '"]' . $poke["name"] . '[/url]' . $shiny2 . ' on the gacha machine! 
    [url="https://forums.novociv.org/pokemon.php?section=gacha"]Would you like to roll?[/url]
		    
	[img]https://forums.novociv.org/pokemon/images/monimages/600px-' . str_pad($roll , 3 , "0" , STR_PAD_LEFT) . $poke["name"] . '.png[/img]';
	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
		(threadid, username, userid, dateline, pagetext, visible) 
	VALUES 
		(1053565, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053565");
    $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "poke_indv 
			(monid, userid, shiny, catch_date, gender) 
		VALUES 
			(" . $roll . ", " . $userid . ", " . $shiny . ", " . time() . ", '" . $gender . "')");

    return $roll . ',' . $shiny;
}
?>