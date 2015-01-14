<?

class Section extends ActiveRecord\Model
{

    static $table_name = 'section';
    static $primary_key = 'id'; 


    public static function info($section_id){
       $section = Section::find($section_id);
       $section = $section->to_array();  
       return $section;
    }

    public static function get($params){

      //var_dump($params);
      if(!isset($params['type'])){
        $sections = Section::all();
      }elseif($params['type']=='tag'){
        $sections = Section::filter($params);
      }
       

       $result= array();
       $result['sections'] = array();
       $i = 0;
       foreach($sections as $section) {
            $section = $section->to_array();
            $categories = Category::get($section['id'], $params);

            array_push($result['sections'], $section);
            $result['sections'][$i]['categories'] = $categories;

            $j = 0;
            foreach($categories as $category) {
                $types = Type::get($category['id'], $params);
                $result['sections'][$i]['categories'][$j]['types'] = $types;
                $j++;
            }
            
            $i++;
        }

        $result['kinds'] = array();
        $kinds = Kind::find_by_sql("SELECT kind.id, kind.name, kind.url FROM kind LEFT JOIN type ON kind.id = type.kind_id WHERE kind.url != type.url GROUP BY kind.id");

        foreach($kinds as $kind) {
            $kind = $kind->to_array();
            $categories = Category::get($section['id'], $params);

            array_push($result['kinds'], $kind);
        }

        return $result;
    }

    public static function filter($params){
      $tag_id = $params['tag_id'];

   
      $result = Section::find_by_sql("SELECT section.id, section.name, section.title, section.url FROM section 
        LEFT JOIN product ON  section.id = product.section_id
        LEFT JOIN product_tag ON product.id = product_tag.product_id
        LEFT JOIN tag ON product_tag.tag_id = tag.id
        WHERE tag.id = '$tag_id'
        GROUP BY section.id
        ");
      

      return $result;
      
    }

}

//$TH = array('Машины'=>$m, 'Самолёты'=>$s, 'Танки'=>$t, 'Корабли'=>$k); 


class Category extends ActiveRecord\Model
{

    static $table_name = 'category';
    static $primary_key = 'id'; 


    public static function info($category_id){
       $category = Category::find($category_id);
       $category = $category->to_array();  
       return $category;
    }

    public static function get($section_id, $params){

       
       if(!isset($params['type'])){
          $categories = Category::all(array('conditions' => "section_id = '$section_id'"));
        }elseif($params['type']=='tag'){
           $categories  = Category::filter($section_id, $params);
        }

       $result = array();
       foreach($categories as $category) {
            $category = $category->to_array();
            array_push($result, $category);
        }
        return $result;
    }

    public static function filter($section_id, $params){
      $tag_id = $params['tag_id'];

   
      $result = Category::find_by_sql("SELECT category.id, category.name, category.title, category.url FROM category 
        LEFT JOIN product ON  category.id = product.category_id
        LEFT JOIN product_tag ON product.id = product_tag.product_id
        LEFT JOIN tag ON product_tag.tag_id = tag.id
        WHERE tag.id = '$tag_id' AND category.section_id = '$section_id' 
        GROUP BY category.id
        ");
      

      return $result;
      
    }

}

class Type extends ActiveRecord\Model
{

    static $table_name = 'type';
    static $primary_key = 'id'; 

    public static function info($type_id){
       $type =  Type::find_by_sql("SELECT type.id, kind.name, type.title, type.url FROM type LEFT JOIN kind ON type.kind_id = kind.id WHERE type.id = '$type_id' ");
       $type = $type[0]->to_array();  
       return $type;
    }

    public static function get($category_id, $params){

       
       if(!isset($params['type'])){
           $types = Type::find_by_sql("SELECT type.id, kind.name, type.title, type.url FROM type LEFT JOIN kind ON type.kind_id = kind.id WHERE category_id = '$category_id' ");
        }elseif($params['type']=='tag'){
           $types  = Type::filter($category_id, $params);
        }

       $result = array();
       foreach($types as $type) {
            $type = $type->to_array();
            array_push($result, $type);
        }
        return $result;
    }

