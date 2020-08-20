<?php

if ($userid == 1 || $userid == 1690)
{

if(isset($_GET['do']) && $_GET['do'] == 'battle') {
    // ############ POST VARIABLES ############
    //'p' means it's POST data
    $vbulletin->input->clean_array_gpc('p', array(
        'exp' => TYPE_NOHTML,
        'team' => TYPE_INT
    ));
    $teamid = clean_number($vbulletin->GPC['team'], 9999);
    $exp_r = explode(',', $vbulletin->GPC['exp']);

    $result1 = $db->query_first("SELECT
			`decklist`
		FROM 
			`poke_deck`
		WHERE
			`deckid` = $teamid");

    // ############ QUERY VARIABLES ############
    $result = $db->query_read("SELECT 
			`indvid`, 
			`level`,
			`exp`
		FROM 
			`poke_indv`
		WHERE  
			`indvid` IN(" . $result1["decklist"] . ")
		ORDER BY `poke_indv`.`level` DESC");
    $counter = 0;
    while ($resultLoop = $db->fetch_array($result)) {
        $exp = $resultLoop["exp"];
        $exp += $exp_r[$counter];
        $counter++;
        $lvl = $resultLoop['level'];
        $next_lvl = round((($lvl+1)*($lvl+1))/2,0);
        $rlvl = ($exp >= $next_lvl) ? $lvl+1 : $lvl;
        $exp_out .= "when `indvid` = '" . $resultLoop["indvid"] . "' then '" . $exp . "' ";
        $lvl_out .= "when `indvid` = '" . $resultLoop["indvid"] . "' then '" . $rlvl . "' ";
    }
    $pokes = explode(',',$result1['decklist']);
    $ids = implode("','",$pokes);
    $out_qry = "UPDATE `poke_indv`
        SET 
            `exp` = (case " . $exp_out . " end),
            `level` = (case " . $lvl_out . " end)
        WHERE `indvid` in ('" . $ids . "')";

    $db->query_write($out_qry);

} else if(isset($_GET['do']) && $_GET['do'] == 'gym') {
    // ############ POST VARIABLES ############
    //'p' means it's POST data
    $vbulletin->input->clean_array_gpc('p', array(
        'userid' => TYPE_INT,
        'gen' => TYPE_INT,
        'badge' => TYPE_INT,
        'thread' => TYPE_INT
    ));
    $id_user = clean_number($vbulletin->GPC['userid'], 9999);
    $gen = clean_number($vbulletin->GPC['gen'], 99);
    $badge = clean_number($vbulletin->GPC['badge'], 99);
    $threadid = clean_number($vbulletin->GPC['thread'], 99999999);

    if($badge == 1) {
        $insert = "INSERT INTO `poke_gym` (`entryid`, `userid`, `badgeid`, `genid`) 
            VALUES (NULL, '" . $id_user . "', '1', '" . $gen . "')";
        $db->query_write($insert);
    } else {
        $update = "UPDATE `poke_gym` SET `badgeid` = '" . $badge . "' 
            WHERE `userid` = " . $id_user . " AND `genid` = " . $gen;
        $db->query_write($update);
    }

    $badges = array(
        1 => array(
            1 => "Boulder",
            2 => "Cascade",
            3 => "Thunder",
            4 => "Rainbow",
            5 => "Soul",
            6 => "Marsh",
            7 => "Volcano",
            8 => "Earth"
        ),
        2 => array(
            1 => "Zephyr",
            2 => "Hive",
            3 => "Plain",
            4 => "Fog",
            5 => "Storm",
            6 => "Mineral",
            7 => "Glacier",
            8 => "Rising"
        ),
        3 => array(
            1 => "Stone",
            2 => "Knuckle",
            3 => "Dynamo",
            4 => "Heat",
            5 => "Balance",
            6 => "Feather",
            7 => "Mind",
            8 => "Rain"
        )
    );
    $leader = 1758 + $gen*8 + $badge;

    $output = "You have defeated me! I award you the " . $badges[$gen][$badge] . " Badge!";
    $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post (threadid, username, userid, dateline, pagetext, visible) VALUES (" . $threadid . ", 'Gym Leader', " . $leader . ", " . time() . ", '" . $output . "', 1)");
    $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET lastpostid = LAST_INSERT_ID(), lastpost = " . time() . ", replycount = replycount+1, lastposter = 'Gym Leader', lastposterid = " . $leader . " WHERE threadid = " . $threadid);
    $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = " . $leader);
}

} else {
echo "Nothing to see here.";
}
