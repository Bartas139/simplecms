<?php



function perm($perm, $current_role){
	require 'db.php';

	if ($current_role == 'SuperAdmin') {
		$result = 1;
	} else {

		$stmt = $db->prepare("SELECT * FROM role_perm JOIN permissions ON role_perm.perm_id=permissions.id WHERE permissions.name = ? AND role_perm.role_id = ?");
		$stmt->execute(array($perm, $current_role));
		$result = $stmt->fetch();
	
		if (empty($result)) {
			$result = 0;
		}else{
			$result = 1;
		}
	}

	return ($result);
}



