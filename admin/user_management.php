<?php
# pripojeni do db
require 'db.php';

# pristup jen pro prihlaseneho uzivatele
require 'login_required.php';
# pristup jen s perm manage_role
require 'check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_users', $_SESSION['user_id']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Role Management</title>
	
	<link rel="stylesheet" type="text/css" href="styles.css">
	
</head>

<body>
	<div class="col-sm-2">
		<?php include './admin_menu.php'; ?>
	</div>
	<div class="col-sm-10">
	<h1>Administrace uživatelů</h1>

	<?php
        $query = $db->prepare('SELECT * FROM users');
        $query->execute();
        $users = $query->fetchALL(PDO::FETCH_ASSOC);
        echo '<table>';
        foreach ($users as $user){
            echo '<tr>';
        	echo '<td>' . htmlspecialchars($user['id']) . '</td>';
        	echo '<td>' . htmlspecialchars($user['user']) . '</td>'; 
        	echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        	echo '<td><a href="user_delete.php" id="' . htmlspecialchars($user['id']) . '" onclick="return confirm(\'Přejete si smazat uživatele ' . htmlspecialchars($user['user']) . '\')">Smazat</a></td>';
        	echo '</tr>';

            
        }
        echo '</table>';    
    ?>
</div>
		</body>

		</html>