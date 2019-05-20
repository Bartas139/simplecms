<?php
session_start();	
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_roles', $_SESSION['user_id']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}



$stmt = $db->prepare("DELETE FROM roles WHERE id=?");

$stmt->execute(array($_GET['id']));



header('Location: manage_roles.php');