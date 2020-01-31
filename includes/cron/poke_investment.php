<?php



error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))

{

	exit;

}
// I know there is a more efficient way to do this, using joins to grab all info in fewer queries.
// But I suspect number of investments won't exceed a dozen or so.


// ################################# HILL REWARDS #####################################
$qry = "SELECT 
    *
FROM 
    poke_investment 
WHERE 
    balance > 0
    OR int_payable > 0";

$result = $vbulletin->db->query_read($qry);
$log .= 'Query ran.<br>';

while ($resultLoop = $vbulletin->db->fetch_array($result)) {
    // ### Grab variables ###
    $investment_id = $resultLoop['investment_id'];
    $customer = $resultLoop['userid'];
    $apr = $resultLoop['apr'];
    $dpr = $apr/365;
    $pmt = $resultLoop['daily_pmt'];
    $balance = $resultLoop['balance'];
    $int_payable = $resultLoop['int_payable'];

    // Some Logic
    $pmt = min($pmt,($balance + $int_payable));
    if($pmt >= $int_payable) {
        $paid_interest = $int_payable;
        $new_interest = 0;
        $paid_balance = $pmt - $int_payable;
        $new_balance = $balance - $paid_balance;
    } else {
        $paid_interest = $pmt;
        $new_interest = $int_payable - $pmt;
        $paid_balance = 0;
        $new_balance = $balance;
    }

    // ### Calculate Performance ###
    $performance = 0;

    // Posts
    $post_qry = 'SELECT count(*) AS `count` 
        FROM `post` 
        WHERE `userid` = ' . $customer . ' AND `dateline` > (UNIX_TIMESTAMP() - 60*60*24)';
    $post_result = $vbulletin->db->query_first($post_qry);
    $post_score = $post_result["count"] * 0.1;
    $performance += $post_score;
    $log .= 'Post Score: ' . $post_score . '<br>';

    // Threads
    $thread_qry = 'SELECT count(*) AS `count` 
        FROM `thread` 
        WHERE `postuserid` = ' . $customer . ' AND `dateline` > (UNIX_TIMESTAMP() - 60*60*24)';
    $thread_result = $vbulletin->db->query_first($thread_qry);
    $thread_score = $thread_result["count"] * 0.1;
    $performance += $thread_score;
    $log .= 'Thread Score: ' . $thread_score . '<br>';

    // Likes
    $like_qry = 'SELECT count(*) AS `count` 
        FROM `post_thanks` 
        INNER JOIN `post` ON `post_thanks`.`postid` = `post`.`postid` 
        WHERE `post`.`userid` = ' . $customer . ' AND `post_thanks`.`date` > (UNIX_TIMESTAMP() - 60*60*24)';
    $like_result = $vbulletin->db->query_first($like_qry);
    $like_score = $like_result["count"] * 0.25;
    $performance += $like_score;
    $log .= 'Like Score: ' . $like_score . '<br>';

    $log .= 'Performance: ' . $performance . '<br>';
    $performance = min($performance,1);
    $log .= 'Performance: ' . $performance . '<br>';
    $dpr = $dpr * $performance;
    $log .= 'DPR: ' . $dpr . '<br>';

    // ### Calculate Interest ###
    $interest_accrued = $new_balance * $dpr;
    $new_interest += $interest_accrued;

    $log .= "Investment: $investment_id:";
    // ### Make Payment ###

    // Take pengos
    $user_qry = "UPDATE
        user
    SET
        ucash = ucash + $pmt
    WHERE
        userid = $customer";
    $vbulletin->db->query_write($user_qry) or die("user died");

    // Update Loan
    $update_qry = "UPDATE
        poke_investment
    SET
        balance = $new_balance,
        int_payable = $new_interest
    WHERE
        investment_id = $investment_id";
    $vbulletin->db->query_write($update_qry) or die("loan died");

    // Add Payment to Log
    $insert_qry = "INSERT INTO poke_investment_pmt
        (investment_id, dateline, pmt, interest, principal)
    VALUES
        ('$investment_id', '" . time() . "', '$pmt', '$paid_interest', '$paid_balance')";
    $vbulletin->db->query_write($insert_qry) or die("log died");

    $log .= ' all good.';

}

echo $log;
log_cron_action('Investment Stuff' . $log, $nextitem, 1);







?>