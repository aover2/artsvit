<?

class FollowStore extends ActiveRecord\Model
{
    static $table_name = 'follow_store';
    static $primary_key = 'id';


    public static function set($item_id, $action){
        $current_user_id = UID;
        if($action == 'follow'){
            $follow = new FollowStore();
            $follow->store_id = $item_id;
            $follow->follower_user_id = UID;
            $follow->start_time = time();
            $follow->save();

            $followers = FollowUser::find('all', array('conditions' => 'user_id='.UID));
            foreach ($followers as $follower) {
                $follower = $follower->to_array();
                $notification = new Notification();
                $notification->user_id = $follower['follower_user_id'];
                $notification->actor_id = UID;
                $notification->action = 'user_follow_store';
                $notification->object_id =  $item_id;
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();
            }
        }else{
            $current_user_id=UID;
            $follow = FollowStore::find(array('conditions'=>"store_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
            $follow->delete();
        }
    }

    public static function followers_count($item_id){
        return  FollowStore::count(array('conditions'=>"store_id = '$item_id'"));
    }


    public static function stores_count($user_id){
        return FollowStore::count(array('conditions'=>"follower_user_id = '$user_id'"));
    }


     public static function status($item_id){
        $current_user_id = UID;
        return FollowStore::count(array('conditions'=>"store_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
    } 

}

class FollowUser extends ActiveRecord\Model
{
    static $table_name = 'follow_user';
    static $primary_key = 'id';

    public static function set($item_id, $action){
        $current_user_id=UID;

        if($action=='follow'){

            $follow=new FollowUser();
            $follow->user_id = $item_id;
            $follow->follower_user_id=UID;
            $follow->start_time=time();
            $follow->save();

            $notification = new Notification();
            $notification->user_id = $item_id;
            $notification->actor_id = UID;
            $notification->action = 'user_follow';
            $notification->object_id = $item_id;
            $notification->created_time = time();
            $notification->updated_time = time();
            $notification->add();


            $followers = FollowUser::find('all', array('conditions' => 'user_id='.UID));
            foreach ($followers as $follower) {
                $follower = $follower->to_array();
                $notification = new Notification();
                $notification->user_id = $follower['follower_user_id'];
                $notification->actor_id = UID;
                $notification->action = 'user_follow_user';
                $notification->object_id =  $item_id;
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();
            }
            
        }else{
            $follow=FollowUser::find(array('conditions'=>"user_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
            $follow->delete();
        }
    }

    public static function following_count($user_id){
        return FollowUser::count(array('conditions'=>"follower_user_id = '$user_id'"));
    }
    public static function status($item_id){
        $current_user_id=UID;
        return FollowUser::count(array('conditions'=>"user_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
    }

    public static function followers_count($item_id){
        return FollowUser::count(array('conditions'=>"user_id = '$item_id'"));
    }
  
}

class FollowTag extends ActiveRecord\Model
{
    static $table_name = 'follow_tag';
    static $primary_key = 'id';


    public static function set($item_id, $action){
        $current_user_id = UID;
        if($action == 'follow'){
            $follow = new FollowTag();
            $follow->tag_id = $item_id;
            $follow->follower_user_id = UID;
            $follow->start_time=time();
            $follow->save();
            $followers = FollowUser::find('all', array('conditions' => 'user_id='.UID));
            foreach ($followers as $follower) {
                $follower = $follower->to_array();
                $notification = new Notification();
                $notification->user_id = $follower['follower_user_id'];
                $notification->actor_id = UID;
                $notification->action = 'user_follow_tag';
                $notification->object_id =  $item_id;
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();
            }
        }else{
            $current_user_id = UID;
            $follow = FollowTag::find(array('conditions'=>"tag_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
            $follow->delete();
        }
    }

    public static function followers_count($item_id){
        return  FollowTag::count(array('conditions'=>"tag_id = '$item_id'"));
    }


    public static function tags_count($user_id){
        return FollowTag::count(array('conditions'=>"follower_user_id = '$user_id'"));
    }


     public static function status($item_id){
        $current_user_id = UID;
        return FollowTag::count(array('conditions'=>"tag_id = '$item_id' AND follower_user_id = '$current_user_id'")); 
    } 

}


?>