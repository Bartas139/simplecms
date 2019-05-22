<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('manage_users', $_SESSION['user_role']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}
//update uživatele
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
//insert uživatele
    if(!empty($_POST) && (@$_POST['action']=='create')){
    $errors="";

    //Ověření jestli už daný name/email není v databázi?
    $query = $db->prepare('SELECT name, email FROM users WHERE (name=? OR email=?) LIMIT 1');
    $query->execute(array($_POST["name"],$_POST["email"]));
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
        } elseif (empty($_POST["password"])){
            $errors.="Zadej heslo<br />";
        }


        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO users(name, email, role, password) VALUES (?, ?, ?, ?)");
            $stmt->execute(array($_POST["name"], $_POST["email"], $_POST["role"], $hashed));
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

        <ul class="nav nav-tabs admin-nav">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#page1">Seznam uživatelů</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#page2">Vytvořit nového uživatele</a>
          </li>
        </ul>
        <?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
        <div class="tab-content">
        <div id="page1" class="tab-pane active">

	<h2>Výpis uživatelů</h2>
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
            $currentrole = $query->fetchColumn();

            echo '<tr>';
        	echo '<td>' . $user['id'] . '</td>';
        	echo '<td>' . htmlspecialchars($user['name']) . '</td>'; 
        	echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . htmlspecialchars($currentrole) . '</td>';
            echo '<td>' . date( 'd.m.Y H:i:s', strtotime($user['registred']) ) . '</td>';
        	echo '<td><a href="delete_user.php?id='. $user["id"].'" onclick="return confirm(\'Přejete si smazat uživatele ' . htmlspecialchars($user['name']) . '\')">Smazat</a>
                    <a href="#edit'.$user['id'].'" data-toggle="modal">Upravit</a>
            </td>';
        	echo '</tr>'; 
        }
        echo '</table>'; 

        //Počet rolí v databázi, kvůli option size --> validita HTML
        $query = $db->prepare('SELECT COUNT(name) FROM roles');
        $query->execute();
        $rolecount = $query->fetchColumn();

        foreach ($users as $user){
            $query = $db->prepare('SELECT name FROM roles WHERE id=?');
            $query->execute(array($user['role']));
            $currentrole = $query->fetchColumn();

            ?>
                    <div id="edit<?php echo $user["id"] ?>" class="modal fade">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header">
                                      <h4 class="modal-title">Upravit uživatele: <?php echo htmlspecialchars($user['name']); ?></h4>
                                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="post">
                                            <input type="hidden" name="action" value="edit" />
                                            <label for="id<?php echo $user['id']; ?>">ID uživatele (nelze editovat)</label>
                                            <input type="text" name="id" id="id<?php echo $user['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($user['id']) ?>" readonly />
                                            <label for="name<?php echo $user['id']; ?>">Jméno uživatele</label>
                                            <input type="text" name="name" id="name<?php echo $user['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($user['name']) ?>" required />
                                            <label for="email<?php echo $user['id']; ?>">Email uživatele</label>
                                            <input type="text" name="email" id="email<?php echo $user['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($user['email']) ?>" required />
                                            <label for="role<?php echo $user['id']; ?>">Role uživatele</label>
                                            <select class="form-control" id="role<?php echo $user['id']; ?>" name="role" size="<?php echo $rolecount; ?>" required>
                                            <?php
                                                    $query = $db->prepare('SELECT * FROM roles ORDER BY name;');
                                                    $query->execute();
                                                    $roles = $query->fetchALL(PDO::FETCH_ASSOC);

                                                    foreach ($roles as $role){
                                                        if ($role["name"]==$currentrole){
                                                        echo '<option selected value="'.$role["id"].'">'. $role["name"].'</option>';
                                                        }
                                                        else {
                                                        echo '<option value="'.$role["id"].'">'. $role["name"].'</option>';  
                                                        }
                                                    }    
                                                ?>
                                            </select>
                                            <label for="registred<?php echo $user['id']; ?>">Uživatel registrován</label>
                                            <input type="text" name="registred" id="registred<?php echo $user['id']; ?>" class="form-control" value="<?php echo date( 'd.m.Y H:i:s', strtotime($user['registred']) ) ?>" readonly />
                                            <input type="submit" value="Uložit" class="btn btn-primary send"/>
                                    </form>
                                </div>    
                            </div>    
                        </div>
                    </div>
            <?php
        }   
    ?>
</div>
        <div id="page2" class="tab-pane">
            <h2>Nový uživatel</h2>

            <form method="post">
                                            <input type="hidden" name="action" value="create" />
                                            <label for="name">Jméno uživatele</label>
                                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars(@$_POST['name']) ?>" required />
                                            <label for="email">Email uživatele</label>
                                            <input type="text" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars(@$_POST['email']) ?>" required />
                                            <label for="role">Role uživatele</label>
                                            <select class="form-control" id="role" name="role" size="<?php echo $rolecount; ?>" required>
                                            <?php
                                                    $query = $db->prepare('SELECT * FROM roles ORDER BY name;');
                                                    $query->execute();
                                                    $roles = $query->fetchALL(PDO::FETCH_ASSOC);

                                                    foreach ($roles as $role){
                                                        echo '<option value="'.$role["id"].'">'. $role["name"].'</option>';  
                                                    }    
                                                ?>
                                            </select>
                                            <label for="role">Heslo uživatele</label>
                                            <input type="password" name="password" id="password" class="form-control" value="" required />
                                            <input type="submit" value="Uložit" class="btn btn-primary send"/>
                                    </form>
        </div>
    </div>
</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>






