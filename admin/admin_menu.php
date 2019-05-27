<?php 

	$access = perm ('admin_dashboard', $_SESSION['user_role']);

	if ($access==1){
		echo '<div class="dropdown-divider"></div>';
		echo'<a href="'.BASE_PATH.'/admin/" class="dropdown-item">Administrační sekce</a>';
	}
	