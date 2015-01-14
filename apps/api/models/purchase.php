<?

class Purchase extends ActiveRecord\Model
{

    static $table_name = 'purchase';
    static $primary_key = 'id'; 

    public static function add($product_id){

        $product = Product::find($product_id);
        $product->views++;
        $product->save();

        $product = Product::info($product_id);

        if($product){
            $purchase_time = time(); 
            $current_user_id = UID;
            $purchase=new Purchase();
            $purchase->product_id = $product_id;
            $purchase->user_id = $current_user_id;
            $purchase->time = $purchase_time;
            $purchase->save();
            $result = array();
            
            $result['purchase_id'] = $purchase->id;     
            $result['redirect_url'] = Store_affiliate::get_redirect_url($product);
            $result['product'] = $product;

            return $result;
        }else{
            return false;  
        }    

    }

}


?>