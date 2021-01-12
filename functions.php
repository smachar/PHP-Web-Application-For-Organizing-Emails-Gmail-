<?php
    function has_length($value, $options) {
        if(isset($options['min']) && strlen($value) < $options['min']) {
            return false;
        } elseif(isset($options['max']) && strlen($value) > $options['max']) {
            return false;
        }
        return true;
    }
    function has_unique_mailbox_name($name_mail){
        return (new DB)->isMailboxUnique($name_mail);

    }

    function validate($form) {
        $errors = [];

        if(!isset($form['name'])|| trim($form['name']) === ''){
            $errors[] = "Le nom ne peut pas être vide.";
        }elseif (!has_length($form['name'], array('min' => 2, 'max' => 255))) {
            $errors[] = "Le nom doit comporter entre 2 et 255 caractères..";
        }
        if(!isset($form['sender-names']) || empty($form['sender-names']) || trim($form['sender-names'][0]) === ''){
            $errors[] = "Au moins un nom d'expéditeur doit être spécifié.";
        }
        if(!isset($form['domain-names']) || empty($form['domain-names']) || trim($form['domain-names'][0]) === ''){
            $errors[] = "Au moins un nom de domaine doit être spécifié.";
        }
        else{
            foreach($form['domain-names'] as $domain){
                $domain_regex = '/\A[A-Z0-9]+\.[A-Z]{2,}\Z/i';
                if($domain != '' && preg_match($domain_regex, $domain) !== 1){
                    $errors[] = $domain ." : Format de nom de domaine non valide.";
                }
            }
        }
        if(!has_unique_mailbox_name(array('mailbox_name'=>$form['name'], 'email'=>$_SESSION['email']))){
            $errors[] = "'".$form['name']."': Vous avez déjà utilisé ce nom, veuillez en choisir un autre ";
        }

        return $errors;
    }

    function display_error($errors=array()){
        $output = '';
        if(!empty($errors)) {
            $output .= "<div class=\"errors\">";
            $output .= "Merci de corriger les erreurs suivantes:";
            $output .= "<ul>";
            foreach($errors as $error) {
                $output .= "<li>" . $error . "</li>";
            }
            $output .= "</ul>";
            $output .= "</div>";
        }
        return $output;
    }
    function display_mailbox(){
        $output = '';
        if($mailbox_names = (new DB)->getMailboxNames($_SESSION['email'])){
            foreach($mailbox_names as $mailbox){
                $output .= "<li class='sidenav__list-item' onclick=\"displayMailbox('";
                $output .= $mailbox['name'];
                $output .= "')\" >";
                $output .= $mailbox['name']."</li>";
            }
        }
        return $output;
    }
?>