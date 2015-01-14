<?

class Tag extends ActiveRecord\Model
{
    static $table_name = 'tag';
    static $primary_key = 'id'; 

    public static function info($tag_id, $type = null, $tag = null){
        if(!$tag){
           $tag = Tag::find($tag_id);
           $tag=$tag->to_array(); 
        }
        

        if ($tag){

            if($type == 'full'){
                $tag['type'] ='tag_title';
                $tag['products_count'] = Product::getCount($tag['id'], 'tag');
                $tag['title_products'] = Product::get($tag);

                $tag['follow_status'] = FollowTag::status($tag['id']);
                $tag['followers_count'] =  FollowTag::followers_count( $tag['id']);

            }

            $tag['logo'] = file_exists($_SERVER['DOCUMENT_ROOT'].'/u/logotypes/tags/'.$tag['id'].'.png');

            return $tag;              
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

        
        switch($params['type']){
            case 'shop':
      
            if(isset($params['subkind_id']) ){
                if(isset($params['subkind_id'])){ $subkinds = $params['subkind_id']; }

                //or isset($params['subkinds'])
                //if(isset($params['subkinds'])){ $subkinds = $params['subkinds']; }

                $tags = Tag::find_by_sql("SELECT tag.id, tag.value, tag.url FROM tag
                        JOIN product_tag ON tag.id = product_tag.tag_id
                        JOIN product ON product_tag.product_id = product.id
                        WHERE product.subkind_id IN ($subkinds)
                        GROUP BY tag.id");     


            }else{    
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

                $tags = Tag::find_by_sql("SELECT tag.value, tag.id, tag.url FROM tag 
                  LEFT JOIN ".DB_CACHE_NAME.".filter_type_tag ON tag.id = filter_type_tag.tag_id
                  WHERE type_id IN ($types)

                  GROUP BY tag.id
                  ORDER BY tag.id ASC
                  ");
            }
                 
                      
            break;

            case 'product':
                $product_id = $params['product_id'];
                $tags = Tag::find_by_sql("SELECT tag.id, tag.value, tag.url FROM tag
                        JOIN product_tag ON tag.id = product_tag.tag_id
                        JOIN product ON product_tag.product_id = product.id
                        WHERE product.id = $product_id");
            break;

            case 'popular':
                //$count= Tag::count();
                $tags = Tag::find_by_sql("SELECT tag.id, tag.value, tag.url, count(*) as count
                        FROM tag LEFT JOIN follow_tag ON tag.id = follow_tag.tag_id
                        GROUP BY tag.id 
                        ORDER BY count DESC
                        LIMIT $limit, 50");
            break;

            case 'user':
                $user_id = $params['user_id'];
                $tags = Tag::find_by_sql("SELECT follow_tag.tag_id as id FROM follow_tag WHERE follower_user_id = '$user_id' ORDER BY start_time DESC LIMIT $limit , 50");
            break;
            case 'search':
                $assoc = trim($params['q']); 
                $assoc = addslashes($params['q']);
                $assoc = htmlspecialchars($params['q']);
                $assoc = explode(" ", $assoc);
                $query = "SELECT * FROM tag WHERE";
                foreach ($assoc as $searchWord) {
                    if($searchWord == end($assoc)) {
                        $query.=" value LIKE '%$searchWord%' ";
                    }else {
                        $query.=" value LIKE '%$searchWord%' OR ";
                    }
                }
                $query.="ORDER BY id DESC LIMIT $limit, 50";
                var_dump($query);

                $tags = Tag::find_by_sql($query);
            break;
        }

        
        if(!isset($tags)){ return false; }

        $result= array();
        $result_tags = array();

        if($params['type']=='product' or $params['type']=='shop'){
            foreach($tags as $tag) {
                $tag = $tag->to_array();
                array_push($result_tags, $tag);
            }
            $result['tags'] = $result_tags;
            return $result;
        } 

        foreach($tags as $tag) {
            $tag = $tag->to_array();
            if($params['type']== 'user'){
                $tag = Tag::info($tag['id'], 'full');
            }else{
                $tag = Tag::info($tag['id'], 'full', $tag);  
            }
            
            array_push($result_tags, $tag);
        }

        $result['count'] = @$count;
        $result['tags'] = $result_tags;

        return $result;

        
    } 

    public static function add($value, $product_id){
        
           
        if (!empty($value)) {
            $a = explode(" ", $value);
            if(sizeof($a) > 3){ return; } 

            $url = Utils::makeUrl($value);
            $count = (int) Tag::count(array('conditions'=>"url='$url'")); 
            $data = Tag::find(array('conditions'=>"url='$url'")); 
            
            if ($count == 0) {
                $tag = new Tag();
                $tag->value = $value;
                $tag->url = $url;
                $tag->save();
                $tag_id = $tag->id;
            }else{
               $tag_id = $data->id;
            }
            Product_tag::add($tag_id,$product_id);
            
        }
    }       


}

class Product_tag extends ActiveRecord\Model
{
    
    static $table_name = 'product_tag';

    public static function add($tag_id,$product_id){
            $data=Product_tag::find('all',array('conditions' => array('tag_id=? AND product_id=?', $tag_id, $product_id))); 

            if(count($data)==0){
                    $product_tag = new Product_tag();
                    $product_tag->product_id= $product_id;
                    $product_tag->tag_id = $tag_id;
                    $product_tag->save();
            }  
    }
}
?>