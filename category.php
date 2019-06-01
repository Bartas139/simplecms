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

$query = $db->prepare('SELECT posts.id, posts.title, posts.content, posts.thumb_img, posts.category, posts.published, posts.read_count, users.name, categories.name as cat FROM posts JOIN users on posts.author=users.id JOIN categories on posts.category = categories.id WHERE categories.id=?');
$query->execute(array($_GET['id']));
$posts = $query->fetchALL(PDO::FETCH_ASSOC);

$query = $db->prepare('SELECT name FROM categories WHERE id=?');
$query->execute(array($_GET['id']));
$name = $query->fetchColumn();

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
        <h1><?php echo htmlspecialchars($name)  ?></h1>
        <p>Všechny příspěvky z kategorie <?php echo htmlspecialchars($name)  ?></p>
    </div>
</header>   
<main class="container">
    <?php $html_row = 0; foreach ($posts as $post) {
            
            if (($html_row % 3) == 0) {echo '<div class="row">';}
            $html_row =  $html_row + 1;
            $query = $db->prepare('SELECT COUNT(id) FROM comments WHERE post_id=?');
            $query->execute(array($post['id']));
            $comment_count = $query->fetchColumn();
            ?>
            <div class="col-sm-4 py-2">
                <article class="card post h-100">
                    <div class="post-header">
                        <a href="<?php echo BASE_PATH.'/post.php?id='.$post['id'] ?>"><img class="card-img-top thumbnail" <?php
                            if (empty($post['thumb_img'])){
                                echo 'src="'.BASE_PATH.'/uploads/thumbs/default.png"';  
                            } else {
                                echo 'src="'.BASE_PATH.'/uploads/thumbs/' . $post['thumb_img'] .'"';
                            }
                            echo 'alt="Náhledový obrázek: ' . $post['thumb_img'] . '"'; 
                            ?>></a>
                        <div class="post-category"><a href="<?php echo BASE_PATH.'/category.php?id='.$post['category'] ?>"><?php echo htmlspecialchars($post['cat']) ?></a></div>
                    </div>
                    <div class="card-body">
                        <h3 class="post-title"><a href="<?php echo BASE_PATH.'/post.php?id='.$post['id'] ?>"><?php echo htmlspecialchars($post['title']) ?></a></h3>
                        <div class="post-meta">
                            <ul>
                                <li><?php echo htmlspecialchars($post['name']) ?></li>
                                <li><?php
                                    if (date("d.m.Y") == date( 'd.m.Y', strtotime($post['published']))){
                                        echo 'Dnes v ' . date( 'H:i:s', strtotime($post['published']));
                                    } else {
                                        echo date( 'd.m.Y H:i:s', strtotime($post['published']));
                                    }
                                    
                                ?></li>
                                <li><i class="fas fa-eye"></i> <?php echo htmlspecialchars($post['read_count']) ?> <i class="fas fa-comments"></i> <?php echo $comment_count; ?></li>
                            </ul>
                        </div>
                        <div class="post-excerp">
                        <?php
                        //striptags odstraní tagy (protože tam můžou být=>tinymce), explode rozdělí string na pole podle slov, arrayslice ho omezí na 40, implode složí pole zase do stringu,
                            $excerpt = strip_tags ($post['content']);
                            echo implode(' ', array_slice(explode(' ', $excerpt), 0, 40)) . '...<br />';
                        ?>  
                        </div>
                        <a href="<?php echo BASE_PATH.'/post.php?id='.$post['id'] ?>" class="post-read">Číst víc</a>
                    </div>
                </article>
            </div>
            <?php
            if (($html_row % 3) == 0) {echo '</div>';}

        }
        if (($html_row % 3) != 0) {echo '</div>';}
    ?>
</main>
<?php include 'assets/footer.php'; ?>
<?php include 'assets/scripts.php'; ?>
        </body>

        </html>

