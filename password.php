<?php

require "Model/DB.php";
require "session.php";
require "Model/PW.php";

$pass = new Password();

if($pass->getIsPost()){
    if($pass->getLogin()){ //when login from index.php
        $tmessages = $pass->isMatched();
        if($tmessages==-1 || $tmessages===false){
            header('Location: index.php?m=login');
            exit;
        }
        else{
            session_start();
            $_SESSION['sst']=time();
            $_SESSION['email']=$_POST['email'];
            $_SESSION['type']='login';
            $_SESSION['tmessages'] = $tmessages; //from db
            header('Location: main.php');
            exit;
        }
    }
    else{
        switch($pass->getType()){
            case 'Update':
                $r = $pass->updatepass();
                if($r===false) $error='update';
                break;
                //failed case
                //send userd to DSH.
            case 'Set':
                $r = $pass->setPass(); //r is the $stm variable use in prepare
                // $r->debugDumpParams();
                // exit;
                if($r===false) $error='set';
                break;
                //failed case
                //send userd to dashboard, and start collect script
            case 'Log': //existed user updated his token, has pass, should log-in 
                $r = $pass->isMatched();
                if($r===false) $error='login'; //same error msg at main for log in process
                break;
        }
        if($r!==false){ //could be 0
            $_SESSION['type']='login';
            if(is_int($r) && $r>0){ //when r is the tmessages in db
                $_SESSION['tmessages']=$r;
            }
            header('Location: main.php');
            exit;
        }
        else{
            header('Location: index.php?m='.$error);
            exit;
        }
    }
}

//display this when user redirected from oauth.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
    <?php
        switch($pass->getType()){
            case 'Set':
                echo 'Écrire';
                break;
            case 'Update':
                echo 'Changer';
                break;
            case 'Log':
                echo 'Se connecter avec';
                break;
        }
        //echo ($pass->getType()=='Log' ? 'in with': '')
    ?> vos informations</title>
    <link rel="stylesheet" href="style/password.css">
    <script src="js/password.js"></script>
</head>
<body>
    <h3><?php 
    switch($pass->getType()){
        case 'Set':
            echo 'Écrire';
            break;
        case 'Update':
            echo 'Changer';
            break;
        case 'Log':
            echo 'Se connecter avec';
            break;
    }
    ?> vos informations</h3>
    
    <div class='center'>
    <form action="password.php" method='post' class="login-form">
    <?php if($pass->getType()=='Log') echo "<br><small id='texterr'><span>".$_SESSION['email']." Déjà enregistré</span></small>";?>
    <?php if($pass->getType()=='Set'){
            echo "<input name='' type='text' placeholder='".$_SESSION['email']."' disabled>";
            echo "<input name='fname' type='text' placeholder='votre prénom'>";
            echo "<input name='lname' type='text' placeholder='votre nom'>";
    }?>
    <input name='password1'  type='password' placeholder='Mot de passe' onkeyup='matchpass()' <?php //if(!$pass->getType()=='Log') echo "onkeyup='matchpass()'"; ?>>
        <?php 
        if($pass->getType()=='Set'){
            echo "<input name='password2' type='password' placeholder='Confirmer le mot de passe' onkeyup='matchpass()'>";
        }
        elseif($pass->getType()=='Update'){
            echo "<input name='oldpassword'  type='password' placeholder='Ancien mot de passe'>";
            echo "<input name='password2' type='password' placeholder='Confirmer le mot de passe' onkeyup='matchpass()'>"; 
        }
        ?>
        <button type="submit" id="btnerr" disabled <?php //if(!$pass->getType()=='Log') echo "disabled" ?>>Confirmer</button>
    </form>
    </div>
</body>
</html>