<?php



session_start();



if(!isset($_SESSION["user_id"])){

	header('Location: signin.php');

	die();

}



# v session je user id uzivatele, ted ho nacteme z db

$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1"); //limit 1 jen jako vykonnostni optimalizace, 2 stejne maily se v db nepotkaji

$stmt->execute(array($_SESSION["user_id"]));

$current_user = $stmt->fetchAll()[0];


# pokud by v db z nejakeho duvodu user nebyl (treba byl mezitim nejak smazan), tak vymaz session a jdi na prihlaseni		

if (!$current_user){

	session_destroy();

	header('Location: index.php');

	die();

}