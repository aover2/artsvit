<?


class Product extends ActiveRecord\Model
{

    static $table_name = 'product';
    static $primary_key = 'id'; 

    public static function info($product_id, $type = null, $product = null){
        if(!$product){
            $product = Product::find($product_id);

            if($type == 'view'){
                $product->views++;
                $product->save();  
            }

            $product = $product->to_array();  
        }
        

        if($product){
                     

            //$product['price'] = Product::currency_exchange($product['price'],$product['currency_id']);
            //$product['oldprice'] = Product::currency_exchange($product['oldprice'],$product['currency_id']);
            //$product['currency_id'] = CURRENCY_ID;

            $product['product_store'] = Store::info($product['store_id']);
            $product['product_currency'] = Product::currency_symbol(CURRENCY_ID);

            $product['purchase_url'] = Store_affiliate::get_redirect_url($product);

            $product['save_status'] = Save::status($product['id']);

            $product['saves_count'] = Save::getCount($product['id'], 'product') + 1;
            $product['comments'] = Comment::get($product['id']);

            $product['tags'] = Tag::get(array('type' => 'product', 'product_id' => $product['id']));


            if(isset($product['add_time'])){
                $product['add_time_iso'] = date('c', $product['add_time']);
            }

            // if (isset($product['add_user_id'])) {
            //     if(!empty($product['add_user_id']) and !isset($product['publisher']) ){
            //         $product['publisher'] = 'user';
            //         $product['user'] = User::info($product['add_user_id']);  
            //     }
            // }

            $first_save = Save::find('first', array('conditions' => 'product_id = '.$product['id']) );
            if($first_save and empty($product['user_id']) ){
                $product['user_id'] = $first_save->user_id;
                $product['publisher'] = 'user';
            }


            if (isset($product['user_id'])) {
                if(!empty($product['user_id'])){
                    $product['user'] = User::info($product['user_id']);
                }
            }

            if (isset($product['tag_id'])) {
                if(!empty($product['tag_id'])){
                    $product['tag'] = Tag::info($product['tag_id']);
                }
            }

            if (!isset($product['publisher'])) {
                $product['publisher'] = 'store';
            }
            

            switch($type){
            
                case 'list':
                    
                break;

                case 'view':
                    
                    $product['similar'] = Product::get(array('type' => 'similar', 'product_id' => $product['id']));
                    $product['store'] = Store::info($product['store_id'], 'view_product');
                    $product['savers'] = User::get(array('type' => 'savers', 'product_id' => $product['id']));

                    $product['structure'] = Product::getStructure($product); 

                    $product['togetfree'] = ceil($product['price']/ 300);             
                break;    
            }
            
            return $product;
        }else{
            return false;
        }    
    }

    public static function getStructure($product){
        $structure = array();

        $type_id = $product['type_id'];
        $section_id = $product['section_id'];
        $category_id = $product['category_id'];

        if(!$type_id and !$section_id and !$category_id){
            return false;
        }

        if($section_id){ $structure['section'] = Section::info($section_id); }
        if($category_id){ $structure['category'] = Category::info($category_id); }
        if($type_id){ $structure['type'] = Type::info($type_id); }

        return $structure;
    } 

    public static function getCount($item_id, $type = null){

        switch($type){
            case 'store':
                $count = Product::count(array('conditions'=>array('store_id=?',$item_id))); 
            break;

            case 'tag':
                $join = 'LEFT JOIN product_tag ON (product.id = product_tag.product_id)';
                $conditions = "product_tag.tag_id = '$item_id'";

                $count = Product::count(array('joins' => $join, 'conditions' => $conditions));
            break;

        }
        return $count;
    } 

