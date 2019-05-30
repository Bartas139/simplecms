<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/php-graph-sdk-5.x/src/Facebook/autoload.php';
require_once 'check_perm.php';
$_SESSION['fcb_errors'] = "";

$fb = new Facebook\Facebook([
  'app_id' => '2422157794674442', // Replace {app-id} with your app id
  'app_secret' => 'b5f88c5a0a4dd23eba95dd98413cee9e',
  'default_graph_version' => 'v3.2',
  ]);

$helper = $fb->getRedirectLoginHelper();
$accessToken = $helper->getAccessToken();

try {

  $response = $fb->get('/me', $accessToken);
} catch(\Facebook\Exceptions\FacebookResponseException $e) {
  $_SESSION['fcb_errors'] .= 'Ověření vaší identity pomocí služby Facebook selhalo. Zkuste to později, nebo použijte jiný způsob přihlášení.<br />';
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  $_SESSION['fcb_errors'] .= 'Přihlášení pomocí služby Facebook se nezdařilo. Zkuste to později, nebo použijte jiný způsob přihlášení.<br />';
  exit;
}

$me = $response->getGraphUser();

$errors = "";

$response = $fb->get('/me?fields=id,name,email', $accessToken);
  $user = $response->getGraphUser();

$query = $db->prepare("SELECT fb_token FROM users WHERE fb_token = ? LIMIT 1");
$query->execute(array($user['id']));
$token_exist = $query->fetchColumn();

$query = $db->prepare("SELECT name, email FROM users WHERE email = ? OR name = ? LIMIT 1");
$query->execute(array($user['email'], $user['name']));
$user_exist = $query->fetch(PDO::FETCH_ASSOC);

if (!empty($token_exist)){
  //Pokud v DB najdu uživatele s odpovídajícím tokenem, tak ho přihlásím
  $query = $db->prepare("SELECT id, name, role FROM users WHERE fb_token = ? LIMIT 1");
  $query->execute(array($user['id']));
  $login = $query->fetch(PDO::FETCH_ASSOC);
  $_SESSION['user_id'] = $login['id'];
  $_SESSION['user_name'] = $login['name'];
  $_SESSION['user_role'] = $login['role'];
  if (isset($_SESSION['source'])){
        header("Location: ". $_SESSION['source']);
        die ();  
      } else {
        header('Location: '.BASE_PATH.'/index.php');
        die ();
      }  
} elseif (!empty($user_exist)) {
  //Pokud daný token v DB není, ověřím zda v DB není email/jméno, které mi facebook dal pod tímto tokenem, pokud ano přihlášení neproběhne
  
  $_SESSION['fcb_errors'] .= 'Uživatel s tímto jménem, nebo emailem je již registrovaný, přihlaste se pomocí emailu a hesla. Pokud heslo neznáte využijte jeho obnovu.<br />';
  header('Location: '.BASE_PATH.'/signin.php');  
} else {
  //Pokud token, email ani jméno v DB není vvytvořím nový záznam
  $stmt = $db->prepare("INSERT INTO users(name, email, fb_token) VALUES (?, ?, ?)");
  $stmt->execute(array($user['name'], $user['email'], $user['id']));
    
  $query = $db->prepare("SELECT id, name, role FROM users WHERE email = ? LIMIT 1");
  $query->execute(array($user['email']));
  $logged = $query->fetch(PDO::FETCH_ASSOC);
    
  $_SESSION['user_id'] = $logged['id'];
  $_SESSION['user_name'] = $logged['name'];
  $_SESSION['user_role'] = $logged['role'];
  if (isset($_SESSION['source'])){
        header("Location: ". $_SESSION['source']);
        die ();  
      } else {
        header('Location: '.BASE_PATH.'/index.php');
        die ();
      }

}
?>