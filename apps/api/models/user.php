<?

class User extends ActiveRecord\Model
{
    static $table_name = 'user';
    static $primary_key = 'id';

  
    public static function info($user_id = null, $type = null, $user = null){
        if(!$user_id){ 
            $user_id = UID;
            $scope =  'id, photo, first_name, last_name, username, sex, email, add_time, location, about, site';
        }else{
            $scope =  'id, photo, first_name, last_name, username, sex, add_time, location, about, site';
        }

        if(!$user){
            $user = User::find($user_id, array('select' => $scope));
            $user=$user->to_array();
        }

        if($user){
            
            if($type == "full"){
                $user['type']='user_title';
                $user['title_products'] = Product::get($user);
                $user['follow_status'] =  FollowUser::status($user['id']);
                $user['islogin'] =  User::islogin();

                $user['followers_count'] =  FollowUser::followers_count($user['id']);
                $user['following_count'] =  FollowUser::following_count($user['id']);
                $user['stores_count'] =  FollowStore::stores_count($user['id']);
                $user['tags_count'] =  FollowTag::tags_count($user['id']);
                
                $user['saves_count'] =  Save::getCount($user['id']);
            }

            if($user['id'] == UID){
               $user['balance'] =  self::balance();
            }

            return $user;
        }else{
            return false;
        }    
    }



