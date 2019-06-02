<?php
session_start();
# pripojeni do db
require '../assets/db.php';

# pristup jen pro prihlaseneho uzivatele
require '../assets/login_required.php';
# pristup jen s perm manage_role
require '../assets/check_perm.php';

//Pro pristup je potrebné opravnení manage_roles
$access = perm ('edit_post', $_SESSION['user_role']);

if ($access == 0){
    http_response_code(403);
    include('../errors/403.php');
    die();
}
//update příspěvku
if(!empty($_POST) && (@$_POST['action']=='edit')){
    $errors="";

    if (empty($_POST["title"])){
            $errors.="Titulek je povinný<br />";
        } elseif(empty($_POST["cat"])){
            $errors.="Kategorie je povinná<br />";
        }

// IF SMAZAT - NEW FILE UPLOAD OR SET NULL ++ IF !SMAZAT - NEW FILE UPLOAD OR SET CURRENT        
if (isset($_POST['delimg'])){
    //smazání souboru
                $query = $db->prepare('SELECT thumb_img FROM posts WHERE id=?');
                $query->execute(array($_GET['id']));
                $filename = $query->fetchColumn();
                $path = '../uploads/thumbs/' . $filename;
                unlink($path);
    if($_FILES['fileToUpload']['size'] > 0) {
                //Nahrání obrázku
                $target_dir = "../uploads/thumbs/";

                //generace unikátního jména souboru
                $query = $db->prepare('SELECT thumb_img FROM posts');
                $query->execute();
                $images = $query->fetchALL(PDO::FETCH_COLUMN, 0);
                $uniq = false;
                $newname = uniqid('thumb_');
                while ($uniq==false){
                   if (in_array($newname, $images)){
                        $newname = uniqid('thumb_');
                   } else {
                        $uniq = true;
                   }
                }

                //přejmenování souboru
                $file_ext = substr(basename($_FILES["fileToUpload"]["name"]), strripos(basename($_FILES["fileToUpload"]["name"]), '.'));
                $newname .= $file_ext;
                $target_file = $target_dir . $newname; 

                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                // pomocí getimagesize zjistíme jestli je to opravdu img
                if(!empty($_POST["fileToUpload"])) {
                    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                    if($check == false){
                        $errors.= "Soubor není obrázek! <br />";
                    }
                }
                // Existuje ten soubor?
                if (file_exists($target_file)) {
                    $errors.= "Soubor již existuje <br />";
                }
                // Ověření velikosti
                if ($_FILES["fileToUpload"]["size"] > 2000000) {
                    $errors.= "Maximální velikost obrázku jsou 2MB<br />";
                }
                // Omezení formátu
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    $errors.= "Jsou dovolené pouze soubory s příponou jpg, jpeg, png a gif<br />";
                }


                // chyby
                if (!empty($errors)) {
                    $errors.= "Soubor se nenahrál <br />";
                // pokud je vše ok nahrát soubor
                } else {
                    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                        $errors.= "Při nahrávání obrázku došlo k chybě<br />";
                    }
                }


             } else {
                $newname = null;
             }
         } else {
            if($_FILES['fileToUpload']['size'] > 0) {
                //Nahrání obrázku
                $target_dir = "../uploads/thumbs/";

                //generace unikátního jména souboru
                $query = $db->prepare('SELECT thumb_img FROM posts');
                $query->execute();
                $images = $query->fetchALL(PDO::FETCH_COLUMN, 0);
                $uniq = false;
                $newname = uniqid('thumb_'); //generace nového jména
                $file_ext = substr(basename($_FILES["fileToUpload"]["name"]), strripos(basename($_FILES["fileToUpload"]["name"]), '.')); //přípona souboru pomocí substringu
                $newname .= $file_ext; //připojení přípony ke jménu
                //ověřen, že je opravdu unikátní
                while ($uniq==false){
                   if (in_array($newname, $images)){
                        $newname = uniqid('thumb_');
                        $newname .= $file_ext;
                   } else {
                        $uniq = true;
                        $target_file = $target_dir . $newname; 
                   }
                }
              

                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                // pomocí getimagesize zjistíme jestli je to opravdu img
                if(!empty($_POST["fileToUpload"])) {
                    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                    if($check == false){
                        $errors.= "Soubor není obrázek! <br />";
                    }
                }
                // Existuje ten soubor?
                if (file_exists($target_file)) {
                    $errors.= "Soubor již existuje <br />";
                }
                // Ověření velikosti
                if ($_FILES["fileToUpload"]["size"] > 5000000) {
                    $errors.= "Maximální velikost obrázku je 5MB<br />";
                }
                // Omezení formátu
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                && $imageFileType != "gif" ) {
                    $errors.= "Jsou dovolené pouze soubory s příponou jpg, jpeg, png a gif<br />";
                }


                // chyby
                if (!empty($errors)) {
                    $errors.= "Soubor se nenahrál <br />";
                // pokud je vše ok nahrát soubor
                } else {
                    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                        $errors.= "Při nahrávání obrázku došlo k chybě<br />";
                    }
                }


             }else {
                $query = $db->prepare('SELECT thumb_img FROM posts WHERE id=?');
                $query->execute(array($_GET['id']));
                $newname = $query->fetchColumn(); 
             }
            
         }
            


         if (empty($errors)) {


            $stmt = $db->prepare("UPDATE posts SET title=?, content=?, thumb_img=?, category=?, locked = ?, locked_user = ? WHERE id=?");
            $stmt->execute(array($_POST["title"], $_POST["content"], $newname, $_POST["cat"], null, null, $_GET['id']));
            header('Location: edit_post.php');
            die ();
        }

    }
