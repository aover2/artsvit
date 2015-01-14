<?

class Notification extends ActiveRecord\Model
{

    static $table_name = 'notification';
    static $primary_key = 'id'; 

    public function add(){

        $this->save();
        if (($this->action == "user_follow")||($this->action == "friend_signup")||($this->action == "refferer_signup")||($this->action == "comment_mention"))
        {
            $email = new Email();
            $email->user_id = $this->user_id;
            $email->type = 'notification';
            $email->add_time = time();
            $email->object_id = $this->id;
            $email->add();
        }
       	
    }

    public static function view(){
        $notifications = Notification::find_all_by_user_id(UID);

        foreach ($notifications as $notification){
            $notification->viewed = 1;
            $notification->updated_time = time();
            $notification->save();
        }
    }

    public static function get(){

        $notifications = Notification::all(array(
            'conditions'=>array('user_id = ? AND viewed = 0', UID),
            'order' => 'id DESC'
        ));

        $result = array();
        foreach ($notifications as $notification){
        
            $notification = $notification->to_array();
            $user = User::find($notification['actor_id']);
            $user = $user ->to_array();
            $notification['actor_photo'] = $user['photo'];
            $notification['actor_name'] = $user['username'];
            $notification['actor_url']= "/".$user['username'];

            $notification['add_time_iso'] = date('c', $notification['created_time']);

            switch ($notification['action']) {
                
                /* для этих все есть в юзере*/
                // case 'refferer_signup':
                // case 'user_follow':
                // case 'friend_signup':
                //     $notification['object_photo'] = false;
                //     $notification['object_name'] = false;
                //     $notification['object_url'] = false;
                //     break;
                
                case 'comment_mention':
                case 'comment_add':
                case 'user_comment_add':
                case 'product_save': 
                    $product = Product::find($notification['object_id']);
                    $product = $product->to_array();
                    $notification['object_photo'] = "/u/thumbs/".$product['store_id']. "/min/".$product['hash'].".jpg";
                    $notification['object_name'] = $product['name'];
                    $notification['object_url'] = "/p/".$product['id']."/".$product['name_url'];
                    break;

                case 'user_follow_store':
                    $store = Store::info($notification['object_id']);
                    $store['logo'] ? $notification['object_photo'] = "/u/logotypes/stores/".$store['id'].".png" : $notification['object_photo'] = "/static/site/i/store-nologo-min.png";
                    $notification['object_name'] = $store['name'];
                    $notification['object_url'] = "/store/".$store['domain'];
                    break;

                case 'user_follow_tag':                  
                    $tag = Tag::info($notification['object_id']);
                    $notification['object_name'] = $tag['value'];
                    $store['logo'] ? $notification['object_photo'] = "/u/logotypes/tags/".$tag['id'].".png" : $notification['object_photo'] = "/static/site/i/tag-nologo-min.png";
                    $notification['object_url'] = "/".$tag['url'];
                    break;

                
                case 'user_follow_user':                  
                    $user = User::info($notification['object_id']);
                    $notification['object_name'] = $user['username'];
                    $notification['object_photo'] = $user['photo'];
                    $notification['object_url'] = "/".$user['username'];
                    break;    
            }

            if(isset($notification['object_name'])){
                $notification['object_name'] = (strlen($notification['object_name']) > 23) ? substr($notification['object_name'],0,20).'...' : $notification['object_name'];    
            }
            

            array_push($result, $notification);
        }
        return $result;
    }

   

}


?>