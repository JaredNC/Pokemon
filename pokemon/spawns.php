<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=pokemon">Pokemon</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo 'Pokemon start<br><br>';

if(isset($_GET['do']) && $_GET['do'] == 'admin' && ($usergroup == 6 || $usergroup == 29)){
    if(isset($_GET['action']) && $_GET['action'] == 'give') {
        // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'note' => TYPE_NOHTML,
	   		'poke' => TYPE_INT,
	   		'user' => TYPE_INT
		));
		$poke = clean_number($vbulletin->GPC['poke'],999);
        $user = clean_number($vbulletin->GPC['user'],9999);
        $note = $db->escape_string($vbulletin->GPC['note']);
        $mqry = $db->query_first("SELECT * FROM `poke_mon` WHERE monid = " . $poke);
        $uqry = $db->query_first("SELECT username FROM `user` WHERE userid = " . $user);
        
        $error = false;
        if($vbulletin->GPC['auth'] != 'Kaos') {
            $error = true;
        }
        if($mqry['monname'] == '') {
            $error = true;
        }
        if($uqry['username'] == '') {
            $error = true;
        }
        
        if(!$error) {
            $shiny = (mt_rand(0,500) == 2) ? 1 : 0;
            if($shiny == 1) {
                $shiny1 = '[g=yellow]SHINY[/g] [highlight] ';
                $shiny2 = ' [/highlight]';
            }
            $gender = (mt_rand(1,2) == 1) ? 'M' : 'F';
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' a ' . $shiny1 . '[url="https://forums.novociv.org/pokemon.php?section=pokemon&do=view&pokemon=' . $poke . '"]' . $mqry['monname'] . '[/url]' . $shiny2 . '. 
            	    
        	[img]https://forums.novociv.org/pokemon/images/monimages/600px-' . str_pad($poke , 3 , "0" , STR_PAD_LEFT) . $mqry['monname'] . '.png[/img]
        	
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            $vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "poke_indv 
        			(monid, userid, shiny, catch_date, gender) 
        		VALUES 
        			(" . $poke . ", " . $user . ", " . $shiny . ", " . time() . ", '" . $gender . "')");
        	// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   'Pokemon Given to you by Admin'; 
				$pmtext     = $a;
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$user'"));
				
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
            echo '<div class=tcg_body>Success! ' . $mqry['monname'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else {
            echo 'Error: ' . $mqry['monname'] . ' ' . $uqry['username'] . ' ' . $username;
        }
        
        
    } else if(isset($_GET['action']) && $_GET['action'] == 'givei') {
        // ############ POST VARIABLES ############
		//'p' means it's POST data
		$vbulletin->input->clean_array_gpc('p', array(
			'auth' => TYPE_NOHTML,
	   		'note' => TYPE_NOHTML,
	   		'poke' => TYPE_INT,
	   		'amount' => TYPE_INT,
	   		'user' => TYPE_INT
		));
		$poke = clean_number($vbulletin->GPC['poke'],999);
        $amount = clean_number($vbulletin->GPC['amount'],999);
        $user = clean_number($vbulletin->GPC['user'],9999);
        $note = $db->escape_string($vbulletin->GPC['note']);
        $mqry = $db->query_first("SELECT * FROM `poke_item_master` WHERE itemid = " . $poke);
        $uqry = $db->query_first("SELECT username FROM `user` WHERE userid = " . $user);
        
        $error = false;
        if($vbulletin->GPC['auth'] != 'Kaos') {
            $error = true;
        }
        if($mqry['name'] == '' && $poke != 999) {
            $error = true;
        }
        if($uqry['username'] == '') {
            $error = true;
        }
        if($amount <= 0 || $amount > 25) {
            $error = true;
        }
        
        if(!$error && $poke != 999) {
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' ' . $amount . ' ' . $mqry['name'] . '. 
            	    
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            
            for($i=0;$i<$amount;$i++) {
			    $vals[] = '(' . $poke . ',' . $user . ',' . time() . ')';
			}
			$valstr = implode(',',$vals);
			
			$db->query_write("INSERT INTO 
				`poke_items` 
				(itemid, userid, purchase_date)
			VALUES 
				" . $valstr);
			// Setup Auto Private Message
			$pmfromid = 15; // Sexbot
			// Send Private Message
			if ($pmfromid) {
				require_once('./includes/class_dm.php'); 
				require_once('./includes/class_dm_pm.php'); 
				//pm system 
				$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
				//pm Titel / Text 
				$pmtitle    =   'Item Given to you by Admin'; 
				$pmtext     = $a;
				$pmfromname = 'Sexbot';           
				$finduser = $db->fetch_array($db->query_read("SELECT * FROM " . TABLE_PREFIX . "user where userid='$user'"));
				
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
            echo '<div class=tcg_body>Success! ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else if(!$error && $poke == 999) {
            $a = '[url="member.php?' . $userid . '"]' . $username . '[/url] just gave ' . $uqry['username'] . ' ' . $amount . ' pokeballs. 
            	    
        	Note: ' . $note;
        	$vbulletin->db->query_write("INSERT INTO " . TABLE_PREFIX . "post 
        		(threadid, username, userid, dateline, pagetext, visible) 
        	VALUES 
        		(1053765, 'Sexbot', 15, " . time() . ", '" . $vbulletin->db->escape_string($a) . "', 1)");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET posts = posts+1 WHERE userid = 15");
        	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET replycount = replycount+1, lastpost = " . time() . ", lastposter = 'Sexbot', lastposterid = 15 WHERE threadid = 1053765");
            
            $db->query_write("UPDATE 
				`user` 
			SET 
				pokeballs = pokeballs+" . $amount . " 
			WHERE userid = " . $user);
            echo '<div class=tcg_body>Success! ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username . '</div>';
        } else {
            echo 'Error: ' . $amount . ' ' . $mqry['name'] . ' ' . $uqry['username'] . ' ' . $username;
        }
        
        
    } else {
        $forums = make_spawn_array();
        $str .= '<div class=tcg_body>';
        
        $fqry = $db->query_read("SELECT `title`, `forumid` FROM `forum`");
        while ($resultLoop = $db->fetch_array($fqry)) {
            $forum_names[$resultLoop['forumid']] = $resultLoop['title'];
        }
        foreach($forums as $value) {
            $str .= '<a href="pokemon.php?section=spawns&do=forum&forum=' . $value . '">' . $forum_names[$value] . '</a><br><br>';
        }
        
        $str .= '</div>';
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