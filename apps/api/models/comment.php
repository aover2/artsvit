<?

class Comment extends ActiveRecord\Model

	{
        static $table_name = 'comment';
        // static $primary_key = 'book_id';

		public static function add($product_id, $value){

            if(empty($value)){
                return false;
            }
            
			$add_time = time();

            $comment = new Comment();
            $comment->value = $value;
            $comment->user_id = UID;
            $comment->product_id = $product_id;
            $comment->add_time = $add_time;
            
            $comment->save();

            $users = Save::all(array('conditions'=>array('product_id='.$product_id)));
            foreach ($users as $user) {
                $user = $user->to_array();
                if ($user['user_id']==UID) continue;   
                $notification = new Notification();
                $notification->user_id = $user['user_id'];
                $notification->actor_id = UID;
                $notification->action = "comment_add";
                $notification->object_id = $product_id;
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();   
            }
            
            $followers = FollowUser::find('all', array('conditions' => 'user_id='.UID));
            foreach ($followers as $follower) {
                $follower = $follower->to_array();
                $notification = new Notification();
                $notification->user_id = $follower['follower_user_id'];
                $notification->actor_id = UID;
                $notification->action = 'user_comment_add';
                $notification->object_id =  $product_id;
                $notification->created_time = time();
                $notification->updated_time = time();
                $notification->add();
            }
            
            $result = $comment->to_array();
            $result['user'] = User::info(UID);
            $result['add_time_iso'] = date('c', $result['add_time']);
			return $result;
		}
    
		public static function get($product_id){

            $count = Comment::count('all', array('conditions' => 'product_id='.$product_id)); 
            $comments = Comment::find('all', array('conditions' => 'product_id='.$product_id, 'order' => 'add_time desc')); 
            $result = array();
            $result_comments = array();

            foreach($comments as $comment) {
                $comment = $comment->to_array();
                $comment['user'] = User::info($comment['user_id']);
                $comment['add_time_iso'] = date('c', $comment['add_time']);
                array_push($result_comments, $comment);
            }

            $result['comments'] = $result_comments;
            $result['count'] = $count;

            return $result;

		}

	}

?>