    public static function get($params){

        if(!isset($params['page_number'])){
            $limit = 0;
        }else{
            $limit = ($params['page_number']-1) * 50;
        }

        switch ($params['type']) {
            case 'popular':
                //$count = User::count();
                $users = User::find_by_sql("SELECT user.id, user.first_name, user.last_name, user.username, user.photo, count(*) as count 
                FROM user 
                LEFT JOIN follow_user ON user.id = follow_user.user_id
                INNER JOIN product_save ON  user.id = product_save.user_id
                GROUP BY user.id 
                HAVING COUNT(product_save.id) >= 3
                ORDER BY count DESC
                LIMIT $limit, 50");
            break;

            case 'wizard':
                //$count = User::count();
                $user = User::info();
                $sex = $user['sex'];
                $users = User::find_by_sql("SELECT user.id, user.first_name, user.last_name, user.username, user.photo, count(*) as count 
                FROM user 
                LEFT JOIN follow_user ON user.id = follow_user.user_id
                INNER JOIN product_save ON  user.id = product_save.user_id
                WHERE  user.sex = '$sex'
                GROUP BY user.id 
                HAVING COUNT(product_save.id) >= 3
                ORDER BY count DESC
                LIMIT $limit, 50");
            break;

            case 'followers':
                $user_id = $params['user_id'];
               $users = User::find_by_sql("SELECT * FROM follow_user JOIN user 
                ON follow_user.follower_user_id = user.id
                WHERE follow_user.user_id = '$user_id' 
                ORDER BY follow_user.start_time DESC
                LIMIT $limit , 50"); 

            break;

            case 'following':
                $user_id = $params['user_id'];
                $users = User::find_by_sql( "SELECT * FROM follow_user JOIN user 
                ON follow_user.user_id = user.id 
                WHERE follow_user.follower_user_id = '$user_id'
                ORDER BY follow_user.start_time DESC 
                LIMIT $limit , 50");

            break;

            case 'store_followers':
                $store_id = $params['store_id'];
                $users = User::find_by_sql("SELECT * FROM follow_store JOIN user 
                ON follow_store.follower_user_id = user.id
                WHERE follow_store.store_id = '$store_id' 
                ORDER BY follow_store.start_time DESC
                LIMIT $limit , 50"); 
            break;

            case 'tag_followers':
                $tag_id = $params['tag_id'];
                $users = User::find_by_sql("SELECT * FROM follow_tag JOIN user 
                ON follow_tag.follower_user_id = user.id
                WHERE follow_tag.tag_id = '$tag_id' 
                ORDER BY follow_tag.start_time DESC
                LIMIT $limit , 50"); 
            break;

            case 'savers':
                $product_id = $params['product_id'];
                $users = User::find_by_sql("SELECT user.id FROM user JOIN product_save 
                ON  user.id = product_save.user_id
                WHERE product_save.product_id = '$product_id'
                ORDER BY product_save.add_time DESC
                LIMIT $limit , 20");

            break;

            case 'find_friends':
                $users = User::find_by_sql("SELECT login_provider.user_id AS id FROM friend 
                LEFT JOIN login_provider ON friend.provider_user_id = login_provider.provider_user_id
                WHERE friend.user_id = '".UID."'
                ORDER BY login_provider.add_time DESC
                LIMIT $limit , 50");
            break;

            case 'referrers':
                $referrer_id = UID;
                $users = User::find_by_sql("SELECT * FROM user 
                    WHERE referrer_id = '$referrer_id'
                    ORDER BY add_time DESC");
                    
                $count = count($users);
 
            break;
            case 'search':
                $assoc = trim($params['q']); 
                $assoc = addslashes($params['q']);
                $assoc = htmlspecialchars($params['q']);
                $assoc = explode(" ", $assoc);
                $query = "SELECT * FROM user WHERE";
                foreach ($assoc as $searchWord) {
                    if($searchWord == end($assoc)) {
                        $query.=" first_name LIKE '%$searchWord%' OR last_name LIKE '%$searchWord%' OR username LIKE '%$searchWord%'";
                    }else {
                        $query.=" first_name LIKE '%$searchWord%' OR last_name LIKE '%$searchWord%' OR username LIKE '%$searchWord%' OR ";
                    }
                }
                $query.="ORDER BY id DESC LIMIT $limit, 50";
                $users = User::find_by_sql($query);
            break;
        }

        if(!$users){ return false;} 

        $result= array();
        $result_users = array();

        foreach($users as $user) {
                $user = $user->to_array();
                if($params['type']=='savers' || $params['type']=='find_friends'){
                    $user = User::info($user['id'], 'full');
                }else{
                   $user = User::info($user['id'], 'full', $user); 
                }
                

                //if($user['id'] != UID){
                    array_push($result_users, $user);
                //}
        }

        $result['count'] = @$count;
        $result['users'] = $result_users;
        return $result;
    }

    public static function set($params){
        if (!UID) return;
        $user = User::find(UID);
        $user->first_name = $params['first_name'];
        $user->last_name = $params['last_name'];
        $user->about = $params['about'];
        $user->location = $params['location'];
        $user->site = preg_replace('#^https?://#', '', $params['site']);
        $user->save();
        return $user;
    }

    public static function balance(){
        $users = User::find_by_sql("SELECT user.first_name, COUNT(product_save.id) AS saves, COUNT(purchase.id) AS purchases
         FROM user
        INNER JOIN product_save ON  user.id = product_save.user_id
        INNER JOIN purchase ON user.id = purchase.user_id
        WHERE referrer_id = ".UID."
        GROUP BY first_name
        HAVING COUNT(product_save.id) >= 10 AND COUNT(purchase.id) >= 1 
        LIMIT 9");

        return count($users) * 300 + 300;
    }

    public static function inviteStat(){

        $balance = self::balance();
        $users = self::get(array('type' => 'referrers'));

        $ps = User::find_by_sql("SELECT product_save.id FROM product_save LEFT JOIN user ON product_save.user_id = user.id WHERE user.referrer_id = ".UID." GROUP BY product_save.id");
        $pu = User::find_by_sql("SELECT purchase.id FROM purchase LEFT JOIN user ON purchase.user_id = user.id WHERE user.referrer_id = ".UID." GROUP BY purchase.id");

        $current_time = time();
        $current_month = getdate($current_time);
        $current_month = $current_month['mon'];
        $first_minute = mktime(0, 0, 0, date("n"), 1);
        $last_minute = mktime(23, 59, 0, date("n"), date("t"));
        $i = 1;
        $labels = array();
        $data =  array();

        $times  = array();
        for($month = $current_month; $i <= 12; $month--) {
            $first_minute = mktime(0, 0, 0, $month, 1);
            $last_minute = mktime(23, 59, 0, $month, date('t', $first_minute));
            $month_name = getdate($first_minute);
            $month_name = $month_name['month'];
            $times[$month_name] = array($first_minute, $last_minute);

            $ic = User::find_by_sql("SELECT id FROM user  WHERE referrer_id = ".UID." AND add_time BETWEEN '$first_minute' AND '$last_minute'");

            array_push($labels, '"'.$month_name.'"');
            array_push($data, count($ic));

            $i++;
            
        }

        //var_dump($labels);
     

        $result = array();

        if($users['count']){ $result['count'] = $users['count']; }else{ $result['count'] = 0; }
        $result['saves'] = count($ps);
        $result['purchases'] = count($pu);
        $result['balance'] = $balance;
        $result['users'] = $users['users'];

        $result['labels'] = array_reverse($labels);
        $result['data'] = array_reverse($data);

        return $result;

    } 

    public static function islogin(){
        return count(User::find_by_sql("SELECT COUNT(*) FROM login WHERE hash = '".USERH."'"));
    }



    public static function get_uid_by_hash($hash){
        $users = User::find_by_sql("SELECT user_id FROM login WHERE hash = '$hash'");

        if($users){
            foreach($users as $user) {
                $user = $user->to_array();
            }

            return $user['user_id'];
        }else{
            return false;
        }   

    }

    public static function login($params){
        $response = array();

        if(isset($params['provider'])){
            $provider_data = self::getProviderData($params);
            $signup_status = self::getSignupStatus($params, @$provider_data);

            $response = $signup_status;

            if($signup_status['status'] == 'registered'){
                self::updateProvider($params);
                $response = self::signin($signup_status);
            }
        }else{
            $signin_status = self::getSigninStatus($params);

            $response = $signin_status;

            if($signin_status['status'] == 'ok'){
                $response = self::signin($signin_status);
            }
        }

        return $response;
        
    }


    private static function getProviderData($params){
        $provider_data = array();

        if($params['provider'] == 'vk'){
            

            $user_fields = 'nickname,screen_name,sex,bdate,city,country,timezone,photo_50,photo_100,photo_200_orig,has_mobile,contacts,education,online,counters,relation,last_seen,status'; 
            $answ =  file_get_contents('https://api.vk.com/method/users.get?user_id='.$params['provider_user_id'].'&fields='.$user_fields.'&v=5.2&access_token='.$params['access_token']);
            $data = json_decode($answ, true);
            $data = $data['response'][0];

            $provider_data['first_name'] = $data['first_name'];
            $provider_data['last_name'] = $data['last_name'];
            if(isset($data['screen_name'])){ 
                $provider_data['username'] = $data['screen_name']; 
            }else{ 
                $provider_data['username'] = $params['provider_user_id']; 
            }
            
            if(isset($data['sex'])){ $provider_data['sex'] = $data['sex']; }else{ $provider_data['sex'] = ''; }
            if(isset($data['bdate'])){ $provider_data['bdate'] = $data['bdate']; }else{ $provider_data['bdate'] = '';}

            $provider_data['photo'] = $data['photo_200_orig'];
           
        }

        return $provider_data;
    }

    private static function getSignupStatus($params, $provider_data){


            $data = LoginProvider::find(array('conditions'=>"provider_user_id = '".$params['provider_user_id']."' AND name = '".$params['provider']."'"));

            $response = array();

            if($data){
                $response['status'] = 'registered';
                $response['user_id'] = $data->user_id;
                
            }else{
                $response['status'] = 'new';
                $response = array_merge($response, $params);
                $response = array_merge($response, $provider_data);
            }

            return $response; 
            
    }

    private static function getSigninStatus($params){
            $email = $params['email'];
            $password = md5(md5($params['password']));

            $data = User::find(array('conditions'=>"email = '$email' AND password = '$password'"));

            $response = array();

            if($data){
                $response['status'] = 'ok';
                $response['user_id'] = $data->id;
                
            }else{
                $response['status'] = 'error';
                $response['msg'] = 'Неверный email или пароль';
            }

            return $response; 
            
    }

    private static function updateProvider($params){
        $login_provider = LoginProvider::find(array('conditions'=>"provider_user_id= '".$params['provider_user_id']."' AND name = '".$params['provider']."'"));
        $login_provider->access_token = $params['access_token'];
        $login_provider->save();
    }
    

    private static function signin($data){
        $response = array();
        $add_time = time();
        $hash = md5($data['user_id'].$add_time);

        $login = new Login();
        $login->user_id = $data['user_id'];
        $login->hash = $hash;
        $login->time = $add_time;
        $login->save();

        $response['status'] = 'logged';
        $response['userH'] = $hash;

        return $response; 
    }

    public static function signup($params){
        $response = array();

        $add_time = time();
        if(isset($params['name'])){
            $name = explode(" ", $params['name']);
            $first_name = $name[0];
            $last_name = @$name[1];
        }else{
            $first_name = $params['first_name'];
            $last_name = $params['last_name'];
        }
       
        $sex = $params['sex'];
        $username = strtolower($params['username']);
        $username = str_replace(" ","",$username);

        $email = strtolower($params['email']);
        $bdate = @$params['bdate'];

        if(isset($params['photo'])){
            $photo = $params['photo'];
        }else{
            $photo = '/static/site/i/user-nophoto-big.png';
        }
    

        $password = md5(md5($params['password']));

        $referrer_id = @$params['referrer_id'];

        
        $response =  User::validateUser($username, $params['email']);
        
        if ($response){
            return $response;
        }

        $user = new User();
        $user->email = $email;
        $user->password = $password;
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->username = $username;
        $user->sex = $sex;
        $user->bdate = $bdate;
        $user->photo = $photo;
        $user->referrer_id = $referrer_id;
        
        $user->add_time = $add_time;
        $user->email_activation_hash = md5($email);
        $user->save();

        $user_id = $user->id;

        if (isset($referrer_id)){
            $notification = new Notification();
            $notification->user_id = $referrer_id;
            $notification->actor_id = $user_id;
            $notification->action = 'refferer_signup';
            $notification->object_id =  false;
            $notification->created_time = time();
            $notification->updated_time = time();
            $notification->add();
        }

        if(isset($params['provider'])){
            self::addProvider($params, $user_id);
            self::addFriends($params, $user_id);
        }

        //Email::sendActivation($email, $first_name, $user->email_activation_hash);
        $email = new Email();
        $email->user_id = $user_id;
        $email->type = 'activation';
        $email->add_time = time();
        $email->save();

        
        $data = array();
        $data['user_id'] = $user_id;
        $response = self::signin($data);

        return $response;
        
    }

    public static function validateUser($username, $email){

        $matching_items = '';
        $matching_items += count(Kind::find_by_url($username));
        $matching_items += count(Type::find_by_url($username));
        $matching_items += count(Category::find_by_url($username));
        $matching_items += count(Section::find_by_url($username));
        $matching_items += count(Tag::find_by_url($username));

        $username_check = User::find_by_username($username);
        $email_check = User::find_by_email($email);

        if ((bool)$matching_items){   
            $response['status'] = 'error';
            $response['msg'] = 'Данное имя пользователя недоступно';
        }else if(preg_match("/\b(api|signup|signin|trending|shop|tags|trendsetters|stores|welcome|wizard|store|p|search|purchase|login|logout|widget|invite|find|sets|set|map)\b/i", $username)){   
            $response['status'] = 'error';
            $response['msg'] = 'Данное имя пользователя недоступно';
        }else if(!preg_match("/[^\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]+$/", $username)){   
            $response['status'] = 'error';
            $response['msg'] = 'Данное имя пользователя недоступно';
        }else if(isset($username_ckeck)){
            $response['status'] = 'error';
            $response['msg'] = 'Имя пользователя занято';
        }else if(isset($email_check)){
            $response['status'] = 'error';
            $response['msg'] = 'Пользователь с таким email уже существует';
        }

        return @$response;

    }

    public static function addProvider($data, $user_id) {
        $add_time = time();

        $login_provider = new LoginProvider();
        $login_provider->name = $data['provider'];
        $login_provider->provider_user_id = $data['provider_user_id'];
        $login_provider->access_token = $data['access_token'];
        $login_provider->user_id = $user_id;
        $login_provider->add_time = $add_time;
        $login_provider->save();
  
    }

    public static function addFriends($data, $user_id){
        $provider = $data['provider'];
        $provider_user_id = $data['provider_user_id'];
        $access_token = $data['access_token'];

        if($provider == 'vk'){

            $friendsForNotif = Friend::find_by_sql("SELECT * FROM friend WHERE provider_user_id = '$provider_user_id'");
            foreach ($friendsForNotif as $friend) {
                $friend = $friend->to_array();
                $notification = new Notification();
                $notification->user_id = $friend['user_id'];
                $notification->actor_id = $user_id;
                $notification->action = 'friend_signup';
                $notification->object_id = $friend['user_id'];
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();
            }

            $user_fields = 'nickname,screen_name,sex,bdate,city,country,timezone,photo_50,photo_100,photo_200_orig,has_mobile,contacts,education,online,relation,last_seen,status,can_write_private_message,can_see_all_posts,can_post,universities'; 
            $answ =  file_get_contents('https://api.vk.com/method/friends.get?user_id='.$provider_user_id.'&order=name&fields='.$user_fields.'&access_token='.$access_token);
            $data = json_decode($answ, true);
            $friends = $data['response'];

            foreach ($friends as $friend) {

                $provider_user_id =  $friend['uid'];
                $photo = $friend['photo_100'];
                $first_name = $friend['first_name'];
                $last_name = $friend['last_name'];
                $sex= $friend['sex'];
                $can_post = $friend['can_post'];
                
                

                $n = Friend::find_by_sql("SELECT * FROM friend WHERE (provider = '$provider' AND user_id = '$user_id' AND provider_user_id = '$provider_user_id')");
                if(count($n)==0){
                    $friend=new Friend();
                    $friend->user_id = $user_id;
                    $friend->provider = $provider;
                    $friend->provider_user_id = $provider_user_id;
                    $friend->photo = $photo;
                    $friend->first_name = $first_name;
                    $friend->last_name = $last_name;
                    $friend->sex = $sex;
                    $friend->can_post = $can_post;
                    $friend->save();
                }    
            }
        }    

        
    
    }


}

class Login extends ActiveRecord\Model
{
    static $table_name = 'login';
    static $primary_key = 'id'; 
}


class LoginProvider extends ActiveRecord\Model
{
    static $table_name = 'login_provider';
    static $primary_key = 'id'; 
}

?>