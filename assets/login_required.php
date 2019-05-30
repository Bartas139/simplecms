<?php

if(!isset($_SESSION["user_id"])){

    header('Location: '.BASE_PATH.'/signin.php');
    die();
}



$stmt = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");

$stmt->execute(array($_SESSION["user_id"]));

$current_user = $stmt->fetchAll()[0];

if (!$current_user){

	session_destroy();

	header('Location: '.BASE_PATH.'/signin.php');
    die();


}