<?php
    $time_to_leave = 6000;

    session_start();
    if(!isset($_SESSION['email']) || !isset($_SESSION['type']) || (time()-$_SESSION['sst']) > $time_to_leave || isset($_GET['getout'])){
        session_destroy();
        header('Location: index.php');
    }
    else {
        $_SESSION['sst'] = time(); //update session time
        
    }

?>