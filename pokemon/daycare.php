<?
//SET WHO CAN VIEW PAGE
$membergroups = explode(',',$vbulletin->userinfo[membergroupids]);
$cash = $vbulletin->userinfo[ucash];
$equip = $vbulletin->userinfo[poke_egg];
$banned = array(0,144,145,146,150,151,243,244,245,249,250,251);

if ($userid != 0 && usergroup != 8 && usergroup != 3 && usergroup != 53 && in_array(81,$membergroups))
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=daycare">Daycare</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo 'Pokemon start<br><br>';

$str .= '<div class="tcg_body"><a href="pokemon.php?section=daycare&do=admit">Admit a Pokemon</a> | <a href="pokemon.php?section=daycare&do=withdraw">Withdraw a Pokemon</a> 
        | <a href="pokemon.php?section=daycare&do=egg">Manage Eggs</a><br><br><br><a href="pokemon.php?section=daycare&do=abandon">Abandon a Pokemon :(</a></div>';

if(isset($_GET['do']) && $_GET['do'] == 'admit'){
    $cash = number_format($cash, '2');
	
	$str = '<div class=party><h1 class=party>Admit a Pokemon</h1><br>' . "\n" . '
	You have ' . $cash . ' pengos and the cost is 100 pengos to admit and 100 + 50 pengos per level to withdraw.<br>' . "\n" . '<br>
	<form class=buy action="pokemon.php?section=daycare&do=transact&action=admit" method="post">';
	
	$str .= 'ID of your pokemon:<br><input name="pokemon" type="text" maxlength="5"><br>
	<input type="hidden" name="auth" value="Day" />
	<br><input type="submit" value="Admit your Pokemon" />
	</form>
	</div>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'withdraw'){
    $str = '<div class="tcg_body">';
    $result = $db->query_read("
	    SELECT 
	        poke_indv.exp as 'exp',
	        poke_indv.level as 'lvl',
	        poke_indv.nick as 'nick',
            poke_mon.monname as 'name',
            poke_indv.indvid as 'id',
            poke_daycare.admit_date as 'date'
	    FROM 
	        poke_indv
        LEFT JOIN
            poke_mon
        ON
            poke_indv.monid = poke_mon.monid
	    INNER JOIN
	        poke_daycare
	    ON
	        poke_indv.indvid = poke_daycare.indvid
        WHERE 
            poke_daycare.userid = $userid
            AND poke_indv.userid = 1675
            AND poke_daycare.withdraw_date = 0");
    while ($resultLoop = $db->fetch_array($result)) {
        $nick = ($resultLoop['nick'] == '') ? $resultLoop['name'] : $resultLoop['nick'];
        $exp_gain = floor((time()-$resultLoop['date'])/(864*7));
		$new_exp = $resultLoop['exp'] + $exp_gain;
		$new_lvl = max(1,floor(sqrt($new_exp*2)));
		$lvl_gain = $new_lvl - $resultLoop['lvl'];
		$cost = ($lvl_gain*$cost+$cost);
        $str .= '<a href="https://forums.novociv.org/pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop['id'] . '">
                ' . $nick . '</a> level ' . $new_lvl . '(+' . $lvl_gain . ') 
                <a href="https://forums.novociv.org/pokemon.php?section=daycare&do=transact&action=withdraw&pokemon=' . $resultLoop['id'] . '">Withdraw This Pokemon</a><br>';
    }
    $str .= '</div>';
    echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'abandon'){
    $str = '<div class="tcg_body">';
    $result = $db->query_read("
	    SELECT 
	        poke_indv.exp as 'exp',
	        poke_indv.level as 'lvl',
	        poke_indv.nick as 'nick',
            poke_mon.monname as 'name',
            poke_indv.indvid as 'id',
            poke_daycare.admit_date as 'date'
	    FROM 
	        poke_indv
        LEFT JOIN
            poke_mon
        ON
            poke_indv.monid = poke_mon.monid
	    INNER JOIN
	        poke_daycare
	    ON
	        poke_indv.indvid = poke_daycare.indvid
        WHERE 
            poke_daycare.userid = $userid
            AND poke_indv.userid = 1675
            AND poke_daycare.withdraw_date = 0");
    while ($resultLoop = $db->fetch_array($result)) {
        $nick = ($resultLoop['nick'] == '') ? $resultLoop['name'] : $resultLoop['nick'];
        $exp_gain = floor((time()-$resultLoop['date'])/(864*7));
		$new_exp = $resultLoop['exp'] + $exp_gain;
		$new_lvl = max(1,floor(sqrt($new_exp*2)));
		$lvl_gain = $new_lvl - $resultLoop['lvl'];
		$cost = ($lvl_gain*$cost+$cost);
        $str .= '<a href="https://forums.novociv.org/pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop['id'] . '">
                ' . $nick . '</a> level ' . $new_lvl . '(+' . $lvl_gain . ') 
                <a href="https://forums.novociv.org/pokemon.php?section=daycare&do=transact&action=abandon&pokemon=' . $resultLoop['id'] . '">Abandon This Pokemon</a><br>';
    }
    $str .= '</div>';
    echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'egg'){
    $str = '<div class="tcg_body"><h1><u>UNCLAIMED EGGS:</u></h1><br>';
    $str_own = '<div class="tcg_body"><h1><u>OWNED EGGS:</u></h1><br>';

    $result = $db->query_read("
	    SELECT
	        MIN(poke_egg.egg_id) AS 'egg_id',
	        MIN(poke_egg.monid) AS 'monid',
	        COUNT(poke_egg.egg_id) AS 'count',
	        poke_mon.monname AS 'name'
	    FROM
	        poke_egg
        LEFT JOIN
            poke_mon
        ON
            poke_egg.monid = poke_mon.monid
	    WHERE 
            poke_egg.ownerid = $userid
            AND poke_egg.userid = 1675
            AND poke_egg.hatch_date = 0
        GROUP BY
            poke_egg.monid");
    while ($resultLoop = $db->fetch_array($result)) {
        $name = $resultLoop['name'];
        $egg_id = $resultLoop['egg_id'];
        $monid = $resultLoop['monid'];
        $count = $resultLoop['count'];
        $str .= '<a href="/pokemon.php?section=pokemon&do=view&pokemon=' . $monid . '">' . $name . '</a> Egg. Owned: (' . $count . ') - 
                    (<a href="/pokemon.php?section=daycare&do=transact&action=egg_take&egg=' . $egg_id . '">Claim this Egg for 50 pengos</a>) - 
                    (<a href="/pokemon.php?section=daycare&do=transact&action=egg_break&egg=' . $egg_id . '">Break this Egg</a>)<br>';
    }
    $result2 = $db->query_read("
	    SELECT
	        poke_egg.egg_id,
	        poke_egg.monid,
	        poke_egg.userid,
	        poke_egg.mom_id,
	        poke_egg.steps,
	        poke_mon.monname,
	        poke_indv.nick 
	    FROM
	        poke_egg
        LEFT JOIN
            poke_mon
        ON
            poke_egg.monid = poke_mon.monid
	    LEFT JOIN
            poke_indv
        ON
            poke_egg.mom_id = poke_indv.indvid
	    WHERE 
            poke_egg.ownerid = $userid
            AND poke_egg.userid <> 1675
            AND poke_egg.hatch_date = 0
        ORDER BY
            poke_egg.monid ASC");
    while ($resultLoop = $db->fetch_array($result2)) {
        if($resultLoop['steps'] < 50) {
            $egg_status = 1;
        } else if($resultLoop['steps'] < 100) {
            $egg_status = 2;
        } else if($resultLoop['steps'] < 150) {
            $egg_status = 3;
        } else if($resultLoop['steps'] < 200) {
            $egg_status = 4;
        } else {
            $egg_status = 5;
        }
        $name = $resultLoop['monname'];
        $egg_id = $resultLoop['egg_id'];
        $monid = $resultLoop['monid'];
        $nick = ($resultLoop['nick'] == '') ? 'No Nickname' : $resultLoop['nick'];
        $mom = '<a href="/pokemon.php?section=pokemon&do=view2&pokemon=' . $resultLoop['mom_id'] . '">' . $nick . '</a>';
        if(in_array($monid,$banned)) {
            $monid = 0;
            $name = '<b>Unknown</b>';
            $mom = 'Unknown';
        }
        if($egg_id == $equip) {
            $str4 = '<a href="/pokemon.php?section=pokemon&do=view&pokemon=' . $monid . '">' . $name . '</a> Egg. Mother: ' . $mom . ' - 
                    <b>Your Active Egg</b> <img alt="More Info!" class=more src="images/icons/icon1.png" onclick="moreinfo(' . $egg_status . ')" /><br>';
        } else if($monid == 0) {
            $str3 = '<a href="/pokemon.php?section=pokemon&do=view&pokemon=' . $monid . '">' . $name . '</a> Egg. Mother: ' . $mom . ' - 
                    (<a href="/pokemon.php?section=daycare&do=transact&action=egg_equip&egg=' . $egg_id . '">Equip This Egg</a>) 
                    (<a href="/pokemon.php?section=daycare&do=gift&egg=' . $egg_id . '">Gift this Egg</a>)<br>';
        } else {
            $str2 .= '<a href="/pokemon.php?section=pokemon&do=view&pokemon=' . $monid . '">' . $name . '</a> Egg. Mother: ' . $mom . ' - 
                    (<a href="/pokemon.php?section=daycare&do=transact&action=egg_equip&egg=' . $egg_id . '">Equip This Egg</a>) 
                    (<a href="/pokemon.php?section=daycare&do=gift&egg=' . $egg_id . '">Gift this Egg</a>)<br>';
        }
    }
    $str .= '</div>';
    $str2 .= $str3;
    $str_own .= $str4 . $str2;
    $str_own .= '</div>';
    echo $str;
    echo $str_own;
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
    echo '<div class="tcg_body"><a href="/pokemon.php?section=daycare&do=transact&action=egg_buy">Buy Mystery Egg for 10 Gacha Tokens</a> (You have ' . $gacha_count . ' gacha tokens.)</div>';
    
} else if(isset($_GET['do']) && $_GET['do'] == 'gift'){
    // ############ GET VARIABLES ############
	//'g' means it's GET data
	$vbulletin->input->clean_array_gpc('g', array(
		'egg' => TYPE_INT
	));
	$egg_id = clean_number($vbulletin->GPC['egg'],99999);
	
    $str = '<div class="tcg_body"><h1><u>GIFT EGG:</u></h1><br>
        <form class=buy action="/pokemon.php?section=daycare&do=transact&action=egg_give" method="post">
            ID of Egg:<br>
            <input name="egg" type="text" maxlength="5" value="' . $egg_id . '"><br><br>
        	ID of User:<br>
            <input name="user" type="text" maxlength="5"><br><br>
        	<br><input type="submit" value="Give Egg" />
    	</form>
    </div>
    ';
    echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'transact'){
	if(isset($_GET['action']) && $_GET['action'] == 'admit') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
			'pokemon' => TYPE_INT
		));
		$pokemon = clean_number($vbulletin->GPC['pokemon'],99999);
		$cost = 100;
		$newcash = $cash - $cost;
		
		// ############ CHECK IF pokemon EXISTS ############
    	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_indv WHERE indvid = $pokemon AND userid = $userid) AS 'Exists'");
		
		if($cost<$cash && $vbulletin->GPC['auth'] == 'Day' && $exists['Exists'] == true) {
			$db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . "
			WHERE userid = " . $userid);
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				userid = 1675
			WHERE 
			    indvid = " . $pokemon);
			$db->query_write("INSERT INTO 
				`poke_daycare` 
				(indvid, userid, admit_date)
			VALUES 
				('$pokemon', '$userid', '" . time() . "')");
			echo 'Admitting Pokemon...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'withdraw') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'pokemon' => TYPE_INT
		));
		$pokemon = clean_number($vbulletin->GPC['pokemon'],99999);
		$cost = 50;
		
		// ############ CHECK IF pokemon EXISTS ############
    	$result = $db->query_first("
    	    SELECT 
    	        poke_indv.exp as 'exp',
    	        poke_indv.level as 'lvl',
    	        poke_indv.nick as 'nick',
                poke_daycare.daycareid as 'id',
                poke_daycare.admit_date as 'date'
    	    FROM 
    	        poke_indv
    	    INNER JOIN
    	        poke_daycare
    	    ON
    	        poke_indv.indvid = poke_daycare.indvid
            WHERE 
                poke_daycare.indvid = $pokemon
                AND poke_daycare.userid = $userid
                AND poke_indv.userid = 1675
                AND poke_daycare.withdraw_date = 0");
                
		$exp_gain = floor((time()-$result['date'])/(864*7));
		$new_exp = $result['exp'] + $exp_gain;
		$new_lvl = max(1,floor(sqrt($new_exp*2)));
		$lvl_gain = $new_lvl - $result['lvl'];
		
		$cost = ($lvl_gain*$cost+$cost);
		$newcash = $cash-$cost;
		
		/*
		echo '
			exp gain: ' . $exp_gain . '
			<br>current: ' . $result['exp'] . ' new: ' . $new_exp . '
			<br>
			<br>lvl gain: ' . $lvl_gain . '
			<br>current: ' . $result['lvl'] . ' new: ' . $new_lvl . '
			<br>cost: ' . $cost . '
			';
		*/	
		
		if($cost<$cash && $result) {
			
			$db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . "
			WHERE userid = " . $userid);
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				userid = " . $userid . ",
				exp = " . $new_exp . ",
				level = " . $new_lvl . "
			WHERE 
			    indvid = " . $pokemon);
			$db->query_write("UPDATE 
				`poke_daycare` 
			SET 
				withdraw_date = " . time() . "
			WHERE 
			    daycareid = " . $result['id']);
			echo 'Withdrawing Pokemon...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
			
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'abandon') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'pokemon' => TYPE_INT
		));
		$pokemon = clean_number($vbulletin->GPC['pokemon'],99999);
		
		// ############ CHECK IF pokemon EXISTS ############
    	$result = $db->query_first("
    	    SELECT 
    	        poke_daycare.daycareid as 'id'
    	    FROM 
    	        poke_daycare
    	    WHERE 
                poke_daycare.indvid = $pokemon
                AND poke_daycare.userid = $userid
                AND poke_daycare.withdraw_date = 0");
                
		if($result) {
			$db->query_write("UPDATE 
				`poke_indv` 
			SET 
				userid = 15
			WHERE 
			    indvid = " . $pokemon);
			$db->query_write("UPDATE 
				`poke_daycare` 
			SET 
				withdraw_date = " . time() . "
			WHERE 
			    daycareid = " . $result['id']);
			echo 'Abandoning Pokemon...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
			
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare"
			//-->
			</script>';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'egg_give') {
	    // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'egg' => TYPE_INT,
			'user' => TYPE_INT
		));
		$egg_id = clean_number($vbulletin->GPC['egg'],9999);
		$getter = clean_number($vbulletin->GPC['user'],9999);
		
		$exists1 = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_egg WHERE egg_id = $egg_id AND userid = $userid) AS 'Exists'");
		$exists2 = $db->query_first("SELECT EXISTS(SELECT 1 FROM user WHERE userid = $getter) AS 'Exists'");
		
		if($exists1['Exists'] == true && $exists2['Exists'] == true && $equip != $egg_id){
		    $db->query_write("UPDATE 
				`poke_egg` 
			SET 
				userid = " . $getter . ",
				ownerid = " . $getter . "
			WHERE 
			    egg_id = " . $egg_id);
			// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   'You\'ve received an egg!'; 
				$pmgave = '[url="https://forums.novociv.org/member.php?' . $userid . '"]' . $username . '[/url]';
				$pmtext     =   $pmgave . ' sent you an egg. View at [url=https://forums.novociv.org/pokemon.php?section=daycare&do=egg]Daycare[/url].';
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$getter'"));
				
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
			echo 'Giving Egg...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
		} else {
		    echo 'Bad egg, bad user, or maybe you have the egg equipped?';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'egg_take') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'egg' => TYPE_INT
		));
		$egg_id = clean_number($vbulletin->GPC['egg'],9999);
		$cost = 50;
		$newcash = $cash-$cost;
		
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_egg WHERE egg_id = $egg_id AND ownerid = $userid AND userid = 1675) AS 'Exists'");
		
		if($cost<$cash && $exists['Exists'] == true) {
			$db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . "
			WHERE userid = " . $userid);
			$db->query_write("UPDATE 
				`poke_egg` 
			SET 
				userid = " . $userid . ",
				ownerid = " . $userid . "
			WHERE 
			    egg_id = " . $egg_id);
			echo 'Claiming Egg...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
			
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'egg_break') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'egg' => TYPE_INT
		));
		$egg_id = clean_number($vbulletin->GPC['egg'],9999);
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_egg WHERE egg_id = $egg_id AND ownerid = $userid AND userid = 1675) AS 'Exists'");
		
		if($exists['Exists'] == true) {
			$db->query_write("UPDATE 
				`poke_egg` 
			SET 
				userid = 15,
				ownerid = 15
			WHERE 
			    egg_id = " . $egg_id);
			echo 'Breaking Egg...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
			
		} else {
			echo 'You don\'t own that egg.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'egg_equip') {
		// ############ GET VARIABLES ############
		//'g' means it's GET data
		$vbulletin->input->clean_array_gpc('g', array(
			'egg' => TYPE_INT
		));
		$egg_id = clean_number($vbulletin->GPC['egg'],9999);
		
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_egg WHERE egg_id = $egg_id AND userid = $userid) AS 'Exists'");
		
		if($exists['Exists'] == true) {
			$db->query_write("UPDATE 
				`user` 
			SET 
				poke_egg = " . $egg_id . "
			WHERE userid = " . $userid);
			echo 'Equiping Egg...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
			
		} else {
			echo 'Not your egg.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
		}
		
	} else if(isset($_GET['action']) && $_GET['action'] == 'egg_buy') {
		$cost = 10;
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
    	
    	$banstr = implode("','",$banned);
    	
		$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_egg WHERE monid IN('" . $banstr . "') AND userid = $userid) AS 'Exists'");
		
		if($exists['Exists'] == false && $gacha_count > $cost-1) {
		    $monid = poke_item_roll(1);
		    $db->query_write("UPDATE 
        		`poke_items` 
        	SET 
        		use_date = " . time() . "
        	WHERE 
        		itemid = 8
        		AND use_date = 0
        		AND userid = " . $userid . "
        	LIMIT " . $cost);
        	
        	$db->query_write("INSERT INTO poke_egg
        		(monid, ownerid, userid, catch_date, mom_id)
        	VALUES
        		(" . $monid . ", " . $userid . ", " . $userid . ", " . time() . ", 0)");
			echo 'Buying Egg...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
			
		} else {
			
			echo 'You don\'t have enough tokens.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php?section=daycare&do=egg"
			//-->
			</script>';
			
			/*
			print_r($banned);
			echo $banstr;
			echo '<br>' . "INSERT INTO poke_egg
        		(monid, ownerid, userid, catch_date, mom_id)
        	VALUES
        		(" . $monid . ", " . $userid . ", " . $userid . ", " . time() . ", 0)";
        	echo '<br>' . "SELECT EXISTS(SELECT 1 FROM poke_egg WHERE monid IN('" . $banstr . "') AND userid = $userid) AS 'Exists'";
        	*/
		}
		
	} else {
		echo 'Error.';
	}
} else {
	echo $str;
}
//USER CAN'T VIEW PAGE
} else {
	echo "You are not in Johto!";
}
?>