    public static function add($product){
           
           if(UID){
                $product['url'] = urldecode($product['url']);
                $product_domain = explode('/', $product['url']);
                $domain = $product_domain[2];
                $domain = str_replace("www.", "", $domain);
                $product['store_id'] = Store::get_id_bydomain($domain);
                $product['store_category'] = null;
                $product['modified_time'] = null;
                $product['available'] = 1;
                $product['oldprice'] = null;
                $product['discount'] = null;
                $product['vendor'] = null;
                $product['type'] = null;
                $product['price'] = str_replace(' ','',$product['price']);
                $product['price'] = str_replace(',','.',$product['price']);
                $product_views = 1;

                if($product['desc']=='null'){$product['desc']='';}

                if(!isset($product['currency_id'])){
                    $product['currency_id'] = '1';
                }

                $product['source'] = 'user';  

           }else{
                $product_views = 0;
                $product['source'] = 'store';       
           }
        
        $product['hash'] = $product_hash = md5($product['url']);
        $item = Product::find(array('conditions'=>"hash = '$product_hash'"));        
        $n = count($item);     

        if(isset($item->store_product_id) AND isset($item->store_id) AND isset($product['store_product_id'])){
            if($item->store_product_id == $product['store_product_id'] AND $item->store_id == $product['store_id']){
                $response = array();
                $response['status'] = 'error';
                $response['error'] = 'already exist';
                return $response;
            }
        }

        if ($product['currency_id'] != '1'){
            $product['price'] = Product::currency_exchange($product['price'], $product['currency_id']);
            $product['currency_id'] = '1';
        }

        if($n == 0){

            $add_time = time();
            $types = array();
            $product['name_url'] = Utils::makeUrl($product['name']);
            $product['img'] = $product['image'];
            $product['desc'] = str_replace("'", "", $product['desc']);
            $product['name'] = str_replace("'", "", $product['name']);

            if(!empty($product['section_id'])){
                $types = Product::categorize($product['section_id'], $product['name']);
                $product['category_id'] = $types['category_id'];
                $product['type_id'] = $types['id'];

                $subkind = Product::defineSubKind($types['id'], $product['name']);
                $product['subkind_id'] = $subkind['id'];

            }else{
                $product['category_id']=null;
                $product['type_id']=null;
                $product['section_id']=null;
            }



            $thumbs = Product::genThumbs($product);
            /*if(!$thumbs){
                $response = array();
                $response['status'] = 'error';
                $response['error'] = 'fail gen thumbs';
                return $response;
            }*/

            $image_file = $_SERVER['DOCUMENT_ROOT'].'/u/thumbs/'.$product['store_id'].'/max/'.$product['hash'].'.jpg';
            $color = Product::detectColor($image_file);
            $product['color_id'] = $color['id'];

            if(empty($product['name'])){
                $response = array();
                $response['status'] = 'error';
                $response['error'] = 'empty name';
                return $response;
            }

            if(!isset($product['store_product_id'])){
                $product['store_product_id'] = null;
            }


            $new_product = new Product();
            $new_product->url = $product['url'];
            $new_product->hash = $product_hash;
            $new_product->img = $product['image'];
            $new_product->name = $product['name'];
            $new_product->description = $product['desc'];
            $new_product->price = $product['price'];
            $new_product->oldprice = $product['oldprice'];
            $new_product->discount = $product['discount'];
            $new_product->name_url = $product['name_url'];
            $new_product->store_id = $product['store_id'];
            $new_product->section_id = $product['section_id'];
            $new_product->add_time = $add_time;
            $new_product->views = $product_views;
            $new_product->available = $product['available'];
            $new_product->modified_time  =  $product['modified_time'];
            $new_product->category_id  =  $product['category_id'];
            $new_product->type_id = $product['type_id'];
            $new_product->subkind_id = $product['subkind_id'];
            $new_product->source = $product['source'];
            $new_product->color_id = $product['color_id'];

            $new_product->store_product_id = $product['store_product_id'];

            //$new_product->currency_id=$product['currency_id'];
            //$new_product->store_category=$product['store_category'];
            
            $new_product->save();
            $product_id=$new_product->id;          
            $product['id'] = $product_id;

           if(UID){
                Save::set($product_id, 'add');
           }

           if(isset($product['comment']) and !empty($product['comment'])){
                Comment::add($product_id, $product['comment']);
           }
           

           if(@$product['keywords']){
                $keywords = explode(",", $product['keywords']);
                $i = 1;  
                foreach($keywords as $keyword) {
                    if($keyword != 'null'){
                        Tag::add(trim($keyword), $product_id);
                        if ($i++ == 3) break;
                    }
                  
                }   
           }
     
           Tag::add($product['vendor'], $product_id);

           return $product; 
                       
        }elseif(!UID){

            $product_id = $item->id;
            $item->price = $product['price'];
            $item->oldprice = $product['oldprice'];
            $item->discount = $product['discount'];
            $item->available = $product['available'];
            $item->modified_time = $product['modified_time'];
            

            //$types=array();

            // if ((empty($item->type_id) or empty($item->category_id) or empty($item->kind_id)) and !empty($item->section_id)){

            //     $types=Product::categorize($item->section_id,$item->name);
            //     $item->category_id=$types['category_id'];
            //     $item->type_id=$types['id'];

            //     $subkind = Product::defineSubKind($types['id'], $item->name);
            //     $item->subkind_id = $subkind['id'];
 
            // }

            // if(empty($item->color_id)){
            //     $image_file = $_SERVER['DOCUMENT_ROOT'].'/u/thumbs/'.$item->store_id.'/max/'.$item->hash.'.jpg';
            //     $color = Product::detectColor($image_file);
            //     $item->color_id = $color['id'];
            // } 

            // if(empty($item->store_product_id)){
            //     $item->store_product_id = $product['store_product_id'];
            // }    

            $item->save();

            //Tag::add($product['vendor'], $product_id);     

            return $product_id.' updated';

        }elseif(UID){
            $item_product = $item->to_array();
            
            if(isset($product['comment']) and !empty($product['comment'])){
                Comment::add($item_product['id'], $product['comment']);
            }

            Save::set($item_product['id'], 'add');

            return $item_product;
            
        }
          
    }

    

