<?php
$microtime = microtime(true); // Gets microseconds
// Do not edit.
if (defined('VB_RELATIVE_PATH'))
{
    chdir('./' . VB_RELATIVE_PATH);
}
// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE); 

// #################### DEFINE IMPORTANT CONSTANTS ####################### 
define('THIS_SCRIPT', 'home');     // change this so you can use other conditionals like "THIS_PAGE" != "home" etc.. in other, real templates.
define('CSRF_PROTECTION', false);   // turn on for token layer security


// ################### PRE-CACHE TEMPLATES AND DATA ###################### 
// cache any templates you want  to use for this mod .

// get special phrase groups 
$phrasegroups = array(); 

// get special data templates from the datastore 
$specialtemplates = array(); 

// pre-cache templates used by all actions 
$globaltemplates = array('', 
); 

// pre-cache templates used by specific actions 
$actiontemplates = array(); 

// ######################### REQUIRE BACK-END ############################ 
// if your page is outside of your normal vb forums directory, you should change directories by uncommenting the next line 
// chdir ('/path/to/your/forums'); 
require_once('./global.php');  // vbulletin file, not uploaded
require_once('./includes/functions_custom.php');
require_once('./includes/functions_pokemon.php');
require_once('./includes/functions_user.php'); // vbulletin file, not uploaded
// ####################################################################### 
// ######################## START MAIN SCRIPT ############################ 
// ####################################################################### 

// ###### YOUR CUSTOM CODE GOES HERE ##### 
//appears in the <title> tags in the head
$pagetitle = 'Pokemon'; 

// ###### NOW YOUR TEMPLATE IS BEING RENDERED ###### 

// register your templates
$templater = vB_Template::create('TEST'); 
$templater->register_page_templates(); 
$templater->register('header', $header); 
$templater->register('headinclude', $headinclude); 
$templater->register('navbar', $navbar); 
$templater->register('footer', $footer); 
$templater->register('pagetitle', $pagetitle); 
// 
//important variables, already queried and ready to use
$userid			=	$vbulletin->userinfo[userid];
$username		=	$vbulletin->userinfo[username];
$usergroup		=	$vbulletin->userinfo[usergroupid];
$userposts			=	$vbulletin->userinfo[posts];
$pokeballs = $vbulletin->userinfo[pokeballs];
$userwealth		=	$vbulletin->userinfo[($vbulletin->options['market_point_name'])];
$cashname		=	$vbulletin->options['market_point'];
$avatarrevision	=	$vbulletin->userinfo[avatarrevision];


if($_GET["foo"] != 'bar') {
// your own custom head and css files
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html dir="ltr" lang="en"> <head> 
'.$headinclude.' 
 <title>'.$pagetitle.'</title>  
   <link href="/pokemon/css/style.css" rel="stylesheet" type="text/css" /> 
  <style type="text/css"> 
<!-- 
.advertisement {display: none !important} 
--> 
</style> 
<script type="text/javascript">
function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}
function moreinfo(id) {
  var myArray = new Array();
  myArray[1] = \'The Egg is cold to the touch.\';
  myArray[2] = \'The Egg is lukewarm.\';
  myArray[3] = \'The Egg is warm to the touch.\';
  myArray[4] = \'The Egg has a few cracks.\';
  myArray[5] = \'The Egg will hatch any day now.\';
  
  alert(myArray[id]);
}
</script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script type="text/javascript" src="https://forums.novociv.org/cards/javascript.js"></script>
<script type="text/javascript" src="https://forums.novociv.org/cards/jquery.tablesorter.js"></script>

 </head> <body> '; 

 // output templates
echo $header; 
}
//SET WHO CAN VIEW PAGE
if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
//if ($usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{

//## START PAGES ##
//HOME PAGE
if(isset($_GET['section']) && $_GET['section'] == 'home'){
	require_once('./pokemon/home.php');

//POKEMON PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'pokemon')){
	require_once('./pokemon/pokemon.php');

//TRADE PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'trade')){
	require_once('./pokemon/trade.php');

//OWNED POKEMON
} else if(isset($_GET['section']) && ($_GET['section'] == 'ownedpokemon')){
	require_once('./pokemon/owned_pokemon.php');
	
//TEAMS PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'team')){
	require_once('./pokemon/team.php');

//FLEX PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'flex')){
	require_once('./pokemon/flex.php');

//BUY PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'buy')){
	require_once('./pokemon/buy.php');
	
//GACHA PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'gacha')){
	require_once('./pokemon/gacha.php');

//REWARD PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'reward')){
	require_once('./pokemon/reward.php');

//SELL PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'sell')){
	require_once('./pokemon/sell.php');

//DAYCARE PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'daycare')){
	require_once('./pokemon/daycare.php');

//SPAWN PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'spawns')){
	require_once('./pokemon/spawns.php');

//LOAN PAGE
} else if(isset($_GET['section']) && ($_GET['section'] == 'loan')){
	require_once('./pokemon/loan.php');

//CATCH ALL PAGE
} else {
	require_once('./pokemon/index.php');
}
//## END PAGES ##
//USER CAN'T VIEW PAGE
} else {
echo "Nothing to see here.";
}
//footer, close everything 
if($_GET["foo"] != 'bar') {
echo "Total Time Elapsed: ".(microtime(true) - $microtime)."s";
echo $footer; 
echo '</body></html>';
}

?> 