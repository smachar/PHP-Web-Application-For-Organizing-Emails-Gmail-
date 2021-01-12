<?php

class User{
    private $client; 
    private $access_token; 
    private $gmail=null;
    private $db=null; 
    private $email = null;
    private $totalMessages = 0;
    private $password = null;

    function __construct(){
        $i=func_num_args();
        if(method_exists($this,$f="__construct$i")){
            call_user_func(array($this, $f));
        }
        else{
            $this->client = new Google_Client();
            $this->client->setApplicationName('MailApp');
            $this->client->setAuthConfigFile('./json/credentials.json');
            $this->client->setAccessType('offline');
            $a=$_SERVER['SERVER_ADDR'];
            if($a=="::1") $a="localhost";
            $uri = 'http://'.$a.'/oauth.php'; //mailapp/Q
            $this->client->setRedirectUri($uri);
            $this->client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        }
    }
    function __construct1(){ //not creating the user for the auth redirect(not first time)
        $this->client = new Google_Client();
        $this->client->setAuthConfigFile('./json/credentials.json');
        $this->client->addScope(Google_Service_Gmail::GMAIL_READONLY);
    }

    function handleError($error){
        if($error=='access_denied') {
            header('Location: index.php?m=access');
            exit;
        }
        elseif($error=='invalid_grant') {
            header('Location: index.php?m=invalid');
            exit;
        }
        else {
            header('Location: index.php?m=unknown');
            exit;
        }
    }
    function getConsent(){
        $auth_url = $this->client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        exit;
    }
    
    function authCode($code){
        //fetchAccessTokenWithAuthCode=authenticate
        $this->access_token = $this->client->fetchAccessTokenWithAuthCode($code); //array
        $this->gmail = new Google_Service_Gmail($this->client);
        $this->email = $this->gmail->users->getProfile("me")->getEmailAddress();
        //$this->totalMessages = $this->gmail->users->getProfile("me")->getMessagesTotal();
        //return true; //if it didnt throw an error so it's done right.
    }
    function isalreadyIn(){
        $result = $this->db->isalreadyIn($this->email)->fetch();
        if($result[0]!=0){ //count(email)
            if(is_null($result[1])) return -1; //password
            else return 1;
        }
        return 0;
        //1 with pass, -1 without pass, 0 not
    }
    function registerUser(){
        if(!$this->email || !$this->access_token) return 0; //prb in authCode

        $access_token_json = json_encode($this->access_token, JSON_UNESCAPED_SLASHES);
        $isIn = $this->isalreadyIn();
        if($isIn == 1){//has password
            $this->db->updateToken($this->email, $access_token_json);
            return -1; //successfuly updated
        }
        elseif($isIn == 0){ //doesn't have password, new
            $this->db->addUser($this->email, $this->password, $access_token_json);
            return 1; //successfuly added, 1st password=NULL
        }
        elseif($isIn == -1){ //has password set to null
            $this->db->updateToken($this->email, $access_token_json);
            return -2; //successfuly updated token, password still null
        }
        else return 0;
        //-2 updated & pass=null, -1 updated & pass!=null, 0 error, 1 added with pass=null 
    }
    function getEmail(){
        return $this->email;
    }
    function setEmail($email){
        if($this->email==null)
            $this->email = $email;
    }
    function getTotalMessages(){
        return $this->totalMessages;
    }
    function setTotalMessages($n){
        $this->totalMessages = $n;
    }
    function getDB(){
        return $this->db;
    }
    function setDB(){
        if($this->db==null)
            $this->db = new DB();
    }
    function setToken(){
        if(!$this->email){
            //echo "no email";
            return false;
        }
        $this->setDB();
        $this->access_token = $this->db->getToken($this->email);//retrive token from db
        if($this->access_token==null){
            //echo "cant get access token from db"; //not valid json
            return false;
        }
        $this->client->setAccessToken($this->access_token); //as array
        if($this->client->isAccessTokenExpired()){
            if($r_access_token = $this->client->fetchAccessTokenWithRefreshToken())
                $this->access_token = $r_access_token;
            else{
                //echo "cant refresh the token!";
                //need to reauth with code again!!
                return false;
                //exit;
            }
        }
        return true;
    }
    function getGmail(){
        return $this->gmail;
    }
    function setGmail(){
        if($this->gmail==null)
            $this->gmail = new Google_Service_Gmail($this->client);
    }
}
?>