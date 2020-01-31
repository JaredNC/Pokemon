<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.5 - Licence Number VBF5DF4C26
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################
$timestamp = TIMENOW;
$activity = TIMENOW - (60*60*24*30);

$totalwealth = $vbulletin->db->query_first("SELECT SUM(ucash + market_bank1 + (gameroom_cash/15)) AS total FROM " . TABLE_PREFIX . "user WHERE 1");
$totalloans = $vbulletin->db->query_first("SELECT SUM(balance + int_payable) AS total FROM " . TABLE_PREFIX . "poke_loan WHERE 1");
$totalinvestments = $vbulletin->db->query_first("SELECT SUM(balance + int_payable) AS total FROM " . TABLE_PREFIX . "poke_investment WHERE 1");
$totalwealth['total'] = intval($totalwealth['total']);
$totalloans['total'] = intval($totalloans['total']);
$totalinvestments['total'] = intval($totalinvestments['total']);
$totalwealth['total'] = $totalwealth['total'] - $totalloans['total'] + $totalinvestments['total'];

$totalactivewealth = $vbulletin->db->query_first("SELECT SUM(ucash + market_bank1 + (gameroom_cash/15)) AS total FROM " . TABLE_PREFIX . "user WHERE lastactivity >= " . $activity);
$totalactivewealth['total'] = intval($totalactivewealth['total']);
$totalactivewealth['total'] = $totalactivewealth['total'] - $totalloans['total'] + $totalinvestments['total'];

$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "inflation 
				(dateline, totalwealth, totalactivewealth) 
			VALUES 
				(" . $timestamp . ", " . $totalwealth['total'] . ", " . $totalactivewealth['total'] . ")");


log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 20:50, Tue Aug 10th 2010
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>