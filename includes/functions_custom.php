<?php
function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}
function purebell($min,$max,$std_deviation,$step=1) {
    $rand1 = (float)mt_rand()/(float)mt_getrandmax();
    $rand2 = (float)mt_rand()/(float)mt_getrandmax();
    $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
    $mean = ($max + $min) / 2;
    $random_number = ($gaussian_number * $std_deviation) + $mean;
    $random_number = round($random_number / $step) * $step;
    if($random_number < $min || $random_number > $max) {
        $random_number = purebell($min, $max,$std_deviation);
    }
    return $random_number;
}
function sendMessage($chatID, $messaggio, $token) {
    //echo "sending message to " . $chatID . "\n";

    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;
    $url = $url . "&text=" . urlencode($messaggio);
    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function queueAPI($threadid, $teamid, $type) {
    $threadid = clean_number($threadid,9999999);
    $teamid = clean_number($teamid,9999);
    $type = clean_number($type,99);
    global $db;
    $db->query_first("INSERT INTO `poke_battle` 
            (`battleid`, `threadid`, `poke_team`, `dateline`, `completed`, `type`) 
        VALUES 
            (NULL, '" . $threadid . "', '" . $teamid . "', '" . time() . "', '0', '" . $type . "');");
}
function sendAPI($threadid, $teamid) {
    $url = "http://python-bot-2048942849.us-east-1.elb.amazonaws.com/api2?id1=" . $teamid . "&thread=" . $threadid;
//    $url = "79.184.128.150:9999/api2?id1=" . $teamid . "&thread=" . $threadid;

    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function clean_number($number, $max) {

  // This function cleans an integer for use in queries.

  $cleaned = floor(min(max((int)$number/1,0),$max));

  return $cleaned;

}
function decode_offer($offer) {
	//This function decodes an offer string.
	$items = explode('^',$offer);
	foreach($items as $value) {
		$temp = explode('#',$value);
		$output[$temp[0]][] = $temp[1];
	}
	return $output;
}
function encode_offer($ucash,$cards) {
	//This function encodes an offer array.
	$output = 'ucash#' . $ucash;
	foreach($cards as $value) {
		$output .= '^card#' . $value;
	}
	return $output;
}
function calc_networth($user) {
    $user_id = clean_number($user, 10000);
    global $db;

    $userqry = "SELECT 
			`user`.`username`, 
            IFNULL((SELECT sum(`balance` + `int_payable`) FROM `poke_loan` WHERE `poke_loan`.`userid` = `user`.`userid`),'0') AS `debt`,
			IFNULL((SELECT sum(`balance` + `int_payable`) FROM `poke_investment` WHERE `poke_investment`.`userid` = `user`.`userid`),'0') AS `assets`,
			FLOOR( `user`.`ucash` + `user`.`market_bank1` + `user`.`gameroom_cash` /15) AS `networth` 
		FROM 
			`user`
		WHERE
		    `userid` = " . $user_id . "
		ORDER BY 
			`networth` - `debt` + `assets` DESC";
    $result = $db->query_first($userqry);
    $networth = (int)$result['networth'] - (int)$result['debt'] + (int)$result['assets'];
    return $networth;
}
function grab_poke_info($card) {
	//This function takes an array of poke ids and returns all the necessary info regarding them.
	//COPIED FROM CARD
	//First clean the inputs, just to be safe.
	if (count($card) == 0) {
		$output = array(0);
	} else {
		foreach($card as $value) {
			$cardid[] = clean_number($value,20000);
		}
		$cardids = implode(',',$cardid);
		//Now count the ids to check against results after the query.
		$count1 = count($cardid);
		if($count1 == 1 && $cardid[0] == 0) {
			$output = array(0);
		} else {
			//Now let's build our query.
			global $db;
			$result = $db->query_read("SELECT 
				`poke_indv`.`indvid` AS 'c_id', 
				`poke_indv`.`level` AS 'c_level', 
				`poke_indv`.`nick` AS 'c_nick', 
				`poke_mon`.`monname` AS 'c_name', 
				`poke_indv`.`shiny` AS 'c_foil',
				`poke_indv`.`monid` AS 'c_masterid',
				`poke_indv`.`indv_item_id` AS 'c_itemid'
			FROM 
				`poke_indv`
				LEFT JOIN (`poke_mon`)
					ON (`poke_indv`.`monid` = `poke_mon`.`monid`)
			WHERE  
				`poke_indv`.`indvid` IN($cardids) 
			ORDER BY 
				`poke_mon`.`monid` ASC");
			//Now let's store the info in a nested array.
			$counter = 0;
			while ($resultLoop = $db->fetch_array($result)) {
				$counter ++;
				$output[$resultLoop["c_id"]] = array(
					"name" => $resultLoop["c_name"],
					"nick" => $resultLoop["c_nick"],
					"level" => $resultLoop["c_level"],
					"masterid" => $resultLoop["c_masterid"],
					"itemid" => $resultLoop["c_itemid"],
					"foil" => $resultLoop["c_foil"]);
			}
			//Now if the counter matches the count, we can output our info. If they don't match we'll output false.
			if($count1 != $counter) { $output = false; }
		}
	}
	return $output; 
}
function grab_mon_info($card) {
	//This function takes an array of master ids and returns all the necessary info regarding them.
	//First clean the inputs, just to be safe.
	foreach($card as $value) {
		$cardid[] = clean_number($value,20000);
	}
	$cardids = implode(',',$cardid);
	//Now count the ids to check against results after the query.
	$counter = count($cardid);
	if($counter == 1 && $cardid[0] == 0) {
		$output = array(0);
	} else {
		//Now let's build our query.
		global $db;
		$result = $db->query_read("SELECT 
			`poke_mon`.`monname` AS 'c_name',
			`poke_mon`.`monid` AS 'c_masterid'
		FROM 
			`poke_mon`
		WHERE  
			`poke_mon`.`monid` IN($cardids) 
		ORDER BY 
			`poke_mon`.`monid` ASC");
		//Now let's store the info in a nested array.
		$counter1 = 0;
		while ($resultLoop = $db->fetch_array($result)) {
			$counter1++;
			$output[$resultLoop["c_masterid"]] = array(
				"name" => $resultLoop["c_name"]);
		}
	}
	return $output; 
}
function owned_poke($user) {
	//This function returns an array of cards owned by the specified user.
	//Clean the input, just to be safe.
	$user = clean_number($user,20000);
	//Grab all cards owned by that user.
	global $db;
	$result = $db->query_read("SELECT
		`poke_indv`.`indvid` AS 'c_id'
	FROM 
		`poke_indv`
	WHERE
		`poke_indv`.`userid` = $user");
	while ($resultLoop = $db->fetch_array($result)) {
		$cards[] = $resultLoop["c_id"];
	}
	return $cards;
}
?>