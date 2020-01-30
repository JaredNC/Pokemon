<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53)
{
    $navbits = construct_navbits(array('/pokemon.php' => 'NewCiv Pokemon', '' => '<a href="/pokemon.php?section=loan">Poke Loans</a>'));
    $navbar = render_navbar_template($navbits);
    echo $navbar;

    $cash = $vbulletin->userinfo[ucash];

    echo '<div class="tcg_body">
Pokemon start<br><br>';
    if(isset($_GET['do']) && $_GET['do'] == 'pay') {
        // ############ GET VARIABLES ############
        //'g' means it's GET data
        $vbulletin->input->clean_array_gpc('g', array(
            'loan' => TYPE_INT
        ));
        $loan_id = clean_number($vbulletin->GPC['loan'],999);
        $cash = number_format($cash, '2');

        $str = '<div class="tcg_body"><h1><u>Make Payment:</u></h1><br>
            <p>You have ' . $cash . ' pengos. All payments must be made in whole numbers.</p>
            <p>If you overpay on the loan, the excess will be refunded.</p>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=pay" method="post">
                ID of Loan:<br>
                <input name="loan" type="text" maxlength="5" value="' . $loan_id . '"><br><br>
                Amount of Payment:<br>
                <input name="amount" type="text" maxlength="5"><br><br>
                <br><input type="submit" value="Make Payment" />
            </form>
        </div>
        ';
        echo $str;
    } else if(isset($_GET['do']) && $_GET['do'] == 'admin' && $userid == 1){
        // ############ GET VARIABLES ############
        $str = '<div class="tcg_body"><h1><u>Make Loan:</u></h1><br>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=make" method="post">
                ID of User:<br>
                <input name="user" type="text" maxlength="5""><br><br>
                Amount of Loan:<br>
                <input name="amount" type="text" maxlength="5"><br><br>
                APR:<br>
                <input name="apr" type="text" maxlength="6"><br><br>
                Daily Payment:<br>
                <input name="daily" type="text" maxlength="5"><br><br>
                <br><input type="submit" value="Make Loan" />
            </form>
        </div>
        ';
        echo $str;
    } else if(isset($_GET['do']) && $_GET['do'] == 'transact'){
        if(isset($_GET['action']) && $_GET['action'] == 'pay') {
            // ############ POST VARIABLES ############
            //'p' means it's POST data
            $vbulletin->input->clean_array_gpc('p', array(
                'loan' => TYPE_INT,
                'amount' => TYPE_INT
            ));
            $loan_id = clean_number($vbulletin->GPC['loan'],999);
            $pmt = clean_number($vbulletin->GPC['amount'],99999);

            // ##### ERROR CHECKING #####
            $error = false;

            // Loan Exists?
            $exists = $db->query_first("SELECT EXISTS(
                                        SELECT 1 FROM poke_loan WHERE loan_id = $loan_id AND userid = $userid
                                        ) AS 'Exists'");
            if($exists['Exists'] == false) {
                $error = true;
                $error_str .= 'Loan does not exist or is not yours.<br>';
            }

            // Can afford payment?
            if($cash < $pmt) {
                $error = true;
                $error_str .= 'You don\'t have enough money.<br>';
            }

            // Amount valid?
            if($pmt == 0){
                $error = true;
                $error_str .= 'Not a valid payment amount.<br>';
            }

            if($error == false) {
                // ############ QUERY VARIABLES ############
                $qry = "SELECT 
                    *
                FROM 
                    `poke_loan`
                WHERE  
                    `userid`=$userid
                    AND loan_id=$loan_id";
                $result = $db->query_first($qry);

                //Grab Variables
                $balance = $result["balance"];
                $int_payable = $result["int_payable"];

                // Some Logic
                $pmt = min($pmt,($balance + $int_payable));
                if($pmt >= $int_payable) {
                    $paid_interest = $int_payable;
                    $new_interest = 0;
                    $paid_balance = $pmt - $int_payable;
                    $new_balance = $balance - $paid_balance;
                } else {
                    $paid_interest = $pmt;
                    $new_interest = $int_payable - $pmt;
                    $paid_balance = 0;
                    $new_balance = $balance;
                }

                if($pmt == 0){
                    $error = true;
                    $error_str .= 'Loan is already paid off.<br>';
                }

                if($error == false){
                    // ### Make Payment ###

                    // Take pengos
                    $user_qry = "UPDATE
                        user
                    SET
                        ucash = ucash - $pmt
                    WHERE
                        userid = $userid";
                    $db->query_write($user_qry);

                    // Update Loan
                    $update_qry = "UPDATE
                        poke_loan
                    SET
                        balance = $new_balance,
                        int_payable = $new_interest
                    WHERE
                        loan_id = $loan_id";
                    $db->query_write($update_qry);

                    // Add Payment to Log
                    $insert_qry = "INSERT INTO poke_loan_pmt
                        (loan_id, dateline, pmt, interest, principal, type)
                    VALUES
                        ('$loan_id', '" . time() . "', '$pmt', '$paid_interest', '$paid_balance', 'manual')";
                    $db->query_write($insert_qry);

                    echo "Loan payment of $pmt made successfully.";
                } else {
                    echo $error_str;
                }
            } else {
                echo $error_str;
            }
        } else if(isset($_GET['action']) && $_GET['action'] == 'make' && $userid == 1) {
            // ############ POST VARIABLES ############
            //'p' means it's POST data
            $vbulletin->input->clean_array_gpc('p', array(
                'user' => TYPE_INT,
                'amount' => TYPE_INT,
                'apr' => TYPE_NOHTML,
                'daily' => TYPE_INT
            ));

            // Give pengos
            $user_qry = "UPDATE
                user
            SET
                ucash = ucash + " . $vbulletin->GPC['amount'] . "
            WHERE
                userid = " . $vbulletin->GPC['user'];
            $db->query_write($user_qry);

            // Make Loan
            $insert_qry = "INSERT INTO poke_loan
                (userid, loan_amount, apr, daily_pmt, start_date, balance, int_payable)
            VALUES
                ('" . $vbulletin->GPC['user'] . "', 
                '" . $vbulletin->GPC['amount'] . "', 
                '" . $vbulletin->GPC['apr'] . "', 
                '" . $vbulletin->GPC['daily'] . "', 
                '" . time() . "', 
                '" . $vbulletin->GPC['amount'] . "', 
                '0')";
            $db->query_write($insert_qry);
            echo 'Good.';
        }
    } else {
        // ############ QUERY VARIABLES ############
        $qry = "SELECT 
            *
        FROM 
            `poke_loan`
        WHERE  
            `userid`=$userid
        ORDER BY 
            `balance` DESC,
            `int_payable` DESC";
        $result = $db->query_read($qry);
        $str .= '<h1>Displaying your loans:</h1>';
        while ($resultLoop = $db->fetch_array($result)) {
            //Grab Variables
            $loan_id = $resultLoop["loan_id"];
            $loan_amount = number_format($resultLoop["loan_amount"], '0');
            $apr = number_format($resultLoop["apr"]*100,'2');
            $daily_pmt = $resultLoop["daily_pmt"];
            $start_date = vbdate("F j, Y, g:i:s A",$resultLoop["start_date"]);
            $balance = number_format($resultLoop["balance"], '4');
            $int_payable = number_format($resultLoop["int_payable"], '4');

            //Make html parts
            $active = (($balance + $int_payable) > 0) ? '' : 'in';

            $div = '<div class="tradelist' . $active . 'active">
                <h1><font size="+1">Loan #' . $loan_id . '</font></h1>
                <p>Amount: ' . $loan_amount . ' Opened on ' . $start_date . '</p>
                <p>APR: ' . $apr . '%</p>
                <p>Daily Payment: ' . $daily_pmt . '</p>
                <p>Principal Owed: ' . $balance . '</p>
                <p>Interest Owed: ' . $int_payable . '</p>
                <p><a href="pokemon.php?section=loan&do=pay&loan=' . $loan_id . '">Make a Payment</a></p>
            </div>';

            $str .= $div;
        }
        echo $str;
    }
} else {
    echo "Nothing to see here.";
}
echo '</div>';