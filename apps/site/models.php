<?php

require_once 'libs/utils.php';
require_once 'libs/twig/lib/Twig/Autoloader.php';

//foreach (glob('apps/site/models/*.php') as $filename)
//{
//  require_once $filename;
//}


class Model extends ActiveRecord\Model 
{
    static $table_name = 'section';

    public static function getSection($url){
       $section = Model::find_by_sql("SELECT * FROM section WHERE url = '$url'");
        if(count($section) == 1){
            $section[0]=$section[0]->to_array();
            return $section[0]; 
        }else{
           return false;
        }
    } 

    

    public static function getCategory($url){
       $section = Model::find_by_sql("SELECT * FROM category WHERE url = '$url'");
        if(count($section) == 1){
            $section[0]=$section[0]->to_array();
            return $section[0]; 
        }else{
           return false;
        }
    } 

    public static function getType($url){
       $section = Model::find_by_sql("SELECT * FROM type WHERE url = '$url'");
        if(count($section) == 1){
            $section[0]=$section[0]->to_array();
            return $section[0]; 
        }else{
           return false;
        }
    }

    public static function getKind($url){
       $section = Model::find_by_sql("SELECT * FROM kind WHERE url = '$url'");
        if(count($section) == 1){
            $section[0]=$section[0]->to_array();
            return $section[0]; 
        }else{
           return false;
        }
    }

    public static function getTypeTag($type_id, $tag_id){
        $type_tag = Model::find_by_sql("SELECT * FROM type_tag WHERE type_id = '$type_id' AND tag_id = '$tag_id' ");
        if(count($type_tag) == 1){
            $type_tag[0]=$type_tag[0]->to_array();
            return $type_tag[0]; 
        }else{
           return false;
        }
    }

    public static function getTypeSubkind($type_id, $subkind_id){
        $type_subkind = Model::find_by_sql("SELECT * FROM type_subkind WHERE type_id = '$type_id' AND subkind_id = '$subkind_id' ");
        if(count($type_subkind) == 1){
            $type_subkind[0]=$type_subkind[0]->to_array();
            return $type_subkind[0]; 
        }else{
           return false;
        }
    }

    public static function getKindTag($kind_id, $tag_id){
        $type_tag=Model::find_by_sql("SELECT * FROM kind_tag WHERE kind_id = '$kind_id' AND tag_id = '$tag_id' ");
        if(count($type_tag) == 1){
            $type_tag[0]=$type_tag[0]->to_array();
            return $type_tag[0]; 
        }else{
           return false;
        }
    }

    public static function getKindSubkind($kind_id, $subkind_id){
        $type_subkind = Model::find_by_sql("SELECT * FROM kind_subkind WHERE kind_id = '$kind_id' AND subkind_id = '$subkind_id' ");
        if(count($type_subkind) == 1){
            $type_subkind[0]=$type_subkind[0]->to_array();
            return $type_subkind[0]; 
        }else{
           return false;
        }
    }

    public static function getCategoryTag($category_id, $tag_id){
        $category_tag=Model::find_by_sql("SELECT * FROM category_tag WHERE category_id = '$category_id' AND tag_id = '$tag_id' ");
        if(count($category_tag) == 1){
            $category_tag[0]=$category_tag[0]->to_array();
            return $category_tag[0]; 
        }else{
           return false;
        }
    }

    public static function getStructure($section_id = null, $category_id = null, $type_id = null, $tag_id = null, $subkind_id = null){
        $structure = array();

        if(!$type_id and $section_id and !$category_id){
            return false;
        }

        if($type_id and !$section_id and !$category_id){
            $type = Model::find_by_sql("SELECT * FROM type WHERE id = '$type_id'");
            $category_id = $type[0]->category_id;
            $category = Model::find_by_sql("SELECT * FROM category WHERE id = '$category_id '");
            $section_id = $category[0]->section_id;

            if(!$tag_id and !$subkind_id){
               $type_id = false; 
            }
            
        }

        if(!$type_id and !$section_id and $category_id){
            $category = Model::find_by_sql("SELECT * FROM category WHERE id = '$category_id '");
            $section_id = $category[0]->section_id;
            $category_id = false;
        }


        if($section_id){ 
            $section = Model::find_by_sql("SELECT * FROM section WHERE id = '$section_id'");
            $structure['section'] = $section[0]->to_array();
        }

        if($category_id){ 
            $category = Model::find_by_sql("SELECT * FROM category WHERE id = '$category_id'");
            $structure['category'] = $category[0]->to_array();
        }

        if($type_id){ 
            $type = Model::find_by_sql("SELECT type.id, kind.name, type.title, type.url FROM type LEFT JOIN kind ON type.kind_id = kind.id WHERE type.id = '$type_id'");
            $structure['type'] = $type[0]->to_array();
        }


        return $structure;
    } 

