<?php

session_start();

require 'assets/db.php';

//Pokud je odeslaný formulář s emailem	
if ($_SERVER["REQUEST_METHOD"] == "POST" && (@$_POST['action']=='generate-token')) {
		
		$email = $_POST['email'];
		
	
		//Ověření, že existuje uživatel se zadaným emailem	
		$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
		$stmt->execute(array($email));
		$existing_user = $stmt->fetchColumn();
		$error = "";
		if(empty($existing_user)){
	
			$error .= 'Pod tímto emailem není nikdo registrován';
		} else {

			//Smazání záznamu tokenu uživatele, pokud nějaký existuje
			$stmt = $db->prepare("SELECT id FROM reset_password WHERE user = ? LIMIT 1");
			$stmt->execute(array($existing_user));
			$existing_token = $stmt->fetchColumn();
			if (!empty($existing_user)){
				$stmt = $db->prepare("DELETE FROM reset_password WHERE id = ?");
				$stmt->execute(array($existing_token));	
			}

			$token = bin2hex(random_bytes(64)); //Generování tokenu
			$stmt = $db->prepare("INSERT INTO reset_password(user, token, expires) VALUES (?, ?, (NOW() + INTERVAL 1 HOUR))"); //insert do databáze využití SQL k nastavení času expirace
			$stmt->execute(array($existing_user, $token));



			$to      = $email;
			$subject = '[noreply]SimpleCMS - Obnova hesla';
			$message = '<p>Obdrželi jsme požadavek na změnu hesla k vašemu účtu.</p>';
			$message .= '<a href="'.BASE_PATH.'/lost-password.php?token='.$token.'&step=3">Odkaz pro obnovu hesla</a>';
			$message .= '<p>Odkaz pro obnovu hesla bude aktivní jen 1 hodinu.</p>';
			$message .= '<p>Na tento email neodpovídejte.</p>';

			$headers = [

			    'MIME-Version: 1.0',

			    'Content-type: text/html; charset=utf-8',

			    'From: noreply@straightplay.cz',

			    'Reply-To: noreply@straightplay.cz',

			    'X-Mailer: PHP/'.phpversion()

			];
			//udělá to string z pole
			$headers = implode("\r\n", $headers);

			if(mail($to, $subject, $message, $headers)){
				header('Location: '.BASE_PATH.'/lost-password.php?step=2');
			}
		}
}

if ($_GET["step"]==3 && isset($_GET["token"])) {
	$stmt = $db->prepare("SELECT id, user, expires FROM reset_password WHERE token = ? LIMIT 1");
	$stmt->execute(array($_GET['token']));
	$token = $stmt->fetch();

	if (!empty($token) && ($token['expires']>date("Y-m-d H:i:s"))){
		
		$valid = true;	
	} else {
		echo '<br />' . date("Y-m-d H:i:s");
		$valid = false;
	}	
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && (@$_POST['action']=='change-password')) {
	$error = "";
	if (empty($_POST['password'])){
		$error .= "Heslo je  povinné<br />";	
		}
		if (strlen($_POST["password"]) < '8') {
            $error .= "Heslo musí mít minimálně 8 znaků<br />";
        }
        if(!preg_match("#[0-9]+#",$_POST["password"])) {
            $error .= "Heslo musí obsahovat minimálně jednu číslici<br />";
        }
        if(!preg_match("#[A-Z]+#",$_POST["password"])) {
            $error .= "Heslo musí obsahovat minimálně jedno velké písmeno<br />";
        }
        if(!preg_match("#[a-z]+#",$_POST["password"])) {
            $error .= "Heslo musí obsahovat minimálně jedno malé písmeno<br />";
        }
	if ($_POST['password']!=$_POST['checkpassword']){
		$error .= "Hesla se neshodují<br />";	
	}
	if (empty($error)){
		//bcrypt: PASSWORD_BCRYPT
		$hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
		
		#vlozime usera do databaze
		$stmt = $db->prepare("UPDATE users SET password = ? WHERE id=?");
		$stmt->execute(array($hashed, $_POST['user']));

		$stmt = $db->prepare("DELETE FROM reset_password WHERE id = ?");
		$stmt->execute(array($_POST['tokenid']));

		$_SESSION['changed'] = true;
		header('Location: '.BASE_PATH.'/lost-password.php?step=4');
	}
}


?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>Obnovení hesla</title>
	<?php include 'assets/styles.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>

<?php if(!isset($_GET['token']) && ($_GET['step']==1)) { ?>

<?php echo (!empty($error)?'<div class="alert alert-danger"><strong>'.$error.'</strong></div>':'');?>
<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Obnovení hesla (Krok 1/2)</h4>
			<p class="text-center">Zadejte váš registrační email</p>
	
	<form method="POST">
		<input type="hidden" name="action" value="generate-token" />
		<div class="form-group input-group">
			<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-user"></i> </span>
		 	</div>
        	<input name="email" class="form-control" placeholder="Váš email" type="text" value="<?php echo htmlspecialchars(@$_POST['email']) ?>">
    	</div> <!-- form-group// -->
    
    	<div class="form-group">
	        <button type="submit" class="btn btn-primary btn-block">Resetovat</button>
	    </div> <!-- form-group// -->      
	    
	    <p class="text-center">Ještě nejste registrovaní? <a href="signup.php">Zaregistrujte se</a>. Znáte své heslo? <a href="signup.php">Přihlaste se</a></p>                                                                 
</form>
</article>
</div> <!-- card.// -->
 
</div>
<?php }elseif (!isset($_GET['token']) && $_GET['step']==2){ ?>

	<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Obnovení hesla</h4>
			<p class="text-center">Na váš email byl odeslán odkaz pro obnovu hesla. Odkaz bude platný 1 hodinu.</p>

</article>
</div> <!-- card.// -->
 
</div>

<?php }elseif ($_GET['step']==3 && isset($valid) && $valid==true){ ?>

	<?php echo (!empty($error)?'<div class="alert alert-danger"><strong>'.$error.'</strong></div>':'');?>
<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Obnovení hesla (Krok 2/2)</h4>
			<p class="text-center">Token je platný, nyní si zvolte nové heslo</p>
	
	<form action="" method="POST">
		<input type="hidden" name="action" value="change-password" />
		<input type="hidden" name="user" value="<?php echo $token['user']; ?>" />
		<input type="hidden" name="tokenid" value="<?php echo $token['id']; ?>" />
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
	        <button type="submit" class="btn btn-primary btn-block">Změnit heslo</button>
	    </div> <!-- form-group// -->      
	    
	    <p class="text-center">Ještě nejste registrovaní? <a href="signup.php">Zaregistrujte se</a>. Znáte své heslo? <a href="signup.php">Přihlaste se</a></p>                                                                 
</form>
</article>
</div> <!-- card.// -->
 
</div>

<?php }elseif ($_GET['step']==3 && isset($valid) && $valid==false){ ?>

	<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Obnovení hesla</h4>
			<p class="text-center">Tento token není platný.</p>

</article>
</div> <!-- card.// -->
 
</div>

<?php }elseif ($_GET['step']==4 && $_SESSION['changed']==true){ ?>

	<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Obnovení hesla</h4>
			<p class="text-center">Nové heslo bylo úspěšně nastaveno, nyní se můžete přihlásit.</p>

</article>
</div> <!-- card.// -->
 
</div>

<?php } else {
	echo '<script type="text/javascript">
   window.location = "' .BASE_PATH.'/signin.php"
</script>';
}
include 'assets/footer.php';
include 'assets/scripts.php'; ?>	

</body>

</html>




