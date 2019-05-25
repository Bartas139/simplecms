<?php

session_start();
# pripojeni do db
require '/assets/db.php';
require '/assets/check_perm.php';
?><!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8" />
	<title>SimpleCMS</title>
	
	<?php include 'assets/styles.php'; ?>
	
</head>


<body>
<?php include 'navbar.php'; ?>	
<div class="container">
	<h1>Poslední příspěvky</h1>
	<?php
        $query = $db->prepare('SELECT id, title, content, thumb_img, author, category, published, read_count FROM posts');
        $query->execute();
        $posts = $query->fetchALL(PDO::FETCH_ASSOC);
        foreach ($posts as $post) {
        	$query = $db->prepare('SELECT name FROM users WHERE id=?');
        	$query->execute(array($post['author']));
        	$author = $query->fetchColumn();
        	$query = $db->prepare('SELECT name FROM categories WHERE id=?');
        	$query->execute(array($post['category']));
        	$category = $query->fetchColumn();
        	?>
        	<div class="card">
			  	<div class="card-header"><h2><?php echo htmlspecialchars($post['title']) ?></h2></div>
			  	<div class="card-body"><?php echo ($post['content']) ?></div>
			  	<div class="card-footer">
			  		<span class="col">Autor: <?php echo htmlspecialchars($author) ?></span>
			  		<span class="col">Kategorie: <?php echo htmlspecialchars($category) ?></span>
			  		<span class="col">Publikováno: <?php echo '<span class="col">' . date( 'd.m.Y H:i:s', strtotime($post['published'])) . '</span>' ?>
			  		<span class="col">Zobrazení: <?php echo htmlspecialchars($post['read_count']) ?></span>
			  	</div>
			</div>
        	<?php	
        }
	?>
</div>
	
<?php include 'assets/scripts.php'; ?>
		</body>

		</html>

