<?php



function perm($perm, $current_role){
	global $db;
	$query = $db->prepare('SELECT name FROM roles WHERE id=? LIMIT 1');
    $query->execute(array($current_role));
    $role_name = $query->fetchColumn();

	if ($role_name == 'SuperAdmin') {
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



