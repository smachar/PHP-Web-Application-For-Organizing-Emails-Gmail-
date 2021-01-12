<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if($_SESSION['tmessages']==0){
        exit;
    }
    require_once "Model/DB.php";
    $db = new DB();

    if(isset($_GET['qr'])){
        $pars = array($_SESSION['email'],$_GET['qr'],$_GET['sen'],$_GET['sub'],$_GET['sni'],$_GET['pla']);
    } else {
        $pars = array($_SESSION['email'],'offset'=>$_GET['offset'] ?? 0);
    }
    $result = $db->getUserEmails($pars);
    if($result->rowCount()){ //bcz tmessages in session!=0 => w're sure we have some emails in db
        $bold='';
        $i=$_GET['offset'] ?? 0;
        foreach($result->fetchAll(PDO::FETCH_NUM) as $row){
            $i+=1;
            $unread='';
            if(strpos($row[5], "UNREAD")!==false) $unread='unread';
            $sender = explode("<", $row[0])[0];
            $subject = substr($row[1],0,30);
            $snippet = substr($row[2], 0,70);
            setlocale(LC_TIME, "fr_FR");
            $date = strftime("%e %b.", strtotime($row[3]));
            //$date = date('D m',$row[3]);
            echo "<div class='overviewcard'>
                <span class='num $unread'>$i</span>
                <span class='sen $unread'>$sender</span>
                <span class='sub $unread'>$subject..</span>
                <span class='sni'>$snippet...</span>
                <span class='dat'>$date</span>
            </div>";
        }
        if($i==$_SESSION['tmessages']){
            echo " ";
        }
    }
    else{
        /*<button class='startbtn' onclick='startcollect()'>start collecting emails</button>
        <div class='loadbtn'><div></div><div></div><div></div><div></div></div> */
        echo "<div class='dashmsg'>Il n'y aucun emails</div> ";
    }             
?>