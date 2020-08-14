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
// Starting a new file based rolling system, to move spawn tables outside of the function.
function poke_roll($forumid) {
    $file = "pokemon/spawns/" . $forumid . ".txt";
    if(file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
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
        0 => 2,
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
        251 => 5,
        377 => 5,
        378 => 5,
        379 => 5,
        380 => 5,
        381 => 5,
        382 => 5,
        383 => 5,
        384 => 5,
        385 => 5,
        386 => 5
        );
	$chances = array(
		20 => 200,
		583 => 10,
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
    $shiny_rate = shiny_rate($userid);
//    $shiny_test = shiny_rate($userid);
//    $shiny_rate = 500;
    $shiny = (mt_rand(0,$shiny_rate) == 2) ? true : false;
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
function shiny_rate($userid) {
    global $vbulletin;
    $exists = $vbulletin->db->query_first("SELECT 
    EXISTS(SELECT 1 FROM poke_indv 
            WHERE shiny = 1 AND userid = 1 AND catch_date > " . (time() - 60*60*24*3) . ") AS 'Exists'");
    if($exists['Exists'] == false) {
        $result = $vbulletin->db->query_first("SELECT 
            SUM(LENGTH(`pagetext`))/COUNT(0) AS 'avg_len', 
            COUNT(DISTINCT `threadid`) AS 'num_threads' 
        FROM 
            `post` 
        WHERE 
            `userid` = " . $userid . " AND `dateline` > " . (time() - 60*60*24*3));
        $avg_len = $result["avg_len"];
        $num_threads = $result["num_threads"];

        if($num_threads >= 5){
            $len_bonus = MAX(0,200-(MAX(0,$avg_len-100)));
            $thread_bonus = MAX(0,200-13*(MAX(0,$num_threads-5)));
            $rate = 100 + $len_bonus + $thread_bonus;
            return $rate;
        } else {
            return 500;
        }
    } else {
        return 500;
    }
}
?>