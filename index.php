<?php

session_start();
# pripojeni do db
require '/assets/db.php';
require '/assets/check_perm.php';
?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>SimpleCMS</title>
	
	<?php include 'assets/styles.php'; ?>
	
</head>


<body>
<?php include 'navbar.php'; ?>	

<?php include 'assets/scripts.php'; ?>
		</body>

		</html>

