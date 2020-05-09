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

}

} else {
echo "Nothing to see here.";
}
