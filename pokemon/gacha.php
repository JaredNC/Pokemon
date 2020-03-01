<?
//SET WHO CAN VIEW PAGE
if ($userid != 0 && usergroup != 8 && usergroup != 3 && usergroup != 53)
//if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53 && $userposts >= 100)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=gacha">Gacha Machine</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo 'Pokemon start<br><br>';

$str .= '<div class="tcg_body"><a href="pokemon.php?section=gacha&machine=1">Base Machine (1 Token)</a> | <a href="pokemon.php?section=gacha&machine=2">Super Machine (3 Tokens)</a>
 | <a href="pokemon.php?section=gacha&machine=3">Gen II Machine (1 Tokens)</a> | <a href="pokemon.php?section=gacha&machine=4">Gen III Machine (1 Token)</a></div>';

if(isset($_GET['do']) && $_GET['do'] == 'roll'){
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'machine' => TYPE_INT
	));
    // ###### GET VARIABLES ######
	if(isset($vbulletin->GPC['machine']) && $vbulletin->GPC['machine'] > 1){
		$machine = clean_number($vbulletin->GPC['machine'],10);
	} else{
		$machine = 1;
	}
	$error = false;
	
	// ############ CHECK IF Machine EXISTS ############
// 	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
//     if($exists['Exists'] == false) {
//         $error = true;
//     }
    
    if($machine == 2) {
	    $cost = 3;
	} else if($machine == 1) {
	    $machine = 1;
	    $cost = 1;
	} else if($machine == 3) {
	    $cost = 1;
    } else if($machine == 4) {
        $cost = 1;
    } else {
	    $error = true;
	}
    
    // ############ CHECK IF token EXISTS ############
	$itemqry = "SELECT 
		count(`indv_item_id`) AS 'count'
	FROM 
		`poke_items`
	WHERE  
		`userid`=$userid
		AND `use_date`=0
		AND `itemid`=8";
	$itemresult = $db->query_first($itemqry);
	$gacha_count = $itemresult["count"];
	
	if($gacha_count < $cost) {
        $error = true;
    }
    
    if(!$error) {
        $db->query_write("UPDATE 
    		`poke_items` 
    	SET 
    		use_date = " . time() . "
    	WHERE 
    		itemid = 8
    		AND use_date = 0
    		AND userid = " . $userid . "
    	LIMIT " . $cost);
    	$lines = file("pokemon/machines/" . $machine . ".txt", FILE_IGNORE_NEW_LINES);
        foreach($lines as $value) {
            $a = explode(',',$value);
            $gacha[$a[0]] = $a[1];
        }
    	$roll = explode(',',poke_gacha($gacha,$userid,$username));
    	$monid = $roll[0];
    	$mon = grab_mon_info(array($monid));
    	$name = $mon[$monid]["name"];
    	
    	$shiny = ($roll[1] == 1) ? 'SHINY ' : '';
    	
    	$str .= '<div class="tcg_body">
    	You just found a ' . $shiny . $name . '!<br>
    	<img src="pokemon/images/monimages/600px-' . str_pad($monid , 3 , "0" , STR_PAD_LEFT) . $name . '.png" /></div>';
    	
    	echo $str;
    } else {
		echo 'Something went wrong.
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php"
		//-->
		</script>';
	}
} else {
    // ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'machine' => TYPE_INT
	));
    // ###### GET VARIABLES ######
	if(isset($vbulletin->GPC['machine']) && $vbulletin->GPC['machine'] > 1){
		$machine = clean_number($vbulletin->GPC['machine'],10);
	} else{
		$machine = 1;
	}
	
	if($machine == 2) {
	    $cost = 3;
	} else if($machine == 3) {
	    $cost = 1;
    } else if($machine == 4) {
        $cost = 1;
    } else {
	    $machine = 1;
	    $cost = 1;
	}
    $itemqry = "SELECT 
		count(`indv_item_id`) AS 'count'
	FROM 
		`poke_items`
	WHERE  
		`userid`=$userid
		AND `use_date`=0
		AND `itemid`=8";
	$itemresult = $db->query_first($itemqry);
	$gacha_count = $itemresult["count"];
	$lines = file("pokemon/machines/" . $machine . ".txt", FILE_IGNORE_NEW_LINES);
    foreach($lines as $value) {
        $a = explode(',',$value);
        $gacha[$a[0]] = $a[1];
    }
    $sum = array_sum($gacha);
    $mons = grab_mon_info(array_keys($gacha));
    
        
	if($gacha_count > $cost-1) {
	    $str .= '<div class="tradelistactive">
	        You have ' . floor($gacha_count/$cost) . ' rolls available.<br>
	        <a href="pokemon.php?section=gacha&do=roll&machine=' . $machine . '">Roll the Machine?</a>
	    </div>';
        // poke_gacha($gacha,$userid,$username);
	} else {
	    $str .= '<div class="tradelistinactive">No rolls left<br>
	    <a href="pokemon.php?section=buy&do=item&item=8">Buy More?</a></div>';
	}
	$str .= '<div class="tcg_body">';
	foreach($gacha as $key => $value) {
        $perc = number_format(($value/$sum)*100, '2');
        $str .= '<a href="pokemon.php?section=pokemon&do=view&pokemon=' . $key . '">' . $mons[$key]['name'] . '</a>: ' . $perc . '%<br>';
    }
    $str .= '</div>';
	echo $str;
}
//USER CAN'T VIEW PAGE
} else {
	echo "Nothing to see here.";
}
?>