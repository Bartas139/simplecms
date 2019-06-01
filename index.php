<?php

session_start();
# pripojeni do db
require 'assets/db.php';
require 'assets/check_perm.php';

if (isset($_GET['offset'])) {
	$offset = (int)$_GET['offset'];
} else {
	$offset = 0;
}

$postonpage = 3;

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

		$query = $db->prepare('SELECT id, title, content, thumb_img, author, category, published, read_count FROM posts ORDER BY published DESC LIMIT ? OFFSET ?');
        $query->bindValue(1, $postonpage, PDO::PARAM_INT);
        $query->bindValue(2, $offset, PDO::PARAM_INT);
        $query->execute();
        $posts = $query->fetchALL(PDO::FETCH_ASSOC);
        $html_row = 0;
        foreach ($posts as $post) {
        	$query = $db->prepare('SELECT COUNT(id) FROM comments WHERE post_id=?');
            $query->execute(array($post['id']));
            $comment_count = $query->fetchColumn();

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
				  		<a href="<?php echo BASE_PATH.'/post.php?id='.$post['id'] ?>"><img class="card-img-top thumbnail" <?php
				  			if (empty($post['thumb_img'])){
				  				echo 'src="'.BASE_PATH.'/uploads/thumbs/default.png"';	
				  			} else {
				  				echo 'src="'.BASE_PATH.'/uploads/thumbs/' . $post['thumb_img'] .'"';
				  			}
				  			echo ' alt="Náhledový obrázek: ' . $post['thumb_img'] . '"'; 
				  			?>></a>
				  		<div class="post-category"><a href="<?php echo BASE_PATH.'/category.php?id='.$post['category'] ?>"><?php echo htmlspecialchars($category) ?></a></div>
				  	</div>
				  	<div class="card-body">
				  		<h3 class="post-title"><a href="<?php echo BASE_PATH.'/post.php?id='.$post['id'] ?>"><?php echo htmlspecialchars($post['title']) ?></a></h3>
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

		<ul class="pagination justify-content-center" style="margin:20px 0">
	  		<?php for($i=1; $i<=ceil($count/$postonpage); $i++) { ?>
	  			<li class="page-item <?= $offset/$postonpage+1==$i ? "active" : ""  ?>"><a class="page-link" href="index.php?offset=<?= ($i-1)*$postonpage ?>"><?= $i ?></a></li>	
	  		<?php } ?>
		</ul>

</div>
<?php include 'assets/footer.php'; ?>
<?php include 'assets/scripts.php'; ?>
		</body>

		</html>

