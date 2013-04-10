<?php
/**
 * Class Application_Model_Analytics
 * 
 * Used for sending information to Google Analytics
 *
 * @category   HiDef
 * @package    HiDef_Magic
 * @subpackage Wand
 * @copyright  Copyright (c) 2010 HiDef Web Inc. (http://www.hidef.co)
 * @version    $Id:$
 * @link       none
 * @since      File available since April 2012 
 * 
 */
class Application_Model_Analytics{
	
	/**
     * Google Analytics account number
     * @access protected
     * @var string
     */
	protected $account=null;
	
	/**
     * The event action
     * @access protected
     * @var string
     */
	protected $eventAction='';
	
	/**
     * The event group
     * @access protected
     * @var string
     */
	protected $eventGroup='user';
	
	/**
     * The event label
     * @access protected
     * @var string
     */
	protected $eventLabel='';
	
	/**
     * The event value
     * @access protected
     * @var string
     */
	protected $eventValue='';
	
	/**
	 * Holds a log of what is sent to Google
     * @access protected
	 * @var array
	 */
	protected $log=array();
	
	/**
     * The page name
     * @access protected
     * @var string
     */
	protected $pageName='';
	
	/**
     * The page title
     * @access protected
     * @var string
     */
	protected $pageTitle='';
	
	/**
	 * The constructor
	 * @param string $account The Google Analytics account number. 
	 */
    public function __construct($account){
    	$this->account=$account;
    }
	
	/**
	 * Sends the event to Google 
	 * @param string $eventAction The event action
	 * @param string $eventLabel The event label
	 * @param string $eventValue The event value. Default 1
	 * @param string $eventGroup The event group. Default "User"
	 * @param string $pageTitle The page title
	 * @param string $pageName The page name
	 * @return void
	 * 
	 */
	public function sendEvent($eventAction,$eventLabel,$eventValue=1,$eventGroup="User",$pageTitle="",$pageName=""){
		$this->eventAction	= $eventAction;
		$this->eventLabel	= $eventLabel;
		$this->eventGroup	= $eventGroup;
		$this->eventValue	= $eventValue;
		$this->pageTitle	= $pageTitle;
		$this->pageName		= $pageName;
		
		$this->sendToGoogle();
	}
	
	/**
	 * Generates the headers to send to Google
	 * @return array
	 * @access private
	 */
	private function getAnalyticsHeaders()
	{
		$headers = array(
			'Accept:*/*',
			'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3',
			'Accept-Encoding:gzip,deflate,sdch',
			'Accept-Language:en-US,en;q=0.8',
			'Connection:keep-alive',
			'Host:www.google-analytics.com',
			'Referer:http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
			'User-Agent:'.$_SERVER['HTTP_USER_AGENT']
		);
		return $headers;
	}
	
	/**
	 * Generates the query parameters to send to Google
	 * @return array
	 * @access private
	 */
	private function getEventQueryParams()
	{
		$queryParams = array();
		$queryParams['utmac'] = $this->account; //account number
		$queryParams['utmcc'] = '__utma=' . $_COOKIE['__utma'] . ';__utmz=' . $_COOKIE['__utmz'] . ';'; //Google Cookie
		$queryParams['utmcs'] = 'UTF-8'; //Browser Encoding
		$queryParams['utmdt'] = $this->pageTitle; //page title
		$queryParams['utme']  = '5('.$this->eventGroup.'*'.$this->eventAction.'*'.$this->eventLabel.')(' . $this->eventValue . ')'; //data 5(EventGroup*EventAction*EventLabel)(EventValue)
		$queryParams['utmfl'] = '10.2 r152'; //flash version
		$queryParams['utmhn'] = $_SERVER['HTTP_HOST']; //host name
		$queryParams['utmje'] = '1'; //jave enabled
		$queryParams['utmn']  = mt_rand(); //unique ID to prevent cacheing
		$queryParams['utmp']  = $this->pageName; //the page name
		$queryParams['utmr']  = $_SERVER['HTTP_REFERER']; //the page referer
		$queryParams['utmsc'] = '32-bit'; //screen color depth
		$queryParams['utmsr'] = '1280x1024'; //screen size
		$queryParams['utmt']  = 'event'; //event, transaction, item, or custom variable
		$queryParams['utmul'] = 'en-us'; //language
		$queryParams['utmwv'] = '4.8.9'; //tracking code version
		return $queryParams;
	}
	
	/**
	 * Returns the log
	 * @return array
	 */
	public function getLog()
	{
		return $this->log;
	} 

	/**
	 * Sends the data to Google
	 * @return void
	 * @access private
	 */
	private function sendToGoogle()
	{
		$params	= $this->getEventQueryParams();
		$headers = $this->getAnalyticsHeaders();
		foreach($params as $k => $param) 
		{
			$params[$k] = $k . '=' . urlencode($param);
		}
		$url = 'http://www.google-analytics.com/__utm.gif?' . implode('&', $params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers );
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		$this->log[]='Sent to URL: '.$url;
		$this->log[]='Headers: '.implode(',',$headers);
		$this->log[]='Response: '.$result;
	}
}