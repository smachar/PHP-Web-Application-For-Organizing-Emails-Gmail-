<?php
class Password{
    private $email;
    private $password;
    private $oldpassword;
    private $db;
    private $login;
    private $ispost;
    private $type;
    private $fname;
    private $lname;

    function __construct(){
        if(!isset($_POST['email'])){
            $this->email = $_SESSION['email'];
        }
        $this->db = new DB();
        if($_SERVER['REQUEST_METHOD'] == 'GET'){ //redirected from oauth.php with session pars
            $this->ispost = false;
            if(isset($_SESSION['type'])){ //should be done from oauth.php
                if($_SESSION['type']=='login'){ //should not happend, but it's a safe redirect
                    header('Location: main.php');
                    exit;
                }
                $this->type = $_SESSION['type']; //used in htmk displayed text
            }
            else{
                header('Location: index.php');
                exit;
            }
        }
        elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
            $this->ispost = true;
            if(isset($_POST['oldpassword'])){ //update
                $this->oldpassword = $_POST['oldpassword'];
                $this->password = $_POST['password1'];
                $this->type = 'Update';
            }
            elseif(isset($_POST['password2'])) { //Set
                $this->password = $_POST['password1'];
                $this->fname = $_POST['fname'];
                $this->lname = $_POST['lname'];
                $this->type = 'Set';
            }
            elseif(isset($_POST['email'])){ //login with email & password, from index.php
                $this->login = true;
                $this->email = $_POST['email'];
                $this->oldpassword = $_POST['password1']; //store it in oldpass to make it easy for query.
            }
            else{ //Log, when the token updated, pass!=null (user has pass, should log-in)
                $this->type = 'Log';
                $this->oldpassword = $_POST['password1'];
            }
        }
        else{
            header('Location: index.php');
            exit;
        }
    }
    function setPass(){
        return $this->db->setPass($this->email, $this->password, $this->fname, $this->lname);
    }
    function isMatched(){
        if(!$this->email || !$this->oldpassword){
            //echo "unvalid email/pass\n";
            return false;
        }
        if($result = $this->db->isMatched($this->email, $this->oldpassword)->fetch(PDO::FETCH_ASSOC)){
            return $result['tmessages'];
        }
        return false;
        // check with ===. bcz result[0] could be 0 (the tmessages in DB)
    }
    function updatepass(){
        if($this->isMatched()===false){
            return false;
        }
        return $this->setPass();
    }
    function getIsPost(){
        return $this->ispost;
    }
    function getLogin(){
        return $this->login;
    }
    function getType(){
        return $this->type;
    }
}
?>