    public static function filter($category_id, $params){
      $tag_id = $params['tag_id'];

   
      $result = Type::find_by_sql("SELECT type.id, kind.name, type.title, type.url FROM type 
        LEFT JOIN kind ON type.kind_id = kind.id
        LEFT JOIN product ON  type.id = product.type_id
        LEFT JOIN product_tag ON product.id = product_tag.product_id
        LEFT JOIN tag ON product_tag.tag_id = tag.id
        WHERE tag.id = '$tag_id' AND type.category_id = '$category_id'
        GROUP BY type.id
        ");
      

      return $result;
      
    }

    public static function getByKind($kind_id){
      $types = Type::find_by_sql("SELECT type.id FROM type 
        LEFT JOIN kind ON type.kind_id = kind.id
        WHERE kind.id = '$kind_id' 
        ");
      
      $result = '';
      foreach($types as $type){
            $type = $type->to_array();
            $result .= $type['id'].',';
        }
      $result = substr($result, 0, strlen($result)-1);  

      return $result;
      
    }

}

class Kind extends ActiveRecord\Model
{

    static $table_name = 'kind';
    static $primary_key = 'id'; 

    public static function info($type_id){
       $type = Type::find($type_id);
       $type = $type->to_array();  
       return $type;
    }

    public static function get($category_id, $params){

       
       if(!isset($params['type'])){
          $types = Type::all(array('conditions' => "category_id = '$category_id'"));
        }elseif($params['type']=='tag'){
           $types  = Type::filter($category_id, $params);
        }

       $result = array();
       foreach($types as $type) {
            $type = $type->to_array();
            array_push($result, $type);
        }
        return $result;
    }

    public static function filter($category_id, $params){
      $tag_id = $params['tag_id'];

   
      $result = Type::find_by_sql("SELECT type.id, type.name, type.title, type.url FROM type 
        LEFT JOIN product ON  type.id = product.type_id
        LEFT JOIN product_tag ON product.id = product_tag.product_id
        LEFT JOIN tag ON product_tag.tag_id = tag.id
        WHERE tag.id = '$tag_id' AND type.category_id = '$category_id'
        GROUP BY type.id
        ");
      

      return $result;
      
    }

}

class SubKind extends ActiveRecord\Model
{

    static $table_name = 'subkind';
    static $primary_key = 'id'; 

    public static function info($id){
        $item = SubKind::find($id);
        $item = $item->to_array();  
        return $item;
    }

    public static function get($params){
     

        if(isset($params['tag_id'])){
          /*if(isset($params['kind_id'])){
            $kind_id = $params['kind_id'];
            $where = "subkind.kind_id = '$kind_id'";
          }else{
              $type = Type::find($params['type_id'])->to_array();
              $kind_id = $type['kind_id']; 
              $where = "subkind.kind_id = '$kind_id' AND product.type_id = ".$params['type_id'];
          }

          $subkinds = Type::find_by_sql("SELECT subkind.id, subkind.url, subkind.name FROM subkind
              LEFT JOIN product ON subkind.id = product.subkind_id
              WHERE ".$where."
              GROUP BY subkind.id"); */
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


          $subkinds = Type::find_by_sql("SELECT subkind.id, subkind.url, subkind.name FROM subkind
            LEFT JOIN ".DB_CACHE_NAME.".filter_type_subkind ON subkind.id = filter_type_subkind.subkind_id
            WHERE filter_type_subkind.type_id IN ($types)
            GROUP BY subkind.id");
        }    

        $result = array();
        foreach($subkinds as $subkind) {
            $subkind = $subkind->to_array();
            array_push($result, $subkind);
        }

        return $result;
    }

}


?>