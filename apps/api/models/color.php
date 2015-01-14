<?

class Color extends ActiveRecord\Model

	{
        static $table_name = 'color';
        // static $primary_key = 'book_id';

		public static function add(){
			
		}
    
		public static function get($params){

            if(isset($params['tag_id'])){

                $colors = Color::find_by_sql("SELECT color.id, color.url, color.name, color.title, color.ico FROM color
                LEFT JOIN product ON  color.id = product.color_id
                LEFT JOIN product_tag ON  product.id = product_tag.product_id
                WHERE product.type_id = ".$params['type_id']." AND product_tag.tag_id = ".$params['tag_id']."
                GROUP BY color.id");

            }elseif(isset($params['subkind_id'])){

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

                $colors = Color::find_by_sql("SELECT color.id, color.url, color.name, color.title, color.ico FROM color
                LEFT JOIN ".DB_CACHE_NAME.".filter_type_color ON  color.id = filter_type_color.color_id
                WHERE ".DB_CACHE_NAME.".filter_type_color.type_id IN ($types)
                GROUP BY color.id");
                

            } 
   
            $result = array();
      

            foreach($colors as $color) {
                $color = $color->to_array();

                array_push($result, $color);
            }


            return $result;

		}

	}

?>