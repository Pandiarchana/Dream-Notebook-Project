<?php
require_once 'google_config.php';

if (isset($_GET['code'])) {

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    $client->setAccessToken($token['access_token']);

    $google_service = new Google\Service\Oauth2($client);

    $data = $google_service->userinfo->get();

    $_SESSION['email'] = $data['email'];
    $_SESSION['name'] = $data['name'];

    header("Location: ../Frontend/Userpage.php");
    exit;
}
?>