<?php

//if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
if ($usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=flex">Leaderboards</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div class="tcg_body">
Pokemon start<br><br>';

// ############ GET VARIABLES ############
$vbulletin->input->clean_array_gpc('g', array(
	'user' => TYPE_INT,
	'do' => TYP_STR,
	'num' => TYPE_INT,
	'date' => TPYE_INT,
	'gen' => TYPE_INT
));
$person = (clean_number($vbulletin->GPC['user'],2000) == 0) ? $userid : clean_number($vbulletin->GPC['user'],2000);
$spectime = clean_number($vbulletin->GPC['date'],time());

$h1 = '<h1 class=party>Highest Level Pokemon<br>
		<a href="/pokemon.php?section=flex&do=toplvl&num=10">Top 10 Pokemon</a> - 
		<a href="/pokemon.php?section=flex&do=toplvl&num=25">Top 25 Pokemon</a> <br>
		<a href="/pokemon.php?section=flex&do=topown&num=10">Top 10 Trainers</a> - 
		<a href="/pokemon.php?section=flex&do=topown&num=25">Top 25 Trainers</a> <br>
		<a href="/pokemon.php?section=flex&do=topdex&num=10">Top 10 Pokedex</a> - 
		<a href="/pokemon.php?section=flex&do=topdex&num=25">Top 25 Pokedex</a> <br>
		<a href="/pokemon.php?section=flex&do=topdex&num=10&gen=2">Top 10 Pokedex - Gen II</a> - 
		<a href="/pokemon.php?section=flex&do=topdex&num=25&gen=2">Top 25 Pokedex - Gen II</a> <br>
		<a href="/pokemon.php?section=flex&do=topdex&num=10&gen=3">Top 10 Pokedex - Gen III</a> - 
		<a href="/pokemon.php?section=flex&do=topdex&num=25&gen=3">Top 25 Pokedex - Gen III</a> <br>
	</h1><br>';

if($vbulletin->GPC['do'] == 'toplvl') {
	// ###### GET VARIABLES ######
	if(isset($vbulletin->GPC['num']) && $vbulletin->GPC['num'] > 10){
		$b = clean_number($vbulletin->GPC['num'],250);
	} else{
		$b = 10;
	}
	
	// ###### QUERY VARIABLES ######
	
	
	
	// ###### MAIN CODE ######
	$str .= '<div class="cards_table">
	' . $h1 . '
	<table id="myTable" class="tablesorter">
		<thead>
		<tr>
			<th><font color=white>Rank</font></th>
			<th><font color=white>Pokemon</font></th>
			<th><font color=white>Trainer</font></th>
			<th><font color=white>Level</font></th>
		</tr>
		</thead>
		<tbody id="fbody">';
	$sqldate = ($spectime > 0) ? 'WHERE `post_thanks`.`date` > ' . $spectime : '';
	$result = $db->query_read("SELECT 
        	`poke_indv`.`indvid` AS `indvid`,
        	`poke_indv`.`monid` AS `monid`,
        	`poke_mon`.`monname` AS `monname`,
        	`poke_indv`.`nick` AS `nick`,
        	`poke_indv`.`level` AS `level`,
        	`poke_indv`.`userid` AS `userid`,
        	`user`.`username` AS `username`
        FROM `poke_indv` 
        LEFT JOIN `poke_mon`
        ON `poke_indv`.`monid` = `poke_mon`.`monid`
        LEFT JOIN `user`
        ON `poke_indv`.`userid` = `user`.`userid`
        WHERE `poke_indv`.`userid` <> 15
        ORDER BY `poke_indv`.`level` DESC
		LIMIT $b");
	$rank=1;
	
	while ($resultLoop = $db->fetch_array($result)) {
		$indvid = $resultLoop["indvid"];
		$nick = ($resultLoop["nick"] == '') ? $resultLoop["monname"] : $resultLoop["nick"];
		$pokemon = '<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $indvid . '">' . $nick . '</a>';
		
		$trainer = '<a href="https://forums.novociv.org/member.php?' . $resultLoop["userid"] . '">' . $resultLoop["username"] . '</a>';
		
		$level = $resultLoop["level"];
		
		$str .= '<tr>
		<td>' . ($rank++) . '</td>
		<td>' . $pokemon . '</td>
		<td>' . $trainer . '</td>
		<td>' . $level . '</td>
		</tr>';
	}
	
	$str .= '</tbody></table></div>';
	
	echo $str;
} else if($vbulletin->GPC['do'] == 'topown') {
	// ###### GET VARIABLES ######
	if(isset($vbulletin->GPC['num']) && $vbulletin->GPC['num'] > 10){
		$b = clean_number($vbulletin->GPC['num'],250);
	} else{
		$b = 10;
	}
	
	// ###### QUERY VARIABLES ######
	
	
	
	// ###### MAIN CODE ######
	$str .= '<div class="cards_table">
	' . $h1 . '
	<table id="myTable" class="tablesorter">
		<thead>
		<tr>
			<th><font color=white>Rank</font></th>
			<th><font color=white>Trainer</font></th>
			<th><font color=white>Number Owned</font></th>
		</tr>
		</thead>
		<tbody id="fbody">';
	$sqldate = ($spectime > 0) ? 'WHERE `post_thanks`.`date` > ' . $spectime : '';
	$result = $db->query_read("SELECT 
        	count(`poke_indv`.`indvid`) AS `count`,
        	`poke_indv`.`userid` AS `userid`,
        	`user`.`username` AS `username`
        FROM `poke_indv` 
        LEFT JOIN `user`
        ON `poke_indv`.`userid` = `user`.`userid`
        WHERE `poke_indv`.`userid` <> 15
        GROUP BY `poke_indv`.`userid`
        ORDER BY `count` DESC
		LIMIT $b");
	$rank=1;
	
	while ($resultLoop = $db->fetch_array($result)) {
		$trainer = '<a href="https://forums.novociv.org/member.php?' . $resultLoop["userid"] . '">' . $resultLoop["username"] . '</a>';
		
		$count = $resultLoop["count"];
		
		$str .= '<tr>
		<td>' . ($rank++) . '</td>
		<td>' . $trainer . '</td>
		<td>' . $count . '</td>
		</tr>';
	}
	
	$str .= '</tbody></table></div>';
	
	echo $str;
} else if($vbulletin->GPC['do'] == 'topdex') {
	// ###### GET VARIABLES ######
	if(isset($vbulletin->GPC['num']) && $vbulletin->GPC['num'] > 10){
		$b = clean_number($vbulletin->GPC['num'],250);
	} else{
		$b = 10;
	}
	if(isset($vbulletin->GPC['gen']) && $vbulletin->GPC['gen'] < 10){
		$gen = clean_number($vbulletin->GPC['gen'],10);
	} else{
		$gen = 1;
	}
    if($gen == 3) {
        $dex = 387;
    } else if($gen == 2) {
        $dex = 252;
    } else {
        $dex = 152;
    }

	// ###### QUERY VARIABLES ######
	
	
	
	// ###### MAIN CODE ######
	$str .= '<div class="cards_table">
	' . $h1 . '
	<table id="myTable" class="tablesorter">
		<thead>
		<tr>
			<th><font color=white>Rank</font></th>
			<th><font color=white>Trainer</font></th>
			<th><font color=white>Unique Number Owned</font></th>
		</tr>
		</thead>
		<tbody id="fbody">';
	$sqldate = ($spectime > 0) ? 'WHERE `post_thanks`.`date` > ' . $spectime : '';
	$result = $db->query_read("SELECT 
        	count(DISTINCT `poke_indv`.`monid`) AS `count`,
        	`poke_indv`.`userid` AS `userid`,
        	`user`.`username` AS `username`
        FROM `poke_indv` 
        LEFT JOIN `user`
        ON `poke_indv`.`userid` = `user`.`userid`
        WHERE `poke_indv`.`userid` <> 15
        AND `poke_indv`.`monid` < $dex
        GROUP BY `poke_indv`.`userid`
        ORDER BY `count` DESC
		LIMIT $b");
	$rank=1;
	
	while ($resultLoop = $db->fetch_array($result)) {
		$trainer = '<a href="https://forums.novociv.org/member.php?' . $resultLoop["userid"] . '">' . $resultLoop["username"] . '</a>';
		
		$count = $resultLoop["count"];
		
		$str .= '<tr>
		<td>' . ($rank++) . '</td>
		<td>' . $trainer . '</td>
		<td><a href="pokemon.php?section=home&do=dex&gen=' . $gen . '&user=' . $resultLoop["userid"] . '">' . $count . '</a></td>
		</tr>';
	}
	
	$str .= '</tbody></table></div>';
	
	echo $str;
} else {
	$str = '<a href="/pokemon.php?section=flex&do=toplvl">Top Pokemon</a><br>
	<a href="/pokemon.php?section=flex&do=topown">Most Owned</a><br>
	<a href="/pokemon.php?section=flex&do=topdex">Best Pokedex - Gen I</a><br>
	<a href="/pokemon.php?section=flex&do=topdex&gen=2">Best Pokedex - Gen II</a><br>
	<a href="/pokemon.php?section=flex&do=topdex&gen=3">Best Pokedex - Gen III</a><br>
	<a href="/pokemon.php?section=flex&do=stats&user=' . $userid . '">Coming Soon - User Stats</a>';
	echo $str;
}
} else {
echo "Nothing to see here.";
}
echo '</div>';