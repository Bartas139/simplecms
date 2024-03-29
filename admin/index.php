<?php

session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require_once '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';
//Pro pristup je potrebné opravnení manage_roles
$access = perm ('admin_dashboard', $_SESSION['user_role']);

if ($access == 0){
	http_response_code(403);
    include('../errors/403.php');
    die();
}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>SimpleCMS - Administrace</title>
	
	<?php include '../assets/styles.php'; ?>
	
</head>


<body>
<?php include '../navbar.php'; ?>
<header class="d-flex align-items-center">
	<div class="container">
		<h1>Administrační sekce</h1>
		<p>Zobrazují se všechny části administrace ke kterým máte oprávnění přistupovat</p>
	</div>
</header>	
<div class="container">	
<div class="row">
<?php
	$query = $db->prepare('SELECT DISTINCT permissions.name, permissions.description, permissions.admin_menu_item, permissions.icon FROM permissions JOIN role_perm WHERE role_perm.role_id=?');
	$query->execute(array($_SESSION["user_role"]));
	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);

	 foreach ($permissions as $perm){
    if ($perm['admin_menu_item']==1 && perm ($perm['name'], $_SESSION['user_role']) == 1){ ?>
            <div class="col-sm-4">
              <a href="<?php echo $perm['name'] ?>.php" class="tile">
                <span class="title"><?php echo $perm['icon'] ?> </span>
                <p><?php echo htmlspecialchars($perm['description']) ?></p>
              </a>
            </div> <?php
    }
  }
?>

</div>


  
</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>