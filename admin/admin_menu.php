<?php 

	$access = perm ('admin_dashboard', $_SESSION['user_role']);

	if ($access==1){
		echo '<div class="dropdown-divider"></div>';
		if (basename(getcwd())=='admin'){
				echo'<a href="./" class="dropdown-item">Administrační sekce</a>';
			} else {
				echo'<a href="admin/index.php" class="dropdown-item">Administrační sekce</a>';
			}
	}
	
?>