<?
//SET WHO CAN VIEW PAGE
if ($userid != 0 && usergroup != 8 && usergroup != 3 && usergroup != 53)
//if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53 && $userposts >= 100)
{
	// ############ CLEAN VARIABLES ############
	$vbulletin->input->clean_array_gpc('g', array(
		'trade' => TYPE_INT
	));
	$trade = clean_number($vbulletin->GPC['trade'],5000);
	
	// ############ CHECK IF TRADE EXISTS ############
	$exists = $db->query_first("SELECT EXISTS(SELECT 1 FROM poke_trade WHERE tradeid = $trade) AS 'Exists'");
	
	// ############ MAIN CODE ############
	if($exists['Exists'] == true) {
		$qrytime = time();
		$result = $db->query_first("SELECT
			`poke_trade`.`user1id` AS '1_id',
			`poke_trade`.`user2id` AS '2_id',
			`poke_trade`.`user1offer` AS '1_offer',
			`poke_trade`.`user2offer` AS '2_offer'
		FROM 
			`poke_trade`
		LEFT JOIN `user` AS `user1`
			ON (`poke_trade`.`user1id` = `user1`.`userid`)
		LEFT JOIN `user` AS `user2`
			ON (`poke_trade`.`user2id` = `user2`.`userid`)
		WHERE
			`poke_trade`.`tradeid` = $trade");
		//FIRST ESTABLISH WHO IS TOP AND WHO IS BOTTOM
		if($result["1_id"] == $userid) {
			$you = 1;
			$they = 2;
		} else {
			$you = 2;
			$they = 1;
		}
		$u1_offer = decode_offer($result[$you . "_offer"]);
	}
	$owned_cards_array = grab_poke_info(owned_poke($userid));
	$counter = 0;
	$cards = (empty($u1_offer["card"])) ? array() : $u1_offer["card"];
	foreach($owned_cards_array as $key => $value) {
		$counter++;
		$foil = ($value["foil"] == 0) ? 'foil0' : 'foil1';
		$selected = (in_array($key,$cards)) ? 'is_selected ' : 'not_selected';
		$checked = (in_array($key,$cards)) ? 'checked' : '';
		$nick = ($value["nick"] == '') ? $value["name"] : $value["nick"];
		$cardimg = '<img class="deck' . $foil . '" src="pokemon/images/monimages/600px-' . str_pad($value["masterid"] , 3 , "0" , STR_PAD_LEFT) . $value["name"] . '.png" /><br>
		<a href="pokemon.php?section=pokemon&do=view2&pokemon=' . $key . '">' . $nick . '</a> Lv. ' . $value["level"];
		$user_owned_cards .= '
		<div class="' . $selected . '">
			<div class="checkbox_wrapper">
				<label class="deck_card" for="cb' . $counter . '">
					' . $cardimg . '
					<div class="checkboxdiv">
						<input name="ids[]" id="cb' . $counter . '" type="checkbox" value="' . $key . '" ' . $checked . '/>
					</div>
				</label>
			</div>
		</div>';
	}
	$str .= 'BELOW ARE THE POKEMON YOU OWN AND THEIR RESPECTIVE IDS
	    <div id="contentcards">
		' . $user_owned_cards . '
	    </div>
	    <script type="text/javascript">
		jQuery(\'input[type="checkbox"]\').click(function () {
		    var days = jQuery(\'input[type="checkbox"]:checked\').length;
		    if (days == 10) {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', true);
		    } else {
		        jQuery(\'input[type="checkbox"]\').not(\':checked\').prop(\'disabled\', false);
		    }
		    document.getElementById("countchecked").innerHTML=days;
		})
		jQuery(\'input[name="other_1"]\').change(function () {
		    jQuery(\'input[type="checkbox"]\').prop({
		        \'checked\': false,
		            \'disabled\': false
		    });
		});
		jQuery(\'.checkbox_wrapper\').on(\'click\', function(e){ 
		var checked = jQuery(this).find(\'input[type="checkbox"]\').is(":checked");
		if(checked){
			jQuery(this).parent().addClass(\'is_selected\').removeClass(\'not_selected\');
		}else{
		        jQuery(this).parent().addClass(\'not_selected\').removeClass(\'selected\');
		}                        
		});
	    </script>';
	echo $str;
//USER CAN'T VIEW PAGE
} else {
	echo "Nothing to see here.";
}
?>