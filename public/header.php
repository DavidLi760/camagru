<header>
    <?php
    if (!isset($_SESSION['user_id'])) {
        echo '<h1>Bienvenue sur Camagru !</h1>';
    }
    else
        echo '<h1>Bienvenue sur Camagru, ' . htmlspecialchars($_SESSION['username']) . ' !</h1>';
    ?>
    
    <nav>
        <button onclick="location.href='home'">Accueil</button>
        <button onclick="location.href='upload.php'">Upload</button>
        <button onclick="location.href='register.php'">S'inscrire</button>
        <button onclick="location.href='login.php'">Connexion</button>
        <button onclick="location.href='profile.php'">Profil</button>
        <?php
        if (isset($_SESSION['user_id'])) {
            echo '<a href="logout.php" style="color: green; font-weight: bold;">Se déconnecter</a>';
        }
        ?>
    </nav>
</header>
