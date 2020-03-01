<?php

if ($userid != 0 && $usergroup != 8 && $usergroup != 3 && $usergroup != 53 && $userposts >= 50)
{
    $navbits = construct_navbits(array('/pokemon.php?section=loan' => 'NewCiv Finance', '' => '<a href="/pokemon.php?section=loan">Finance</a>'));
    $navbar = render_navbar_template($navbits);
    echo $navbar;

    $cash = $vbulletin->userinfo[ucash];
    $leverage = 10;
    $apr = '0.1731';
    $alt_id = $vbulletin->userinfo[alt_id];

    echo '<div class="tcg_body">
Finance start<br><br>';
    if(isset($_GET['do']) && $_GET['do'] == 'withdraw') {
        // ############ GET VARIABLES ############
        //'g' means it's GET data
        $vbulletin->input->clean_array_gpc('g', array(
            'investment' => TYPE_INT
        ));
        $investment_id = clean_number($vbulletin->GPC['investment'], 9999);

        $str = '<div class="tcg_body"><h1><u>Make Withdrawal:</u></h1><br>
            <p>All early withdrawals must be made in whole numbers, and carry a 5% service charge.</p>
            <p>If you try to withdraw more than the value of the investment, the amount will be adjusted down.</p>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=withdraw" method="post">
                ID of Investment:<br>
                <input name="investment" type="text" maxlength="5" value="' . $investment_id . '"><br><br>
                Amount of Withdrawal:<br>
                <input name="amount" type="text" maxlength="5"><br><br>
                <br><input type="submit" value="Make Withdrawal" />
            </form>
        </div>
        ';
        echo $str;
    } else if(isset($_GET['do']) && $_GET['do'] == 'pay') {
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
    } else if(isset($_GET['do']) && $_GET['do'] == 'apply_loan') {
        $qry = "SELECT 
                    sum(balance) AS 'debt'
                FROM 
                    `poke_loan`
                WHERE  
                    `userid`=$userid";
        $result = $db->query_first($qry);

        $cash = number_format($cash, '2');
        $networth = calc_networth($userid);
        $debt = $result["debt"];
        $limit = max($networth * $leverage - $debt,0);

        if($alt_id == 0) {
            $str .= '<div class="tradelistactive">
                <p>Your total networth is ' . number_format($networth, '2') . '.</p>
                <p>Your total debt is ' . number_format($debt, '2') . '.</p>
                <p>Your available credit is ' . number_format($limit, '2') . '.</p>
            </div>';

            $str .= '<div class="tcg_body"><h1><u>Long Term Loan:</u></h1><br>
                <p>Annual Interest rate: ' . number_format($apr * 100, '2') . '</p>
                <p>Daily Payment: 75</p>
                <p>Loan Amount: 50,000</p>
                <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_loan" method="post">
                    <input name="loan" type="hidden" value="1"><br>
                    <input type="submit" value="Take Loan" />
                </form>
            </div>
            ';

            $str .= '<div class="tcg_body"><h1><u>Mid Term Loan:</u></h1><br>
                <p>Annual Interest rate: ' . number_format($apr * 100, '2') . '</p>
                <p>Daily Payment: 100</p>
                <p>Loan Amount: 25,000</p>
                <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_loan" method="post">
                    <input name="loan" type="hidden" value="2"><br>
                    <input type="submit" value="Take Loan" />
                </form>
            </div>
            ';

            $str .= '<div class="tcg_body"><h1><u>Short Term Loan:</u></h1><br>
                <p>Annual Interest rate: ' . number_format($apr * 100, '2') . '</p>
                <p>Daily Payment: 250</p>
                <p>Loan Amount: 10,000</p>
                <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_loan" method="post">
                    <input name="loan" type="hidden" value="3"><br>
                    <input type="submit" value="Take Loan" />
                </form>
            </div>
            ';
        } else {
            $str .= '<div class="tradelistinactive">
                <p>Your total networth is ' . number_format($networth, '2') . '.</p>
                <p>Your total debt is ' . number_format($debt, '2') . '.</p>
                <p>Your available credit is ' . number_format($limit, '2') . '.</p>
                <p>alt_id: ' . $alt_id . '</p>
                <p>ALTS CAN\'T TAKE LOANS</p>
            </div>';
        }
        echo $str;
    } else if(isset($_GET['do']) && $_GET['do'] == 'apply_investment') {
        $str .= '<div class="tradelistactive">
            <p>You have ' . number_format($cash, '2') . ' pengos.</p>
        </div>';

        $str .= '<div class="tcg_body"><h1><u>Long Term Investment:</u></h1><br>
            <p>Investment Amount: 50,000</p>
            <p>Potential APR: 30%</p>
            <p>Daily Payout: 100</p>
            <p>Time to final payout: ~645 Days</p>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_investment" method="post">
                <input name="investment" type="hidden" value="1"><br>
                <input type="submit" value="Buy Investment" />
            </form>
        </div>
        ';

        $str .= '<div class="tcg_body"><h1><u>Mid Term Investment:</u></h1><br>
            <p>Investment Amount: 25,000</p>
            <p>Potential APR: 25%</p>
            <p>Daily Payout: 200</p>
            <p>Time to final payout: ~130 Days</p>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_investment" method="post">
                <input name="investment" type="hidden" value="2"><br>
                <input type="submit" value="Buy Investment" />
            </form>
        </div>
        ';

        $str .= '<div class="tcg_body"><h1><u>Short Term Investment:</u></h1><br>
            <p>Investment Amount: 10,000</p>
            <p>Potential APR: 20%</p>
            <p>Daily Payout: 500</p>
            <p>Time to final payout: ~21 Days</p>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=make_investment" method="post">
                <input name="investment" type="hidden" value="3"><br>
                <input type="submit" value="Buy Investment" />
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
        $str .= '<div class="tcg_body"><h1><u>Make Investment:</u></h1><br>
            <form class=buy action="/pokemon.php?section=loan&do=transact&action=make2" method="post">
                ID of User:<br>
                <input name="user" type="text" maxlength="5""><br><br>
                Amount of Investment:<br>
                <input name="amount" type="text" maxlength="5"><br><br>
                APR:<br>
                <input name="apr" type="text" maxlength="6"><br><br>
                Daily Payment:<br>
                <input name="daily" type="text" maxlength="5"><br><br>
                <br><input type="submit" value="Make Investment" />
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
            $loan_id = clean_number($vbulletin->GPC['loan'], 999);
            $pmt = clean_number($vbulletin->GPC['amount'], 99999);

            // ##### ERROR CHECKING #####
            $error = false;

            // Loan Exists?
            $exists = $db->query_first("SELECT EXISTS(
                                        SELECT 1 FROM poke_loan WHERE loan_id = $loan_id AND userid = $userid
                                        ) AS 'Exists'");
            if ($exists['Exists'] == false) {
                $error = true;
                $error_str .= 'Loan does not exist or is not yours.<br>';
            }

            // Can afford payment?
            if ($cash < $pmt) {
                $error = true;
                $error_str .= 'You don\'t have enough money.<br>';
            }

            // Amount valid?
            if ($pmt == 0) {
                $error = true;
                $error_str .= 'Not a valid payment amount.<br>';
            }

            if ($error == false) {
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
                $pmt = min($pmt, ($balance + $int_payable));
                if ($pmt >= $int_payable) {
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

                if ($pmt == 0) {
                    $error = true;
                    $error_str .= 'Loan is already paid off.<br>';
                }

                if ($error == false) {
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
        } else if(isset($_GET['action']) && $_GET['action'] == 'withdraw') {
            // ############ POST VARIABLES ############
            //'p' means it's POST data
            $vbulletin->input->clean_array_gpc('p', array(
                'investment' => TYPE_INT,
                'amount' => TYPE_INT
            ));
            $investment_id = clean_number($vbulletin->GPC['investment'],9999);
            $pmt = clean_number($vbulletin->GPC['amount'],99999);

            // ##### ERROR CHECKING #####
            $error = false;

            // Loan Exists?
            $exists = $db->query_first("SELECT EXISTS(
                                        SELECT 1 FROM poke_investment WHERE investment_id = $investment_id AND userid = $userid
                                        ) AS 'Exists'");
            if($exists['Exists'] == false) {
                $error = true;
                $error_str .= 'Investment does not exist or is not yours.<br>';
            }

            // Amount valid?
            if($pmt == 0){
                $error = true;
                $error_str .= 'Not a valid withdrawal amount.<br>';
            }

            if($error == false) {
                // ############ QUERY VARIABLES ############
                $qry = "SELECT 
                    *
                FROM 
                    `poke_investment`
                WHERE  
                    `userid`=$userid
                    AND investment_id=$investment_id";
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
                    $error_str .= 'Investment is already paid out.<br>';
                }

                if($error == false){
                    // ### Make Withdrawal ###

                    // Take pengos
                    $user_qry = "UPDATE
                        user
                    SET
                        ucash = ucash + ($pmt*0.95)
                    WHERE
                        userid = $userid";
                    $db->query_write($user_qry);

                    // Update Loan
                    $update_qry = "UPDATE
                        poke_investment
                    SET
                        balance = $new_balance,
                        int_payable = $new_interest
                    WHERE
                        investment_id = $investment_id";
                    $db->query_write($update_qry);

                    // Add Payment to Log
                    $insert_qry = "INSERT INTO poke_investment_pmt
                        (investment_id, dateline, pmt, interest, principal, type)
                    VALUES
                        ('$loan_id', '" . time() . "', '$pmt', '$paid_interest', '$paid_balance', 'manual')";
                    $db->query_write($insert_qry);

                    echo "Investment withdrawal of $pmt made successfully. Fee: " . ($pmt*0.05);
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
        } else if(isset($_GET['action']) && $_GET['action'] == 'make_loan') {
            // ############ POST VARIABLES ############
            //'p' means it's POST data
            $vbulletin->input->clean_array_gpc('p', array(
                'loan' => TYPE_INT
            ));

            // ##### ERROR CHECKING #####
            $error = false;

            //Set values based on loan
            if($vbulletin->GPC['loan'] == 1) {
                $amount = 50000;
                $daily_pmt = 75;
            } else if($vbulletin->GPC['loan'] == 2) {
                $amount = 25000;
                $daily_pmt = 100;
            } else if($vbulletin->GPC['loan'] == 3) {
                $amount = 10000;
                $daily_pmt = 250;
            } else {
                $error = true;
                $error_str .= 'Not a valid loan type.<br>';
            }

            //Check if can take loan
            $qry = "SELECT 
                    sum(balance) AS 'debt'
                FROM 
                    `poke_loan`
                WHERE  
                    `userid`=$userid";
            $result = $db->query_first($qry);

            $networth = calc_networth($userid);
            $debt = $result["debt"];
            $limit = max($networth * $leverage - $debt,0);
            if($amount > $limit) {
                $error = true;
                $error_str .= 'Loan amount exceeds your credit limit.<br>';
            }

            if($alt_id != 0) {
                $error = true;
                $error_str .= 'Alts can\'t take loans.<br>';
            }

            if($error == false) {
                // Give pengos
                $user_qry = "UPDATE
                    user
                SET
                    ucash = ucash + " . $amount . "
                WHERE
                    userid = " . $userid;
                $db->query_write($user_qry);

                // Make Loan
                $insert_qry = "INSERT INTO poke_loan
                    (userid, loan_amount, apr, daily_pmt, start_date, balance, int_payable)
                VALUES
                    ('" . $userid . "', 
                    '" . $amount . "', 
                    '" . $apr . "', 
                    '" . $daily_pmt . "', 
                    '" . time() . "', 
                    '" . $amount . "', 
                    '0')";
                $db->query_write($insert_qry);
                echo 'Good.';
            } else {
                echo $error_str;
            }
        } else if(isset($_GET['action']) && $_GET['action'] == 'make2' && $userid == 1) {
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
                ucash = ucash - " . $vbulletin->GPC['amount'] . "
            WHERE
                userid = " . $vbulletin->GPC['user'];
            $db->query_write($user_qry);

            // Make Loan
            $insert_qry = "INSERT INTO poke_investment
                (userid, investment_amount, apr, daily_pmt, start_date, balance, int_payable)
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
        } else if(isset($_GET['action']) && $_GET['action'] == 'make2' && $userid == 1) {
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
                ucash = ucash - " . $vbulletin->GPC['amount'] . "
            WHERE
                userid = " . $vbulletin->GPC['user'];
            $db->query_write($user_qry);

            // Make Loan
            $insert_qry = "INSERT INTO poke_investment
                (userid, investment_amount, apr, daily_pmt, start_date, balance, int_payable)
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
        } else if(isset($_GET['action']) && $_GET['action'] == 'make_investment') {
            // ############ POST VARIABLES ############
            //'p' means it's POST data
            $vbulletin->input->clean_array_gpc('p', array(
                'investment' => TYPE_INT
            ));

            // ##### ERROR CHECKING #####
            $error = false;

            //Set values based on loan
            if($vbulletin->GPC['investment'] == 1) {
                $amount = 50000;
                $daily_pmt = 100;
                $apr = '0.3000';
            } else if($vbulletin->GPC['investment'] == 2) {
                $amount = 25000;
                $daily_pmt = 200;
                $apr = '0.2500';
            } else if($vbulletin->GPC['investment'] == 3) {
                $amount = 10000;
                $daily_pmt = 500;
                $apr = '0.2000';
            } else {
                $error = true;
                $error_str .= 'Not a valid investment type.<br>';
            }

            //Check if can take investment
            if($amount > $cash) {
                $error = true;
                $error_str .= 'Investment amount exceeds your available cash.<br>';
            }

            if($error == false) {
                // Take pengos
                $user_qry = "UPDATE
                    user
                SET
                    ucash = ucash - " . $amount . "
                WHERE
                    userid = " . $userid;
                $db->query_write($user_qry);

                // Make Investment
                $insert_qry = "INSERT INTO poke_investment
                    (userid, investment_amount, apr, daily_pmt, start_date, balance, int_payable)
                VALUES
                    ('" . $userid . "', 
                    '" . $amount . "', 
                    '" . $apr . "', 
                    '" . $daily_pmt . "', 
                    '" . time() . "', 
                    '" . $amount . "', 
                    '0')";
                $db->query_write($insert_qry);
                echo 'Good.';
            } else {
                echo $error_str;
            }
        }
    } else {
        // ############ LOANS ############
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

        $str .= '<div class="tradelistactive">
            <p>Your total networth is ' . number_format(calc_networth($userid),'2') . '.</p>
            <p><a href="/pokemon.php?section=loan&do=apply_loan">Take A Loan.</a></p>
            <p><a href="/pokemon.php?section=loan&do=apply_investment">Purchase An Investment.</a></p>
        </div>';

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

            //Calc interest so far
            $interest_qry = 'SELECT sum(interest) AS `interest` 
                FROM `poke_loan_pmt` 
                WHERE `loan_id` = ' . $loan_id;
            $interest_result = $vbulletin->db->query_first($interest_qry);
            $interest = number_format($interest_result["interest"],'2');

            //Make html parts
            $active = (($balance + $int_payable) > 0) ? '' : 'in';

            $div = '<div class="tradelist' . $active . 'active">
                <h1><font size="+1">Loan #' . $loan_id . '</font></h1>
                <p>Amount: ' . $loan_amount . ' Opened on ' . $start_date . '</p>
                <p>APR: ' . $apr . '%</p>
                <p>Daily Payment: ' . $daily_pmt . '</p>
                <p>Principal Owed: ' . $balance . '</p>
                <p>Interest Owed: ' . $int_payable . '</p>
                <p>Total Interest Paid: ' . $interest . '</p>
                <p><a href="pokemon.php?section=loan&do=pay&loan=' . $loan_id . '">Make a Payment</a></p>
            </div>';

            $str .= $div;
        }

        // ############ INVESTMENTS ############
        $qry = "SELECT 
            *
        FROM 
            `poke_investment`
        WHERE  
            `userid`=$userid
        ORDER BY 
            `balance` DESC,
            `int_payable` DESC";
        $result = $db->query_read($qry);
        $str .= '<h1>Displaying your investments:</h1>';

        // ### Calculate Performance ###
        $performance = 0;

        // Get time window
        $time_qry = 'SELECT dateline 
        FROM `poke_investment_pmt` 
        WHERE `type` = "automatic"
        ORDER BY `dateline` DESC
        LIMIT 1';
        $time_result = $vbulletin->db->query_first($time_qry);

        // Posts
        $time_var = $time_result['dateline'];
        $post_qry = 'SELECT count(*) AS `count` 
        FROM `post` 
        WHERE `userid` = ' . $userid . ' AND `dateline` > ' . $time_var;
        $post_result = $vbulletin->db->query_first($post_qry);
        $post_score = $post_result["count"] * 0.1;
        $performance += $post_score;

        // Threads
        $thread_qry = 'SELECT count(*) AS `count` 
        FROM `thread` 
        WHERE `postuserid` = ' . $userid . ' AND `dateline` > ' . $time_var;
        $thread_result = $vbulletin->db->query_first($thread_qry);
        $thread_score = $thread_result["count"] * 0.1;
        $performance += $thread_score;

        // Likes
        $like_qry = 'SELECT count(*) AS `count` 
        FROM `post_thanks` 
        INNER JOIN `post` ON `post_thanks`.`postid` = `post`.`postid` 
        WHERE `post`.`userid` = ' . $userid . ' AND `post_thanks`.`date` > ' . $time_var;
        $like_result = $vbulletin->db->query_first($like_qry);
        $like_score = $like_result["count"] * 0.25;
        $performance += $like_score;

        $performance = min($performance,1);
        $str .= '<p>Investment performance for today is currently at ' . $performance*100 . '%.</p>';

        while ($resultLoop = $db->fetch_array($result)) {
            //Grab Variables
            $investment_id = $resultLoop["investment_id"];
            $investment_amount = number_format($resultLoop["investment_amount"], '0');
            $apr = number_format($resultLoop["apr"]*100,'2');
            $daily_pmt = $resultLoop["daily_pmt"];
            $start_date = vbdate("F j, Y, g:i:s A",$resultLoop["start_date"]);
            $balance = number_format($resultLoop["balance"], '4');
            $int_payable = number_format($resultLoop["int_payable"], '4');

            //Calc interest so far
            $interest_qry = 'SELECT sum(interest) AS `interest` 
                FROM `poke_investment_pmt` 
                WHERE `investment_id` = ' . $investment_id;
            $interest_result = $vbulletin->db->query_first($interest_qry);
            $interest = number_format($interest_result["interest"],'2');

            //Make html parts
            $active = (($balance + $int_payable) > 0) ? '' : 'in';

            $div = '<div class="tradelist' . $active . 'active">
                <h1><font size="+1">Investment #' . $investment_id . '</font></h1>
                <p>Amount: ' . $investment_amount . ' Opened on ' . $start_date . '</p>
                <p>APR Potential: ' . $apr . '%</p>
                <p>Daily Payment: ' . $daily_pmt . '</p>
                <p>Principal Remaining: ' . $balance . '</p>
                <p>Yesterday\'s Profit Earned: ' . $int_payable . '</p>
                <p>Total Interest Earned: ' . $interest . '</p>
                <p><a href="pokemon.php?section=loan&do=withdraw&investment=' . $investment_id . '">Make an early Withdrawal (5% fee)</a></p>
            </div>';

            $str .= $div;
        }
        echo $str;
    }
} else {
    echo "Nothing to see here.";
}
echo '</div>';