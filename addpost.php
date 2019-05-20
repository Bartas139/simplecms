<?php
session_start();
# pripojeni do db
require 'db.php';

# pristup jen pro prihlaseneho uzivatele
require 'login_required.php';

require 'check_perm.php';

$access = perm ('add_post', $_SESSION['user_id']);


?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>PHP Shopping App</title>
	
	<link rel="stylesheet" type="text/css" href="styles.css">
	
</head>

<body>
	<h1>Administrace příspěvků</h1>
	<?php
		if ($access == 1){
	?>
		ADD POST
	<?php } else { ?>
		Nemáš oprávnění přidávat příspěvky
	<?php } ?>
	

		</body>

		</html>



