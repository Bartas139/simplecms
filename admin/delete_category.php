<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('edit_cat', $_SESSION['user_role']);

if ($access == 0){
	http_response_code(403);
    include('../errors/403.php');
    die();
}



$stmt = $db->prepare("DELETE FROM categories WHERE id=?");

$stmt->execute(array($_GET['id']));



header('Location: edit_cat.php');