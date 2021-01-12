<?php 

class DB{
    private $host = 'localhost';
    private $user = 'mailapp_admin';
    private $pass = 'AA123mailapp_admin@pass';
    private $database='mailapp';
    public $connection;
    public $isopen;
    public $limit = 5;

    function __construct(){
        try{
            $this->connection=new PDO("mysql:host=$this->host;dbname=$this->database",$this->user,$this->pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "conncetion failed: ".$e->getMessage();
            exit;
        }
    }
    function addUser($email, $pass=null, $access_token){
        $query = "insert into users(email,hashed_password,access_token) values(?,?,?)";
        $pars = array($email);
        //if($pass==null) $pars[] = 'NULL'; //for 1st time access
        //else 
        $pars[] = sha1($pass); 
        $pars[] = $access_token;
        return $this->execute($query, $pars);
    }
    function updateToken($email, $access_token){
        $query =  "update users set access_token=? where email=?";
        $pars = array($access_token, $email);
        return $this->execute($query, $pars);
    }
    function setPass($e, $p, $fname='', $lname=''){
        $pars = array(sha1($p));
        $query =  "update users set hashed_password=?";
        if($fname!='') {
            $query .= ", first_name=?";
            $pars[] = $fname;
        }
        if($lname!='') {
            $query .= ", last_name=?";
            $pars[] = $lname;
        }
        $query .= " where email=?";
        $pars[] = $e;
        return $this->execute($query, $pars);
        // $query  = "update users set hashed_password='".sha1($p)."' where email='$e'";
        // return $this->execute($query);
    }
    function isMatched($e, $p){
        $query =  "select tmessages from users where email=? and hashed_password=?";
        $pars = array($e, sha1($p));
        return $this->execute($query, $pars);

        // $query = "select email from users where email='$e' and hashed_password='".sha1($p)."'";
        // return $this->execute($query);
    }
    function tMessagesInDb($e){
        $query =  "select tmessages from users where email=?";
        $pars = array($e);
        return $this->execute($query, $pars);

        // $query = "select email from users where email='$e' and hashed_password='".sha1($p)."'";
        // return $this->execute($query);
    }

    function isalreadyIn($e){
        $query = "select count(email), hashed_password from users where email=?";
        $pars = array($e);
        return $this->execute($query, $pars);
    }
    function getToken($e){
        $query = "select access_token from users where email=?";
        $pars = array($e);
        $stm = $this->execute($query, $pars);
        return json_decode($stm->fetch()[0], true); //as array
    }
    function getUserEmails($pars){
        $qr_pars = array(':mail'=>$pars[0]);
        $query = "select email.sender,email.subject,email.snippet,email.date,email.id,email.labels from email, users where users.email=:mail and users.id=email.userId";
        if(isset($pars[1])){
            if($pars[2]=='true'){
                $query .= " and email.sender like :sender";
                $qr_pars[':sender'] = "%$pars[1]%";
            }
            if($pars[3]=='true'){
                $query .= " and email.subject like :subject";
                $qr_pars[':subject'] = "%$pars[1]%";
            }
            if($pars[4]=='true'){
                $query .= " and email.snippet like :snippet";
                $qr_pars[':snippet'] = "%$pars[1]%";
            }
            if($pars[5]=='true'){
                $query .= " and email.plain like :plain";
                $qr_pars[':plain'] = "%$pars[1]%";
            }
        }
        $query .= " order by email.date desc";
        if(isset($pars['offset']) && $pars['offset'] >= 0) {
            $query .= " limit $this->limit offset :offset";
            $qr_pars[':offset'] = $pars['offset'];
        }
        return $this->execute($query, $qr_pars);
    }
    function execute($query, $pars=null){
        try{
            $stm=$this->connection->prepare($query);
        }catch(PDOException $e){
            echo "preparing failed: ".$e->getMessage();
            exit;
        }
        if(isset($pars[':offset'])){
            foreach($pars as $key => $value){
                if($key==':offset'){
                    $stm->bindParam($key, intval($value), PDO::PARAM_INT);
                    unset($key);
                    unset($value);
                }
                else{
                    $stm->bindParam($key, $value);
                    unset($key);
                    unset($value);
                }
            }
            $stm->execute();
            return $stm;
        } else{
            $stm->execute($pars);
            return $stm;
        }
    }

    function getUserId($email){
        $query = "select id from users where email=?";
        $r = $this->execute($query, array($email));
        if($r){
            return $r->fetch(PDO::FETCH_ASSOC)['id'];
        }
        return false;
        // if(!($id = $r->fetch(PDO::FETCH_ASSOC)['id'])){
        //     return false;
        // }
        // return $id;
    }
    function addMailbox($mailbox, $email){
        $mailbox[] = $this->getUserId($email);
        $query = "insert into mailbox(`name`,`senders`,`domains`,`keys`,`idU`) values(?,?,?,?,?)";
        return $this->execute($query, $mailbox);
    }
    function getMailboxNames($email){
        $sql = "select name from mailbox where idU=?";
        $result = $this->execute($sql, array($this->getUserId($email)));
        if(!($names = $result->fetchAll(PDO::FETCH_ASSOC))){
            return false;
        }
        return $names;
    }
    function isMailboxUnique($name_mail){
        $sql = "select count(id) from mailbox where name=? and idU=?";
        $pars[] = $name_mail['mailbox_name'];
        $pars[] = $this->getUserId($name_mail['email']);
        $result = $this->execute($sql, $pars);
        if(!($count_row = $result->fetch(PDO::FETCH_NUM)) || $count_row[0]!=0){
            return false;
        }
        return true;;
    }
    function getMailboxByName($name_mail){
        $sql = "select * from mailbox where name=? and idU=? limit 1";
        $pars[] = $name_mail['mailbox_name'];
        $pars[] = $this->getUserId($name_mail['email']);
        $result = $this->execute($sql, $pars);
        return $result;
    }

    function getMailbox($name_mail){
        if(!($result = $this->getMailboxByName($name_mail))){
            return false;
        }
        if(!($mailbox = $result->fetch(PDO::FETCH_ASSOC))){
            return false;
        }
        //mailbox = Array( [id] => 6 [name] => um6p2 [senders] => Ismail Bachchar,Taoufik Rachad,Hassan Machkour [domains] => linkedin.com,um6p.ma [idU] => 25 ) 
        $sender_names = []; //;
        foreach(explode(",", $mailbox['senders']) as $sender){
            $sender_names[] = "%".$sender."%";
        }
        $domain_names = []; //;
        foreach(explode(",", $mailbox['domains']) as $domain){
            $domain_names[] = "%".$domain."%";
        }
        $key_names = []; //;
        foreach(explode(",", $mailbox['keys']) as $key){
            $key_names[] = "%".$key."%";
        }
        
        $senders_in  = str_repeat('sender  like ? OR ', count($sender_names)-1) . 'sender  like ?';
        $domains_in  = str_repeat('sender  like ? OR ', count($domain_names)-1) . 'sender  like ?';
        $keys_in  = str_repeat('plain  like ? OR ', count($key_names)-1) . 'plain  like ?';
        
        $sql = "SELECT sender, subject, snippet, date, labels FROM email WHERE userId=? and ($senders_in OR $domains_in OR $keys_in)";
        $sql .= " order by date desc";
        // var_dump($this->execute($sql, array_merge((array)$mailbox['idU'], $sender_names, $domain_names))->debugDumpParams());
        // exit;
        return $this->execute($sql, array_merge((array)$mailbox['idU'], $sender_names, $domain_names, $key_names));
    }
}
?>