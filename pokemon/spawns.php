<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
$navbits = construct_navbits(array('/pokemon.php' => 'Pokemon', '' => '<a href="/pokemon.php?section=pokemon">Pokemon</a>')); 
$navbar = render_navbar_template($navbits); 
echo $navbar;
echo '<a href="https://forums.novociv.org/pokemon.php?section=spawns&do=admin">Spawn Tables Home</a><br><br>';

if(isset($_GET['do']) && $_GET['do'] == 'forum' && ($usergroup == 6 || $usergroup == 29)) {
    $vbulletin->input->clean_array_gpc('g', array(
        'forum' => TYPE_INT
    ));
    $forumid = clean_number($vbulletin->GPC['forum'],9999);
    $file = 'pokemon/spawns/' . $forumid . '.txt';
    if(file_exists($file)) {
        $textarea = file_get_contents($file);
        $str .= '
        <div class=tcg_body>
            <form class=buy action="pokemon.php?section=spawns&do=admin&action=update" method="post">
                <input type="hidden" name="forum" value="' . $forumid . '" />
            	Spawn Table for forumid ' . $forumid . ':<br>
                <textarea name="spawn" id="vB_Editor_QR_textarea" rows="10" cols="80" dir="ltr" tabindex="1">' . $textarea . '</textarea>
                <br>
            	<input type="hidden" name="auth" value="Kaos" />
            	<br><input type="submit" value="Update Spawn Table" />
        	</form>
        </div>
        ';
        echo $str;
    } else {
        $str .= "Forum does not exist yet.<br><br>";
        $str .= '
        <div class=tcg_body>
            <form class=buy action="pokemon.php?section=spawns&do=admin&action=update" method="post">
                ID of Forum:<br>
                <input name="forum" type="text" maxlength="5" value="' . $forumid . '"><br><br>
                Spawn Table:<br>
                <textarea name="spawn" id="vB_Editor_QR_textarea" rows="10" cols="80" dir="ltr" tabindex="1"></textarea>
                <br>
            	<input type="hidden" name="auth" value="Kaos" />
            	<br><input type="submit" value="Update Spawn Table" />
        	</form>
        </div>
        ';
        echo $str;
    }
} else if(isset($_GET['do']) && $_GET['do'] == 'admin' && ($usergroup == 6 || $usergroup == 29)){
    if(isset($_GET['action']) && $_GET['action'] == 'update') {
        // ############ POST VARIABLES ############
        //'p' means it's POST data
        $vbulletin->input->clean_array_gpc('p', array(
            'spawn' => TYPE_NOHTML,
            'forum' => TYPE_INT
        ));
        $forumid = clean_number($vbulletin->GPC['forum'], 9999);
        $spawn = $vbulletin->GPC['spawn'];

        #First file
        $handler = fopen('pokemon/spawns/' . $forumid . '.txt', 'w+'); #this creates the file if it doesn't exist
        $file1 = fwrite($handler, $spawn);
        fclose($handler);

        echo 'Success!<br><br>
        <a href="pokemon.php?section=spawns&do=forum&forum=' . $forumid . '">
        Go back to Edit</a><br><br>';

    } else if(isset($_GET['action']) && $_GET['action'] == 'redirect') {
        $vbulletin->input->clean_array_gpc('p', array(
            'forum' => TYPE_INT
        ));
        $forumid = clean_number($vbulletin->GPC['forum'], 9999);
        echo '<script type="text/javascript">
        <!--
        window.location = "https://forums.novociv.org/pokemon.php?section=spawns&do=forum&forum=' . $forumid . '"
        //-->
        </script>';
    } else {
        $forums = make_spawn_array();
        $str .= '
        <div class=tcg_body>
            <form class=buy action="pokemon.php?section=spawns&do=admin&action=redirect" method="post">
                ID of Forum:<br>
                <input name="forum" type="text" maxlength="5"><br><br>
            	<br><input type="submit" value="Make New Spawn Table" />
        	</form>
        </div>
        ';
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