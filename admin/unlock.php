<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

$access = perm ('edit_post', $_SESSION['user_role']);

if ($access == 0){
    http_response_code(403);
    include('../errors/403.php');
    die();
}

if (!empty($_GET['id'])) {


            $stmt = $db->prepare("UPDATE posts SET locked = ?, locked_user = ? WHERE id=?");
            $stmt->execute(array(null, null, $_GET['id']));
            header('Location: edit_post.php');
            die ();
        } else {
        	header('Location: edit_post.php');
        }