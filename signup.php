<?php

session_start();

require 'db.php';
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$email = $_POST['email'];
	$password = $_POST['password'];
	$nick = $_POST['nick'];
	
	# TODO PRO STUDENTY osetrit vstupy, email a heslo jsou povinne, atd.
	# TODO PRO STUDENTY jde se prihlasit prazdnym heslem, jen prototyp, pouzit filtry

	# $password = md5($_POST['password']); #chybi salt
	
	# $password = hash("sha256" , $password); #chybi salt
	
	# viz http://php.net/manual/en/function.password-hash.php
	# salt lze generovat rucne (nedoporuceno), nebo to nechat na php, ktere salt rovnou pridat do hashovaneho hesla
	
	/**
	 * We just want to hash our password using the current DEFAULT algorithm.
	 * This is presently BCRYPT, and will produce a 60 character result.
	 *
	 * Beware that DEFAULT may change over time, so you would want to prepare
	 * By allowing your storage to expand past 60 characters (255 would be good)
	 */
	# dalsi moznosti je vynutit bcrypt: PASSWORD_BCRYPT
	$hashed = password_hash($password, PASSWORD_DEFAULT);
	
	#vlozime usera do databaze
	$stmt = $db->prepare("INSERT INTO users(user, email, password) VALUES (?, ?, ?)");
	$stmt->execute(array($nick, $email, $hashed));
	
	#ted je uzivatel ulozen, bud muzeme vzit id posledniho zaznamu pres last insert id (co kdyz se to potka s vice requesty = nebezpecne), nebo nacist uzivatele podle mailove adresy (ok, bezpecne)
	
	$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji
	$stmt->execute(array($email));
	$user_id = (int)$stmt->fetchColumn();
			
	$_SESSION['user_id'] = $user_id;
		
	header('Location: index.php');
	
}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>PHP Shopping App</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">
	<link rel="stylesheet" href="styles.css">
</head>

<body>
	
<div class="container">
	<div class="card bg-light">
		<article class="card-body mx-auto" style="max-width: 400px;">
			<h4 class="card-title mt-3 text-center">Registrace</h4>
			<p class="text-center">Začněte vytvořením svého účtu</p>
			<p>
				<a href="" class="btn btn-block btn-facebook"> <i class="fab fa-facebook-f"></i>   Registrovat přes Facebook</a>
			</p>
			<p class="divider-text">
        		<span class="bg-light">Nebo</span>
    		</p>
	
	<form action="" method="POST">
		<div class="form-group input-group">
			<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-user"></i> </span>
		 	</div>
        	<input name="nick" class="form-control" placeholder="Uživatelské jméno" type="text">
    	</div> <!-- form-group// -->
    
    	<div class="form-group input-group">
    		<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-envelope"></i> </span>
		 	</div>
        	<input name="email" class="form-control" placeholder="Emailová adresa" type="email">
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

<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>	
</body>

</html>