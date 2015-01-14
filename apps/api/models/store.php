<?

class Store extends ActiveRecord\Model
{
    static $table_name = 'store';
    static $primary_key = 'id'; 

    public static function info($store_id, $type = null){
        $store=Store::find($store_id);

        if($store){
            $store = $store->to_array();


            if($type == 'view' or $type == 'list' or $type == 'view_product'){
                if($type == 'view_product'){$store_title_pc = 9; }else{ $store_title_pc = 4;}
                $store['title_products'] = Product::get(array('type' => 'store_title', 'store_id' => $store['id'], 'store_title_pc' => $store_title_pc));
                $store['follow_status'] = FollowStore::status($store['id']);
                $store['followers_count'] =  FollowStore::followers_count( $store['id']);
                $store['products_count'] = Product::getCount($store['id'], 'store');
            }

            if($type == 'view_product'){
                $store_info = Store_info::find_by_store_id($store['id']);
                if($store_info){
                    $store['info']= $store_info->to_array();
                }
            }

            $store['logo'] = file_exists($_SERVER['DOCUMENT_ROOT'].'/u/logotypes/stores/'.$store['id'].'.png');

            return $store;
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
                //$count = Store::count();
                $stores = Store::find_by_sql("SELECT store.id, store.name, store.domain, count(*) as count 
                FROM store LEFT JOIN follow_store ON store.id = follow_store.store_id
                GROUP BY store.id
                ORDER BY count DESC
                LIMIT $limit , 50");
            break;
            
            case 'user':
                $user_id = $params['user_id'];
                $stores =Store::find_by_sql("SELECT follow_store.store_id as id FROM follow_store WHERE follower_user_id = '$user_id' ORDER BY start_time DESC LIMIT $limit , 50");
            break;
            case 'shop':
                /*if(isset($params['type_id'])){
                    $where = 'product.type_id = '.$params['type_id'];
                }

                if(isset($params['section_id'])){
                    $where = 'product.section_id = '.$params['section_id'];
                }

                if(isset($params['category_id'])){
                    $where = 'product.category_id = '.$params['category_id'];
                }

                if(isset($params['kind_id'])){
                    $where = 'product.kind_id = '.$params['kind_id'];
                }*/

                //$count = Store::count();

                if(isset($params['kind_id'])){
                    $kind_types = Type::find_by_sql("SELECT id FROM type WHERE kind_id = ".$params['kind_id']);

                    $types = array();
                    foreach($kind_types as $kind_type) {
                      $kind_type = $kind_type->to_array();
                      array_push($types, $kind_type['id']);
                    }
                    $types = join(',', $types);
                }else{
                    $types = $params['type_id'];
                }

                if(isset($params['tag_id'])){

                    $stores = Store::find_by_sql("SELECT store.id, store.name, store.domain
                    FROM store 
                    LEFT JOIN product ON store.id = product.store_id
                    LEFT JOIN product_tag ON product.id = product_tag.product_id
                    WHERE product.type_id IN ($types) AND product_tag.tag_id = ".$params['tag_id']."
                    GROUP BY store.id");

                }elseif(isset($params['subkind_id'])){

                    $stores = Store::find_by_sql("SELECT store.id, store.name, store.domain
                    FROM store 
                    LEFT JOIN product ON store.id = product.store_id
                    WHERE product.subkind_id = ".$params['subkind_id']."
                    GROUP BY store.id"); 

                }else{

                    $stores = Store::find_by_sql("SELECT store_id AS id 
                        FROM ".DB_CACHE_NAME.".filter_type_store WHERE type_id IN ($types) GROUP BY store_id ");
                         
                    
                }
                
            break;

            case 'search':
                $assoc = trim($params['q']); 
                $assoc = addslashes($params['q']);
                $assoc = htmlspecialchars($params['q']);
                $assoc = explode(" ", $assoc);
                $query = "SELECT * FROM store WHERE";
                foreach ($assoc as $searchWord) {
                    if($searchWord == end($assoc)) {
                        $query.=" name LIKE '%$searchWord%' ";
                    }else {
                        $query.=" name LIKE '%$searchWord%' OR ";
                    }
                }
                $query.="ORDER BY id DESC LIMIT $limit, 50";
                $stores = Store::find_by_sql($query);
            break;
            /*
                    public static function wizard($limit){
                        
                        $limit = $limit / 2;
                        $stores =Store::find_by_sql("SELECT store.id, store.name, store.domain, count(*) as count 
                            FROM store, follow_store 
                            WHERE store.id = follow_store.id
                            GROUP BY store.id 
                            ORDER BY count DESC
                            LIMIT $limit , 25");
                        $result = array();
                            foreach($stores as $store) {
                                $store = $store->to_array();
                                $store['wizard']= '1';
                                $store = Store::info_extended($store);
                                array_push($result, $store);
                            }
                        return $result;
                            
                    }

            */    

        }

        $result= array();
        $result_stores = array();

        foreach($stores as $store) {
                $store = $store->to_array();
                if($params['type'] == 'shop'){
                    $store = Store::info($store['id']); 
                }else{
                    $store = Store::info($store['id'], 'list');  
                }

                
                array_push($result_stores, $store);
        }

        $result['count'] = @$count;
        $result['stores'] = $result_stores;
        return $result;
    }



   



    public static function get_id_bydomain($domain){

        $store=Store::find(array('conditions' => array('domain=?', $domain)));
        if($store){
            $store=$store->to_array();
            $store_id=$store['id'];
        }else{
            $add_time = time();
            $store=new Store();
            $store->name=$domain;
            $store->domain=$domain;
            $store->add_time=$add_time;
            $store->save();
            $store_id=$store->id;
        }
        return $store_id;    
    }

}

class Store_affiliate extends ActiveRecord\Model
{
    static $table_name = 'store_affiliate';
    static $primary_key = 'id';

    public static function get($id){
        $store_affiliate=Store_affiliate::find(array('conditions'=>"store_id='$id'"));
        return $store_affiliate;
    }

    public static function set($id,$deeplink){
        $store_affiliate=Store_affiliate::find(array('conditions'=>"store_id='$id'"));
        $store_affiliate->deeplink=$deeplink;
        $store_affiliate->save();
    }

    public static function remove($id){
        $store=Store_affiliate::find(array('conditions'=>"store_id='$id'"));
        $store->delete();
    }

    public static function get_redirect_url($product){
        $store_id = $product['store_id'];
    
        try{
             $store_affiliate = Store_affiliate::find(array('conditions'=>"store_id='$store_id'"));
         }catch (\Exception $e){
              //not found
         }
        if(!empty($store_affiliate->deeplink)){
            $redirect_url = $store_affiliate->deeplink.$product['url']; 
        }else{
            $redirect_url = $product['url'];
        }
        return $redirect_url;
    }

}



class Store_info extends ActiveRecord\Model
{
    static $table_name = 'store_info';
    static $primary_key = 'id';
}


?>