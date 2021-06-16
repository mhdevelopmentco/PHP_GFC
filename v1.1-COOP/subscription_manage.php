<?php
require_once('lib/init.php');

global $session_uid, $userClass, $mailClass, $userDetails, $mainClass, $paymentClass;

$user_name = $userDetails->username;
$user_email = $userDetails->email;

$location_items = [];
//$location_items['starter'][5] = 19;    //gfc_california_starter_monthly
//$location_items['starter'][10] = 20;    //gfc_florida_starter_monthly
//$location_items['team'][5] = 21;        //gfc_california_team_monthly
//$location_items['team'][10] = 22;        //gfc_florida_team_monthly

$active_states = $mainClass->getActiveStates();
foreach ($active_states as $active_state) {
    $location_items['starter'][$active_state->id] = $active_state->personal_plan;
    $location_items['team'][$active_state->id] = $active_state->team_plan;
}


if (!empty($_POST['update_subscription'])) {

    //UPDATE SUBSCRIPTION
    $locations = [];
    $selected_locations = $_POST['selected_locations'];
    $locations = explode(',', $selected_locations);

    $extra_users = $_POST['extra_users'];

    $uid = $session_uid;
    $userClass->clearSubscriptionLocations($uid);
    $userClass->addSubscriptionLocations($uid, $locations);
    $userClass->setExtraUsers($uid, $extra_users);
    loadUserDetails();

    $trial = $userDetails->used_trial == 0 ? true : false;

    $cc_number = $_POST['cc_number'];
    $cc_csc = $_POST['cc_csc'];
    $cc_name = $_POST['cc_name'];

    $cc_month = $_POST['cc_expire_month'];
    $cc_year = $_POST['cc_expire_year'];


    $cc_address = $_POST['cc_address'];
    $cc_city = $_POST['cc_city'];
    $cc_zip = $_POST['cc_zip'];
    $cc_country = $_POST['cc_country'];
    $cc_state = $_POST['cc_state'];

    $total_price = $_POST['total_price'];

    $customer_phone = $_POST['customer_phone'];

    $customer_data = array(
        'company' => $userDetails->org_name,
        'bill_addr1' => $cc_address,
        'bill_city' => $cc_city,
        'bill_state' => $cc_state,
        'bill_postcode' => $cc_zip,
        'bill_country' => $cc_country,
        'external_key' => 'abcd' . mt_rand(1, 10000) . time(),
        'superuser_phone' => $customer_phone
    );

    $sub_type = $extra_users > 1 ? 'team' : 'starter';

    $item_ids = [];
    foreach ($locations as $loc_id) {
        array_push($item_ids, $location_items[$sub_type][$loc_id]);
    }

    $locations_count = sizeof($userClass->getSubscriptionLocations($session_uid));
    //$users_count = 1 + $userDetails->extra_users;
    $users_count = $userDetails->extra_users;

    //$line_quantity = $locations_count * $users_count;
    $line_quantity = $users_count;

    $card_process = true;
    if ($cc_number) {

        //card info update

        //card processing = update + create new card
        $cards = $paymentClass->getCardByCustomerId($userDetails->co_customer_id)->card;

        if (count($cards) > 0) {
            $card = $cards[0];
            $card_id = $card->creditcard_id;
            $paymentClass->deleteCard($card_id);
        }

        $created_card = $paymentClass->createCard($userDetails->id, $userDetails->co_customer_id, $cc_number, $cc_year, $cc_month, $cc_name, $cc_address, $cc_city, $cc_zip, $cc_country, $cc_state);

        if ($created_card->status != "success") {
            $card_process = false;
            $err_msg = $created_card->message;
        }
    }

    $err_msg = '';

    if ($card_process) {

        $package_id = $userDetails->sub_id;

        $update_subscription = $paymentClass->updateSubscription($package_id, $item_ids, $line_quantity, $created_card->id);

        $paymentClass->updateCustomer($userDetails->co_customer_id, $customer_data);

        if ($update_subscription->status == 'success') {

            $package_id = $update_subscription->id;

            $subscription = $paymentClass->getSubscription($package_id);

            if ($subscription->status == "success") {

                $current_subscription = $subscription->subscription[0];

                $sub_status = $current_subscription->package_status_state;

                $userClass->updateSubscriptionInfo($uid, $sub_status, $package_id);

                $_SESSION['fail_sub_count'] = 0;

                if ($trial) {

                    if ($_SERVER['HTTP_HOST'] != "localhost") {

                        $mail_subject = 'Subscription Info Updated';

                        $mail_content = 'Hi ' . $user_name . '!';
                        $mail_content .= '\r\n';
                        $mail_content .= 'You updated your GofetchCode subscription successfully.';

                        $mail_content_html = str_replace('\r\n', '<br />', $mail_content);

                        $mailClass->sendMail($user_email, $mail_subject, $mail_content, $mail_content_html);
                    }

                    $userClass->extendSubscription($uid, 7);
                    $url = BASE_URL . 'subscription_result.php?action=update_success';
                    header("Location: $url");
                    exit();

                } else {
                    $userClass->setUsedTrial($uid, 1);
                    $send_invoice = $paymentClass->invoiceSubscription($package_id);
                    if ($send_invoice->status == 'success') {
                        $pay_invoice = $paymentClass->payInvoice($userDetails->co_customer_id, $send_invoice->id, $total_price);
                        if ($pay_invoice->status == 'success') {
                            $userClass->extendSubscription($session_uid, 30);
                        } else {
                            $err_msg = $pay_invoice->message;
                            $_SESSION['err_msg'] = $err_msg;
                        }
                    } else {
                        $err_msg = $send_invoice->message;
                        $_SESSION['err_msg'] = $err_msg;
                    }

                    $url = BASE_URL . 'account_billing.php';
                    header("Location: $url");
                    exit();
                }


            } else {
                $err_msg = $subscription->message;
            }


        } else {
            $err_msg = $update_subscription->message;
        }
    }

    count_fail_sub();
    $_SESSION['err_msg'] = $err_msg;
    //fail subscription
    $url = BASE_URL . 'subscription_result.php?action=update_failed';
    header("Location: $url");
    exit();

} else if (!empty($_POST['create_subscription'])) {

    //CREATE SUBSCRIPTION

    $locations = [];
    $selected_locations = $_POST['selected_locations'];
    $locations = explode(',', $selected_locations);

//    if (isset($_POST['locations']) && is_array($_POST['locations'])) {
//        foreach ($_POST['locations'] as $location) {
//            $state_check = isset($mainClass->getStates()[$location]);
//            if ($state_check)
//                array_push($locations, $location);
//        }
//    }

    $extra_users = $_POST['extra_users'];

    $uid = $session_uid;
    $userClass->clearSubscriptionLocations($uid);
    $userClass->addSubscriptionLocations($uid, $locations);
    $userClass->setExtraUsers($uid, $extra_users);
    loadUserDetails();

    $trial = $userDetails->used_trial == 0 ? true : false;

    $cc_number = $_POST['cc_number'];
    $cc_csc = $_POST['cc_csc'];
    $cc_name = $_POST['cc_name'];

    $cc_month = $_POST['cc_expire_month'];
    $cc_year = $_POST['cc_expire_year'];

    $cc_address = $_POST['cc_address'];
    $cc_city = $_POST['cc_city'];
    $cc_zip = $_POST['cc_zip'];
    $cc_country = $_POST['cc_country'];
    $cc_state = $_POST['cc_state'];

    $total_price = $_POST['total_price'];

    $customer_phone = $_POST['customer_phone'];

    $customer_data = array(
        'company' => $userDetails->org_name,
        'bill_addr1' => $cc_address,
        'bill_city' => $cc_city,
        'bill_state' => $cc_state,
        'bill_postcode' => $cc_zip,
        'bill_country' => $cc_country,
        'external_key' => 'abcd' . mt_rand(1, 10000) . time(),
        'superuser_phone' => $customer_phone
    );

    $sub_type = $extra_users > 1 ? 'team' : 'starter';

    $item_ids = [];
    foreach ($locations as $loc_id) {
        array_push($item_ids, $location_items[$sub_type][$loc_id]);
    }

    $locations_count = sizeof($userClass->getSubscriptionLocations($session_uid));
    //$users_count = 1 + $userDetails->extra_users;
    $users_count = $userDetails->extra_users;

    //$line_quantity = $locations_count * $users_count;
    $line_quantity = $users_count;

    $created_card = $paymentClass->createCard($userDetails->id, $userDetails->co_customer_id, $cc_number, $cc_year, $cc_month, $cc_name, $cc_address, $cc_city, $cc_zip, $cc_country, $cc_state);

    $err_msg = '';

    if ($created_card->status == 'success') {
        $created_subscription = $paymentClass->createSubscription($userDetails->id, $userDetails->co_customer_id, $item_ids, $created_card->id, $line_quantity, $trial ? 7 : 0);

        $paymentClass->updateCustomer($userDetails->co_customer_id, $customer_data);

        if ($created_subscription->status == 'success') {

            $package_id = $created_subscription->id;

            $subscription = $paymentClass->getSubscription($package_id);

            if ($subscription->status == "success") {

                $current_subscription = $subscription->subscription[0];

                $sub_status = $current_subscription->package_status_state;

                $userClass->updateSubscriptionInfo($uid, $sub_status, $package_id);

                $_SESSION['fail_sub_count'] = 0;

                if ($trial) {
                    $userClass->extendSubscription($uid, 7);

                    //send subscription success
                    if ($_SERVER['HTTP_HOST'] != "localhost") {

                        $mail_subject = 'Subscribe to GofetchCode';

                        $mail_content = 'Hi ' . $user_name . '!';
                        $mail_content .= '\r\n';
                        $mail_content .= 'You subscribed successfully to GofetchCode.';

                        $mail_content_html = str_replace('\r\n', '<br />', $mail_content);

                        $mailClass->sendMail($user_email, $mail_subject, $mail_content, $mail_content_html);
                    }


                    $url = BASE_URL . 'subscription_result.php?action=create_success';
                    header("Location: $url");
                    exit();

                } else {
                    $userClass->setUsedTrial($uid, 1);
                    $send_invoice = $paymentClass->invoiceSubscription($package_id);
                    if ($send_invoice->status == 'success') {
                        $pay_invoice = $paymentClass->payInvoice($userDetails->co_customer_id, $send_invoice->id, $total_price);
                        if ($pay_invoice->status == 'success') {
                            $userClass->extendSubscription($session_uid, 30);
                        } else {
                            $err_msg = $pay_invoice->message;
                            $_SESSION['err_msg'] = $err_msg;
                        }
                    } else {
                        $err_msg = $send_invoice->message;
                        $_SESSION['err_msg'] = $err_msg;
                    }

                    $url = BASE_URL . 'account_billing.php';
                    header("Location: $url");
                    exit();
                }


            } else {
                $err_msg = $subscription->message;
            }


        } else {
            $err_msg = $created_subscription->message;
        }
    } else {
        $err_msg = $created_card->message;
    }

    count_fail_sub();
    $_SESSION['err_msg'] = $err_msg;
    //fail subscription
    $url = BASE_URL . 'subscription_result.php?action=create_failed';
    header("Location: $url");
    exit();

} else if (!empty($_POST['cancel_subscription'])) {

    try {
        $uid = $session_uid;

        $package_id = $_POST['package_id'];

        $cancelSubscription = $paymentClass->cancelSubscription($package_id);

        if ($cancelSubscription->status == 'success') {
            $userClass->extendSubscription($session_uid, -1);
            $userClass->clearSubscriptionLocations($uid);
            $userClass->setExtraUsers($uid, 0);
            $userClass->setUsedTrial($uid, 1);
            $userClass->updateSubscriptionInfo($uid, 'c', $package_id);
            loadUserDetails();

            $_SESSION['first_cancel_sub'] = true;

            if ($_SERVER['HTTP_HOST'] != "localhost") {
                //send email to customer
                $user_email = $userDetails->email;
                $mail_subject = "You unsubscribed from Gofetch Successfully";
                $mail_content = "We have cancelled your current subscription as per your request.";
                $mail_content .= '\r\n';
                //$mail_content .=" You will be charged $... until mm/dd/yy. ";
                $mail_content .= "You can contact us at support@gofetchcode.com in case you need help.";
                $mail_content_html = str_replace('\r\n', '<br />', $mail_content);
                $mailClass->sendMail($user_email, $mail_subject, $mail_content, $mail_content_html);
            }

            echo 'success';
        } else {
            echo 'failed';
        }

    } catch (Exception $e) {
        echo "failed";
    }

    exit();

} else if (!empty($_POST['suspend_subscription'])) {

    try {
        $uid = $session_uid;

        $package_id = $_POST['package_id'];

        $suspendSubscription = $paymentClass->suspendSubscription($package_id);

        if ($suspendSubscription->status == 'success') {
            $userClass->updateSubscriptionInfo($uid, 's', $package_id);
            loadUserDetails();

            if ($_SERVER['HTTP_HOST'] != "localhost") {
                //send email to customer
                $user_email = $userDetails->email;
                $mail_subject = "You suspended your subscription from Gofetch Successfully";
                $mail_content = "You can contact us at support@gofetchcode.com in case you need help.";
                $mail_content_html = str_replace('\r\n', '<br />', $mail_content);
                $mailClass->sendMail($user_email, $mail_subject, $mail_content, $mail_content_html);
            }

            echo 'success';
        } else {

            echo $suspendSubscription->message;
        }

    } catch (Exception $e) {
        echo "failed";
    }

    exit();

} else if (!empty($_POST['send_feedback'])) {
    global $userDetails, $mailClass;

    $result = true;

    if ($_SERVER['HTTP_HOST'] != "localhost") {

        $user_email = $userDetails->email;
        $mail_subject = 'Feedback from Customer';
        $mail_content = $_POST['feedback'];
        $mail_content_html = str_replace('\r\n', '<br />', $mail_content);
        $result = $mailClass->getMail($user_email, null, $mail_subject, $mail_content, $mail_content_html);
    }

    $_SESSION['first_cancel_sub'] = false;

    if ($result) {
        echo "success";
    } else {
        echo "Could not be sent the feedback. Please try again later.";
    }

    exit();
}