<?php
$query = $db->prepare('SELECT id, name FROM categories ORDER BY name ASC');
$query->execute();
$categories = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_PATH; ?>">SimpleCMS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navbarNavDropdown" class="navbar-collapse collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <?php
                    
                        echo'<a class="nav-link" href="'. BASE_PATH . '">Domů</a>';
                    
                    ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Kategorie
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <?php
                    foreach ($categories as $category) { ?>
                        
                            <?php echo '<a class="dropdown-item" href="'.BASE_PATH.'/kategorie/'.$category['id'].'">'.htmlspecialchars($category['name']).'</a>'; ?>        
                          
                    <?php }
                    ?>
                    </div> 
                </li>
            </ul>
            <ul class="navbar-nav">
               <?php
        if (@$_SESSION['user_id']>0 && !empty($_SESSION['user_name'])){ ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <?php echo htmlspecialchars($_SESSION['user_name']) ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <?php
                                        echo '<a class="dropdown-item" href="'.BASE_PATH.'/logout.php">Odhlásit</a>';

                        ?>

                        <?php include 'admin/admin_menu.php'; ?>
                    </div>
                </li>
                <?php }else{ ?>
                <li class="nav-item">
                    <?php
                                    echo '<a class="nav-link" href="'.BASE_PATH.'/signin.php">Přihlásit</a>';

                    ?>
                    
                </li>
                <li class="nav-item">
                    <?php
                                    echo '<a class="nav-link" href="'.BASE_PATH.'/signup.php">Registrovat</a>';

                    ?>
                </li><?php }
        ?>
            </ul>
        </div>
        </div>
    </nav>
  