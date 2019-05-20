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

if(!empty($_POST) && (@$_POST['action']=='update')){
	$errors="";

	//Pokud není zaškrtlé žádné pole, tak toto pole nevznikne a není možné s ním pracovat.
	if (!isset($_POST['checkbox'])){
		$_POST['checkbox'] = [];
	}


	//existuje už role?
	$query = $db->prepare('SELECT name FROM roles WHERE name=? LIMIT 1');
	$query->execute(array($_POST["name"]));
	$roleexist = $query->fetchColumn();

	if ($_POST["name"] != $_POST["rolename"]){
		//todo zjistit jestli se nový název už nenacháí v DB, když ne update název a pak inserty, když ano error
		if (empty($_POST["name"])){
			$errors.="Kategorie musí mít název<br />";
		} elseif (!empty($roleexist)) {
			$errors.="Kategorie s tímto názvem už existuje<br />";
		} else {
			$query = $db->prepare('UPDATE roles SET name=? WHERE id=?');
			$query->execute(array($_POST["name"], $_POST["role"]));
		}

	}

	if (empty($errors)){
		//Výber všech perm přiřazených dané roli
		$query = $db->prepare('SELECT perm_id FROM role_perm WHERE role_id = ?');
		$query->execute(array($_POST["role"]));
		$permissions = $query->fetchALL(PDO::FETCH_COLUMN, 0); //fetch do obyčejného array

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
		header('Location: manage_roles.php');
	}
}

if(!empty($_POST) && (@$_POST['action']=='create')){
	$errors="";

	//Pokud není zaškrtlé žádné pole, tak toto pole nevznikne a není možné s ním pracovat.
	if (!isset($_POST['checkbox'])){
		$_POST['checkbox'] = [];
	}
	//Oveření chyb
	if (empty($_POST['name'])){
		$errors.="Kategorie musí mít název<br />";
	}

	$query = $db->prepare('SELECT name FROM roles WHERE name=? LIMIT 1');
	$query->execute(array($_POST["name"]));
	$roleexist = $query->fetchColumn();
	if (!empty($roleexist)){
		$errors.="Kategorie s názvem: ".$roleexist.", již existuje<br />";
	}

	if (empty($errors)){
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


		header('Location: manage_roles.php');
	}
	
}

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Administrace rolí</title>
	
	<?php include '../assets/styles.php'; ?>
	
</head>

<body>
	<?php include '../navbar.php'; ?>
	<div class="container">
	<h1>Administrace rolí a přístupů</h1>
	<?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
	<?php
        $query = $db->prepare('SELECT * FROM roles ORDER BY name;');
        $query->execute();
        $roles = $query->fetchALL(PDO::FETCH_ASSOC);
        foreach ($roles as $role){
            
        	if ($role['name'] == 'SuperAdmin'){
        		echo '<div class="card adminrole"><div class="card-header"><h2 id="' . htmlspecialchars($role["name"]) . '">'. htmlspecialchars($role["name"]) .'</h2></div>';
        		echo '<div class="card-body">Tato role má všechna oprávnění a nelze ji měnit</div></div>';
        	} else {
        		echo '<div class="card adminrole"><div class="card-header"><h2 id="' . htmlspecialchars($role["name"]) . '">'. htmlspecialchars($role["name"]) .'</h2></div>';
        		echo '<form method="post"><div class="card-body">';
        		echo '<input type="hidden" name="action" value="update" />';
        		echo '<input type="hidden" name="role" value="'.$role["id"].'" />';
        		echo '<input type="hidden" name="rolename" value="'.htmlspecialchars($role["name"]).'" />';
				echo '<div class="form-group"><label for="name" >Upravte, pokud nechcete upravit název role:</label>';
        		echo '<input class="form-control" type="text" id="name" name="name" value="'.htmlspecialchars($role["name"]).'" /></div>';
	            $query = $db->prepare('SELECT permissions.id, permissions.name, permissions.description FROM Permissions RIGHT JOIN role_perm ON permissions.id=role_perm.perm_id WHERE (role_perm.role_id=?)');
	        	$query->execute(array($role["id"]));
	        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
	        	echo '<h3>Aktuální oprávnění </h3>';
	        	foreach ($permissions as $permission){
	        		echo '<div class="custom-control custom-checkbox mb-3">';
		            echo '<input class="custom-control-input" id="'. $permission["id"] . $role["id"] .'" type="checkbox" value="' . $permission["id"] . '" name="checkbox[]" checked />';
		            echo '<label class="custom-control-label" for="'.$permission["id"].  $role["id"] .'" >'.htmlspecialchars($permission["description"]).'</label>';
		            echo '</div>';
	        	}
	        	
	        	$query = $db->prepare('SELECT * from Permissions WHERE (id, name) NOT IN (SELECT permissions.id, permissions.name FROM Permissions RIGHT JOIN role_perm ON permissions.id=role_perm.perm_id WHERE (role_perm.role_id=?))');
	        	$query->execute(array($role["id"]));
	        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
	        	echo '<h3>Dostupná oprávnění </h3>';
	        	foreach ($permissions as $permission){
		            echo '<div class="custom-control custom-checkbox mb-3">';
		            echo '<input class="custom-control-input" id="'. $permission["id"] . $role["id"] . '" type="checkbox" value="' . $permission["id"] . '" name="checkbox[]" />';
		            echo '<label class="custom-control-label" for="'.$permission["id"]. $role["id"] .'" >'.htmlspecialchars($permission["description"]).'</label>';
		            echo '</div>';
	        		}
	        	echo '</div><div class="card-footer"><input type="submit" value="Uložit" class="btn btn-primary send"/>';
	        	echo '<a role="button" class="btn btn-primary send" href="delete_role.php?id='. $role["id"].'" onclick="return confirm(\'Přejete si smazat roli ' . htmlspecialchars($role['name']) . '\')">Smazat</a>';

	        	echo '</form></div></div>';	
        		}

            
        }    
    ?>
<div class="card adminrole"><div class="card-header"><h2>Vytvořit novou roli</h2></div>
<form method="post">
   <div class="card-body">                 
      <input type="hidden" name="action" value="create" />
                   
                    <div class="form-group">
                        <label for="name">Název</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars(@$_POST['name']) ?>" required />
                    </div>
                    <?php
					$query = $db->prepare('SELECT * from Permissions');
						        	$query->execute();
						        	$permissions = $query->fetchALL(PDO::FETCH_ASSOC);
						        	echo '<br />Dostupná oprávnění <br />';
						        	foreach ($permissions as $permission){
						        		echo '<div class="custom-control custom-checkbox mb-3">';
							            echo '<input class="custom-control-input" id="'. $permission["id"] .'" type="checkbox" value="' . $permission["id"] . '" name="checkbox[]" />';
							            echo '<label class="custom-control-label" for="'.$permission["id"].'" >'.htmlspecialchars($permission["description"]).'</label>';
							            echo '</div>';
						        		}
                    ?>
                </div><div class="card-footer">
                    <div class="form-group">
                        <input type="submit" value="Vytvořit" class="btn btn-primary send"/>
                    </div>
                    </div>    
                </form>
</div>
        </div>

</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>