    public static function get($params){

        if(!isset($params['page_number'])){
            $limit = 0;
        }else{
            $limit = ($params['page_number']-1) * 50;
        }

        $filter = '';

        if(isset($params['section_id'])){
            $section_id = $params['section_id'];
            $filter.=" AND product.section_id='$section_id'"; 
        }

        if(isset($params['category_id'])){
            $category_id = $params['category_id'];
            $filter.=" AND product.category_id='$category_id'"; 
        }

        if(isset($params['type_id'])){
            $type_id = $params['type_id'];
            $filter.=" AND product.type_id='$type_id'"; 
        }

        if(isset($params['kind_id'])){
            $types = Type::getByKind($params['kind_id']);
            $filter.=" AND product.type_id IN ($types)"; 
            //var_dump($types);
        }
        
        /*if(isset($params['price'])){
            if($params['price'] !== 'any'){
                $price=explode('-',$params['price']);
                $max_price=array_pop($price);
                $min_price=array_pop($price);
                $filter.=" AND product.price BETWEEN '$min_price' AND '$max_price'"; 
            }
        }*/

       
        switch($params['type']){
            
            case 'user_title':
                $user_id = $params['id'];
                $products = Product::find_by_sql("SELECT * FROM product_save LEFT JOIN product 
                        ON product_save.product_id = product.id
                        WHERE product_save.user_id = '$user_id'
                        ORDER BY product_save.id DESC 
                        LIMIT 4");
            break;

            case 'store_title':

                $store_id = $params['store_id'];
                $products_count = $params['store_title_pc'];

                $products = Product::find_by_sql("SELECT id, hash, store_id, name_url, name 
                    FROM product 
                    WHERE store_id = '$store_id'
                    ORDER BY id DESC
                    LIMIT $products_count");
            break;
            
            case 'tag_title':
                $tag_id=$params['id'];
                $products = Product::find_by_sql("SELECT product.id, product.hash, product.store_id, product.name_url, product.name 
                        FROM product 
                        LEFT JOIN product_tag ON product.id=product_tag.product_id
                        WHERE product_tag.tag_id = '$tag_id' 
                        LIMIT 4");
            break;

            case 'shop':
                $limit_count = 50;

                /*if(isset($params['section_id']) or isset($params['category_id']) ){
                    $time = time() - 60*60*24*30; 
                    $filter .= ' AND product.add_time > '.$time;
                }*/

                $conditions = "available = 1 " . $filter;

                if(isset($params['tag_id'])){
                  $tag_id = $params['tag_id'];  
                  $join = 'LEFT JOIN product_tag ON (product.id = product_tag.product_id)';
                  $conditions = $conditions." AND product_tag.tag_id = $tag_id";  
                }

                if(isset($params['subkind_id'])){
                  $subkind_id = $params['subkind_id'];  
                  $conditions = $conditions." AND product.subkind_id = $subkind_id";  
                }

                if(isset($params['tags'])){
                  $tags= $params['tags'];  
                  $join = 'LEFT JOIN product_tag ON (product.id = product_tag.product_id)';
                  $conditions = $conditions." AND product_tag.tag_id IN ($tags)";  
                }

                if(isset($params['subkinds'])){
                  $subkinds = $params['subkinds'];  
                  $conditions = $conditions." AND product.subkind_id IN ($subkinds)";  
                }

                if(isset($params['colors'])){
                  $colors = $params['colors'];  
                  $conditions = $conditions." AND product.color_id IN ($colors)";  
                }

                if(isset($params['stores'])){
                  $stores = $params['stores'];  
                  $conditions = $conditions." AND product.store_id IN ($stores)";  
                }

                if(isset($params['sort'])){
                   switch($params['sort']){
                        case 'popular':
                            $sort = 'product.views DESC';
                        break;
                        case 'new':
                            $sort = 'product.add_time DESC';
                        break;
                        case 'pricelh':
                            $sort = 'product.price ASC';
                        break;
                        case 'pricehl':
                            $sort = 'product.price DESC';
                        break;
                   } 
                }else{
                    $sort = 'product.views DESC';
                }


                //$count = Product::count(array('joins' => @$join, 'conditions' => $conditions));
                //$count = Product::find_by_sql("SELECT COUNT(id) FROM product USE INDEX (PRIMARY) WHERE ".$conditions);
                $products = Product::all(array(
                    'joins' => @$join,
                    'conditions' => $conditions,
                    'order' => $sort,
                    'limit' => $limit_count,
                    'offset' => $limit
                     ));

                $count = count($products);
            break;

            case 'trending':
                $time = time() - 60*60*24*14; 
                $filter .= 'product.add_time > '.$time;

                if(isset($params['trending_section_id']) and !empty($params['trending_section_id'])){
                   $filter .= ' AND product.section_id =  '.$params['trending_section_id']; 
                }

                $conditions = $filter;
                //"available IN (1) ".

                $info_scope = 'product.id, product.url,  product.add_time, product.views, product.hash, product.name, product.name_url,  product.store_id, product.price, product.oldprice, product.discount';
                $products = Product::find_by_sql("SELECT $info_scope FROM product FORCE INDEX (add_time)
                JOIN product_save ON product.id = product_save.product_id   
                WHERE ".$conditions." 
                GROUP BY product.id
                ORDER BY product.views
                DESC LIMIT $limit, 50");
                 
            break;

            case 'new':
                $time = time() - 60*60*24*1; 
                $filter .= 'product.add_time > '.$time;

                $conditions = $filter;

    
                $products = Product::find_by_sql("SELECT * FROM product FORCE INDEX (add_time) WHERE ".$conditions." ORDER BY views DESC LIMIT 0, 20");
                 
            break;

            case 'store':
                $store_id=$params['store_id'];

                $conditions = "store_id='$store_id'".$filter;

                $count = Product::count(array('conditions' => $conditions));
                $products = Product::find_by_sql("SELECT * FROM product FORCE INDEX (store_id) WHERE ".$conditions." ORDER BY views DESC LIMIT $limit, 50");

            break;

            case 'sale':
                $products = Product::all(array(
                    'conditions' => array('discount >0'),
                    'order' => 'id DESC',
                    'limit' => 50,
                    'offset' => $limit
                     ));
            break;

            case 'feed':   
                $user = USER::info();
                $user_id = $user['id'];
                $sex = $user['sex'];
                $time = time() - (24 * 60 * 60 * 30); 
                $info_scope = 'product.id, product.url,  product.views, product.hash, product.name, product.name_url,  product.store_id, product.price, product.oldprice, product.discount';
               
                $products = Product::find_by_sql("SELECT * FROM (
                        (SELECT  $info_scope, product_save.add_time AS add_time, product_save.user_id AS publisher_id, 'user' AS publisher
                        FROM product FORCE INDEX (PRIMARY)
                        LEFT JOIN product_save ON product.id = product_save.product_id
                        WHERE product_save.user_id = '$user_id'
                        )
                        UNION
                        (SELECT $info_scope, product.add_time AS add_time, feed.publisher_id AS publisher_id, feed.publisher AS publisher
                        FROM ".DB_MAIN_NAME.".product FORCE INDEX (PRIMARY)        
                        LEFT JOIN ".DB_CACHE_NAME.".feed ON product.id = feed.product_id  
                        WHERE feed.user_id = '$user_id'
                        )) t
                        GROUP BY id
                        ORDER BY add_time DESC
                        LIMIT $limit, 50
                        ");

              

                if(empty($products)){


                    $products = Product::find_by_sql("SELECT * FROM ((SELECT  $info_scope, '' as tag_id, product_save.add_time AS add_time, product_save.user_id AS user_id,  product_save.id AS save_id, 'user' AS publisher
                        FROM product FORCE INDEX (PRIMARY)
                        JOIN product_save ON product.id = product_save.product_id 
                        JOIN follow_user ON product_save.user_id = follow_user.user_id
                        WHERE follow_user.follower_user_id = '$user_id'
                        ) 
                        UNION
                        (SELECT  $info_scope, '' as tag_id, product.add_time AS add_time, '' AS user_id, '' AS save_id, 'store' AS publisher
                        FROM product IGNORE INDEX (store_id) FORCE INDEX (PRIMARY)
                        JOIN follow_store ON product.store_id = follow_store.store_id
                        WHERE follow_store.follower_user_id = '$user_id' AND product.section_id IN ($sex, 3, NULL)  
                        AND EXISTS (SELECT * FROM product_save WHERE product_save.product_id = product.id)   
                        ORDER BY product.id DESC 
                        LIMIT 1000
                        )
                        UNION
                        (SELECT  $info_scope, product_tag.tag_id as tag_id, product.add_time AS add_time, '' AS user_id,  '' AS save_id, 'tag' AS publisher
                        FROM product FORCE INDEX (PRIMARY)
                        JOIN product_tag ON product.id = product_tag.product_id 
                        JOIN follow_tag ON product_tag.tag_id = follow_tag.tag_id
                        WHERE follow_tag.follower_user_id = '$user_id' AND product.section_id IN ($sex, 3, NULL)
                        AND EXISTS (SELECT * FROM product_save WHERE product_save.product_id = product.id) 
                        ORDER BY product.id DESC
                        LIMIT 1000
                        )) t
                        GROUP BY id
                        ORDER BY add_time DESC
                        LIMIT $limit, 50
                        ");

                }

            break; 

            case 'tag':
                $tag_id = $params['tag_id'];

                $join = 'LEFT JOIN product_tag ON (product.id = product_tag.product_id)';
                $conditions = "product.available = 1 AND product_tag.tag_id = '$tag_id'".$filter;

                //$count = Product::count(array('joins' => $join, 'conditions' => $conditions));
                $products = Product::all(array(
                    'joins' => $join,
                    'conditions' => $conditions,
                    'order' => 'views DESC',
                    'limit' => 50,
                    'offset' => $limit
                     ));

            break;

            case 'user':
                $user_id = $params['user_id'];
                $products = Product::find_by_sql("SELECT *, 
                    product_save.id AS save_id,
                    product_save.add_time AS add_time, 
                    'user' AS publisher 
                    FROM product_save 
                    LEFT JOIN product ON product_save.product_id = product.id
                    WHERE product_save.user_id = '$user_id'
                    ORDER BY product_save.id DESC 
                    LIMIT $limit, 50");
            break;

            case 'search':
                $query = trim($params['query']); 
                //$query = mysqli_real_escape_string($params['query']);
                $query = htmlspecialchars($params['query']);

                
                $products = Product::all(array(
                    'conditions' => "available = 1 AND name LIKE '%$query%'".$filter,
                    'order' => 'views DESC',
                    'limit' => 50,
                    'offset' => $limit
                     ));
            break;

            case 'similar':
                $product_id=$params['product_id'];
                $product = Product::find($product_id)->to_array();
                $type_id = $product['type_id'];
                $store_id = $product['store_id'];
                $where = '';

                $tags = Product::find_by_sql("SELECT tag.id
                    FROM tag
                    LEFT JOIN product_tag ON tag.id = product_tag.tag_id 
                    WHERE product_tag.product_id = '$product_id' ");

                
                if($tags){
                    $tags_ids_a = array();
                    foreach($tags as $tag) {
                        $tag = $tag->to_array();
                        array_push($tags_ids_a, $tag['id']);
                    }
                    $tags_ids = join(',',$tags_ids_a);

                    $where .= " AND product_tag.tag_id IN ('$tags_ids') ";
                    $join = 'LEFT JOIN product_tag ON product.id = product_tag.product_id';
                }
                
                if($type_id){
                     $where .= " AND product.type_id = '$type_id' ";
                }

                if(!$type_id and !$tags){
                     return false;
                }

                //var_dump($where);
                //exit();
                

                $info_scope = 'product.id, product.add_time, product.url,  product.views, product.hash, product.name, product.name_url,  product.store_id, product.price, product.oldprice, product.discount';
                $products = Product::find_by_sql("SELECT $info_scope
                    FROM product
                    ".@$join." 
                    WHERE product.available = 1 AND product.id != '$product_id' ".$where." GROUP BY product.id ORDER BY product.views DESC LIMIT 20");

                if(!$products){ return false;} 
            break;

            case 'wizard':
                $user = USER::info();
                $sex = $user['sex'];
                $products = Product::find_by_sql("SELECT * FROM product 
                    WHERE section_id = '$sex' 
                    ORDER BY views DESC
                    LIMIT $limit, 50");
            break;
        
        }

        //if (!$products) {return false;} 
            
        $result = array();
        $result_products = array();

        if($params['type']=='tag_title' or $params['type']=='user_title'  or $params['type']=='store_title'){

            if(isset($params['store_title_pc'])){
                $pc = $params['store_title_pc'];
            }else{
                $pc = 4;
            }

            for ($i = 0; $i <= $pc-1; $i++) {
                if(isset($products[$i])){
                    $product = $products[$i]->to_array();
                }else{
                    $product = null;
                }
                
                array_push($result_products, $product);
            }

             $result['products'] = $result_products;
             return $result;
        } 

        foreach($products as $product) {
            $product = $product->to_array();
            if(@$product['publisher'] == 'user'){
                if(!isset($product['user_id'])){
                    $product['user_id'] =  $product['publisher_id'];
                }
                
            }

            if(@$product['publisher']=='store'){
                if(!isset($product['store_id'])){
                    $product['store_id'] =  $product['publisher_id'];
                }
            }

            if(@$product['publisher']=='tag'){
                if(!isset($product['tag_id'])){
                    $product['tag_id'] =  $product['publisher_id'];
                }
            }

            $product = Product::info($product['id'], 'list', $product);
            array_push($result_products, $product);
        }

        
        $result['count'] = @$count;
        if($result['count'] > 5000){$result['count'] = 5000;}
        
        $result['products'] = $result_products;
        

        return $result;
    }



    public static function currency_exchange($price, $exchange_currency_id){
        if($exchange_currency_id == CURRENCY_ID){
            return $price;
        }else{
            $currency_id = CURRENCY_ID;
            $data=Product::find_by_sql("SELECT * FROM currency_rates 
                                        WHERE exchange_currency_id=  '$exchange_currency_id' 
                                        AND currency_id = '$currency_id'");
            foreach($data as $currency) {
                $exchange_rate = $currency->rate;
                $new_price = round($price*$exchange_rate, 2);
            }
            return @$new_price;
        }
    }

    public static function categorize($section, $name){
        $title=mb_convert_case($name, MB_CASE_LOWER, "UTF-8"); 

        $types=Product::find_by_sql("SELECT type.id AS id, category.id AS category_id, kind.query FROM type
            LEFT JOIN kind ON type.kind_id = kind.id
            LEFT JOIN category ON type.category_id=category.id 
            WHERE category.section_id='$section'");

        foreach($types as $type) {
            $type = $type->to_array();
            $queries=explode(",",$type['query']);

            foreach ($queries as $query) {
                if(!empty($query)){
                    if (substr_count($title, $query) > 0){
                        return $type;
                    }
                }    
            }
        }

        return false;  
    }

    public static function defineSubKind($type_id, $name){

        $title=mb_convert_case($name, MB_CASE_LOWER, "UTF-8"); 

        $subkinds = Product::find_by_sql("SELECT subkind.id AS id, subkind.query FROM subkind
            LEFT JOIN kind ON subkind.kind_id = kind.id
            LEFT JOIN type ON type.kind_id = kind.id 
            WHERE type.id = '$type_id'");

        foreach($subkinds as $subkind) {
            $subkind = $subkind->to_array();
            $queries = explode(",",$subkind['query']);

            foreach ($queries as $query) {
                if(!empty($query)){
                    if (substr_count($title, $query) > 0){
                        return $subkind;
                    }
                }    
            }
        }

        return false;  

    }

    public static function gettypes($category){
        $types=Product::find_by_sql("SELECT type.id AS type_id, type.name AS type_name
                FROM type
                LEFT JOIN category ON type.category_id=category.id 
                WHERE type.category_id='$category'");
        $result = array();
        foreach($types as $type) {
                $type = $type->to_array();
                array_push($result, $type);
            }
        return $result;
    }

    public static function getcategory($section){
           $categories=Product::find_by_sql("SELECT id AS category_id, name AS category_name
            FROM category 
            WHERE section_id = '$section'"); 
        $result = array();
        foreach($categories as $category) {
                $category = $category->to_array();
                $category['types']=Product::gettypes($category['category_id']);
                array_push($result, $category);
            }
        return $result;
    }

    public static function currency_symbol($currency_id){
        
        switch ($currency_id) {
            case '1': $currency_symbol = 'руб.'; break;
            case '2': $currency_symbol = '₴';  break;
            case '3': $currency_symbol = '$';  break;   
            case '4': $currency_symbol = '€';  break; 
            case '5': $currency_symbol = '£'; break;               
            
            default:
                $currency_symbol = '';
                break;
        }
        
        return $currency_symbol;
        
    }

    public static function genThumbs($product){
        $types = array(
            "min" => "110",
            "med" => "220",
            "max" => "640",
        );

        $i = 0;
        foreach ($types as $type => $size) {
            $destination_file = $_SERVER['DOCUMENT_ROOT'].'/u/thumbs/'.$product['store_id'].'/'.$type.'/'.$product['hash'].'.jpg';
            //if(!file_exists($destination_file)){
                $thumb = Product::make_thumb($product['img'], $destination_file, $size, $type);
                if($thumb){
                    $i++;    
                }   
            //}
        }

        if($i == 3){
            return true; 
        }else{
            return false;
        }
        
    }

    public static function make_thumb($original_file, $destination_file, $square_size, $type){
    
        $folder = substr($destination_file, 0, strripos($destination_file, "/"));   
        if(!is_dir($folder)){ 
            mkdir($folder, 0755, true);
        }
    

        // get width and height of original image
        
        if ($imagedata = @getimagesize($original_file)) {} else {return false;}
   
        $original_width = $imagedata[0];    
        $original_height = $imagedata[1];
        
        if($original_width > $original_height){
            $new_height = $square_size;
            $new_width = $new_height*($original_width/$original_height);
        }
        if($original_height > $original_width){
            $new_width = $square_size;
            $new_height = $new_width*($original_height/$original_width);
        }
        if($original_height == $original_width){
            $new_width = $square_size;
            $new_height = $square_size;
        }
        
        $new_width = round($new_width);
        $new_height = round($new_height);
        
        // load the image
        if(exif_imagetype($original_file) == IMAGETYPE_JPEG){
            $original_image = imagecreatefromjpeg($original_file);
        }elseif(exif_imagetype($original_file) == IMAGETYPE_GIF){
            $original_image = imagecreatefromgif($original_file);
        }elseif(exif_imagetype($original_file) == IMAGETYPE_PNG){
            $original_image = imagecreatefrompng($original_file);
        }else{
            $original_image = imagecreatefromjpeg($original_file);  
        }
        
        
        $smaller_image = imagecreatetruecolor($new_width, $new_height);
        $square_image = imagecreatetruecolor($square_size, $square_size);
        

        imagecopyresampled($smaller_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        
        
        if($type == 'min'){
            if($new_width>$new_height){
                $difference = $new_width-$new_height;
                $half_difference =  round($difference/2);
                imagecopyresampled($square_image, $smaller_image, 0-$half_difference+1, 0, 0, 0, $square_size+$difference, $square_size, $new_width, $new_height);
            }
            if($new_height>$new_width){
                $difference = $new_height-$new_width;
                $half_difference =  round($difference/2);
                imagecopyresampled($square_image, $smaller_image, 0, 0-$half_difference+1, 0, 0, $square_size, $square_size+$difference, $new_width, $new_height);
            }
            if($new_height == $new_width){
                imagecopyresampled($square_image, $smaller_image, 0, 0, 0, 0, $square_size, $square_size, $new_width, $new_height);
            }
            
            
            if($original_width < $square_size or $original_height < $square_size){
                imagejpeg($original_image,$destination_file, 80);
            }else{
                imagejpeg($square_image,$destination_file, 80); 
            }
        }else{
            
            if($original_width < $square_size or $original_height < $square_size){
                imagejpeg($original_image,$destination_file, 80);
            }else{
                imagejpeg($smaller_image,$destination_file, 80); 
            }
            
        }


      
        imagedestroy($original_image);
        imagedestroy($smaller_image);
        imagedestroy($square_image);

        return $destination_file . ' generated';

    }

    public static function detectColor($img){

        $gicp = new GeneratorImageColorPalette();
        $product_colors = $gicp->getImageColor($img, 2, $image_granularity = 20);

        if(!$product_colors){ return false; }        
        $avp_colors =  array_values($product_colors); 
        $akp_colors =  array_keys($product_colors); 
          
        $ratio = round( $avp_colors[0] / $avp_colors[1] );

        if($akp_colors[0] == 'FFFFFF' and $ratio <= 15){
            $product_base_color = $akp_colors[1];
        }elseif($ratio > 20){
            return false; 
        }else{
            $product_base_color = $akp_colors[0];
        }
        //var_dump($product_colors, $ratio, $product_base_color);
        //return;
        

        $colors = Product::find_by_sql("SELECT * FROM color");

        foreach($colors as $color) {
            $color = $color->to_array();
            $queries = explode(",",$color['query']);

            foreach ($queries as $query) {
                if(!empty($query)){
                    if (substr_count($product_base_color, $query) > 0){
                        return $color;
                    }
                }    
            }
        }

        return false;


    }     

}


class Feed extends ActiveRecord\Model

    {
        static $table_name = 'feed';

    }
 
?>