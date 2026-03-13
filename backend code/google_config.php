<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();

$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/Dream-Notebook-Project/backend code/google_callback.php');

$client->addScope('email');
$client->addScope('profile');
?>