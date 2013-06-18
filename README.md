php_gradenet_api
================

PHP API for Gradenet

## Usage

require('./gradenet_api.class.php');
// Create a message and send it
	$api = gradenet_api::getInstance()
  ->setServer('[Gradenet Instance URL]')
	->setToken('[Gradenet API Token')
  ;

### Authentication

try {
	$return = $api->authenticate("[UID]","[PASSWORD]");
}
catch (Exception $e)
{
	echo "<p><strong>Message Error: ". $e->getMessage().'</strong></p>';
}


