<?php

session_start();
# pripojeni do db
require 'assets/db.php';
require 'assets/check_perm.php';

if (!isset($_GET['id'])) {
    header('Location: '.BASE_PATH);
} else {
    $query = $db->prepare('SELECT id FROM posts WHERE id=?');
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

//Load counter
if (!isset($_SESSION['visited'])){

    $_SESSION['visited'] = array();
    array_push ($_SESSION['visited'], $_GET['id']);
    $query = $db->prepare('UPDATE posts SET read_count=read_count+1 WHERE id=?');
    $query->execute(array($_GET["id"])); 
} else {
    if (!in_array($_GET['id'], $_SESSION['visited'])){
        array_push ($_SESSION['visited'], $_GET['id']);
        $query = $db->prepare('UPDATE posts SET read_count=read_count+1 WHERE id=?');
        $query->execute(array($_GET["id"])); 
    }
}

$query = $db->prepare('SELECT posts.id, posts.title, posts.content, posts.thumb_img, posts.category, posts.published, posts.read_count, users.name as author, categories.name FROM posts JOIN users on posts.author=users.id JOIN categories on posts.category = categories.id WHERE posts.id=? LIMIT 1');
$query->execute(array($_GET['id']));
$post = $query->fetch(PDO::FETCH_ASSOC);

if (date("d.m.Y") == date( 'd.m.Y', strtotime($post['published']))){
                                        $published = 'Dnes v ' . date( 'H:i:s', strtotime($post['published']));
                                    } else {
                                        $published = date( 'd.m.Y H:i:s', strtotime($post['published']));
                                    }

//Zpracování komentářů
//úprava komentáře
if(!empty($_POST) && (@$_GET['action']=='edit') && !empty(@$_GET['comment'])){
    $errors="";

        if (empty($_POST["comment"])){
            $errors.="Komentář nesmí být prázdný<br />";
        }

        //Ověření, že komentář existuje v databázi
        $query = $db->prepare('SELECT id FROM comments WHERE id=?');
        $query->execute(array($_GET['comment']));
        $exist = $query->fetchColumn();
        if (empty($exist)){
            $errors.="Komentář, který se pokoušíte upravit, neexistuje.<br />";
        } 

        $query = $db->prepare('SELECT author FROM comments WHERE id=?');
        $query->execute(array($_GET['comment']));
        $author = $query->fetchColumn();
        $access = perm ('manage_comments', $_SESSION['user_role']);
        //Nesmíš upravit cizí komentář, když nemáš admin perm
        if ($_SESSION['user_id']!=$author && $access==0){
            $errors.="Nemáš oprávnění k úpravě tohoto komentáře<br />";    
        }

        if (empty($errors)) {
            $query = $db->prepare('UPDATE comments SET content=? WHERE id=?');
            $query->execute(array($_POST["comment"], $_GET["comment"]));
            
            header('Location: post.php?id='.$_GET['id']);
        }

    }
//smazání komentáře
if(!empty(@$_GET['comment']) && (@$_GET['action']=='delete')){
    $errors="";
        $query = $db->prepare('SELECT author FROM comments WHERE id=?');
        $query->execute(array($_GET['comment']));
        $author = $query->fetchColumn();

        //Ověření, že komentář existuje v databázi
        $query = $db->prepare('SELECT id FROM comments WHERE id=?');
        $query->execute(array($_GET['comment']));
        $exist = $query->fetchColumn();
        if (empty($exist)){
            $errors.="Komentář, který se pokoušíte smazat, neexistuje.<br />";
        } 

        $access = perm ('manage_comments', $_SESSION['user_role']);
        //Nesmíš smazat cizí komentář, když nemáš admin perm
        if ($_SESSION['user_id']!=$author && $access==0){
            $errors.="Nemáš oprávnění ke smazání tohoto komentáře<br />";    
        }

        if (empty($errors)) {
            $query = $db->prepare('DELETE FROM comments WHERE id=?');
            $query->execute(array($_GET["comment"]));
            
            header('Location: post.php?id='.$_GET['id']);
        }

    }
//odpověď komentáře
if(!empty($_POST) && !empty(@$_GET['comment']) && (@$_GET['action']=='response')){
        $errors="";

        //Ověření, že komentář existuje v databázi
        $query = $db->prepare('SELECT id FROM comments WHERE id=?');
        $query->execute(array($_GET['comment']));
        $exist = $query->fetchColumn();
        if (empty($exist)){
            $errors.="Komentář, na který se pokoušíte odpovědět, neexistuje.<br />";
        } 
        
        if (empty($_POST["comment"])){
            $errors.="Komentář nesmí být prázdný<br />";
        } 

        if (empty($errors)) {
            $query = $db->prepare('INSERT INTO comments(content, author, post_id, response_to) VALUES (?, ?, ?, ?)');
            $query->execute(array($_POST["comment"], $_SESSION["user_id"], $_GET["id"], $_GET["comment"]));
            
            header('Location: post.php?id='.$_GET['id']);
        }

    }
//vložení komentáře
if(!empty($_POST) && (@$_POST['action']=='insert')){
        $errors="";

        if (empty($_POST["comment"])){
            $errors.="Komentář nesmí být prázdný<br />";
        } 

        if (empty($errors)) {
            $query = $db->prepare('INSERT INTO comments(content, author, post_id) VALUES (?, ?, ?)');
            $query->execute(array($_POST["comment"], $_SESSION["user_id"], $_GET["id"]));
            
            header('Location: post.php?id='.$_GET['id']);
        }

    }

//rekurzivní výpis komentářů
function comments ($id, $response) {
    global $db;
    
    if (is_null($response)){
        $query = $db->prepare('SELECT comments.id, comments.content, comments.response_to, comments.comment_date, comments.author, users.name FROM comments LEFT JOIN users on comments.author=users.id WHERE comments.post_id=? AND comments.response_to IS NULL');
        $query->execute(array($id));
        $comments = $query->fetchALL(PDO::FETCH_ASSOC);
    } else {
        $query = $db->prepare('SELECT comments.id, comments.content, comments.response_to, comments.comment_date, comments.author, users.name FROM comments LEFT JOIN users on comments.author=users.id WHERE comments.post_id=? AND comments.response_to = ?');
        $query->execute(array($id, $response));
        $comments = $query->fetchALL(PDO::FETCH_ASSOC);
    }
    foreach ($comments as $comment) {
            ?>
            <div class="single-post-comment media p-3">
                <img src="<?php echo BASE_PATH ?>/uploads/default_user.png" alt="John Doe" class="mr-3 mt-3 rounded-circle" style="width:60px;">
              
              <div class="media-body">
                <h4><?php if(!empty($comment['name'])){echo htmlspecialchars($comment['name']);}else{echo '<span class="deleted-user">Tento uživatel byl smazán</span>';} ?> <small><i><?php echo htmlspecialchars($comment['comment_date']); ?></i></small></h4>
                <?php
                if(isset($_SESSION["user_id"])){
                    $access = perm ('manage_comments', $_SESSION['user_role']);
                    if (($_SESSION['user_id']==$comment['author']) || $access==1){
                        echo '<a href="' . BASE_PATH. '/post.php?id=' .$id . '&comment='. $comment['id'] .'&action=edit">Upravit</a> | ';
                        echo '<a href="' . BASE_PATH. '/post.php?id=' .$id . '&comment='. $comment['id'] .'&action=delete" onclick="return confirm(\'Přejete si smazat komentář\')">Odstranit</a> | ';
                    }
                    echo '<a href="' . BASE_PATH. '/post.php?id=' .$id . '&comment='. $comment['id'] .'&action=response">Odpovědět</a>';
                }?>
                <p><?php echo $comment['content']; ?></p>
                <?php comments ($id, $comment['id']); ?>
              </div>
            </div>
            <?php    
            }        
}

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
    <div class="container single-post-header">
        <img class="float-left single-post-img" <?php
                            if (empty($post['thumb_img'])){
                                echo 'src="'.BASE_PATH.'/uploads/thumbs/default.png"';  
                            } else {
                                echo 'src="'.BASE_PATH.'/uploads/thumbs/' . $post['thumb_img'] .'"';
                            }
                            echo ' alt="Náhledový obrázek: ' . $post['thumb_img'] . '"'; 
                            ?>>
        <h1><?php echo htmlspecialchars($post['title'])  ?></h1>
        <p><?php echo 'Autor: ' . htmlspecialchars($post['author']) . ' | Publikováno: ' .$published. ' | <a href="' . BASE_PATH. '/kategorie/' .$post['category'] . '">Kategorie: '.htmlspecialchars($post['name']).'</a> | Počet zobrazení: ' . $post['read_count'] ?></p>
    </div>
</header>   
<main class="container">
    <?php echo (!empty($errors)?'<div class="alert alert-danger"><strong>'.$errors.'</strong></div>':'');?>
    <div class="row">
        <article class="col-sm-8">
            <?php echo $post['content'] ?>
            <h2>Komentáře</h2>
            <?php $query = $db->prepare('SELECT COUNT(id) FROM comments WHERE post_id=?');
                $query->execute(array($_GET['id']));
                $count = $query->fetchColumn();
                if ($count==0){echo 'Buď první, kdo tento příspvěk okomentuje.';} else {echo 'Počet komentářů u tohoto příspěvku' . $count ;}
            ?>
            <?php comments ($_GET['id'], null); ?>
            <?php
                if (!empty(@$_GET['comment']) && (@$_GET['action']=='response')){
                    $query = $db->prepare('SELECT users.name, comments.content FROM comments JOIN users ON comments.author=users.id WHERE comments.id=?');
                    $query->execute(array($_GET['comment']));
                    $response = $query->fetch();
                    echo 'Odpověď na komentář uživatele: ' . htmlspecialchars($response['name']) . '<br />';
                    echo $response['content'];                 
                }    
            ?>
            <?php if(!empty($_SESSION["user_id"])){ ?>
            <form method="post">
                <?php
                    if (!isset($_GET['action']) && !isset($_GET['comment'])){
                        echo '<input type="hidden" name="action" value="insert" />';
                    }
                ?>
                <label for="comment">Komentář</label>
                <textarea name="comment" id="content" class="form-control" placeholder="Napiš komentář...">
                    <?php
                        if (!empty(@$_GET['comment']) && (@$_GET['action']=='edit')){
                            $query = $db->prepare('SELECT comments.content FROM comments WHERE comments.id=?');
                            $query->execute(array($_GET['comment']));
                            $editcomment = $query->fetchColumn();
                            echo htmlspecialchars($editcomment);
                        }   
                    ?>
                </textarea>
                <input type="submit" value="Odeslat" class="btn btn-primary send">
            </form> <?php } else {echo 'Pro přidání komentáře se musíš přihlásit';} ?>       
        </article>
        <aside class="col-sm-3 offset-sm-1 single-post-aside">
            <div class="single-post-aside-panel">
                <h2>Nejčtenější v kategorii: <?php echo htmlspecialchars($post['name']) ?></h2>
                <ul class="list-unstyled">
                <?php
                    //Nejčtenější v kategorii
                    $query = $db->prepare('SELECT id, title FROM posts WHERE category=? ORDER BY read_count DESC LIMIT 2');
                    $query->execute(array($post['category']));
                    $mostread = $query->fetchALL(PDO::FETCH_ASSOC);
                    foreach ($mostread as $mostreadpost) {
                            echo '<li><a href="'.BASE_PATH.'/clanek/'.$mostreadpost['id'] . '/'.$mostreadpost['title'].'">';
                            echo '<span class="fas fa-angle-double-right text-primary"></span> ';
                            echo htmlspecialchars($mostreadpost['title']) . '<br/>';
                            echo '</a></li>';
                        }    
                ?>
                </ul>
            </div>
        </aside>
    </div>
    
                            
</main>
<?php include 'assets/footer.php'; ?>
<?php include 'assets/scripts.php'; ?>
        </body>

        </html>

