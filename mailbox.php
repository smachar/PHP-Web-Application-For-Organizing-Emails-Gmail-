<?php
    require "session.php";
    require "functions.php";
    require "Model/DB.php";

    if(!isset($_SESSION['type']) || $_SESSION['type']!='login'){
        header('Location: index.php?out=true');
        exit;
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $errors = validate($_POST);
        if(empty($errors)){
            $mailbox = [];
            foreach($_POST as $key=>$value){
                if($key=="name"){
                    $mailbox[] = $value;
                }else{
                    $mailbox[] = join(',', $value); //should not be hand-encoded (what if user data contains that ',')
                }
            }
            $result = (new DB)->addMailbox($mailbox, $_SESSION['email']);
            // $stm->debugDumpParams();
            // exit;
            if(!$result){
                $msg = "Can't add new mail box to database, please try later!";
                exit($msg);
            }
            //$_SESSION['msg'] = "The ".$mailbox[0]."mailbox was added successfully.";
            header('location: main.php');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une nouvelle boîte de réception</title>
    <link rel="stylesheet" href="style/mailbox.css">
    <script type="text/javascript">
        function addNode(name) {
            const id = '_' + Math.random().toString(36).substr(2, 9);
            var html = `<input name='${name}-names[]' type='text' placeholder='ajouter un autre'><button id='${id}' onclick="return deleteNode(this,'${name}')" >-</button>`;
            var ele = document.createElement('div');
            ele.setAttribute("id", id);
            ele.innerHTML = html;
            document.getElementById(name).appendChild(ele);
            return false;
        }
        function deleteNode(e, name){
            if (!e) var e = window.event;
            var child = document.getElementById(e.id);
            var parent = document.getElementById(name);
            parent.removeChild(child);
            return false;
        }
    </script>
</head>
<body>
    <h1>Créer une nouvelle boîte de réception</h1>
        <?php
            if(isset($errors)) echo display_error($errors);
        ?>
    <div class="center">
        <form action="mailbox.php" method='post'>
            <div class="field">
                <label for="name">Nom:</label>
                <input name= "name" type="text" placeholder='um6p'>
            </div>

            <div class="field">
                <label for="sender-names">Expéditeur(trice):</label>
                <div id="sender">
                    <input name= "sender-names[]" type="text" placeholder='UM6P Events'>
                    <button onclick="return addNode('sender')">+</button>
                    
                </div>
            </div>
            <div class="field">
                <label for="domain-names">Nom de domaine de l'organisation:</label>
                <div id="domain">
                    <input name= "domain-names[]" type="text" placeholder='um6p.ma'>
                    <button onclick="return addNode('domain')">+</button>
                </div>
            </div>
            <div class="field">
                <label for="key-names">L'email contient le mot:</label>
                <div id="key">
                    <input name= "key-names[]" type="text" placeholder='urgent'>
                    <button onclick="return addNode('key')">+</button>
                </div>
            </div>
            <br>
            <div class="button">
                <button type="submit">Enregistrer</button>
                <button type="reset">Réinitialiser</button>
            </div>
        </form>
    </div>
    
</body>
</html>