<?php
require_once 'google_config.php';

$authUrl = $client->createAuthUrl();

header('Location: ' . $authUrl);
exit;
?>