<?php



error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))

{

	exit;

}



// ################################# HILL REWARDS #####################################
$qry = "SELECT 
    poke_indv.indvid as 'indvid',
    poke_indv.monid as 'monid',
    poke_indv.nick as 'nick',
    poke_daycare.userid as 'userid',
    poke_daycare.admit_date as 'date'
FROM 
    poke_indv 
INNER JOIN
    poke_daycare
ON
    poke_indv.indvid = poke_daycare.indvid
WHERE 
poke_indv.gender LIKE 'F' 
AND poke_indv.userid = 1675
AND monid IN(
        SELECT 
            poke_indv.monid 
        FROM
            poke_indv 
        WHERE
            poke_indv.gender LIKE 'M' 
            AND poke_indv.userid = 1675    
        GROUP BY 
            poke_indv.monid) 
ORDER BY `poke_indv`.`monid`  DESC";

$result = $vbulletin->db->query_read($qry);
$banned = array(0,144,145,146,150,151,243,244,245,249,250,251);
while ($resultLoop = $vbulletin->db->fetch_array($result)) {
    if($resultLoop['date'] < (time() - 60*60*24) && mt_rand(1,3) == 1 && !in_array($resultLoop['monid'],$banned)){
        $evo = 1;
        $monid = $resultLoop['monid'];
        while($evo==1){
            $res = $vbulletin->db->query_first("SELECT monid FROM poke_evo WHERE evo_monid = " . $monid);
            if($res['monid'] > 0) {
                $monid = $res['monid'];
            } else {
                $evo = 0;
            }
        }
        $vbulletin->db->query_write("
        	INSERT INTO poke_egg
        		(monid, ownerid, catch_date, mom_id)
        	VALUES
        		(" . $monid . ", " . $resultLoop['userid'] . ", " . time() . ", " . $resultLoop['indvid'] . ")
        ");
        // Setup Auto Private Message
    	$pmfromid = 1675; // Pokemon Daycare
    	// Send Private Message
    	if ($pmfromid) {
    		require_once('./includes/class_dm.php'); 
    		require_once('./includes/class_dm_pm.php'); 
    		//pm system 
    		$pmSystem   =   new vB_DataManager_PM( $vbulletin ); 
    		//pm Titel / Text 
    		$nick = ($resultLoop['nick'] == '') ? 'No Nickname' : $resultLoop['nick'];
    		$pmtitle    =   "Your pokemon has laid an egg!"; 
    		$pmtext     =   "[url=https://forums.novociv.org/pokemon.php?section=pokemon&do=view2&pokemon=" . $resultLoop['indvid'] . "]Your pokemon " . $nick . "[/url] has laid an egg at the daycare! 
    		                    Please visit [url=https://forums.novociv.org/pokemon.php?section=daycare&do=egg]The Daycare[/url] to pick it up! Eggs cost 50 pengos to recover.";
    		$pmfromname = 'Pokemon Daycare';           
    		$finduser = $vbulletin->db->fetch_array($vbulletin->db->query_read("SELECT username FROM user where userid=" . $resultLoop['userid']));
    		
    		$pmSystem->verify_message( $pmtext ); 
    		$pmSystem->verify_title( $pmtitle ); 
    		
    		//Set the fields 
    		$pmSystem->set('fromuserid', $pmfromid); 
    		$pmSystem->set('fromusername', $pmfromname); 
    		$pmSystem->set('title', $pmtitle); 
    		$pmSystem->set('message', $pmtext); 
    		$pmSystem->set('dateline', TIMENOW); 
    		$pmSystem->set('iconid', 4);
    			$pmSystem->set_recipients($finduser['username'], $botpermissions);
    		
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
    }
}


log_cron_action('', $nextitem, 1);







?>