    public static function popularTypes(){
        $structure = array();
        $sections = Model::find_by_sql("SELECT * FROM section");
        $time = time() - 60*60*24*30; 
        $i = 0;

        foreach($sections as $section) {
            $section = $section->to_array();
            $section_id = $section['id'];
            array_push($structure, $section);

            $types = Model::find_by_sql("SELECT type.id, type.name, type.url, count(*) as count FROM type 
                LEFT JOIN category ON type.category_id = category.id  LEFT JOIN product ON type.id = product.type_id 
                WHERE product.add_time > '$time' AND category.section_id = '$section_id' 
                GROUP BY type.id ORDER BY count DESC LIMIT 10");
            
            $types_result = array();
            foreach($types as $type) {
                $type = $type->to_array();
                array_push($types_result, $type);
            }

            $structure[$i]['types'] = $types_result;
            
            $i++;
        }

        return $structure;
    } 

    public static function getProfileId($username){
        $user= Model::find_by_sql("SELECT id FROM user WHERE username = '$username'");
        if(count($user) == 1){
            $user[0] =$user[0]->to_array();
            return $user[0]['id']; 
        }else{
           return false;
        }
    }

    public static function getTagId($url){
       $tag=Model::find_by_sql("SELECT id FROM tag WHERE url = '$url'");
        if(count($tag) == 1){
            $tag[0]=$tag[0]->to_array();
            return $tag[0]['id']; 
        }else{
           return false;
        }
    }

    public static function getSubKindId($url, $type_id = null, $kind_id = null){
        if(isset($type_id )){ 
            $type = Model::find_by_sql("SELECT kind_id FROM type WHERE id = '$type_id'");
            $type = $type[0]->to_array();
            $kind_id = $type['kind_id']; 
        }

        $subkind = Model::find_by_sql("SELECT id FROM subkind WHERE kind_id = '$kind_id' AND url = '$url'");
        if($subkind){
            $subkind [0] = $subkind [0]->to_array();
            return $subkind [0]['id']; 
        }else{
           return false;
        }
    }

    public static function get_store_id_bydomain($domain){
        $store = Model::find_by_sql("SELECT id FROM store WHERE domain = '$domain'");
        if(count($store) == 1){
            $store[0] = $store[0]->to_array();
            return $store[0]['id']; 
        }else{
           return false;
        }
    }

    public static function getSetIdByUrl($url){
        $set = Model::find_by_sql("SELECT id FROM `set` WHERE url = '$url'");
        if($set){
            $set[0]=$set[0]->to_array();
            return $set[0]['id']; 
        }else{
           return false;
        }
    }

    public static function api($method,$params=null){
        if(!isset($_COOKIE['userH'])){$userH = false;}else{$userH =$_COOKIE['userH'];}
        return json_decode(file_get_contents(SITE_ROOT.'/api/'.$method.'?userH='.$userH.'&'.$params), true);
    }

    

}

class User extends ActiveRecord\Model
{
    static $table_name = 'user';

    public static function activateEmail($hash){

        $user = User::find_by_email_activation_hash($hash);

        if( count($user) == 1  && empty($user->email_verified) ){
            $user->email_verified = true;
            $user->save();
            
            $mail = new Email();
            $mail->user_id = $user->id;
            $mail->type = 'welcome';
            $mail->add_time = time();
            $mail->save();

            return true; 
        }else{
            return false;
        }
        
    }


}

class Email extends ActiveRecord\Model
{   
    static $table_name = 'email';
}


class Auth extends ActiveRecord\Model
{
    static $table_name = 'login';
    static $primary_key = 'id'; 


    public static function islogin(){
        $hash = @$_COOKIE['userH'];
        $result = Auth::find_by_sql("SELECT count(*) AS total FROM login WHERE hash = '$hash'");
        $result[0]=$result[0]->to_array();
        $islogin = $result[0]['total']; 
        
        return $islogin;  
    }

    public static function needlogin(){
        if( Auth::islogin() !== '1'){
            header('Location: '.SITE_ROOT);
        } 
    }

    public static function login($params){
        $islogin = Auth::islogin();
        $api_url = SITE_ROOT.'/api/user.login?';
        
        if(isset($_COOKIE['referrer_id'])){
            $referrer_id = $_COOKIE['referrer_id'];
        }else{
            $referrer_id = null;
        }
            
            switch ($params['provider']) {
                case 'vk':
                    if(!isset($params['vk_code'])){
                        $vk_login_url = 'https://oauth.vk.com/authorize?client_id='.VK_CLIENT_ID.'&redirect_uri='.SITE_ROOT.VK_CALLBACK.'&display=touch&scope='.VK_SCOPE.'&response_type=code';
                        header('Location: '.$vk_login_url);
                        exit();   
                    }else{
                        $vk_code = $params['vk_code'];    
                    }

                    $answ = file_get_contents('https://oauth.vk.com/access_token?client_id='.VK_CLIENT_ID.'&client_secret='.VK_SECRET.'&code='.$vk_code.'&redirect_uri='.SITE_ROOT.VK_CALLBACK);
                    $data = json_decode($answ);

                    
                    $access_token = $data->{'access_token'};
                    $provider_user_id = $data->{'user_id'};

                    $api_request = 'provider='.$params['provider'].'&referrer_id='.$referrer_id.'&access_token='.$access_token.'&provider_user_id='.$provider_user_id;
                    break;
            }

        $data = file_get_contents($api_url.$api_request);  
        $data = json_decode($data, true);

        //var_dump($data);
        //exit();

        return $data;
    }



}

?>