<?php

session_start();
# pripojeni do db
require '../assets/db.php';

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>SimpleCMS</title>
	
	<?php include '../assets/styles.php'; ?>
	
</head>


<body>
<?php include '../navbar.php'; ?>
<div class="container">	
 <div class="row">
    <div class="col-sm-4">
        <a href="manage_users.php" class="tile">
          <h3 class="title"><i class="fas fa-users"></i></h3>
          <p>Administrace uživatelů</p>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="manage_roles.php" class="tile">
          <h3 class="title"><i class="fas fa-user-tag"></i></h3>
          <p>Administrace uživatelských rolí</p>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="admin_posts.php" class="tile">
          <h3 class="title"><i class="fas fa-plus-square"></i></h3>
          <p>Správa přípěvků</p>
        </a>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-4">
        <a href="admin_category.php" class="tile">
          <h3 class="title"><i class="fas fa-layer-group"></i></h3>
          <p>Správa kategorií</p>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="#" class="tile">
          <h3 class="title"><i class="fas fa-question"></i></h3>
          <p>Zatím nic</p>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="#" class="tile">
          <h3 class="title"><i class="fas fa-question"></i></h3>
          <p>Zatím nic</p>
        </a>
    </div>
  </div>
</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>

