<?php 

	$query = $db->prepare('SELECT DISTINCT permissions.name, permissions.description, permissions.admin_menu_item FROM permissions JOIN role_perm WHERE role_perm.role_id=?');
	$query->execute(array($_SESSION["user_role"]));
	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);


	echo '<div class="dropdown-divider"></div>';
	if (basename(getcwd())=='admin'){
				echo'<a href="./" class="dropdown-item">Administrační sekce</a>';
			} else {
				echo'<a href="admin/index.php" class="dropdown-item">Administrační sekce</a>';
			}
?>