<?php

session_start();

require 'assets/db.php';
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		$email = $_POST['email'];
		$password = $_POST['password'];
	
			
		$stmt = $db->prepare("SELECT * FROM users WHERE name = ? OR email = ? LIMIT 1");
		$stmt->execute(array($email, $email));
		$existing_user = @$stmt->fetchAll()[0];
	$error = "";
		if(password_verify($password, $existing_user["password"])){
	
			$_SESSION['user_id'] = $existing_user["id"];
			$_SESSION['user_name'] = $existing_user["name"];
			$_SESSION['user_role'] = $existing_user["role"];

			if (isset($_SESSION['source'])){
				header("Location: ". $_SESSION['source']);	
			} else {
				header('Location: index.php');
			}
			
	
		} else {
	
			$error .= 'Nesprávné přihlašovací údaje';
	
		}		
	
}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>Login</title>
	<?php include 'assets/styles.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>
<?php echo (!empty($error)?'<div class="alert alert-danger"><strong>'.$error.'</strong></div>':'');?>
<div class="container full-screen d-flex">
	
	<div class="mx-auto card bg-light justify-content-center align-self-center sign-form">
		<article class="card-body mx-auto">
			<h4 class="card-title mt-3 text-center">Přihlášení</h4>
			<p class="text-center">Přihlaste se pomocí svého účtu, nebo přes Facebook</p>
			<p>
				<a href="" class="btn btn-block btn-facebook"> <i class="fab fa-facebook-f"></i>   Přihlásit přes Facebook</a>
			</p>
			<p class="divider-text">
        		<span class="bg-light">Nebo</span>
    		</p>
	
	<form action="" method="POST">
		<div class="form-group input-group">
			<div class="input-group-prepend">
		    	<span class="input-group-text"> <i class="fa fa-user"></i> </span>
		 	</div>
        	<input name="email" class="form-control" placeholder="Uživatelské jméno" type="text" value="<?php echo htmlspecialchars(@$_POST['email']) ?>">
    	</div> <!-- form-group// -->
    
    	<div class="form-group input-group">
	    	<div class="input-group-prepend">
			    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
			</div>
	        <input class="form-control" placeholder="Heslo" type="password" name="password">
	    </div> <!-- form-group// -->

	    <div class="form-group">
	        <button type="submit" class="btn btn-primary btn-block">Přihlásit k účtu</button>
	    </div> <!-- form-group// -->      
	    
	    <p class="text-center">Ještě nejste registrovaní? <a href="signup.php">Zaregistrujte se</a> </p>                                                                 
</form>
</article>
</div> <!-- card.// -->
 
</div> 
<?php include 'assets/scripts.php'; ?>	

</body>

</html>