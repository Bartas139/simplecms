<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/php-graph-sdk-5.x/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '2422157794674442', // Replace {app-id} with your app id
  'app_secret' => 'b5f88c5a0a4dd23eba95dd98413cee9e',
  'default_graph_version' => 'v3.2',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email'];

try {
  if (isset($_SESSION['facebook_access_token'])) {

    $accessToken = $_SESSION['facebook_access_token'];

} else {

    $accessToken = $helper->getAccessToken();
      }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo '<h1>Přihlášení se nepovedlo, zkuste to později</h1>';
  echo 'Graph error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo '<h1>Přihlášení se nepovedlo, zkuste to později</h1>';
  echo 'Facebook SDK error: ' . $e->getMessage();
  exit;
}

try {
  // Returns a `Facebook\FacebookResponse` object
  $response = $fb->get('/me?fields=id,name,email', $accessToken);
  $user = $response->getGraphUser();
  $name = $user->getProperty('name');
  $email = $user->getProperty('email');
  $_SESSION['facebook_access_token'] = $accessToken;
  $stmt = $db->prepare("INSERT INTO users(name, email) VALUES (?, ?)");
  $stmt->execute(array($name, $email));
  
  $query = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
  $query->execute(array($email));
  $logged = $query->fetch(PDO::FETCH_ASSOC);
    
  $_SESSION['user_id'] = $logged['id'];
  $_SESSION['user_name'] = $logged['name'];
  $_SESSION['user_role'] = $logged['role'];
  
  header('Location: index.php');

} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo '<h1>Přihlášení se nepovedlo, zkuste to později</h1>';
  echo 'Graph error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo '<h1>Přihlášení se nepovedlo, zkuste to později</h1>';
  echo 'Facebook SDK error: ' . $e->getMessage();
  exit;
}





