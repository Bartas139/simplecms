<?php

session_start();

require 'assets/db.php';
require_once __DIR__ . '/assets/php-graph-sdk-5.x/src/Facebook/autoload.php';
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	

	$errors = "";
	if (empty($_POST['nick'])){
		$errors .= "Jméno je povinné<br />";	
	}
	if (strlen($_POST["nick"]) < '4') {
            $errors .= "Jméno musí mít alespoň 4 znaky<br />";
        }
	if (empty($_POST['email'])){
		$errors .= "Email je povinný<br />";	
	}
	if (!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
        $errors.="Zadej platný email<br />";
    }
	
	
		if (empty($_POST['password'])){
		$errors .= "Heslo je  povinné<br />";	
		}
		if (strlen($_POST["password"]) < '8') {
            $errors .= "Heslo musí mít minimálně 8 znaků<br />";
        }
        if(!preg_match("#[0-9]+#",$_POST["password"])) {
            $errors .= "Heslo musí obsahovat minimálně jednu číslici<br />";
        }
        if(!preg_match("#[A-Z]+#",$_POST["password"])) {
            $errors .= "Heslo musí obsahovat minimálně jedno velké písmeno<br />";
        }
        if(!preg_match("#[a-z]+#",$_POST["password"])) {
            $errors .= "Heslo musí obsahovat minimálně jedno malé písmeno<br />";
        }
	if ($_POST['password']!=$_POST['checkpassword']){
		$errors .= "Hesla se neshodují<br />";	
	}

	$query = $db->prepare('SELECT name, email FROM users WHERE (name=? OR email=?) LIMIT 1');
    $query->execute(array($_POST["nick"],$_POST["email"]));
    $userexist = $query->fetch(PDO::FETCH_ASSOC);
	
    if ($userexist["name"]==$_POST["nick"]) {
            $errors.="Uživatel s tímto jménem již existuje<br />";
        } 
        if ($userexist["email"]==$_POST["email"]) {
            $errors.="Uživatel s tímto emailem již existuje<br />";
        }
	
	if (empty($errors)){
		//bcrypt: PASSWORD_BCRYPT
		$hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
		
		#vlozime usera do databaze
		$stmt = $db->prepare("INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)");
		$stmt->execute(array($_POST['nick'], $_POST['email'], $hashed, BASE_ROLE));
		
		#ted je uzivatel ulozen, bud muzeme vzit id posledniho zaznamu pres last insert id (co kdyz se to potka s vice requesty = nebezpecne), nebo nacist uzivatele podle mailove adresy (ok, bezpecne)
		
		$query = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
		$query->execute(array($_POST['email']));
		$user = $query->fetch(PDO::FETCH_ASSOC);
		
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['user_name'] = $user['name'];
		$_SESSION['user_role'] = $user['role'];
		header('Location: '.BASE_PATH.'/index.php');
	}
}

$fb = new Facebook\Facebook([
  'app_id' => '2216515835051068', // Replace {app-id} with your app id
  'app_secret' => '58baf11e44c78f02f85d8dc91fdea43d',
  'default_graph_version' => 'v3.2',
  ]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email'];
$loginUrl = $helper->getLoginUrl('https://cms.straightplay.cz/assets/fb_callback.php', $permissions);
	
?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>Simple CMS - Registrace</title>
	<?php include 'assets/styles.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>
<?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
<div class="container full-screen d-flex">
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Registrace</h4>
			<p class="text-center">Začněte vytvořením svého účtu</p>
			<p>
				<a href="<?php echo htmlspecialchars($loginUrl) ?>" class="btn btn-block btn-facebook"> <i class="fab fa-facebook-f"></i>   Registrovat přes Facebook</a>
			</p>
			<p class="divider-text">
        		<span class="bg-light">Nebo</span>
    		</p>
	
	<form method="POST">
		<div class="form-group input-group">
			<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-user"></i> </span>
		 	</div>
        	<input name="nick" class="form-control" placeholder="Uživatelské jméno" type="text" value="<?php echo htmlspecialchars(@$_POST['nick']) ?>">
    	</div> <!-- form-group// -->
    
    	<div class="form-group input-group">
    		<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-envelope"></i> </span>
		 	</div>
        	<input name="email" class="form-control" placeholder="Emailová adresa" type="email" value="<?php echo htmlspecialchars(@$_POST['email']) ?>">
    	</div> <!-- form-group// -->
    
	    <div class="form-group input-group">
	    	<div class="input-group-prepend">
			    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
			</div>
	        <input class="form-control" placeholder="Heslo" type="password" name="password">
	    </div> <!-- form-group// -->

	    <div class="form-group input-group">
	    	<div class="input-group-prepend">
			    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
			</div>
	        <input class="form-control" placeholder="Zopakujte heslo" type="password" name="checkpassword">
	    </div> <!-- form-group// -->

	    <div class="form-group">
	        <button type="submit" class="btn btn-primary btn-block">Založit účet</button>
	    </div> <!-- form-group// -->      
	    
	    <p class="text-center">Již jste registrovaní? <a href="signin.php">Přihlašte se</a> </p>                                                                 
</form>
</article>
</div> <!-- card.// -->

</div> 
<?php include 'assets/footer.php'; ?>
<?php include 'assets/scripts.php'; ?>
</body>

</html>