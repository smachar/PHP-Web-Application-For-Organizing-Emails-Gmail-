<?php
if(isset($_SESSION['type'])&&$_SESSION['type']=='login'){
  header('Location: main.php');
  exit;
}
//if((isset($_GET['out']) && $_GET['out']=='true') || isset($_GET['m'])){
session_start();
session_destroy();
//}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification/Autorisation</title>
    <link rel="stylesheet" href="style/main.css">
    <script>
        var matchpass = function(){
          p1=document.getElementsByName('email')[0].value;
          p2=document.getElementsByName('password1')[0].value;
          btnerr = document.getElementById('btnerr');
          if(p1!='' && p2!=''){
              btnerr.disabled = false;
              btnerr.style.backgroundColor = '#87FFBF'; 
          }
          else{
              btnerr.disabled = true;
              btnerr.style.backgroundColor = '#EA3C53';
          }
        }
    </script>
</head>

<body>
    <div class="center">
      <a href="oauth.php"><button>Accorder l'accès à votre compte Gamail</button></a>
      <div class="text">
        <small class="small-text">Ou, Se connecter</small>
        <?php 
        if(isset($_GET['m'])){
            switch($_GET['m']){
                case 'login':
                    echo "<small id='texterr'><span>E-mail ou mot de passe incorrect!</span></small>";
                    break;
                case 'set':
                    echo "<small id='texterr'><span>Erreur lors de la création de vos informations! Merci d'essayer plus tard</span></small>";
                    break;
                case 'update':
                    echo "<small id='texterr'><span>Erreur lors de la modification de vos informations! Merci d'essayer plus tard</span></small>";
                    break;
            }
        } 
        ?>
      </div>
      <form action="password.php" method='post'>
        <input name= "email" type="text" placeholder="Email" onkeyup="matchpass()">
        <input name="password1" type="password" placeholder="Mot de passe" onkeyup="matchpass()">
        <button type="submit" id="btnerr" disabled>Se connecter</button>
        <!--<a href="http://localhost/mailapp/password.php?p=forgot">Forgot password</a>-->
      </form>
    </div>
</body>

</html>
