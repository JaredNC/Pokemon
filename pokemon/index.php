<?
//SET WHO CAN VIEW PAGE
//if ($userid != 0 && usergroup != 8 && usergroup != 3 && usergroup != 53)
//if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53 && $userposts >= 100)
if ($usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{

$navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;


	// ############ MAIN CODE ############
	$str = '<div class="tcg_body">
	Welcome ' . $username . '!<br>
	You have ' . $pokeballs . ' pokeballs! <a href="/buy.php?do=buypokeballs">Buy More</a><br>';
	$str .= '<a href="pokemon.php?section=gacha">Roll the Pokemon Gacha Machine</a><br>';
	$str .= '<a href="pokemon.php?section=reward">Daily Rewards</a><br>';
    $str .= '<a href="pokemon.php?section=flex">Leaderboards</a><br>';

    $result1 = $db->query_first("SELECT count(*) AS `count` FROM `poke_indv` WHERE `userid`=$userid");
	if($result1["count"] > 0) { 
		$str .= '<a href="pokemon.php?section=home&do=list">Owned Pokemon List</a><br>';
		$str .= '<a href="pokemon.php?section=home&do=ilist">Owned Item List</a><br>';
		$str .= '<a href="pokemon.php?section=home&do=dex">Pokedex</a><br>';
		$str .= '<a href="pokemon.php?section=buy">Poke Mart</a><br>';
		$str .= '<a href="showthread.php?1053575">Trade in Pokemon</a><br>';
		$str .= '<a href="pokemon.php?section=daycare">Pokemon Daycare</a><br>';
		$str .= '<a href="pokemon.php?section=trade&do=list">Trades List</a><br>';
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_trade WHERE (user1id = $userid AND active = 1) OR (user2id = $userid AND active = 1)) AS 'Exists'");
		if($exists['Exists'] != true) { $str .= '<a href="pokemon.php?section=trade&do=start">Start a Trade</a><br>'; }
		$result = $db->query_first("SELECT count(*) AS `count` FROM `poke_request` WHERE `user2id`=$userid AND `responded`=0");
		if($result["count"] > 0) { $str .= '<a href="pokemon.php?section=home&do=request">Outstanding Requests (' . $result["count"] . ')</a><br>'; }
	}
	$str .= '<a href="pokemon.php?section=team">Manage Teams</a></div>';
	echo $str;

//USER CAN'T VIEW PAGE
} else {
	echo "Nothing to see here.";
}
?>