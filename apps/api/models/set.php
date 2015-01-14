<?

class Set extends ActiveRecord\Model
{
    static $table_name = 'set';

    public static function info($set_id){
       
        $set = Set::find($set_id);


        if($set){
            $set = $set->to_array();
            $set_id = $set['id'];

            $set_products_result = array();
            $set_products = SetProduct::all(array('conditions' => 'set_id = '.$set_id));

            foreach($set_products as $product) {
	            $product = $product->to_array();
	            $product = Product::info($product['product_id']);
	            array_push($set_products_result, $product);
	        }

	        $set['products'] = $set_products_result;
            
            return $set;
        }else{
            return false;
        }    
    }

	public static function add(){
		/*$add_time = time();

        $comment = new Comment();
        $comment->value = $value;
        $comment->user_id = UID;
        $comment->product_id = $product_id;
        $comment->add_time = $add_time;
        
        $comment->save();

        $result = $comment->to_array();
        $result['user'] = User::info(UID);
        $result['add_time_iso'] = date('c', $result['add_time']);
		return $result;*/
	}

	public static function get($params){

		if(!isset($params['page_number'])){
            $limit = 0;
        }else{
            $limit = ($params['page_number']-1) * 50;
        }

        switch ($params['type']) {
            case 'all':
                $count = Set::count();
                $sets = Set::find_by_sql("SELECT * FROM `set`
                ORDER BY add_time DESC
                LIMIT $limit, 50");
        
            break;

            case 'shop':
                $sets = Set::find_by_sql("SELECT * FROM `set`
                ORDER BY add_time DESC
                LIMIT 5");
        
            break;
        }    


        $result= array();
        $result_sets = array();

        foreach($sets as $set) {
            $set = $set->to_array();

            $set = self::info($set['id']);

            array_push($result_sets, $set);
        }

        $result['count'] = @$count;
        $result['sets'] = $result_sets;
        return $result;

	}

}

class SetProduct extends ActiveRecord\Model
{
    static $table_name = 'set_product';


}

?>