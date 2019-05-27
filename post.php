<?php

session_start();
# pripojeni do db
require '/assets/db.php';
require '/assets/check_perm.php';

if (!isset($_GET['id'])) {
    header('Location: ./');
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

?><!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" />
    <title>SimpleCMS</title>
    
    <?php include 'assets/styles.php'; ?>
    
</head>


<body>
<?php include 'navbar.php'; ?>
<header class="d-flex align-items-center">
    <div class="container">
        <h1>SimpleCMS</h1>
        <p>Základní CMS vytvořeno v rámci kurzu 4iz278</p>
    </div>
</header>   
<div class="container">
    <h2>Poslední příspěvky</h2>
    
    <?php
        $count = $db->query("SELECT COUNT(id) FROM posts")->fetchColumn();

        $query = $db->prepare('SELECT id, title, content, thumb_img, author, category, published, read_count FROM posts ORDER BY published DESC LIMIT 3 OFFSET ?');
        $query->bindValue(1, $offset, PDO::PARAM_INT);
        $query->execute();
        $posts = $query->fetchALL(PDO::FETCH_ASSOC);
        $html_row = 0;
        foreach ($posts as $post) {
            $query = $db->prepare('SELECT name FROM users WHERE id=?');
            $query->execute(array($post['author']));
            $author = $query->fetchColumn();
            $query = $db->prepare('SELECT name FROM categories WHERE id=?');
            $query->execute(array($post['category']));
            $category = $query->fetchColumn();
            
            if (($html_row % 3) == 0) {echo '<div class="row">';}
            $html_row =  $html_row + 1;
            ?>
            <div class="col-sm-4 py-2">
                <article class="card post h-100">
                    <div class="post-header">
                        <a href="#post"><img class="card-img-top thumbnail" <?php
                            if (empty($post['thumb_img'])){
                                echo 'src="uploads/thumbs/default.png"';    
                            } else {
                                echo 'src="uploads/thumbs/' . $post['thumb_img'] .'"';
                            }
                            echo 'alt="Náhledový obrázek: ' . $post['thumb_img'] . '"'; 
                            ?>></a>
                        <div class="post-category"><a href="#category"><?php echo htmlspecialchars($category) ?></a></div>
                    </div>
                    <div class="card-body">
                        <h3 class="post-title"><a href=""><?php echo htmlspecialchars($post['title']) ?></a></h3>
                        <div class="post-meta">
                            <ul>
                                <li><?php echo htmlspecialchars($author) ?></li>
                                <li><?php
                                    if (date("d.m.Y") == date( 'd.m.Y', strtotime($post['published']))){
                                        echo 'Dnes v ' . date( 'H:i:s', strtotime($post['published']));
                                    } else {
                                        echo date( 'd.m.Y H:i:s', strtotime($post['published']));
                                    }
                                    
                                ?></li>
                                <li><i class="fas fa-eye"></i> <?php echo htmlspecialchars($post['read_count']) ?> <i class="fas fa-comments"></i> 0</li>
                            </ul>
                        </div>
                        <div class="post-excerp">
                        <?php
                            echo strip_tags(implode(' ', array_slice(explode(' ', $post['content']), 0, 80))) . '...<br />';
                        ?>  
                        </div>
                        <a href="#" class="post-read">Číst víc</a>
                    </div>
                </article>
            </div>
            <?php
            if (($html_row % 3) == 0) {echo '</div>';}

        }
        if (($html_row % 3) != 0) {echo '</div>';}
    ?>

        <ul class="pagination justify-content-center" style="margin:20px 0">
            <?php for($i=1; $i<=ceil($count/$postonpage); $i++) { ?>
                <li class="page-item <?= $offset/$postonpage+1==$i ? "active" : ""  ?>"><a class="page-link" href="index.php?offset=<?= ($i-1)*$postonpage ?>"><?= $i ?></a></li>   
            <?php } ?>
        </ul>

</div>

<?php include 'assets/scripts.php'; ?>
        </body>

        </html>

