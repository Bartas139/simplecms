<?php

session_start();
# pripojeni do db
require 'assets/db.php';
require 'assets/check_perm.php';

if (!isset($_GET['id'])) {
    header('Location: '.BASE_PATH);
} else {
    $query = $db->prepare('SELECT id FROM categories WHERE id=?');
    $query->execute(array($_GET['id']));
    $exist = $query->fetchColumn();
    if (empty($exist)){
        http_response_code(404);
        include('errors/404.php');
        die();
    }      
}
//Odkud jsi přišel - požito pro případný návrat po přihlášení
$_SESSION['source'] = $_SERVER['REQUEST_URI'];

$query = $db->prepare('SELECT categories.name, categories.description, posts.id, posts.title FROM categories JOIN posts ON categories.id = posts.category WHERE categories.id=? ORDER BY posts.id DESC');
$query->execute(array($_GET['id']));
$posts = $query->fetchALL(PDO::FETCH_ASSOC);


?><!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" />
    <title>SimpleCMS</title>
    
    <?php include 'assets/styles.php'; ?>
    <script>
  tinymce.init({
    selector: '#content'
  });
  </script>
    
</head>


<body>
<?php include 'navbar.php'; ?>
<header class="d-flex align-items-center">
    <div class="container">
        <h1><?php echo htmlspecialchars($post['title'])  ?></h1>
        <p><?php echo 'Autor: ' . htmlspecialchars($author) . ' | Publikováno: ' .$published. ' | <a href="' . BASE_PATH. '/category.php?id=' .$post['category'] . '">Kategorie: '.htmlspecialchars($category).'</a> | Počet zobrazení: ' . $post['read_count'] ?></p>
    </div>
</header>   
<main class="container">
     
</main>

<?php include 'assets/scripts.php'; ?>
        </body>

        </html>

