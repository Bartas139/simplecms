<?php
//pripojeni do db na serveru eso.vse.cz
$db = new PDO('mysql:host=localhost:3306;dbname=cms;charset=utf8', 'root', '');
//vyhazuje vyjimky v pripade neplatneho SQL vyrazu
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

define('BASE_PATH','https://eso.vse.cz/~cham09/cms');