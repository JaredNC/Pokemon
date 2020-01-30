<?php



error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))

{

	exit;

}



// ################################# HILL REWARDS #####################################
$qry = "SELECT 
    *
FROM 
    poke_loan 
WHERE 
    balance > 0
    OR int_payable > 0";

$result = $vbulletin->db->query_read($qry);
$log .= 'Query ran. ';

while ($resultLoop = $vbulletin->db->fetch_array($result)) {
    // ### Grab variables ###
    $loan_id = $resultLoop['loan_id'];
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

    // ### Calculate Interest ###
    $interest_accrued = $new_balance * $dpr;
    $new_interest += $interest_accrued;

    $log .= "Loan: $loan_id:";
    // ### Make Payment ###

    // Take pengos
    $user_qry = "UPDATE
        user
    SET
        ucash = ucash - $pmt
    WHERE
        userid = $customer";
    $vbulletin->db->query_write($user_qry) or die("user died");

    // Update Loan
    $update_qry = "UPDATE
        poke_loan
    SET
        balance = $new_balance,
        int_payable = $new_interest
    WHERE
        loan_id = $loan_id";
    $vbulletin->db->query_write($update_qry) or die("loan died");

    // Add Payment to Log
    $insert_qry = "INSERT INTO poke_loan_pmt
        (loan_id, dateline, pmt, interest, principal)
    VALUES
        ('$loan_id', '" . time() . "', '$pmt', '$paid_interest', '$paid_balance')";
    $vbulletin->db->query_write($insert_qry) or die("log died");

    $log .= ' all good.';

}

echo $log;
log_cron_action('Loan Stuff' . $log, $nextitem, 1);







?>