<?php
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_users', $_SESSION['user_id']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Administrace uživatelů</title>
	
	<?php include '../assets/styles.php'; ?>
</head>

<body>
    <?php include '../navbar.php'; ?>
	<div class="container">
	<h1>Administrace uživatelů</h1>

	<?php
        $query = $db->prepare('SELECT * FROM users');
        $query->execute();
        $users = $query->fetchALL(PDO::FETCH_ASSOC);

        
        ?>
        <table class="table table-striped">
        <thead>
              <tr>
                <th>ID</th>
                <th>Nick</th>
                <th>Email</th>
                <th>Role</th>
                <th>Zaregistrován</th>
                <th>Akce</th>
              </tr>
            </thead>
            <?php
        foreach ($users as $user){
            $query = $db->prepare('SELECT name FROM roles WHERE id=?');
            $query->execute(array($user['role']));
            $role = $query->fetchColumn();

            echo '<tr>';
        	echo '<td>' . $user['id'] . '</td>';
        	echo '<td>' . htmlspecialchars($user['name']) . '</td>'; 
        	echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . htmlspecialchars($role) . '</td>';
            echo '<td>' . $user['registred'] . '</td>';
        	echo '<td><a href="delete_user.php?id='. $user["id"].'" onclick="return confirm(\'Přejete si smazat uživatele ' . htmlspecialchars($user['name']) . '\')">Smazat</a>
                    <a href="#">Upravit</a>
            </td>';
        	echo '</tr>';

            
        }
        echo '</table>';    
    ?>
</div><?php include '../assets/scripts.php'; ?>
		</body>

		</html>