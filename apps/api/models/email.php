<?

class Email extends ActiveRecord\Model
{   

    static $db = DB_MAIN_NAME;
    static $table_name = 'email'; 

    public function add()
    {
        $user = User::find($this->user_id);
        
        if ($user->email_verified) {
            $this->save();
        }
    }

}



?>