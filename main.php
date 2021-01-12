<?php
    require "session.php";
    require "functions.php";
    require "Model/DB.php";
    if(!isset($_SESSION['type']) || $_SESSION['type']!='login'){
        header('Location: index.php?out=true');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur votre panneau</title>
    <link rel="stylesheet" href="style/load.css">
    <link rel="stylesheet" href="style/dashboard.css">
    <script src="js/functions.js"></script>
</head>
<body>
    <div class="container">
        <header class="header">
            <form action="" name='search' class='searchform' >
                <input type="text" placeholder='Rechercher pour "mot clé" ' id='qr' autofocus onkeyup="displayEmails()">
                <div class="criteria">
                    <span>Dans: </span>
                    <input type="checkbox" name='sen' >
                    <label for="sen" id='sen'>Expéditeur</label>
                    <input type="checkbox" name='sub' checked>
                    <label for="sub" id='sub'>Sujet</label>
                    <input type="checkbox" name='sni'>
                    <label for="sni" id='sni'>Extrait</label>
                    <input type="checkbox" name='pla'>
                    <label for="pla" id='pla'>Contenu</label>
                    <span class="profile"><?php echo $_SESSION['email']; ?></span>
                </div>
            </form>
            
        </header>
        <aside class="sidenav">
            <div class='mailbox' >
                <h4>Boîte de réception : </h4>
                <ul class='sidenav__list'>
                    <li class='sidenav__list-item' onclick="displayMailbox('all')">Tout afficher</li>
                    <?php echo display_mailbox(); ?>
                </ul>
            </div>
            <div class='other_links'>
                <h4>Autres actions: </h5>
                <ul>
                    <li ><a href='mailbox.php'>Nouvelle boîte de réception</a></li>
                    <li ><a href='index.php?out=true'>Déconnecter</a></li>
                </ul>
            </div>
        </aside>
        <main class="main">
            <div class="title">
                <h2>Tout afficher</h2>
            </div>
            <button class='refresh' onclick="startcollect('yes')">Actualiser</button>
            <div class="main-overview">
                <?php 
                    if($_SESSION['tmessages']==0){
                        echo "<button class='startbtn' onclick='startcollect()'>Commencer à récupérer vos emails</button>
                        <div class='loadbtn'><div></div><div></div><div></div><div></div></div>
                        <div class='dashmsg'>Votre boîte de réception est vide</div>";
                    }
                    else require "getEmails.php";
                    ?>
            </div>
            <?php if($_SESSION['tmessages']!=0){
            echo "<div class='load-more-button' onClick='loadMore()'>Charger Plus</div>";}
            ?>
        </main>

        <footer class="footer">
            <div class="footer__copyright">&copy; 2020</div>
        </footer>
    </div>
</body>
</html>