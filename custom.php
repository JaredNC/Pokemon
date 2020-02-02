<?php

/**
 * If you want to move this file to the root of your website, change this
 * line to your vBulletin directory and uncomment it (delete the //).
 *
 * For example, if vBulletin is installed in '/forum' the line should
 * state:
 *
 *    define('VB_RELATIVE_PATH', 'forum');
 *
 * Note: You may need to change the cookie path of your vBulletin
 * installation to enable your users to log in at the root of your website.
 * If you move this file to the root of your website then you should ensure
 * the cookie path is set to '/'.
 *
 * See 'Admin Control Panel
 *    ->Cookies and HTTP Header Options
 *      ->Path to Save Cookies
 */

//define('VB_RELATIVE_PATH', 'forums');


// Do not edit.
if (defined('VB_RELATIVE_PATH'))
{
    chdir('./' . VB_RELATIVE_PATH);
}


/**
 * You can choose the default script here.  Uncomment the appropriate line
 * to set the default script.  Note: Only uncomment one of these, you must
 * add // to comment out the script(s) that you DO NOT want to use as your
 * default script.
 *
 * You can choose the default script even if you do not plan to move this
 * file to the root of your website.
 */



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
require_once('./global.php'); 
// ####################################################################### 
// ######################## START MAIN SCRIPT ############################ 
// ####################################################################### 

//This appears in your breadcrumbs navigation.

$navbits = construct_navbits(array('/custom.php' => '<a href=/custom.php>Custom Pages</a>')); 
$navbar = render_navbar_template($navbits); 

// ###### YOUR CUSTOM CODE GOES HERE ##### 
//appears in the <title> tags in the head
$pagetitle = 'Custom Pages'; 

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
$userid            =    $vbulletin->userinfo[userid];
$username        =    $vbulletin->userinfo[username];
$cash = $vbulletin->userinfo[ucash];
$gcash = $vbulletin->userinfo[gameroom_cash];
$invites = $vbulletin->userinfo[invites];
$usergroup        =    $vbulletin->userinfo[usergroupid];
$avatarrevision =    $vbulletin->userinfo[avatarrevision];
//

// your own custom head and css files
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html dir="ltr" lang="en"> <head> 
'.$headinclude.' 
 <title>'.$pagetitle.'</title>  
   <link href="fstyle.css" rel="stylesheet" type="text/css" /> 
  <style type="text/css"> 
<!-- 
.advertisement {display: none !important} 
--> 
</style> 
 </head> <body> '; 
 // output templates
echo $header, $navbar; 
//content here 
if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$str = '';

//####### IM LINKS #######
$str .=
'<div class=party><h1 class=party>Finance Page</h1><br>
	This page lets you view your loans and investments.
	<div class=recruit>
		<form action="/pokemon.php?section=loan" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### IM LINKS #######
$str .=
'<div class=party><h1 class=party>IM Links</h1><br>
	This page lets you set custom account icons under your avatar.
	<div class=recruit>
		<form action="/imlinks.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### FORUM FILTER #######
$str .= 
'<div class=party><h1 class=party>Search Filter</h1><br>
	This page lets you unfilter forums you previously removed from New Search.
	<div class=recruit>
		<form action="/tvf.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### TAG LIST #######
$str .= 
'<div class=party><h1 class=party>Tag List</h1><br>
	This page lets you view tag lists for various forums. Use the &forumid=X modifier in the URL if you want something other than 517.
	<div class=recruit>
		<form action="/tvf.php?do=taglist" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### SPECIAL BUY #######
$str .= 
'<div class=party><h1 class=party>Special Buy</h1><br>
	This page lets you purchase invites, gameroom chips, and Global Announcements, sell gameroom chips, and gift invites.
	<div class=recruit>
		<form action="/buy.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### NET WORTH LEADERBOARD #######
$str .= 
'<div class=party><h1 class=party>Net Worth Leaderboard & User Stats</h1><br>
	This page displays user rankings by Net Worth, and detailed user stats.
	<div class=recruit>
		<form action="flex.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### FORTS #######
/*$str .= 
'<div class=party><h1 class=party>Forts</h1><br>
	This page has group forts. It has been put on hold indefinitely.
	<div class=recruit>
		<form action="fort.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';*/
/*
//####### KING OF THE HILL #######
$str .= 
'<div class=party><h1 class=party>King of the Hill</h1><br>
	This page allows users to compete for control of various hills.
	<div class=recruit>
		<form action="hill.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';
*/
//####### MOVIES LIST #######
$str .= 
'<div class=party><h1 class=party>Movies List</h1><br>
	This page allows users to vote for their preferred movie. Useful for Movie Night.
	<div class=recruit>
		<form action="movies.php?do=list" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### PARTY #######
$str .= 
'<div class=party><h1 class=party>Party Info</h1><br>
	This page displays members of each party, and allows for audits of Representatives.
	<div class=recruit>
		<form action="party.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### HURT/HEAL #######
$str .= 
'<div class=party><h1 class=party>Hurt One Heal One</h1><br>
	Play the classic game of Hurt One Heal One.
	<div class=recruit>
		<form action="hurt.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### DICE ROLLING #######
$str .= 
'<div class=party><h1 class=party>Dice Rolling</h1><br>
	Let\'s you roll a dice with 2-100 sides.
	<div class=recruit>
		<form action="dice.php" method="post">
			<input type="submit" value="Visit this Page" />
		</form>
	</div>
</div>';

//####### NCAC 2014 EDIT VOTES #######
//if($_GET['form'] == 'ncac2015') {
//$formid = $db->query_first("SELECT `id` FROM `formresults` WHERE `fid` = 9 AND `userid` = " . $userid);
//echo '			<script type="text/javascript">
//			<!--
//			window.location = "http://forums.novociv.org/misc.php?do=editformresult&id=' . $formid['id'] . '&fid=9"
//			//-->
//			</script>';
//}

echo $str;
} else {
echo "Nothing to see here.";
}
//footer, close everything 
echo $footer; 
echo '</body></html>';
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 16:53, Mon Nov 8th 2010
|| # CVS: $RCSfile$ - $Revision: 35749 $
|| ####################################################################
\*=====================================================================*/

?> 