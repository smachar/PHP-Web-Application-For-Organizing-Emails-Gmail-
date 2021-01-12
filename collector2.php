<?php
//php -a -d auto_prepend_file=collector2.php

require "Model/DB.php";
require "vendor/autoload.php";

require "Model/User.php";

$user = new User(1);

session_start();
$user->setEmail($_SESSION['email']);
$user->setTotalMessages($_SESSION['tmessages']);

//$user->setEmail('emailapp22@gmail.com');
// $user->setEmail('ismailbachar0@gmail.com');
// $user->setTotalMessages(0);


function getSendSubj(){
    $fg=2;
    global $payload;
    foreach($payload->headers as $h){
        if($fg==1){
            if($h->name=='Subject'){
                $subject = $h->value;
                break;
            }
        }
        elseif($fg==0){
            if($h->name=='From'){
                $sender = $h->value;
                break;
            }
        }
        else{
            if($h->name=='From'){
                $sender = $h->value;
                $fg=1;
            }
            elseif($h->name=='Subject'){
                $subject = $h->value;
                $fg=0;
            }
        }
    }
    return array($sender, $subject);
}


if($user->setToken()){
    $user->setGmail();
    $tmessages = $user->getGmail()->users->getProfile("me")->getMessagesTotal();
    $rest = $tmessages - $user->getTotalMessages(); //from session

    if($rest>50) $rest = 50; //limit to collect
    
    $collected=0;

    if($rest!=0){
        $src = $user->getGmail()->users_messages;
        $page_token = null;
        $email_q = "insert into email values (?,?,?,?,?,?,?,?,?)";
        $atta_q = "insert into attachment values (?,?,?,?,?)";

        $update_qr = "update users set tmessages=? where email=?";

        $user->setDB(); //create db connection if it's not yet done

        $stm_email = $user->getDB()->connection->prepare($email_q);
        $stm_atta = $user->getDB()->connection->prepare($atta_q);

        $stm_update = $user->getDB()->connection->prepare($update_qr);
        $stm_update->execute(array($tmessages, $user->getEmail()));

        do{
            //getting from the page a list of emails ids.
            if($page_token)
                $msg_ids = $src->listUsersMessages($user->getEmail(), array('PageToken'=>$page_token));
            else $msg_ids = $src->listUsersMessages($user->getEmail());

            $page_token = $msg_ids->nextPageToken;

            foreach($msg_ids->messages as $msg){
                try{
                    $user->getDB()->connection->beginTransaction();
                    //echo "collected=", $collected, "\n";
                    if($rest == $collected) {
                        break 2;
                    }
                    $msg_id = $msg->id;
                    //echo "id: $msg_id\n";
                    $msg_data = $src->get($user->getEmail(), $msg_id);

                    $internalDate = (int)$msg_data->internalDate/1000;
                    $labels = "";
                    foreach($msg_data->labelIds as $label){
                        $labels .=  $label.",";
                    }
                    $snippet = $msg_data->snippet;
                    $payload = $msg_data->payload;
                    [$sender, $subject] = getSendSubj();

                    switch($payload->mimeType){
                        case 'text/html':
                            $html = base64_decode(str_replace(array('-', '_'),array('+','/'),$payload->body->data));
                            $plain = preg_replace("/<.*?>/","",$html);
                            break;
                        case 'multipart/alternative':
                            $plain = base64_decode(str_replace(array('-', '_'),array('+','/'),$payload->parts[0]->body->data));
                            $html = base64_decode(str_replace(array('-', '_'),array('+','/'),$payload->parts[1]->body->data));
                            break;
                        case 'multipart/mixed':
                            $plain = base64_decode(str_replace(array('-', '_'),array('+','/'),$payload->parts[0]->parts[0]->body->data));
                            $html = base64_decode(str_replace(array('-', '_'),array('+','/'),$payload->parts[0]->parts[1]->body->data));
                            $attId = $payload->parts[1]->body->attachmentId;
                            //echo "attId:",$attId, "\n";
                            $attData = $user->getGmail()->users_messages_attachments->get($user->getEmail(), $msg_id, $attId)->data;
                            $attData = base64_decode(str_replace(array('-', '_'),array('+','/'),$attData));
                            $file_name = $payload->parts[1]->filename;
                            $type = $payload->parts[1]->mimeType;
                            break;   
                    }

                    $email_data = array(
                        $msg_id,
                        $labels,
                        $internalDate,
                        $sender,
                        $subject,
                        $snippet,
                        $plain,
                        $html,
                    );
                    if(!$userId = $user->getDB()->getUserId($user->getEmail())){
                        echo "cant get user id, maybe email not valid";
                        exit;
                    }
                    $email_data[] = (int)$userId;
                    $stm_email->execute($email_data);

                    if(isset($attId)){ //fix this to run this script in the background
                        $atta_data = array(
                            $attId,
                            $attData,
                            $msg_id,
                            $type,
                            $file_name,
                        );
                        $stm_atta->execute($atta_data);
                        unset($attId);
                        //echo "attachment data added \n";
                    }

                    $user->getDB()->connection->commit();
                    $collected += 1;
                    //echo "successfuly added!\n";
                }catch(PDOException $e){
                    $user->getDB()->connection->rollback();
                    throw $e;
                }
            }
        }while($page_token); //go to next page if exists
        
        $_SESSION['tmessages'] = $tmessages; //update session
        $user->setTotalMessages($tmessages); //update 'user'
    }
}

require "getEmails.php";

?>