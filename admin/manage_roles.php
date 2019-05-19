<?php

# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_roles', $_SESSION['user_id']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}

if(!empty($_POST) && (@$_POST['action']=='update')){
	//Pokud není zaškrtlé žádné pole, tak toto pole nevznikne a není možné s ním pracovat.
	if (!isset($_POST['checkbox'])){
		$_POST['checkbox'] = [];
	}

	//Výber všech perm přiřazených dané roli
	$query = $db->prepare('SELECT perm_id FROM role_perm WHERE role_id = ?');
	$query->execute(array($_POST["role"]));
	$permissions = $query->fetchALL(PDO::FETCH_COLUMN, 0);

	//Vložení vybraných do databáze, popřípadě ponechání původních
	foreach($_POST['checkbox'] as $value) {
		if (!in_array($value, $permissions)){
			//INSERT VALUE TO role_perm  + role ID
			$query = $db->prepare('INSERT INTO role_perm (role_id, perm_id) VALUES (?, ?)');
			$query->execute(array($_POST["role"],$value));
		}
	}

	//Smazaní těch co nejsou zaškrtlé
	$noncheck = array_diff($permissions, $_POST['checkbox']);
	foreach($noncheck as $value) {
		$stmt = $db->prepare("DELETE FROM role_perm WHERE role_id=? AND perm_id=?");
		$stmt->execute(array($_POST['role'], $value));
	}
	header('Location: role_management.php');
}

if(!empty($_POST) && (@$_POST['action']=='create')){
	//Pokud není zaškrtlé žádné pole, tak toto pole nevznikne a není možné s ním pracovat.
	if (!isset($_POST['checkbox'])){
		$_POST['checkbox'] = [];
	}

	//vložení role
	$query = $db->prepare('INSERT INTO roles (name) VALUES (?)');
	$query->execute(array($_POST["name"]));

	//získání ID role, přes role name (přes last inserted nebezpečné)
	$query = $db->prepare('SELECT id FROM Roles WHERE name=? LIMIT 1');
	$query->execute(array($_POST["name"]));
	$newid = $query->fetchColumn();

	//Vložení vybraných do databáze
	foreach($_POST['checkbox'] as $value) {
			$query = $db->prepare('INSERT INTO role_perm (role_id, perm_id) VALUES (?, ?)');
			$query->execute(array($newid,$value));
	}


	header('Location: role_management.php');
}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Role Management</title>
	
	<?php include '../assets/styles.php'; ?>
	
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container">
	<h1>Administrace rolí a přístupů</h1>

	<?php
        $query = $db->prepare('SELECT * FROM roles ORDER BY name;');
        $query->execute();
        $roles = $query->fetchALL(PDO::FETCH_ASSOC);
        foreach ($roles as $role){
            
        	if ($role['name'] == 'SuperAdmin'){
        		echo '<h2 id="' . htmlspecialchars($role["name"]) . '">'. htmlspecialchars($role["name"]) .'</h2>';
        		echo 'Tato role má všechna oprávnění a nelze ji měnit';
        	} else {
        		echo '<h2 id="' . htmlspecialchars($role["name"]) . '">'. htmlspecialchars($role["name"]) .'</h2>';
        		echo '<form method="post">';
        		echo '<input type="hidden" name="action" value="update" />';
        		echo '<input type="hidden" name="role" value="'.$role["id"].'" />';
	            $query = $db->prepare('SELECT permissions.id, permissions.name FROM Permissions RIGHT JOIN role_perm ON permissions.id=role_perm.perm_id WHERE (role_perm.role_id=?)');
	        	$query->execute(array($role["id"]));
	        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
	        	echo '<br />Aktuální oprávnění <br />';
	        	foreach ($permissions as $permission){
		            echo '<input type="checkbox" value="' . $permission["id"] . '" name="checkbox[]" checked>'. $permission["name"] .'</input>';
	        	}
	        	
	        	$query = $db->prepare('SELECT * from Permissions WHERE (id, name) NOT IN (SELECT permissions.id, permissions.name FROM Permissions RIGHT JOIN role_perm ON permissions.id=role_perm.perm_id WHERE (role_perm.role_id=?))');
	        	$query->execute(array($role["id"]));
	        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
	        	echo '<br />Dostupná oprávnění <br />';
	        	foreach ($permissions as $permission){
		            echo '<input type="checkbox" value="' . $permission["id"] . '" name="checkbox[]">'. $permission["name"] .'</input>';
	        		}
	        	echo '<input type="submit" value="Odeslat" />';	
	        	echo '</form>';	
        		}

            
        }    
    ?>
<h2>Vytvořit novou roli</h2>
<form method="post">
                    <?php echo (!empty($chyby)?'<div class="alert alert-danger"><strong>'.$chyby.'</strong></div>':'');?>
      <input type="hidden" name="action" value="create" />
                   
                    <div class="form-group">
                        <label for="name">Název</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php htmlspecialchars(@$_REQUEST['name']) ?>" required />
                    </div>
                    <?php
					$query = $db->prepare('SELECT * from Permissions');
						        	$query->execute();
						        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
						        	echo '<br />Dostupná oprávnění <br />';
						        	foreach ($permissions as $permission){
							            echo '<input type="checkbox" value="' . $permission["id"] . '" name="checkbox[]">'. $permission["name"] .'</input>';
						        		}
                    ?>
                    <div class="form-group">
                        <input type="submit" value="Odeslat" class="btn btn-primary"/>
                    </div>    
                </form>

        </div>

</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>


