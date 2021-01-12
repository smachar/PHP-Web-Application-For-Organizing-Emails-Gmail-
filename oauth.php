<?php
require "Model/DB.php";
require "vendor/autoload.php";
require "Model/User.php";

$user = new User();

if(isset($_GET['code'])) {
    $user->authCode($_GET['code']);
    $user->setDB();
    $rg = $user->registerUser();
    if($rg!=0){
        session_start();
        $_SESSION['sst'] = time();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['tmessages'] = $user->getTotalMessages(); //0
        if($rg==1 || $rg==-2){ //added or updated token & pass=null
            $_SESSION['type']='Set'; //send him to set a password
        }
        elseif($rg==-1){ //token updated pass!=null(user has password)
            //$_SESSION['tmessages'] = $user->getDb()->tMessagesInDb();
            $_SESSION['type']='Log';
        }
        header('Location: password.php');
        exit;
    }
    else{
        //add redirect back to main with this error
        echo "didnt registered!";
        exit;
    }
}
elseif(isset($_GET['error'])) $user->handleError($_GET['error']);
else $user->getConsent(); //when clicking on auth button
