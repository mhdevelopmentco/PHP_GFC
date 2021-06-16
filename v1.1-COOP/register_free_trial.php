<?php
require('lib/init.php');

$username = '';
$password = '';
$password_2 = '';
$email = '';
$first_name = '';
$last_name = '';


$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_2 = $_POST['password_2'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$customer_phone  = $_POST['phone_number'];
//$card_number = $_POST['card_number'];

$userRegistration = $userClass->userRegistration($username, $password, $email, $first_name, $last_name, '');

if ($userRegistration === 'USERNAME_ALREADY_EXISTS') {
    $message = 'Username is already in use.';
    $response = ['result' => 'fail', 'message' => $message];
    echo json_encode($response);

} else if ($userRegistration === 'EMAIL_ALREADY_EXISTS') {
    $message = 'Email is already in use.';
    $response = ['result' => 'fail', 'message' => $message];
    echo json_encode($response);

} else if ($userRegistration) {
    $uid = $userRegistration;
    //update phone number
    $userDetails = $userClass->userDetails($uid);
    $paymentClass->updatePhone($userDetails->co_customer_id, $customer_phone);

    $response = ['result' => 'success'];
    echo json_encode($response);
} else {
    $response = ['result' => 'fail', 'message' => "Problem occurred while registering your free trial. Please try again later."];
    echo json_encode($response);
}

?>

