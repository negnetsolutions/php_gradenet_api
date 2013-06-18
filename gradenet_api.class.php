<?php
/**
* gradenet_api
*/

class gradenet_api
{
	static protected $instance;
	private $_apiKey = '';
	private $_debugMode = self::DEBUG_OFF;
	private $_server_address = '';
  private $_api_request = '';
  private $_api_payload = array();
  private $gradenetResponse = null;
	
	const DEBUG_OFF = 0;
	const DEBUG_VERBOSE = 1;
	
	public static function getInstance()
	{
		return new self();
	}
	function __construct()
	{
	}
  public function getUserInfo($uid)
  {
    $this->_api_request = 'user_info';
    $this->_api_payload['uid'] = $uid;

    return $this->transmit();
  }
  public function authenticate($uid,$password)
  {
    $this->_api_request = 'authenticate';
    $this->_api_payload['uid'] = $uid;
    $this->_api_payload['password'] = $password;

    return $this->transmit();
  }
	public function setServer($address)
	{
		$this->_server_address = $address;
		return $this;
	}
	public function &setToken($key)
	{
		$this->_apiKey = $key;
		return $this;
	}
	public function &debug($mode = self::DEBUG_VERBOSE)
	{
		$this->_debugMode = $mode;
		return $this;
	}
	private function transmit()
	{
		$data = $this->_api_payload;
    
    $headers = array(
      'Accept: application/json',
      'Content-Type: application/json',
      'X-Server-Token: ' . $this->_apiKey
    );
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_server_address.'/api/'.$this->_api_request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$return = curl_exec($ch);
		$curlError = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				
		if ($curlError !== '') {
			throw new Exception($curlError);
		}
		
		if (!$this->_isTwoHundred($httpCode)) {
			if ($httpCode == 422) {
				$return = json_decode($return);
				throw new Exception($return->Message, $return->ErrorCode);
			} else {
				throw new Exception("Error. Gradenet returned HTTP code {$httpCode} with message \"{$return}\"", $httpCode);
			}
		}
		
		// check return status
		$return_status = json_decode($return, true);
		$this->gradenetResponse = $return_status;
		
		if (($this->_debugMode & self::DEBUG_VERBOSE) === self::DEBUG_VERBOSE) {
			echo "<pre>".print_r( array(
        'request'=>$this->_server_address.'/api/'.$this->_api_request,
				'json' => json_encode($data),
				'headers' => $headers,
				'return' => $return,
				'curlError' => $curlError,
				'httpCode' => $httpCode,
				'return_status' => $return_status
			)
			, true)."</pre>";
		}
		
    if( $return_status['Status'] == 'ERROR' )
      throw new Exception($return_status['ErrorCode']);
    
		return $return_status['DATA'];
	}
	public function getResponse()
	{
		return $this->gradenetResponse;
	}
	private function _isTwoHundred($value)
	{
		return intval($value / 100) == 2;
	}
}
