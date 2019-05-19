<?php



function perm($perm, $current_id){
	require 'db.php';

	$stmt = $db->prepare("SELECT roles.name FROM users RIGHT JOIN roles ON users.role=roles.id WHERE users.id=?");
	$stmt->execute(array($current_id));
	$current_role = $stmt->fetchColumn(0);

	if ($current_role == 'SuperAdmin') {
		return (1);
	} else {

		$stmt = $db->prepare("SELECT id FROM permissions WHERE name=?");
		$stmt->execute(array($perm));
		$perm_id = $stmt->fetch();

		$stmt = $db->prepare("SELECT role FROM users WHERE id=?");
		$stmt->execute(array($current_id));
		$role_id = $stmt->fetch();

		$stmt = $db->prepare("SELECT * FROM role_perm WHERE perm_id=? AND role_id=? ");
		$stmt->execute(array($perm_id['id'], $role_id['role']));
		$result = $stmt->fetch();
	
		if (empty($result)) {
			return (0);
		}else{
			return (1);
		}
	}


}