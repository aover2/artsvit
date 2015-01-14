<?

class Save extends ActiveRecord\Model
{
    static $table_name = 'product_save';
    static $primary_key = 'id'; 

    public static function info($save_id){

        $save = Save::find($save_id);
        if ($save){
            $save = $save->to_array();
            return $save;              
        }else{
            return false;
        }    
    }

    public static function set($product_id, $action){

        $current_user_id = UID;

        if($action=='add'){
            $add_time = time();
            $saves = Save::all(array('conditions'=>array('product_id=? AND user_id=?',$product_id,$current_user_id))); 
            if(count($saves)==0){
                $save=new Save();
                $save->product_id=$product_id;
                $save->user_id=$current_user_id;
                $save->add_time=$add_time;
                $save->save();

                $users = Save::all(array('conditions'=>array('product_id='.$product_id)));
                foreach ($users as $user) {
                    $user = $user->to_array();
                    if ($user['user_id']==UID) continue;   
                    $notification = new Notification();
                    $notification->user_id = $user['user_id'];
                    $notification->actor_id = UID;
                    $notification->action = "product_save";
                    $notification->object_id = $product_id;
                    $notification->created_time = time();
                    $notification->updated_time = time();
                    $notification->add();   
                }

                return $product_id;
            }else{
                return $product_id;  
            }  
        }elseif($action=='delete'){
            $save=Save::find(array('conditions'=>array('product_id=? AND user_id=?',$product_id, $current_user_id)));
            $save->delete();
            return $product_id.' unscrubbed';
        }
    }


    public static function getCount($item_id, $type = null){

        if($type=='product'){
            $saves = Save::all(array('conditions'=>array('product_id=?',$item_id))); 
        }else{
            $saves = Save::all(array('conditions'=>array('user_id=?',$item_id))); 
        }
       return count($saves);
    }


     public static function status($product_id){
        
        $current_user_id = UID;
        $saves=Save::all(array('conditions'=>array('product_id=? AND user_id=?',$product_id,$current_user_id)));
        return count($saves);    
    } 

    
}
?>