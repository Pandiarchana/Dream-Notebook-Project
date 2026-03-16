<?php
session_start();

// Load Google API Client library
require_once __DIR__ . '/../vendor/autoload.php';

// Create Google Client
$client = new Google\Client();

// Replace these with your actual credentials from Google Cloud Console
$client->setClientId('YOUR_REAL_CLIENT_ID');
$client->setClientSecret('YOUR_REAL_CLIENT_SECRET');

// Redirect URI (must match the one in Google Cloud Console)
$client->setRedirectUri('http://localhost/Dream-Notebook-Project/backend_code/google_callback.php');

// Request user information
$client->addScope('email');
$client->addScope('profile');
?>