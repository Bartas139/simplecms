<?php

session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';
//Pro pristup je potrebné opravnení manage_roles
$access = perm ('admin_dashboard', $_SESSION['user_role']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>SimpleCMS - Administrace</title>
	
	<?php include '../assets/styles.php'; ?>
	
</head>


<body>
<?php include '../navbar.php'; ?>
<div class="container">	
<div class="row">
<?php
	$query = $db->prepare('SELECT DISTINCT permissions.name, permissions.description, permissions.admin_menu_item, permissions.icon FROM permissions JOIN role_perm WHERE role_perm.role_id=?');
	$query->execute(array($_SESSION["user_role"]));
	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);

	 foreach ($permissions as $perm){
    if ($perm['admin_menu_item']==1){ ?>
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