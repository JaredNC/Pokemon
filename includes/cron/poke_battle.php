<?php



error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))

{

	exit;

}


require_once(DIR . '/includes/functions_custom.php');

// ################################# HILL REWARDS #####################################
$qry = "SELECT 
    `battleid`,
    `threadid`,
    `poke_team`,
    `type`
FROM 
    `poke_battle`
WHERE 
     `completed` = 0
ORDER BY `dateline`  ASC";

$result = $vbulletin->db->query_read($qry);
while ($resultLoop = $vbulletin->db->fetch_array($result)) {
    if($resultLoop["type"] == 0) {
        $url = "http://python-bot-2048942849.us-east-1.elb.amazonaws.com/api2?id1="
            . $resultLoop["poke_team"] . "&thread=" . $resultLoop["threadid"];
//    $url = "79.184.128.150:9999/api2?id1=" . $teamid . "&thread=" . $threadid;
    } elseif($resultLoop["type"] == 1){
        $url = "http://python-bot-2048942849.us-east-1.elb.amazonaws.com/api3?id1="
            . $resultLoop["poke_team"] . "&thread=" . $resultLoop["threadid"];
    }

    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    curl_exec($ch);
    $user_qry = "UPDATE
        `poke_battle`
    SET
        completed = 1
    WHERE
        battleid = " . $resultLoop["battleid"];
    $vbulletin->db->query_write($user_qry) or die("user died");
}

log_cron_action('', $nextitem, 1);







?>