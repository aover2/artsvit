<?

require_once 'libs/getImageColor.php';
require_once 'libs/utils.php';

foreach (glob('apps/api/models/*.php') as $filename)
{
  require_once $filename;
}



?>