//insert příspěvek
    if(!empty($_POST) && (@$_POST['action']=='create')){
    $errors="";

        if (empty($_POST["title"])){
            $errors.="Titulek je povinný<br />";
        } elseif(empty($_POST["cat"])){
            $errors.="Kategorie je povinná<br />";
        }

if($_FILES['fileToUpload']['size'] > 0) {
            //Nahrání obrázku
            $target_dir = "../uploads/thumbs/";

            //generace unikátního jména souboru
            $query = $db->prepare('SELECT thumb_img FROM posts');
            $query->execute();
            $images = $query->fetchALL(PDO::FETCH_COLUMN, 0);
            $uniq = false;
            $newname = uniqid('thumb_');
            while ($uniq==false){
               if (in_array($newname, $images)){
                    $newname = uniqid('thumb_');
               } else {
                    $uniq = true;
               }
            }

            //přejmenování souboru
            $file_ext = substr(basename($_FILES["fileToUpload"]["name"]), strripos(basename($_FILES["fileToUpload"]["name"]), '.'));
            $newname .= $file_ext;
            $target_file = $target_dir . $newname; 

            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            // pomocí getimagesize zjistíme jestli je to opravdu img
            if(!empty($_POST["fileToUpload"])) {
                $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                if($check == false){
                    $errors.= "Soubor není obrázek! <br />";
                }
            }
            // Existuje ten soubor?
            if (file_exists($target_file)) {
                $errors.= "Soubor již existuje <br />";
            }
            // Ověření velikosti
            if ($_FILES["fileToUpload"]["size"] > 5000000) {
                $errors.= "Maximální velikost obrázku je 5MB<br />";
            }
            // Omezení formátu
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $errors.= "Jsou dovolené pouze soubory s příponou jpg, jpeg, png a gif<br />";
            }
         
           

            // chyby
            if (!empty($errors)) {
                $errors.= "Soubor se nenahrál <br />";
            // pokud je vše ok nahrát soubor
            } else {
                if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    $errors.= "Při nahrávání obrázku došlo k chybě<br />";
                }
            }

         }

         if (empty($errors)) {
            $stmt = $db->prepare("INSERT INTO posts(title, content, thumb_img, author, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array($_POST["title"], $_POST["content"], $newname, $_SESSION['user_id'], $_POST["cat"]));
            header('Location: edit_post.php');
        }

    }
        

?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>CMS - Administrace příspěvků</title>
	
	<?php include '../assets/styles.php'; ?>
    <script>
  tinymce.init({
    selector: '#content'
  });
  </script>
</head>

<body>
    <?php include '../navbar.php'; ?>
	<div class="container">
        <h1>Administrace příspěvků</h1>
