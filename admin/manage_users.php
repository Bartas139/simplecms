<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_users', $_SESSION['user_id']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}

if(!empty($_POST) && (@$_POST['action']=='edit')){
    $errors="";

    //Ověření jestli už daný name/email není v databázi?
    $query = $db->prepare('SELECT name, email FROM users WHERE (name=? OR email=?) AND NOT id=? LIMIT 1');
    $query->execute(array($_POST["name"],$_POST["email"],$_POST["id"]));
    $userexist = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($_POST["name"])){
            $errors.="Uživatel musí mít jméno<br />";
        } elseif(empty($_POST["email"])){
            $errors.="Uživatel musí mít email<br />";
        } elseif ($userexist["name"]==$_POST["name"]) {
            $errors.="Uživatel s tímto jménem již existuje<br />";
        } elseif ($userexist["email"]==$_POST["email"]) {
            $errors.="Uživatel s tímto emailem již existuje<br />";
        } elseif(empty($_POST["role"])){
            $errors.="Uživatel musí mít roli<br />";
        } elseif (!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
            $errors.="Zadej platný email<br />";
        }


        if (empty($errors)) {
            $query = $db->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
            $query->execute(array($_POST["name"], $_POST["email"], $_POST["role"], $_POST["id"]));
            //Pokud uživatel upraví sám sebe, je nutné ho upravit v session
            if($_POST["id"]==$_SESSION['user_id']){
                $_SESSION['user_name'] = $_POST["name"];
                $_SESSION['user_role'] = $_POST["role"];
            }
            header('Location: manage_users.php');
        }

    }

        

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
    <?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
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
                    <a href="#edit'.$user['id'].'" data-toggle="collapse">Upravit</a>
            </td>';
        	echo '</tr>'; ?>
            <tr id="edit<?php echo $user["id"] ?>" class="collapse out">
                <form method="post">
                        <input type="hidden" name="action" value="edit" />
                        <td><input type="text" name="id" id="id" class="form-control" value="<?php echo htmlspecialchars($user['id']) ?>" readonly /></td>
                        <td><input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($user['name']) ?>" required /></td>
                        <td><input type="text" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']) ?>" required /></td>
                        <td><select class="form-control" id="role" name="role" required>
                        <?php
                                $query = $db->prepare('SELECT * FROM roles ORDER BY name;');
                                $query->execute();
                                $roles = $query->fetchALL(PDO::FETCH_ASSOC);

                                foreach ($roles as $role){
                                    if ($role["name"]==$user['role']){
                                    echo '<option selected value="'.$role["id"].'">'. $role["name"].'</option>';
                                    }
                                    else {
                                    echo '<option value="'.$role["id"].'">'. $role["name"].'</option>';  
                                    }
                                }    
                            ?>
                        </select></td>
                        <td><input type="text" name="registred" id="registred" class="form-control" value="<?php echo htmlspecialchars($user['registred']) ?>" readonly /></td>
                        <td><input type="submit" value="Uložit" class="btn btn-primary send"/></td>
                </form>    
            </tr> 
            <?php

            
        }
        echo '</table>';    
    ?>
</div><?php include '../assets/scripts.php'; ?>
		</body>

		</html>