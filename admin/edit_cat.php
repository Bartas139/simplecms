<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení edit_cat
$access = perm ('edit_cat', $_SESSION['user_role']);

if ($access == 0){die ('Chyba  403: Nemáte oprávnění pro přístup na tuto stránku');}
//update uživatele
if(!empty($_POST) && (@$_POST['action']=='edit')){
    $errors="";

    //Ověření jestli už daný name není v databázi?
    $query = $db->prepare('SELECT name FROM categories WHERE name=? AND NOT id=? LIMIT 1');
    $query->execute(array($_POST["name"],$_POST["id"]));
    $categoryexist = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($_POST["name"])){
            $errors.="Kategorie musí mít název<br />";
        } elseif ($categoryexist["name"]==$_POST["name"]) {
            $errors.="Kategorie s tímto názvem už existuje<br />";
        } elseif(empty($_POST["description"])){
            $errors.="Kategorie musí mít popis<br />";
        } 

        if (empty($errors)) {
            $query = $db->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
            $query->execute(array($_POST["name"], $_POST["description"], $_POST["id"]));
            
            header('Location: edit_cat.php');
        }

    }
//insert cat
    if(!empty($_POST) && (@$_POST['action']=='create')){
    $errors="";

    $query = $db->prepare('SELECT name FROM categories WHERE name=? LIMIT 1');
    $query->execute(array($_POST["name"]));
    $categoryexist = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($_POST["name"])){
            $errors.="Kategorie musí mít název<br />";
        } elseif ($categoryexist["name"]==$_POST["name"]) {
            $errors.="Kategorie s tímto názvem už existuje<br />";
        } elseif(empty($_POST["description"])){
            $errors.="Kategorie musí mít popis<br />";
        } 


        if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO categories(name, description) VALUES (?, ?)");
            $stmt->execute(array($_POST["name"], $_POST["description"]));
            header('Location: edit_cat.php');
        }

    }
        

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Administrace kategorií</title>
	
	<?php include '../assets/styles.php'; ?>
</head>

<body>
    <?php include '../navbar.php'; ?>
	<div class="container">
        <h1>Administrace kategorií</h1>

        <ul class="nav nav-tabs admin-nav">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#page1">Seznam kategorií</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#page2">Vytvořit novou kategorii</a>
          </li>
        </ul>
        <?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
        <div class="tab-content">
        <div id="page1" class="tab-pane active">

	<h2>Výpis kategorií</h2>
	<?php
        $query = $db->prepare('SELECT id, name, description FROM categories');
        $query->execute();
        $categories = $query->fetchALL(PDO::FETCH_ASSOC);

        
        ?>
        <table class="table table-striped">
        <thead>
              <tr>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(1)')" style="cursor:pointer">ID <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(2)')" style="cursor:pointer">Název <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(3)')" style="cursor:pointer">Popis <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(4)')" style="cursor:pointer">Počet příspěvků <i class="fas fa-sort-down"></i></th>
                <th>Akce</th>
              </tr>
            </thead>
            <?php
        foreach ($categories as $category){
            $query = $db->prepare('SELECT COUNT(category) FROM posts WHERE id=?');
            $query->execute(array($category['id']));
            $postcount = $query->fetchColumn();

            echo '<tr class="searchrow">';
        	echo '<td>' . $category['id'] . '</td>';
        	echo '<td>' . htmlspecialchars($category['name']) . '</td>'; 
        	echo '<td>' . htmlspecialchars($category['description']) . '</td>';
            echo '<td>' . $postcount . '</td>';
        	echo '<td><a href="delete_category.php?id='. $category["id"].'" onclick="return confirm(\'Přejete si smazat uživatele ' . htmlspecialchars($category['name']) . '\')">Smazat</a>
                    <a href="#edit'.$category['id'].'" data-toggle="modal">Upravit</a>
            </td>';
        	echo '</tr>';
            } 
            echo '</table>'; 
           foreach ($categories as $category){
           ?>
                    <div id="edit<?php echo $category["id"] ?>" class="modal fade">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header">
                                      <h4 class="modal-title">Upravit kategorii: <?php echo htmlspecialchars($category['name']); ?></h4>
                                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="post">
                                            <input type="hidden" name="action" value="edit" />
                                            <label for="id<?php echo $category['id']; ?>">ID kategorie (nelze editovat)</label>
                                            <input type="text" name="id" id="id<?php echo $category['id']; ?>" class="form-control" value="<?php echo $category['id']; ?>" readonly />
                                            <label for="name<?php echo $category['id']; ?>">Jméno kategorie</label>
                                            <input type="text" name="name" id="name<?php echo $category['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required />
                                            <label for="name<?php echo $category['id']; ?>">Popis kategorie</label>
                                            <input type="text" name="description" id="description<?php echo $category['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($category['description']); ?>" required />
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
            <h2>Nová kategorie</h2>

            <form method="post">
                                            <input type="hidden" name="action" value="create" />
                                            <label for="name">Jméno kategorie</label>
                                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars(@$_POST['name']) ?>" required />
                                            <label for="description">Popis kategorie</label>
                                            <input type="text" name="description" id="description" class="form-control" value="<?php echo htmlspecialchars(@$_POST['description']) ?>" required />

                                            <input type="submit" value="Uložit" class="btn btn-primary send"/>
                                    </form>
        </div>
    </div>
</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>