<?php if(empty($_GET)) { ?>
        <ul class="nav nav-tabs admin-nav">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#page1">Seznam příspěvků</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#page2">Vytvořit nový příspěvek</a>
          </li>
        </ul>
        <?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
        <div class="tab-content">
        <div id="page1" class="tab-pane active">

	<h2>Výpis příspěvků</h2>
    <form>
        <div class="form-group input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" class="form-control" placeholder="Zadejte termín, který chcete vyhledat v tabulce uživatelů" oninput="w3.filterHTML('#list', '.searchrow', this.value)">
        </div>
    </form>
	<?php
        $query = $db->prepare('SELECT id, title, author, category, published, read_count FROM posts ORDER BY published DESC');
        $query->execute();
        $posts = $query->fetchALL(PDO::FETCH_ASSOC);

        
        ?>
        <table id="list" class="table table-striped">
        <thead class="thead-dark">
              <tr>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(1)')" style="cursor:pointer">ID <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(2)')" style="cursor:pointer">Titulek <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(3)')" style="cursor:pointer">Autor <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(4)')" style="cursor:pointer">Kategorie <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(5)')" style="cursor:pointer">Publikace <i class="fas fa-sort-down"></i></th>
                <th onclick="w3.sortHTML('#list', '.searchrow', 'td:nth-child(6)')" style="cursor:pointer">Počet zobrazení <i class="fas fa-sort-down"></i></th>
                <th>Akce</th>
              </tr>
            </thead>
            <?php
        foreach ($posts as $post){
            $query = $db->prepare('SELECT name FROM categories WHERE id=?');
            $query->execute(array($post['category']));
            $category = $query->fetchColumn();

            $query = $db->prepare('SELECT name FROM users WHERE id=?');
            $query->execute(array($post['author']));
            $author = $query->fetchColumn();

            echo '<tr class="searchrow">';
        	echo '<td>' . $post['id'] . '</td>';
        	echo '<td>' . htmlspecialchars($post['title']) . '</td>'; 
        	echo '<td>' . htmlspecialchars($author) . '</td>';
            echo '<td>' . htmlspecialchars($category) . '</td>';
            echo '<td>' . date( 'd.m.Y H:i:s', strtotime($post['published']) ) . '</td>';
            echo '<td>' . htmlspecialchars($post['read_count']) . '</td>';
        	echo '<td><a href="delete_post.php?id='. $post["id"].'" onclick="return confirm(\'Přejete si smazat příspěvek s ID ' . htmlspecialchars($post['id']) . '\')">Smazat</a>
                    <a href="?action=edit&id='.$post['id'].'">Upravit</a>
            </td>';
        	echo '</tr>'; 
        }
        echo '</table>'; 
?>

