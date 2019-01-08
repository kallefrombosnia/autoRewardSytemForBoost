<?php

function init($ip){
    if (!file_exists('boosts.json')) {
        $apicall = file_get_contents('http://api.gametracker.rs/demo/json/server_boosts/'.$ip.'/');
        $apicall= json_decode(stripslashes(html_entity_decode($apicall)));
        $fp = fopen('boosts.json', 'w');
        fwrite($fp, json_encode($apicall));
        fclose($fp);
    }
}

function getServerBoosts($ip){
    $boostlist = file_get_contents('http://api.gametracker.rs/demo/json/server_boosts/'.$ip.'/');
    return $boostlist;
}

function checkNewBoosts($ip){
    $newlist = json_decode(getServerBoosts($ip),true);
    $oldlist = json_decode(file_get_contents("boosts.json"),true);
    $difference= @array_diff_assoc($newlist['boosts'],$oldlist['boosts']);

    if(!empty($difference)){
        return $difference;
    }
    return false;
}

function checkNumberOfBoosts($name,$db){
    $stmt = $db->prepare("SELECT id,have,needed FROM `boost` WHERE `nickname`= ?");
    $stmt->bind_param("s", $name);   
    $stmt->execute();                           
    $stmt->bind_result($result,$have,$needed);                    
    $stmt->fetch();
    $stmt->close();

    if(!is_null($result)){
        if($have >= $needed){
            return true;
        }
    }
    return false;
}


function makeAdmin($name,$password,$type,$db){
    $stmt = $db->prepare("SELECT flags FROM `boost` WHERE `nickname`= ?");
    $stmt->bind_param("s", $name);   
    $stmt->execute();                           
    $stmt->bind_result($flagsdb);                    
    $stmt->fetch();
    $stmt->close();
    $stmt = $db->prepare("SELECT id,access FROM `admins` WHERE `auth`= ?");
    $stmt->bind_param("s", $name);   
    $stmt->execute();                           
    $stmt->bind_result($id,$flagsparse);                    
    $stmt->fetch();
    $stmt->close();

    if(!is_null($id)){
        $flags = str_split($flagsdb);
        $flagsparse1 = str_split($flagsparse);
        foreach($flags as $flag){
            if(@strpos($flagsparse,$flag) !== false) continue;
            $flagsparse1[] = $flag;    
        }
        $flags = (implode("",$flagsparse1));
        if(is_array($flags)) sort($flags);
        $stmt = $db->prepare("UPDATE admins SET access= ? WHERE auth=?");
        $stmt->bind_param("ss", $flags, $name);     
        if(!$stmt->execute()) echo $stmt->error;
        $stmt->close();
        deleteBoostEntry($name,$db);
    }else{
        if($name !== ""){
            $stmt = $db->prepare("INSERT INTO admins(`auth`,`password`, `access`, `flags`) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $name, $password, $flagsdb, $type);     
            if(!$stmt->execute()) echo $stmt->error;
            $stmt->close();
            deleteBoostEntry($name,$db);
        }
    }
    
}

function updateJsonBoostList($data){
    unlink('boosts.json');
    $data = json_decode(stripslashes(html_entity_decode($data)));
    $fp = fopen('boosts.json', 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
}

function deleteBoostEntry($name,$db){
    $stmt = $db->prepare("DELETE FROM `boost` WHERE `nickname`=?");
    $stmt->bind_param("s", $name);     
    $stmt->execute(); 
    $stmt->close();
}

function main($ip,$db){

    include_once('./config.php');
    
    if(checkNewBoosts($ip)){  

        $boosts = checkNewBoosts($ip);
        $commandlist = (array)json_decode(file_get_contents("class.json"),true);

        foreach($boosts as $key => $boost){

            if($boost['boost']['status']!= "ok"){
                // TODO: Add logging for bad boost
                continue;
            }

            $boostinvoker = explode(" ", $boost['boost']['name']);
            $invokerphone = explode('X', $boost['boost']['phone']);
            $command = array_pop($boostinvoker);
            $name = implode(" ", $boostinvoker);
            $password = $invokerphone[0];

            $flags = "";
            $needed = "";

            foreach($commandlist as $cmd){
                $count = (count($cmd)-1);
                for($i= 0; $i<=$count; $i++){  
                    if(array_key_exists($command,$cmd[$i])){
                        foreach($cmd[$i] as $command){
                            $flags = $command['flags'];
                            $needed = $command['boosts'];
                        } 
                    }
                }  
            }
            
            $stmt = $db->prepare("SELECT id,have,flags,nickname FROM `boost` WHERE `nickname`= ?");
            $stmt->bind_param("s", $name);   
            $stmt->execute();                           
            $stmt->bind_result($result,$have,$flagsdb,$nicknamedb);                    
            $stmt->fetch();
            $stmt->close();

            if(!is_null($result)){

                if($name == $nicknamedb){

                    $have++;
                    $stmt = $db->prepare("UPDATE boost SET have=? WHERE nickname=?");
                    $stmt->bind_param("ss", $have, $name);     
                    $stmt->execute(); 
                    $stmt->close();

                    // TODO: Add logging for good boost

                    if(checkNumberOfBoosts($name,$db)){
                        makeAdmin($name,$password,'a',$db);                   
                    }

                }else{

                    $have++;
                    $stmt = $db->prepare("UPDATE boost SET have=? WHERE nickname=?");
                    $stmt->bind_param("ss", $have, $name);     
                    $stmt->execute(); 
                    $stmt->close();

                    // TODO: Add logging for good boost

                    if(checkNumberOfBoosts($name,$db)){
                        makeAdmin($name,$password,'a',$db);        
                    }
                }

            }else{

                if($name !== ""){
                    $have = 1;
                    $stmt = $db->prepare("INSERT INTO boost(nickname,have,needed,flags) VALUES (?,?,?,?)");
                    $stmt->bind_param("ssss", $name, $have, $needed, $flags);     
                    $stmt->execute(); 
                    $stmt->close();
                }

                if(checkNumberOfBoosts($name,$db)){
                    makeAdmin($name,$password,'a',$db);      
                }
            }

        } 
        updateJsonBoostList(getServerBoosts($ip));
    }

}





