<?php
if($_GET["foo"] != 'bar') {
$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=trade">Trading</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div id="tcg_body">';
}
if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53 && $usergroup != 79)
{
/*if($userid != 1) {
	exit("Trade is temporarily closed for upgrades.</div>" . $footer);
}*/
if(isset($_GET['do']) && $_GET['do'] == 'view' && isset($_GET['trade']) && $_GET['trade'] <= 50000){
	$timeout_time = time() + (60*5);
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'trade' => TYPE_INT
	));
	$trade = clean_number($vbulletin->GPC['trade'],50000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_trade WHERE tradeid = $trade) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$qrytime = time();
		$result = $db->query_first("SELECT
			`poke_trade`.`user1id` AS '1_id',
			`poke_trade`.`user2id` AS '2_id',
			`user1`.`username` AS '1_username', 
			`user2`.`username` AS '2_username',
			`poke_trade`.`user1offer` AS '1_offer',
			`poke_trade`.`user2offer` AS '2_offer',
			`poke_trade`.`user1approve` AS '1_approve',
			`poke_trade`.`user2approve` AS '2_approve',
			`poke_trade`.`lastaction` AS 'lastaction',
			`poke_trade`.`active` AS 'active'
		FROM 
			`poke_trade`
		LEFT JOIN `user` AS `user1`
			ON (`poke_trade`.`user1id` = `user1`.`userid`)
		LEFT JOIN `user` AS `user2`
			ON (`poke_trade`.`user2id` = `user2`.`userid`)
		WHERE
			`poke_trade`.`tradeid` = $trade");
		$bothapprove = ($result["1_approve"] == 1 && $result["2_approve"] == 1) ? true : false;
		if($result["1_id"] == $userid || $result["2_id"] == $userid || $userid == 1) {
			//FIRST ESTABLISH WHO IS TOP AND WHO IS BOTTOM
			if($result["1_id"] == $userid) {
				$you = 2;
				$they = 1;
			} else {
				$you = 1;
				$they = 2;
			}
			
			//SET UP AVATARS
			$avatar1 = fetch_avatar_url($result[$you . "_id"]);
			if ($avatar1 != '')
			{
				$avatar1url = $avatar1[0];
			}
			$avatar2 = fetch_avatar_url($result[$they . "_id"]);
			if ($avatar2 != '')
			{
				$avatar2url = $avatar2[0];
			}
			
			//DETERMINE NAMES
			$topuser = '<a href="member.php?' . $result[$you . "_id"] . '">' . $result[$you . "_username"] . '</a>';
			$botuser = '<a href="member.php?' . $result[$they . "_id"] . '">' . $result[$they . "_username"] . '</a>';
			
			//GRAB GENERIC INFO
			$tradeactive = ($result["active"] == 0) ? 'Inactive' : 'Active';
			$lastactionstr = vbdate("F j, Y, g:i:s A",$result["lastaction"]);
			
			//POPULATE OFFER ARRAYS
			$u1_offer = decode_offer($result[$you . "_offer"]);
			$u2_offer = decode_offer($result[$they . "_offer"]);
			$u1_cards = grab_poke_info($u1_offer["card"]);
			$u2_cards = grab_poke_info($u2_offer["card"]);
			
			//SET UP CARD OFFER STRINGS
			if(array_shift(array_values($u1_cards)) == 0) {
				$offer1 .= '<img class="foil0" src="pokemon/images/nocard.jpg" alt="No pokemon offered." height="100%" />';
			} else {
				foreach($u1_cards as $key => $value) {
					$nick = ($value["nick"] == '') ? $value["name"] : $value["nick"];
					$offer1 .= '
					<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $value["masterid"] . '">
						<img class="foil' . $value["foil"] . '" src="pokemon/images/monimages/600px-' . str_pad($value["masterid"] , 3 , "0" , STR_PAD_LEFT) . $value["name"] . '.png" alt="' . $value["name"] . '" height="90%" />
					</a>
		            <a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $key . '">' . $nick . '</a> Lv. ' . $value["level"];
				}
			}
			if(array_shift(array_values($u2_cards)) == 0) {
				$offer2 .= '<img class="foil0" src="pokemon/images/nocard.jpg" alt="No pokemon offered." height="100%" />';
			} else {
				foreach($u2_cards as $key => $value) {
					$nick = ($value["nick"] == '') ? $value["name"] : $value["nick"];
					$offer2 .= '<span class="pcontainer" id="' . $key . '">
					<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $value["masterid"] . '">
						<img class="foil' . $value["foil"] . '" src="pokemon/images/monimages/600px-' . str_pad($value["masterid"] , 3 , "0" , STR_PAD_LEFT) . $value["name"] . '.png" alt="' . $value["name"] . '" height="90%" />
					</a>
		            <a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $key . '">' . $nick . '</a> Lv. ' . $value["level"] . '</span>';
				}
			}
			
			//SET UP APPROVALS
			$u1_approve = ($result[$you . "_approve"] == 1) ? 'top_approved.png' : 'top_undecided.png';
			$u2_approve = ($result[$they . "_approve"] == 1) ? 'bot_approved.png' : 'bot_undecided.png';
			
			//MAKE TRADE BUTTON
			if($bothapprove && $result["active"] == 1) {
				$maketrade .= '
						<div id="makeTrade">
							<form class="trade" action="/pokemon.php?section=trade&do=trade&trade=' . $trade . '&action=final" method="post">
								<input type="hidden" name="auth" value="Final" />
								<input type="hidden" name="qrytime" value="' . $qrytime . '" />
								<input type="image" src="pokemon/images/maketrade.png" alt="Make Trade" width="300" height="50">
							</form>
						</div>';
			}
			
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
				$yourdecks = explode(',',$resultLoop["decklist"]);
				if(array_intersect($yourdecks,$u1_offer["card"])) {
					$warning .= '<p>Found pokemon from team ' . $resultLoop["deckid"] . '! Approving this offer will delete the team!</p>';
				}
			}
			if($warning != '') {
			$str .= '
				<div class="warning">
					' . $warning . '
				</div>
			';
			}
			
			//LETS MAKE OUR HTML STRING
			$str .= '
			<div class="content">
				<div class="topAvatar">
					<img src="' . $avatar1url . '" width="100%" height="100%" />
				</div>
				<div class="topTradeInfo">
					<p><bold>' . $topuser . '</bold> is offering <bold>' . $u1_offer["ucash"][0] . '</bold> pengos and the following pokemon.</p>
				</div>
				<div class="topRightInfo">
					Pokemon start<br />
					Trade between ' . $topuser . ' and ' . $botuser . '<br />
					Trade Status: ' . $tradeactive . '<br />
					Last action: ' . $lastactionstr . '
				</div>
				<div class="topStatus">
					<img src="pokemon/images/' . $u1_approve . '" width="100%" height="100%" />
				</div>
				<div class="topBox">
					' . $offer1 . '
				</div>
				<div class="botBox">
					' . $offer2 . '
				</div>
				<div class="botStatus">
					<img src="pokemon/images/' . $u2_approve . '" width="100%" height="100%" />
				</div>
				<div class="botAvatar">
					<img src="' . $avatar2url . '" width="100%" height="100%" />
				</div>
				<div class="botTradeInfo">
					<p><bold>' . $botuser . '</bold> is offering <bold>' . $u2_offer["ucash"][0] . '</bold> pengos and the above pokemon.</p>
					<div class="tradeform">
						<form class="trade" action="/pokemon.php?section=trade&do=trade&trade=' . $trade . '&action=update" method="post">
							<!-- START SHOW CARDS -->
							<div id="ownedcards">
								<span></span>
							</div>
							<!-- END SHOW CARDS -->
							Ammount of Pengos you are offering<br />
							<input name="ucash" type="text" maxlength="5" value="' . $u2_offer["ucash"][0] . '">(Max 50,000)<br />
							<input type="hidden" name="auth" value="Trade" />
							<input type="hidden" name="qrytime" value="' . $qrytime . '" />
							<input type="image" src="pokemon/images/updateoffer.png" alt="Submit" width="275" height="35"> 
						</form>
					</div>
					<div class="tradeoptions">
						<form class="trade" action="/pokemon.php?section=trade&do=trade&trade=' . $trade . '&action=approve" method="post">
							<input type="hidden" name="auth" value="Approve" />
							<input type="hidden" name="qrytime" value="' . $qrytime . '" />
							<input type="image" src="pokemon/images/approve.png" alt="Approve" width="275" height="35"> 
						</form>
					</div>
					<div class="cancelTrade">
						<form class="trade" action="/pokemon.php?section=trade&do=trade&trade=' . $trade . '&action=cancel" method="post">
							<input type="hidden" name="auth" value="Cancel" />
							<input type="hidden" name="qrytime" value="' . $qrytime . '" />
							<input type="image" src="pokemon/images/canceltrade.png" alt="Approve" width="275" height="35"> 
						</form>
					</div>
				</div>
				' . $maketrade . '
			</div>
			<div id="showcards">
				<img src="pokemon/images/viewmypokemon.png" width="200" height="40"/>
			</div>
			';
			$str .= '<script type="text/javascript">
			jQuery(document).ready(
			function () {
				jQuery("#showcards").click(function () {
				    var slide = jQuery("#ownedcards");
				    if (!slide.data("loaded")) {
				        slide.load("https://forums.novociv.org/pokemon.php?section=ownedpokemon&foo=bar&trade=' . $trade . '");
				        slide.data("loaded", true);
				    }
				    slide.slideToggle("slow");
				});
			});
			var auto_refresh = setInterval(function () {
			  if(' . $timeout_time . ' > (new Date().getTime() / 1000)) {
			    jQuery.get(\'https://forums.novociv.org/check_update.php?r=' . $result["lastaction"] . '&trade=' . $trade . '&poke=1\', function(data) {
			      if (jQuery.trim(data) > 0) {
			        jQuery(\'#tcg_body\').load(\'pokemon.php?section=trade&do=view&trade=' . $trade . '&foo=bar\').fadeIn("slow");
			        clearInterval(auto_refresh);
			      }
			    });
			  }
			}, 1000);
			</script>';
		} else {
			$str .= 'You are not part of that trade!<br>';
		}
	} else {
		$str .= 'That trade does not exist!<br>';
	}
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'trade' && isset($_GET['trade']) && $_GET['trade'] <= 50000) {
	// ############ GET VARIABLES ############
	//'g' means it's POST data
	$vbulletin->input->clean_array_gpc('g', array(
		'trade' => TYPE_INT,
   		'action' => TYPE_NOHTML
	));
	// ############ POST VARIABLES ############
	//'p' means it's POST data
	$vbulletin->input->clean_array_gpc('p', array(
		'auth' => TYPE_NOHTML,
		'ids' => TYPE_ARRAY,
   		'ucash' => TYPE_INT,
   		'qrytime' => TYPE_INT,
   		'cards' => TYPE_NOHTML
	));
	$trade = clean_number($vbulletin->GPC['trade'],50000);
	
	$result = $db->query_first("SELECT
		`poke_trade`.`user1id` AS '1_id',
		`poke_trade`.`user2id` AS '2_id',
		`poke_trade`.`lastaction` AS 'lastaction',
		`poke_trade`.`active` AS 'active',
		`poke_trade`.`user1offer` AS '1_offer',
		`poke_trade`.`user2offer` AS '2_offer',
		`poke_trade`.`user1approve` AS '1_approve',
		`poke_trade`.`user2approve` AS '2_approve'
	FROM 
		`poke_trade`
	WHERE
		`poke_trade`.`tradeid` = $trade");
	$microtime2 = microtime(true); // Gets microseconds
	if(empty($result)) {
		echo 'That trade does not exist!';
		sleep(2);
		echo '
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php"
		//-->
		</script>';
	} else if($result["active"] == 0) {
		echo 'That trade is not active!';
		sleep(2);
		echo '
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php?section=trade&do=view&trade=' . $trade . '"
		//-->
		</script>';
	} else if($vbulletin->GPC['qrytime'] <= $result["lastaction"]) {
		echo 'That trade has been updated since you last viewed it!';
		sleep(2);
		echo '
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php?section=trade&do=view&trade=' . $trade . '"
		//-->
		</script>';
	} else if(!($result["1_id"] == $userid || $result["2_id"] == $userid)) {
		echo 'You are not a part of that trade!';
		sleep(2);
		echo '
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php"
		//-->
		</script>';
	} else if(isset($_GET['action']) && $vbulletin->GPC['action'] == 'update') {
		$amount = clean_number($vbulletin->GPC['ucash'],50000);
		
		$cards_owned = owned_poke($userid);
		if(count($vbulletin->GPC['ids']) > 0) {
			foreach($vbulletin->GPC['ids'] as $value) {
				$ids[] = clean_number($value,20000);
			}
		} else {
			$ids = array(0);
		}
		$cards_offered = array_unique(array_map('intval', $ids));
		//$cards_offered = array_unique(explode(',',$vbulletin->GPC['cards']));
		$cards_empty = ($vbulletin->GPC['cards'] == '') ? true : false;
		
		if($amount>max($userwealth,0)) {
			echo 'You don\'t have that many ' . $cashname . '!';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=trade&do=view&trade=' . $trade . '"
			//-->
			</script>';
		} else if(count(array_diff($cards_offered, $cards_owned)) && !$cards_empty) {
			echo 'You offered pokemon that you don\'t have!';
			//echo '<br>Offered:<br>' . print_r($cards_offered, true) . '<br>Have:<br>' . print_r($cards_owned, true);
		} else if($vbulletin->GPC['auth'] == 'Trade') {	
			//var_dump($cards_owned);
			//echo '<br><br>Offered:<br>' . print_r($cards_offered, true) . '<br>Have:<br>' . print_r($cards_owned, true);
			
			$offer_string = encode_offer($amount,$cards_offered);
			//echo '<br>' . $offer_string . '<br>';
			$offeruserid = ($result["1_id"] == $userid) ? '`user1offer`' : '`user2offer`';
			//echo "Window of error: ".(microtime(true) - $microtime2)."s<br>";
			$db->query_write("UPDATE 
				`poke_trade` 
			SET 
				" . $offeruserid . " = '" . $offer_string . "', 
				`lastaction` = " . time() . ",
				`user1approve` = 0,
				`user2approve` = 0
			WHERE 
				`tradeid` = " . $trade);
			echo 'Updating Offer...';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=trade&do=view&trade=' . $trade . '"
			//-->
			</script>';
		} else {
			echo 'Something went wrong!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $vbulletin->GPC['action'] == 'approve') {
		if($vbulletin->GPC['auth'] == 'Approve') {
			if($result["1_id"] == $userid) {
				$offeruserid = '`user1approve`';
				$otherapprove = $result["2_approve"];
			} else if($result["2_id"] == $userid) {
				$offeruserid = '`user2approve`';
				$otherapprove = $result["1_approve"];
			} else {
				exit("You broke something!");
			}
			//echo "Window of error: ".(microtime(true) - $microtime2)."s<br>";
			$db->query_write("UPDATE 
				`poke_trade` 
			SET 
				`lastaction` = " . time() . ",
				" . $offeruserid . " = 1
			WHERE 
				`tradeid` = " . $trade);
			echo 'Approving Offer...';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=trade&do=view&trade=' . $trade . '"
			//-->
			</script>';
		} else {
			echo 'Something went wrong!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $vbulletin->GPC['action'] == 'cancel') {
		if($vbulletin->GPC['auth'] == 'Cancel') {
			if($result["1_id"] == $userid) {
				$offeruserid = '`user1approve`';
				$otherapprove = $result["2_approve"];
			} else if($result["2_id"] == $userid) {
				$offeruserid = '`user2approve`';
				$otherapprove = $result["1_approve"];
			} else {
				exit("You broke something!");
			}
			//echo "Window of error: ".(microtime(true) - $microtime2)."s<br>";
			$db->query_write("UPDATE 
				`poke_trade` 
			SET 
				`lastaction` = " . time() . ",
				`active` = 0
			WHERE 
				`tradeid` = " . $trade);
			echo 'Canceling Offer...';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=trade"
			//-->
			</script>';
		} else {
			echo 'Something went wrong!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $vbulletin->GPC['action'] == 'final') {
		$result1 = $db->query_first("SELECT
			`poke_trade`.`user1id` AS '1_id',
			`poke_trade`.`user2id` AS '2_id',
			`poke_trade`.`lastaction` AS 'lastaction',
			`poke_trade`.`active` AS 'active',
			`poke_trade`.`user1offer` AS '1_offer',
			`poke_trade`.`user2offer` AS '2_offer',
			`poke_trade`.`user1approve` AS '1_approve',
			`poke_trade`.`user2approve` AS '2_approve'
		FROM 
			`poke_trade`
		WHERE
			`poke_trade`.`tradeid` = " . $trade);
		if($result1["1_id"] == $userid) {
			$otheruser = $result1["2_id"];
			$u1_offer = decode_offer($result1["1_offer"]);
			$u2_offer = decode_offer($result1["2_offer"]);
		} else if($result1["2_id"] == $userid) {
			$otheruser = $result1["1_id"];
			$u1_offer = decode_offer($result1["2_offer"]);
			$u2_offer = decode_offer($result1["1_offer"]);
		} else {
			exit("You broke something!");
		}
		$u1array = implode(',',$u1_offer["card"]);
		$u2array = implode(',',$u2_offer["card"]);
		$result2 = $db->query_first("SELECT
			ucash
		FROM 
			user
		WHERE
			userid = " . $otheruser);
		$othercash = $result2["ucash"];
		$newuserwealth = $userwealth - $u1_offer["ucash"][0] + $u2_offer["ucash"][0];
		$newothercash = $othercash - $u2_offer["ucash"][0] + $u1_offer["ucash"][0];
		
		if($result1["1_approve"] == 0 || $result1["2_approve"] == 0) {
			echo 'Someone doesn\'t approve!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else if($u1_offer["ucash"][0] > max($userwealth,0) || $u2_offer["ucash"][0] > max($othercash,0)) {
			echo 'Someone can\'t afford this!
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else {
			$db->query_write("UPDATE 
				`poke_trade` 
			SET  
				`lastaction` = " . time() . ",
				`active` = 0
			WHERE 
				`tradeid` = " . $trade);
			echo '<div>Closing trade...</div>';
			$db->query_write("UPDATE 
				`user` 
			SET  
				`ucash` = " . $newuserwealth . "
			WHERE 
				`userid` = " . $userid);
			echo '<div>Redifining your wealth...</div>';
			$db->query_write("UPDATE 
				`user` 
			SET  
				`ucash` = " . $newothercash . "
			WHERE 
				`userid` = " . $otheruser);
			echo '<div>Redifining their wealth...</div>';
			$db->query_write("UPDATE 
				`poke_indv` 
			SET  
				`userid` = " . $userid . ",
				`catch_date` = " . time() . ",
				`friend` = 0
			WHERE 
				`indvid` IN($u2array)");
			echo '<div>Giving you pokemon...</div>';
			$db->query_write("UPDATE 
				`poke_indv` 
			SET  
				`userid` = " . $otheruser . ",
				`catch_date` = " . time() . ",
				`friend` = 0
			WHERE 
				`indvid` IN($u1array)");
			echo '<div>Giving them pokemon...</div>';
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
				
			$result3 = $db->query_read($qry);
			echo '<div>Validating your teams...</div>';
			while ($resultLoop = $db->fetch_array($result3)) {
				$yourdecks = explode(',',$resultLoop["decklist"]);
				if(array_intersect($yourdecks,$u1_offer["card"])) {
					echo '<div>Found pokemon from team ' . $resultLoop["deckid"] . '! Deleting the team...</div>';
					$db->query_write("UPDATE 
						`poke_deck` 
					SET 
						`userid` = 15
					WHERE 
						`deckid` = " . $resultLoop["deckid"]);
					$db->query_write("UPDATE 
						`user` 
					SET 
						`poke_team` = 0
					WHERE 
						`userid` = " . $userid);
				}
			}
			// ############ QUERY VARIABLES ############
			$qry = "SELECT 
				`poke_deck`.`decklist`,
				`poke_deck`.`name`,
				`poke_deck`.`deckid`
			FROM 
				`poke_deck`
			WHERE  
				`poke_deck`.`userid` = " . $otheruser . " 
			ORDER BY 
				`poke_deck`.`deckid` ASC";	
			$result4 = $db->query_read($qry);
			echo '<div>Validating their teams...</div>';
			while ($resultLoop = $db->fetch_array($result4)) {
				$theirdecks = explode(',',$resultLoop["decklist"]);
				if(array_intersect($theirdecks,$u2_offer["card"])) {
					echo '<div>Found pokemon from team ' . $resultLoop["deckid"] . '! Deleting the team...</div>';
					$db->query_write("UPDATE 
						`poke_deck` 
					SET 
						`userid` = 15
					WHERE 
						`deckid` = " . $resultLoop["deckid"]);
					$db->query_write("UPDATE 
						`user` 
					SET 
						`poke_team` = 0
					WHERE 
						`userid` = " . $otheruser);
				}
			}
			$u1_cards = grab_poke_info($u1_offer["card"]);
			$u2_cards = grab_poke_info($u2_offer["card"]);
			$evo_qry = $db->query_read("SELECT
        			`monid`,
        			`evo_monid`,
        			`methodo`
        		FROM 
        			`poke_spec_evo`
        		WHERE
        			`method` = 0");
        	while ($resultLoop = $db->fetch_array($evo_qry)) {
        	    if($resultLoop["methodo"] == 'trade') {
        	        $evos[] = $resultLoop["monid"];
        	    } else {
        	        $ievos[] = $resultLoop["monid"];
        	        $methodo[$resultLoop["monid"]] = $resultLoop["methodo"];
        	    }
        	    $spec_evo[$resultLoop["monid"]] = $resultLoop["evo_monid"];
        	}

			foreach($u1_cards as $key => $value) {
			    if(in_array($value["masterid"],$evos)){
			        $db->query_write("UPDATE 
        				`poke_indv` 
        			SET 
        				monid = " . $spec_evo[$value["masterid"]] . " 
        			WHERE 
        				indvid = " . $key);
        			
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
        				if(array_intersect($yourdecks,array($key))) {
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
        			echo 'Evolving pokemon...';
			    } else if(in_array($value["masterid"],$ievos)){
			        //do nothing
			        if($value["itemid"] == $methodo[$value["masterid"]]) {
    			            $db->query_write("UPDATE 
            				`poke_indv` 
            			SET 
            				monid = " . $spec_evo[$value["masterid"]] . " ,
            				indv_item_id = 0
            			WHERE 
            				indvid = " . $key);
            			
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
            				if(array_intersect($yourdecks,array($key))) {
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
            			echo 'Evolving pokemon...';
			        }
			    }
			}
			foreach($u2_cards as $key => $value) {
			    if(in_array($value["masterid"],$evos)){
			        $db->query_write("UPDATE 
        				`poke_indv` 
        			SET 
        				monid = " . $spec_evo[$value["masterid"]] . " 
        			WHERE 
        				indvid = " . $key);
        			
        			//CHECK IF YOU WILL DELETE DECK
        			// ############ QUERY VARIABLES ############
        			$qry = "SELECT 
        				`poke_deck`.`decklist`,
        				`poke_deck`.`deckid`
        			FROM 
        				`poke_deck`
        			WHERE  
        				`poke_deck`.`userid` = " . $otheruser . " 
        			ORDER BY 
        				`poke_deck`.`deckid` ASC";
        				
        			$result8 = $db->query_read($qry);
        			while ($resultLoop = $db->fetch_array($result8)) {
        				$yourdeck = $resultLoop["deckid"];
        				$yourdecks = explode(',',$resultLoop["decklist"]);
        				if(array_intersect($yourdecks,array($key))) {
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
        			echo 'Evolving pokemon...';
			    } else if(in_array($value["masterid"],$ievos)){
			        //do nothing
			        if($value["itemid"] == $methodo[$value["masterid"]]) {
    			            $db->query_write("UPDATE 
            				`poke_indv` 
            			SET 
            				monid = " . $spec_evo[$value["masterid"]] . " ,
            				indv_item_id = 0
            			WHERE 
            				indvid = " . $key);
            			
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
            				if(array_intersect($yourdecks,array($key))) {
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
            			echo 'Evolving pokemon...';
			        }
			    }
			}
			echo 'Sucess! <a href="pokemon.php">Continue.</a>';
		}
	} else {
		echo 'Something went wrong!
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php"
		//-->
		</script>';
	}
} else if(isset($_GET['do']) && $_GET['do'] == 'list') {
	// ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'num' => TYPE_INT
	));
	
	$num = clean_number($vbulletin->GPC['num'],100);
	$num = ($num == 0) ? 5 : $num;
	
	// ############ QUERY VARIABLES ############
	$qry = "SELECT 
		`poke_trade`.`tradeid` AS 't_id', 
		`poke_trade`.`user1id` AS '1_id',
		`poke_trade`.`user2id` AS '2_id',
		`user1`.`username` AS '1_name', 
		`user2`.`username` AS '2_name',
		`poke_trade`.`lastaction` AS 'lastaction',
		`poke_trade`.`active` AS 'active'
	FROM 
		`poke_trade`
	LEFT JOIN `user` AS `user1`
		ON (`poke_trade`.`user1id` = `user1`.`userid`)
	LEFT JOIN `user` AS `user2`
		ON (`poke_trade`.`user2id` = `user2`.`userid`)
	WHERE  
		`poke_trade`.`user1id`=$userid
		OR `poke_trade`.`user2id`=$userid 
	ORDER BY 
		`poke_trade`.`tradeid` DESC
	LIMIT 0," . $num;
	$result = $db->query_read($qry);
	$str .= '<h1>Displaying your trades:</h1>';
	while ($resultLoop = $db->fetch_array($result)) {
		$trade = '<a href="pokemon.php?section=trade&do=view&trade=' . $resultLoop["t_id"] . '">
			Trade</a> between <a href="member.php?' . $resultLoop["1_id"] . '">' . $resultLoop["1_name"] . '</a> and <a href="member.php?' . $resultLoop["2_id"] . 
			'">' . $resultLoop["2_name"] . '</a>.';
		$active = ($resultLoop["active"] == 0) ? 'inactive' : 'active';
		$lastaction = 'Last action was at ' . vbdate("F j, Y, g:i:s A",$resultLoop["lastaction"]);
		
		$str .= '<div class="tradelist' . $active . '">
			' . $trade . '<br>
			' . $lastaction . '
		</div>';
	}	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'start') {
	if(isset($_GET['action']) && $_GET['action'] == 'start') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'person' => TYPE_INT,
	   		'auth' => TYPE_NOHTML
		));
		
		$request = clean_number($vbulletin->GPC['person'],50000);
		$result = $db->query_first("SELECT `username` FROM `user` WHERE `userid` = $request");
		$person = $result["username"];
		$qry = "SELECT EXISTS(
			SELECT 1 
			FROM poke_trade 
			WHERE 
				((user1id = $userid) AND (active = 1))
				OR ((user1id = $request) AND (active = 1))
				OR ((user2id = $userid) AND (active = 1))
				OR ((user2id = $request) AND (active = 1))
				)
		AS 'Exists'";
		$exists = $db->query_first($qry);
		$exists2 = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_request WHERE user2id = $request AND user1id = $userid AND responded = 0) AS 'Exists'");
		// ############ MAIN CODE ############
		if($person == '') {
			echo 'That member does not exist!';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';	
		} else if($exists['Exists'] == true) {
			echo 'One of the specified members are already in an active trade!';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';		
		} else if($exists2['Exists'] == true) {
			echo 'You already have an outstanding trade request with that person!';
			sleep(2);
			echo '<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';		
		} else {
			$db->query_write("INSERT INTO 
				`poke_request` 
				(user1id, user2id, responded, dateline)
			VALUES 
				('$userid', '$request', 0, " . time() . ")");
			$result2 = $db->query_first("SELECT `requestid` FROM `poke_request` WHERE requestid = LAST_INSERT_ID()");
			$requestid = $result2["requestid"];
			// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   $vbphrase['poke_trade_request_pm_title']; 
				$pmtext     =   $vbphrase['poke_trade_request_pm_body'];
				$pmstoren = 'Trade pokemon. Click [url="https://forums.novociv.org/pokemon.php?section=home&do=request&action=respond&request=' . $requestid . '"]Here[/url] to accept or deny';
				$pmtext     = str_replace("[NAME]", "$pmstoren", "$pmtext");
				$pmgave = '[url="https://forums.novociv.org/member.php?' . $userid . '"]' . $username . '[/url]';
				$pmtext     = str_replace("[DONATE]", "$pmgave", "$pmtext");
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$request'"));
				
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
			echo 'You\'re good to go.';
			sleep(2);
			echo '
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else {
		$str .= '<div id="trade_form">
			<form class="trade" action="/pokemon.php?section=trade&do=start&action=start" method="post">
				ID of the person you wish to trade with:<br>
				<input name="person" type="text" maxlength="5"><br>
				<input type="hidden" name="auth" value="Start" />
				<input type="submit" value="Request Trade!" />
			</form>
		</div>';
		echo $str;
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
if($_GET["foo"] != 'bar') {
echo '</div>';
}