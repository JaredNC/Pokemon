<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=buy">Poke Mart</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<div class="tcg_body">
Pokemon start<br><br>';

// ############ QRY VARIABLES ############
$mqry = $db->query_read("SELECT * FROM `poke_item_master` WHERE 1");
while ($resultLoop = $db->fetch_array($mqry)) {
    $item[$resultLoop['itemid']]['name'] = $resultLoop['name'];
    $item[$resultLoop['itemid']]['cost'] = $resultLoop['cost'];
}

//important variables, already queried and ready to use
$cash = $vbulletin->userinfo[ucash];
$pokeballs = $vbulletin->userinfo[pokeballs];
$membergroups = explode(',',$vbulletin->userinfo[membergroupids]);


if(isset($_GET['do']) && $_GET['do'] == 'transact') {
	if(isset($_GET['action']) && $_GET['action'] == 'pokeballs') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'amount' => TYPE_INT
		));
		$amount = clean_number($vbulletin->GPC['amount'],500);
		$cost = $amount*50;
		$newcash = $cash - $cost;
		$newpoke = $pokeballs + $amount;
		if($cost<$cash && $vbulletin->GPC['auth'] == 'Oak') {
			$db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . ", 
				pokeballs = " . $newpoke . " 
			WHERE userid = " . $userid);
			$db->query_write("INSERT INTO 
				`specialbuy` 
				(userID, username, affecteduserID, affectedusername, transtype, transamount, time)
			VALUES 
				('$userid', '$username', '$userid', '$username', 'Buy Poke', '$amount', '" . time() . "')");
			echo 'Buying Pokeballs...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'porygon') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'amount' => TYPE_INT
		));
		$amount = clean_number($vbulletin->GPC['amount'],10);
		$cost = $amount*2000;
		$newcash = $cash - $cost;
		$newpoke = $pokeballs + $amount;
		if($cost<$cash && $vbulletin->GPC['auth'] == 'Oak') {
			$sh1 = (mt_rand(1,500) == 250) ? 1 : 0;
			$gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
            $db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . "
			WHERE userid = " . $userid);
			$vbulletin->db->query_write("INSERT INTO 
			    `poke_indv` 
    			(monid, userid, shiny, catch_date, gender) 
    		VALUES 
    			(137, " . $userid . ", " . $sh1 . ", " . time() . ", '" . $gender . "')");
			$db->query_write("INSERT INTO 
				`specialbuy` 
				(userID, username, affecteduserID, affectedusername, transtype, transamount, time)
			VALUES 
				('$userid', '$username', '$userid', '$username', 'Porygon', '$amount', '" . time() . "')");
			if($sh1 == 0) {
    			echo 'Buying Porygon...
    			<script type="text/javascript">
    			<!--
    			window.location = "/pokemon.php"
    			//-->
    			</script>';
			} else {
			    echo '<div class=tcgbody>You found a SHINY porygon!!!</div>';
			}
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'gen2') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
			'starter' => TYPE_INT
		));
		$starter = clean_number($vbulletin->GPC['starter'],160);
		$cost = 3000;
		$newcash = $cash - $cost;
		$membergroups[] = 81;
		$mems = implode(',',$membergroups);
		if($cost<$cash && $vbulletin->GPC['auth'] == 'Elm' && in_array($starter,array(152,155,158))) {
			$sh1 = (mt_rand(1,500) == 250) ? 1 : 0;
			$gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
            $db->query_write("UPDATE 
				`user` 
			SET 
				ucash = " . $newcash . ",
				membergroupids = '" . $mems . "'
			WHERE userid = " . $userid);
			$vbulletin->db->query_write("INSERT INTO 
			    `poke_indv` 
    			(monid, userid, shiny, catch_date, gender) 
    		VALUES 
    			(" . $starter . ", " . $userid . ", " . $sh1 . ", " . time() . ", '" . $gender . "')");
			$db->query_write("INSERT INTO 
				`specialbuy` 
				(userID, username, affecteduserID, affectedusername, transtype, transamount, time)
			VALUES 
				('$userid', '$username', '$userid', '$username', 'Gen2', '$amount', '" . time() . "')");
			echo 'Buying Gen II...
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		} else {
			echo 'You don\'t have enough money.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else if(isset($_GET['action']) && $_GET['action'] == 'item') {
		// ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'amount' => TYPE_INT,
	   		'item' => TYPE_INT
		));
		$amount = clean_number($vbulletin->GPC['amount'],500);
		$itemid = clean_number($vbulletin->GPC['item'],500);
		
		if($item[$itemid]['cost'] > 0) {
    		$cost = $amount*$item[$itemid]['cost'];
    		$newcash = $cash - $cost;
    		$newpoke = $pokeballs + $amount;
    		if($cost<$cash && $vbulletin->GPC['auth'] == 'Oak' && $amount > 0) {
    			$db->query_write("UPDATE 
    				`user` 
    			SET 
    				ucash = " . $newcash . " 
    			WHERE userid = " . $userid);
    			
    			for($i=0;$i<$amount;$i++) {
    			    $vals[] = '(' . $itemid. ',' . $userid . ',' . time() . ')';
    			}
    			$valstr = implode(',',$vals);
    			
    			$db->query_write("INSERT INTO 
    				`poke_items` 
    				(itemid, userid, purchase_date)
    			VALUES 
    				" . $valstr);
    			
    			$db->query_write("INSERT INTO 
    				`specialbuy` 
    				(userID, username, affecteduserID, affectedusername, transtype, transamount, time)
    			VALUES 
    				('$userid', '$username', '$userid', '$username', '" . $item[$itemid]['name'] . "', '$amount', '" . time() . "')");
    			echo 'Buying ' . $item[$itemid]['name'] .'...
    			<script type="text/javascript">
    			<!--
    			window.location = "/pokemon.php"
    			//-->
    			</script>';
    		} else {
    			echo 'You don\'t have enough money.
    			<script type="text/javascript">
    			<!--
    			window.location = "/pokemon.php"
    			//-->
    			</script>';
		    }
		} else {
		    echo 'Invalid Item.
			<script type="text/javascript">
			<!--
			window.location = "/pokemon.php"
			//-->
			</script>';
		}
	} else {
		echo 'Error.';
	}
} else if(isset($_GET['do']) && $_GET['do'] == 'buypokeballs') {
	$cash = number_format($cash, '2');
	$cost = 50;
	
	$str = '<div class=party><h1 class=party>Buy Pokeballs</h1><br>' . "\n" . '
	You have ' . $cash . ' pengos and the cost is ' . $cost . ' pengos per pokeball.<br>' . "\n" . '<br>
	<form class=buy action="pokemon.php?section=buy&do=transact&action=pokeballs" method="post">';
	
	$str .= 'Amount of pokeballs you are buying:<br><input name="amount" type="text" maxlength="5"><br>(Max 500)<br>
	<input type="hidden" name="auth" value="Oak" />
	<br><input type="submit" value="Buy Pokeballs" />
	</form>
	</div>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'buyporygon') {
	$cash = number_format($cash, '2');
	$cost = 2000;
	
	$str = '<div class=party><h1 class=party>Buy Porygon</h1><br>' . "\n" . '
	You have ' . $cash . ' pengos and the cost is ' . $cost . ' pengos per porygon.<br>' . "\n" . '<br>
	<form class=buy action="pokemon.php?section=buy&do=transact&action=porygon" method="post">';
	
	$str .= 'Amount of porygon you are buying:<br><input name="amount" type="text" maxlength="5"><br>(Max 10)<br>
	<input type="hidden" name="auth" value="Oak" />
	<br><input type="submit" value="Buy Porygon" />
	</form>
	</div>';
	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'buygen2') {
	if(!in_array(81,$membergroups)){
    	$cash = number_format($cash, '2');
    	$cost = 200;
    	
    	$str = '<div class=party><h1 class=party>Buy Johto Tourist Visa</h1><br>' . "\n" . '
    	You have ' . $cash . ' pengos and the cost is ' . $cost . ' pengos.<br>' . "\n" . '<br>
    	<form class=buy action="pokemon.php?section=buy&do=transact&action=gen2" method="post">
    	<select name="starter">
            <option value="152">Chikorita</option>
            <option value="155">Cyndaquil</option>
            <option value="158">Totodile</option>
        </select>
    	<input type="hidden" name="auth" value="Elm" />
    	<br><input type="submit" value="Buy Gen2" />
    	</form>
    	</div>';
	} else {
	    $str = '<div class=party>You already have access.</div>';
	}	
	echo $str;
} else if(isset($_GET['do']) && $_GET['do'] == 'item') {
    if(isset($_GET['item']) && $_GET['item'] > 0) {
    	// ############ CLEAN VARIABLES ############
    	$vbulletin->input->clean_array_gpc('g', array(
    		'item' => TYPE_INT
    	));
    	$itemid = clean_number($vbulletin->GPC['item'],500);
    	
    	if($item[$itemid]['cost'] > 0) {
        	$cash = number_format($cash, '2');
        	$cost = number_format($item[$itemid]['cost'], '2');
        	$name = $item[$itemid]['name'];
        	
        	$str = '<div class=party><h1 class=party>Buy ' . $name . '</h1><br>' . "\n" . '
        	You have ' . $cash . ' pengos and the cost is ' . $cost . ' pengos per ' . $name . '.<br>' . "\n" . '<br>
        	<form class=buy action="pokemon.php?section=buy&do=transact&action=item" method="post">';
        	
        	$str .= 'Amount of ' . $name . ' you are buying:<br><input name="amount" type="text" maxlength="5"><br>(Max 500)<br>
        	<input type="hidden" name="auth" value="Oak" />
        	<input type="hidden" name="item" value="' . $itemid . '" />
        	<br><input type="submit" value="Buy ' . $name . '" />
        	</form>
        	</div>';
        	
        	echo $str;
    	} else {
    	    echo 'Error';
    	}
    } else {
    	echo 'Invalid Item.
		<script type="text/javascript">
		<!--
		window.location = "/pokemon.php"
		//-->
		</script>';
    }
} else {
	$str = '<a href="/pokemon.php?section=buy&do=buypokeballs">Buy Poke Balls</a><br>';
	if(!in_array(81,$membergroups)){
	    $str .= '<a href="/pokemon.php?section=buy&do=buygen2">Buy Johto Tourist Visa</a><br>';
	}
	$str .= '<a href="/pokemon.php?section=buy&do=buyporygon">Buy Porygon</a><br>';
	for($i=1;$i<=count($item);$i++) {
	    $str .= '<a href="/pokemon.php?section=buy&do=item&item=' . $i . '">Buy ' . $item[$i]['name'] . '</a><br>';
	}
	echo $str;
}
} else {
echo "Nothing to see here.";
}
echo '</div>';