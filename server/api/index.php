<?php
require_once('../classes/Request.php');
require_once('../classes/Login.php');
require_once('../classes/Authenticator.php');

$request = new Request();
$request->parse();

$success = true;
$error = '';
$data = '';

if($request->getType() === "POST"){
    switch ($request->getValue('action')) {
        case 'login':
            $login = new Login();
            $username = $request->getValue('username');
            if($login->login($username, $request->getValue('password'))) {
                $authenticator = new Authenticator();
                $success = true;
                $data = $authenticator->getQRCode('VWO', $username);
            } else {
                $success = false;
                $error = 'Wrong Credentials';
            }
            break;
        case 'mfaVerify':
            $authenticator = new Authenticator();
            if($authenticator->verifyOTP($request->getValue('username'), $request->getValue('otp'))){
                $success = true;
                $data = "User Valid";
            } else {
                $success = false;
                $error = "Invalid OTP";
            }
            break;
        default:
            $success = false;
            $error = 'Invalid Action';
            break;
    }
} else {
    $success = false;
    $error = 'Invalid Request Method';
}

$response = ["success" => $success];

if($success){
    $response['data'] = $data;
} else {
    $response['error'] = $error;
}

header("Content-type:application/json");
echo json_encode($response);