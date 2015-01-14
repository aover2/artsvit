<?
class Boot 
{

	static function start(){

		require_once 'libs/activerecord/ActiveRecord.php';

		$connections = array(
	 			'main' => 'mysql://'.DB_LOGIN.':'.DB_PASS.'@'.DB_HOST.'/'.DB_MAIN_NAME.'?charset=utf8',
  			);

		ActiveRecord\Config::initialize(function($cfg) use ($connections)
		{ 	
  			$cfg->set_model_directory('apps');
  			$cfg->set_connections($connections);
		    $cfg->set_default_connection('main');
		});
  
		define('SITE_ROOT', 'http://'.$_SERVER['HTTP_HOST']);

		switch($_SERVER['REQUEST_METHOD']){
		    case 'GET': $request = &$_GET; break;
		    case 'POST': $request = &$_POST; break;
		}

		$url = htmlspecialchars(trim($_SERVER['REQUEST_URI']));
     
	   

		$url_data = explode('?', $url);
		$url_map = explode('/', $url_data[0]);

		$url_query = parse_url($url);
		$url_query = @$url_query['query'];

		$subdomen = explode('.', $_SERVER['HTTP_HOST']);
		$subdomen = $subdomen[0];

		@$page_name = $url_map[1];
		@$subpage_name = $url_map[2];
		@$subsubpage_name = $url_map[3];

		$app_name = null;
		$controller_name = null;


		if(substr($url_data[0], -1) == '/' AND $page_name != null){
	        header("HTTP/1.1 301 Moved Permanently");
	        header("Location: ".substr($url_data[0], 0, strlen($url_data[0])-1));
	        exit();
	    };

  
	   
      switch($page_name){
		    case 'api': $app_name = 'api'; break;
		    case 'about': $app_name = 'about'; break;
		    case 'admin': $app_name = 'admin'; break;
		    default: $app_name = 'site';
		}


		if($app_name){
			require_once  'apps/'.$app_name.'/models.php';
			require_once  'apps/'.$app_name.'/controllers.php';
		}	
	}


	public static function pagenotexist(){

        header('404', true, 404);
        include  'templates/404.html';
        exit();
    }


}

?>