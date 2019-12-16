<?php
$banned = array(103,678,16,851,1464,64,24,1639,1448,113,656,82,647);
$userposts			=	$vbulletin->userinfo[posts];

if ($userid != 0 && usergroup != 8 && usergroup != 3 && usergroup != 53 && $userposts >= 250 && !in_array($userid,$banned))
{
$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=home">Pokemon Home</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;

echo '<div class="tcg_body">
Rewards<br><br>';

$rquery = "SELECT
		`rewardid`,
		`dateline`	
	FROM 
		`daily_reward`
	WHERE 
		`userid` = $userid
		AND `type` <> 0
	ORDER BY 
		`dateline` DESC
	LIMIT 1";
$r_result = $db->query_first($rquery);
$first_time = ($r_result["dateline"] == 0) ? TRUE : FALSE;
$one_day = time() - (60*60*24);
$can_reward = ($r_result["dateline"] < $one_day) ? 1 : 0;
$banned = array(103,678,16,851,1464,64,24,1639,1448,113,656,82,647);
if(isset($_GET['do']) && $_GET['do'] == 'roll' && $first_time) {
    // ###### MAIN CODE ######
    $balls = poke_item_discover($userid, $username, 1053346, 999);
    $candy = poke_item_discover($userid, $username, 1053346, 6);
    $gacha = poke_item_discover($userid, $username, 1053346, 8);
    $value = $balls + ($candy*10) + ($gacha*100);
    $str = '<div class=party>
	<h1 class=party>Daily Reward</h1><br>
    <div class=recruit>
        Rolling...
    <br>
    <br>
    You won ' . $balls . ' pokeballs!
    <br>
    <br>
    You won ' . $candy . ' rare candies!
    <br>
    <br>
    You won ' . $gacha . ' gacha tokens!
    </div>
    <br>
    </div>';
	echo $str;
    $db->query_write("INSERT INTO 
    		`daily_reward` 
    		(userid, dateline, type, amount)
    	VALUES 
    		('$userid', '" . time() . "', '1', '$value')");
} else if(isset($_GET['do']) && $_GET['do'] == 'roll' && $can_reward) {
    $result = $db->query_first("SELECT 
			FLOOR( ucash + market_bank1 + gameroom_cash /15 ) AS networth 
		FROM 
			user 
		WHERE
		    userid = " . $userid);
    $penalty = floor($result['networth']/20000);
    // ###### MAIN CODE ######
    $balls = poke_item_discover($userid, $username, 1053346, 999);
    if(mt_rand(1,2+$penalty) == 1) {
        $candy = poke_item_discover($userid, $username, 1053346, 6);
    }
    if(mt_rand(1,4+$penalty) == 1) {
        $gacha = poke_item_discover($userid, $username, 1053346, 8);
    }
    $value = $balls + ($candy*10) + ($gacha*100);
    $str = '<div class=party>
	<h1 class=party>Daily Reward</h1><br>
    <div class=recruit>
        Rolling...
    <br>
    <br>
    You won ' . $balls . ' pokeballs!';
    if($candy > 0) {
        $str.= '<br>
        <br>
        You won ' . $candy . ' rare candies!';
    }
    if($gacha > 0) {
        $str.= '<br>
        <br>
        You won ' . $gacha . ' gacha tokens!';
    }
    $str .= '</div>
    <br>
    </div>';
	echo $str;
    $db->query_write("INSERT INTO 
    		`daily_reward` 
    		(userid, dateline, type, amount)
    	VALUES 
    		('$userid', '" . time() . "', '1', '$value')");
} else if($first_time) {
	// ###### MAIN CODE ######
	$str = '<div class=party>
	<h1 class=party>Daily Reward</h1><br>
    <div class=recruit>
        It\'s your first time!<br>
        Please click <a href=pokemon.php?section=reward&do=roll>Here</a> to roll!
    </div>
    <br>
    </div>';
	echo $str;
} else if ($can_reward) {
	// ###### MAIN CODE ######
	$str = '<div class=party>
	<h1 class=party>Daily Reward</h1><br>
    <div class=recruit>
        Please click <a href=pokemon.php?section=reward&do=roll>Here</a> to roll!
    </div>
    <br>
    </div>';
	echo $str;
} else {
	// ###### MAIN CODE ######
	$remaining = round(($r_result["dateline"] - $one_day)/(60*60),2);
	$str = '<div class=party>
	<h1 class=party>Daily Reward</h1><br>
    <div class=recruit>
        You have already claimed a reward. Please wait ' . $remaining . ' hours until next reward.
    </div>
    <br>
    </div>';
	echo $str;
}
} else {
echo "Nothing to see here.";
}
echo '</div>';