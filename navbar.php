
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navbarNavDropdown" class="navbar-collapse collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
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
                        <a class="dropdown-item" href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Odhlásit</a>
                    </div>
                </li>
                <?php }else{ ?>
                <li class="nav-item">
                    <a class="nav-link" href="signin.php">Přihlásit</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="signup.php">Registrovat</a>
                </li><?php }
        ?>
            </ul>
        </div>
        </div>
    </nav>
  