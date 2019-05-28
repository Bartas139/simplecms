<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/php-graph-sdk-5.x/src/Facebook/autoload.php';
require_once 'check_perm.php';

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
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$me = $response->getGraphUser();

$errors = "";

$response = $fb->get('/me?fields=id,name,email', $accessToken);
  $user = $response->getGraphUser();

$query = $db->prepare("SELECT fb_token FROM users WHERE fb_token = ? LIMIT 1");
$query->execute(array($user['id']));
$token_exist = $query->fetchColumn();

$query = $db->prepare("SELECT name, email FROM users WHERE email = ? OR name = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
$query->execute(array($user['email'], $user['name']));
$user_exist = $query->fetch(PDO::FETCH_ASSOC);

if (!empty($token_exist)){
  //Pokud v DB najdu uživatele s odpovídajícím tokenem, tak ho přihlásím
  $query = $db->prepare("SELECT id, name, role FROM users WHERE fb_token = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
  $query->execute(array($user['id']));
  $login = $query->fetch(PDO::FETCH_ASSOC);
  $_SESSION['user_id'] = $login['id'];
  $_SESSION['user_name'] = $login['name'];
  $_SESSION['user_role'] = $login['role'];  
} elseif (!empty($user_exist)) {
  //Pokud daný token v DB není, ověřím zda v DB není email/jméno, které mi facebook dal pod tímto tokenem, pokud ano přihlášení neproběhne
  
  $errors .= 'Uživatel s tímto jménem, nebo emailem je již registrovaný, přihlaste se pomocí emailu a hesla. Pokud heslo neznáte využijte jeho obnovu.';  
} else {
  //Pokud token, email ani jméno v DB není vvytvořím nový záznam
  $stmt = $db->prepare("INSERT INTO users(name, email, fb_token) VALUES (?, ?, ?)");
  $stmt->execute(array($user['name'], $user['email'], $user['id']));
    
  $query = $db->prepare("SELECT id, name, role FROM users WHERE email = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
  $query->execute(array($user['email']));
  $logged = $query->fetch(PDO::FETCH_ASSOC);
    
  $_SESSION['user_id'] = $logged['id'];
  $_SESSION['user_name'] = $logged['name'];
  $_SESSION['user_role'] = $logged['role'];
  header('Location: '.BASE_PATH.'/index.php');
}
?><!DOCTYPE html>

<html>

<head>
  <meta charset="utf-8" />
  <title>Simple CMS - Facebook</title>
  <?php include 'styles.php'; ?>
</head>

<body>
<?php include '../navbar.php'; ?>
<div class="container full-screen d-flex">
  
  <div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
    <article class="card-body mx-auto">
      <h4 class="card-title mt-3 text-center">Přihlášení</h4>
      <?php if(empty($errors)){echo '<p class="text-center">Přihlaste se pomocí svého účtu, nebo přes Facebook</p>'; } else {echo '<div class="alert alert-danger"><strong>'.$errors.'</strong></div>';} ?>
</article>
</div> <!-- card.// -->
 
</div> 
<?php include 'scripts.php'; ?>  

</body>

</html>