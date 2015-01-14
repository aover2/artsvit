<? 
header('P3P: CP="IDC DSP COR IVAi IVDi OUR TST"');
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
echo "1";


    Twig_Autoloader::register();
    $twig_loader = new Twig_Loader_Filesystem('templates/site');
    $twig = new Twig_Environment($twig_loader);


    $twig->addGlobal('site_root', SITE_ROOT);
    $twig->addGlobal('url_query', $url_query);
    $twig->addGlobal('browser', Utils::getBrowser());
    $twig->addGlobal('referer', @$_SERVER["HTTP_REFERER"]);
    
    
    $twig->addGlobal('page_name', $page_name); 
    $twig->addGlobal('subpage_name', $subpage_name); 
    $twig->addGlobal('subsubpage_name', $subsubpage_name); 

    $twig->addGlobal('version', '1460'); 


    
    
    
    /*if(empty($current_user['email']) and $page_name != 'signup' and $islogin==1){
        header("Location: ".SITE_ROOT.'/signup/');
        exit();
    }*/

//    $twig->addGlobal('islogin', $islogin);
    /*$page_number = 0;
    if(is_numeric($page_name) and empty($subpage_name)){ $page_number =  $page_name;}
    if(is_numeric($subpage_name) and empty($subsubpage_name)){ $page_number = $subpage_name;}
    if(is_numeric($subsubpage_name)){ $page_number = $subsubpage_name;}

    if($page_number > 1){
        if($page_name != 'purchase'){
          pagenotexist($twig);  
        }
        $current_url = $_SERVER['REQUEST_URI'];
        //$current_url = str_replace('/'.$page_number, "", $_SERVER['REQUEST_URI']);
    }elseif($page_number == 1){
        header("Location: ".str_replace($page_number, "", $_SERVER['REQUEST_URI']));
        exit();
    }else{   
        $current_url = $_SERVER['REQUEST_URI'];
        $page_number = 1;
    }



    if($page_number > 100 
        and $page_name != 'purchase' 
        and $page_name != 'p' 
        and $page_name != 'tags' 
        and $page_name != 'stores' 
        and $page_name != 'trendsetters'
        and $page_name != 'out' ){

        pagenotexist($twig);
    }
    */

    if($page_name != 'logout'){
        //header( 'Cache-Control: max-age=3600' );
    }else{
        //header("Cache-Control: no-cache, must-revalidate");
        //header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    }
    
    $current_url = $_SERVER['REQUEST_URI'];
    $current_url = strtok($current_url,'?');    

    $twig->addGlobal('current_url', $current_url);

    $sections = Model::api('sections.get');
    $twig->addGlobal('sections', $sections);

    /* $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
    $ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");

    if ($iphone || $ipod == true AND $page_name !== 'm'){ 
        //header('Location: '.$site_root.'/m/ios');
    }elseif($android == true AND $page_name !== 'm'){
        //header('Location: '.$site_root.'/m/android');
    }*/ 


    function pagenotexist($twig){

        header('404', true, 404);
        echo $twig->render('404.html');
        exit();
    }


if($page_name !== ''){
    switch($page_name){

        case 'settings': $controller_name = 'settings'; break;
        case 'signup':   $controller_name = 'signup'; break;
        case 'signin':   $controller_name = 'signin'; break;
        case 'activation': $controller_name = 'activation'; break;

        case 'trending':   $controller_name = 'trending'; break;
        case 'tags':   $controller_name = 'tags'; break;
        case 'trendsetters':   $controller_name = 'trendsetters'; break;
        case 'stores': $controller_name = 'stores'; break;

        case 'welcome': $controller_name = 'welcome'; break;
        case 'wizard': Auth::needlogin(); $controller_name = 'wizard'; break;

        case 'store':    $controller_name = 'store'; break;
        case 'p':  $controller_name = 'product'; break;
        case 'search':   $controller_name = 'search'; break;
        case 'purchase':  $controller_name = 'purchase'; break;
        case 'login':  $controller_name = 'login'; break;
        case 'logout':   $controller_name = 'logout'; break;
        case 'widget':   $controller_name = 'widget'; break;
        
        case 'invite': Auth::needlogin();  $controller_name = 'invite'; break;
        case 'find': Auth::needlogin();  $controller_name = 'find'; break;

        case 'sets':  $controller_name = 'sets'; break;
        case 'set':  $controller_name = 'set'; break;

        case 'map':  $controller_name = 'map'; break;

        //case 'out':  $controller_name = 'out'; break;
    
        default:
            if($section = Model::getSection($page_name)){
                $controller_name = 'shop';
                $twig->addGlobal('section', $section); 
            }elseif($category = Model::getCategory($page_name)){
                $controller_name = 'shop';
                $twig->addGlobal('category', $category); 
            }elseif($type = Model::getType($page_name)){
                $controller_name = 'shop';
                $twig->addGlobal('type', $type);
            }elseif($kind = Model::getKind($page_name)){
                $controller_name = 'shop';
                $twig->addGlobal('kind', $kind);     
            }elseif(Model::getProfileId($page_name)){
                $controller_name = 'user';        
            }elseif(Model::getTagId($page_name)){
                $controller_name = 'tag';    
            }else{    
                pagenotexist($twig);
            }
        }

}else{
    if($islogin == 1){
        $controller_name = 'feed';
    }else{
        //header('Location: '.SITE_ROOT.'/welcome');
        $controller_name = 'trending';   
    }
}
    $twig->addGlobal('controller_name', $controller_name); 

switch ($controller_name) {

  
    case 'trending': 

        echo $twig->render('home.html',
            array(
                
            ));
        break;        

    default:
         echo $twig->render('home.html',
            array(
                
            ));
        break;
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

if(SITE_ROOT != 'http://scrubbly.ru' AND SITE_ROOT != 'http://scrubbly.dev'){
    echo 'Page generated in '.$total_time.' seconds.';    
}

?>