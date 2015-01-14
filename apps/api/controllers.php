<?php
header('Allow: GET, PUT, PATCH, DELETE, HEAD, OPTIONS');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type');


$method = $subpage_name;
if(!isset($request['limit'])){ $limit = 0; }else{$limit = $request['limit'];}


if(isset($_COOKIE['userH'])){
   define('USERH', $_COOKIE['userH']); 
}else{
    if(isset($request['userH'])){
        define('USERH', $request['userH']); 
    }else{
        define('USERH', null);  
    }   


}

if(USERH == null){    
    define('UID', null);
}else{
    $uid = User::get_uid_by_hash(USERH);
    if($uid){
        define('UID', $uid);   
    }else{
        define('UID', null);
    }
}
    
define("CURRENCY_ID", 1);


switch($method){

    case 'notifications.get':
        $response= Notification::get();
        break;

    case 'notifications.view':
        $response= Notification::view();
        break;    

    case 'sections.get':
        $response= Section::get($request);
        break;

    case 'subkinds.get':
        $response = SubKind::get($request);
        break;    

    case 'shop.getcategory':
        $response= Product::getcategory($request['section_id']);
        break; 

    case 'stores.search':
        $response=Store::search($request['query'],$limit);
        break;

    case 'users.search':
        $response=User::search($request['query'],$limit);
        break;
        
    case 'products.add':
        $response = Product::add($request);
        break;

    case 'product.getUsers':
        $response=Save::getusers($request['product_id'],$limit, $request['type']);
        break;
        
    case 'products.genThumbs':
        $response = Product::genThumbs($request['product']);
        break;        

    case 'products.get':
        $response =Product::get($request);
        break;  

    case 'products.getById':
        $response =Product::info(@$request['product_id'], @$request['type']);
        break;     

    case 'purchase.add':
        $product_id = $request['product_id'];
        $response = Purchase::add($product_id);
        break;                

    case 'stores.getById':
        $response = Store::info($request['store_id'], @$request['type']);
        break;


    case 'stores.get':
        $response = Store::get($request);
        break;   


    case 'tags.getById':
        $response = Tag::info($request['tag_id'], @$request['type']);
        break; 

    case 'tags.get':
        $response=Tag::get($request);
        break;                       

    case 'users.get':
        $response = User::get($request);
        break;

    case 'users.getById':
        $response = User::info(@$request['user_id'], @$request['type']);
        break; 


    case 'users.referrers':
        $response = User::referrers();
        break;

    case 'users.inviteStat':
        $response = User::inviteStat();
        break;        

    case 'users.store_followers':
        $store_id = $request['store_id'];
        $response = User::store_followers($store_id, $limit);
        break;

    case 'user.login':
        $response = User::login($request);
    break;  

    case 'user.set':
        $response = User::set($request);
    break;  

    case 'user.signup':
        $response = User::signup($request);
    break;

            
    case 'friends.getList':
        if(isset($request['product_id'])){
            $product_id=$request['product_id'];
            $response = Friend::getList($limit, $product_id);
        }else{
            $response = Friend::getList($limit, $product_id=null);
        }
        break;       

    case 'follow.set':

         switch($request['type']){

            case 'store':  
                FollowStore::set($request['item_id'], $request['action']);
            break;

            case 'user':  
                FollowUser::set($request['item_id'], $request['action']);
            break;

            case 'tag':  
                FollowTag::set($request['item_id'], $request['action']);
            break;

        }

    break;

    case 'follow.status':
        if ($request['type']=='store'){
            echo  FollowStore::status($request['item_id']);
        }else if($request['type']=='store'){
            echo  FollowUser::status($request['item_id']);
        }else if($request['type']=='tag'){
            echo  FollowTag::status($request['item_id']);
        }    
        break;

     case 'save.getById':
        $save_id = $request['save_id'];
        $response = Save::info($save_id);
        break;      
                    
    case 'save.set':
        Save::set($request['product_id'], $request['action']);
        break;

    case 'save.status':
        $product_id = $request['product_id'];
        echo Save::status($product_id);
        break; 

    case 'comments.add':
        if ($request['value']){
            $value=$request['value'];
            $product_id=$request['product_id'];
            $response=Comment::add($product_id, $value);
        }
        break;

    case 'comments.get':
        $product_id=$request['product_id'];
        $response = Comment::get($product_id);
        break;

    case 'sets.getById':
        $response = Set::info($request['set_id']);
        break;

    case 'subkinds.getById':
        $response = SubKind::info($request['id']);
        break;       

    case 'sets.get':
        $response = Set::get($request);
        break;

    case 'colors.get':
        $response = Color::get($request);
        break;
        

    case 'detectcolor':
        $response = Product::detectColor($request['img']);
        break;         


    default: echo 'error';     
}

if(isset($response)){ echo json_encode($response); }

?>