</div>
        <div id="page2" class="tab-pane">
            <h2>Nový Příspěvek</h2>

            <?php
            //Počet rolí v databázi, kvůli option size --> validita HTML
            $query = $db->prepare('SELECT COUNT(name) FROM categories');
            $query->execute();
            $catcount = $query->fetchColumn();
            ?>

            <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="create" />
                                            <label for="title">Titulek</label>
                                            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars(@$_POST['title']) ?>" required />
                                            <label for="fileToUpload">Náhledový obrázek</label>
                                            <div class="custom-file">
                                                <label class="custom-file-label" for="fileToUpload">Vybrat soubor</label>
                                                <input type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload">
                                            </div>
                                            <label for="content">Obsah</label>
                                            <textarea name="content" id="content" class="form-control"><?php echo htmlspecialchars(@$_POST['content']) ?></textarea>
                                            <label for="cat">Kategorie příspěvku</label>
                                            <select class="form-control" id="cat" name="cat" size="<?php echo $catcount; ?>" required>
                                            <?php
                                                    $query = $db->prepare('SELECT name, id FROM categories ORDER BY name;');
                                                    $query->execute();
                                                    $cats = $query->fetchALL(PDO::FETCH_ASSOC);

                                                    foreach ($cats as $cat){
                                                        echo '<option value="'.$cat["id"].'">'. htmlspecialchars($cat["name"]).'</option>';  
                                                    }    
                                                ?>
                                            </select>
                                            <input type="submit" name="submit" value="Publikovat" class="btn btn-primary send"/>

                                    </form>
        </div>
    </div>
    <?php }
        if(!empty($_GET) && (@$_GET['action']=='edit') ) {
            $query = $db->prepare('SELECT posts.id, posts.title, posts.content, posts.thumb_img, posts.author, posts.category, posts.published, posts.read_count, categories.name as currentcat FROM posts JOIN categories ON posts.category = categories.id WHERE posts.id=?');
            $query->execute(array($_GET['id']));
            $post = $query->fetch(PDO::FETCH_ASSOC);

            //Kdyby např, někdo mezitím záznam smazal
            if (empty($post)){ echo 'Tento příspěvek neexistuje'; }
            else {

                $query = $db->prepare('SELECT posts.locked + INTERVAL 15 MINUTE AS expires, posts.locked_user, users.name FROM posts JOIN users ON posts.locked_user = users.id WHERE posts.id=?');
                $query->execute(array($_GET['id']));
                $lock = $query->fetch();

                if (date("Y-m-d H:i:s") < date( 'Y-m-d H:i:s', strtotime($lock['expires'])) && $lock['locked_user']!=$_SESSION['user_id']){
                    echo 'Tento záznam v současné době upravuje: ' . $lock['name'] . ', expirace zámku je v tuto chvíli nastavena na: '.date( 'd.m.Y H:i:s', strtotime($lock['expires'])).'<br /><a href="edit_post.php">Vrátit se zpět</a>';
                } else {

                $stmt = $db->prepare("UPDATE posts SET locked_user=?, locked=NOW() WHERE id=?");
                $stmt->execute(array($_SESSION['user_id'], $_GET['id']));

                $query = $db->prepare('SELECT posts.locked + INTERVAL 15 MINUTE AS expires FROM posts WHERE posts.id=?');
                $query->execute(array($_GET['id']));
                $lock = $query->fetch();   

            $query = $db->prepare('SELECT COUNT(name) FROM categories');
            $query->execute();
            $catcount = $query->fetchColumn();
            ?><?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
                                        <div class="alert alert-danger">Záznam je pro ostatní uživatele uzamčen do: <?php echo date( 'd.m.Y H:i:s', strtotime($lock['expires'])); ?></div>
                                        <form method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="edit" />
                                            
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <label for="title">Titulek</label>
                                                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($post['title']) ?>" required />
                                                    <label for="cat">Kategorie příspěvku</label>
                                                    <select class="form-control" id="cat" name="cat" size="<?php echo $catcount; ?>" required>
                                                    <?php
                                                            $query = $db->prepare('SELECT name, id FROM categories ORDER BY name;');
                                                            $query->execute();
                                                            $cats = $query->fetchALL(PDO::FETCH_ASSOC);

                                                            foreach ($cats as $cat){
                                                                if ($cat["name"]==$post['currentcat']){
                                                                    echo '<option value="'.$cat["id"].'" selected>'. htmlspecialchars($cat["name"]).'</option>';
                                                                } else {
                                                                    echo '<option value="'.$cat["id"].'">'. htmlspecialchars($cat["name"]).'</option>';
                                                                } 
                                                            }    
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6">
                                                    <p>Aktuální obrázek</p>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="delimg" name="delimg">
                                                        <label class="custom-control-label" for="delimg">Smazat obrázek? <span id="yes">Obrázek bude smazán</span><span id="no">Obrázek bude zachován</span></label>
                                                    </div>
                                                    <?php if(!empty($post['thumb_img'])){ ?><img class="img-fluid" id="current-post-img" src="<?php echo BASE_PATH.'/uploads/thumbs/'.$post['thumb_img'] ?>" alt="Aktuální obrázek pro příspěvěk id: <?php echo $post['id'] ?>"><?php } ?>
                                                    
                                                    <div id="upload" <?php if(!empty($post['thumb_img'])){echo 'style="display: none"';} ?>>
                                                        <label for="fileToUpload">Nahrát nový obrázek</label>
                                                        <div class="custom-file">
                                                            <label class="custom-file-label" for="fileToUpload">Vybrat soubor</label>
                                                            <input type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <label for="content">Obsah</label>
                                            <textarea name="content" id="content" class="form-control" value="<?php echo htmlspecialchars($post['content']) ?>"><?php echo htmlspecialchars($post['content']) ?></textarea>
                                            
                                            <input type="submit" name="submit" value="Publikovat" class="btn btn-primary send"/>
                                            <a href="unlock.php?id=<?php echo $_GET['id'] ?>">Zrušit a vrátit se na přehled</a>
                                        </form>
            <?php
        }}
        }
    ?>

</div>
<?php include '../assets/scripts.php'; ?>
		</body>

		</html>






