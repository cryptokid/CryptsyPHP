<?php

namespace \CryptCoin\Exchange

class Cryptsy{

	protected $_secretKey = null;
	protected $_apiKey = null;
	protected $_lastResult = null;

	public function __construct(){
	}

	public function setSecretKey( $key ){
		$this->_secretKey( $key );
	}

	public function setAPI( $api ){
		$this->_apiKey = $api;
	}

	public function getGeneralMarketData( $marketID = null ){

		// If no market is specified return all markets
		if ( is_null($marketID) ){
			return $this->processPublic('marketdatav2');
		}

		return $this->processPublic( 'singlemarketdata', array('marketid' => $marketID) );
	}

	public function getGeneralOrderBookData( $marketID = null ) {

		if ( is_null( $marketID) ){
			return $this->processPublic( 'orderdata', null, 'public' );
		}

		return $this->processPublic( 'singleorderdata' , array('marketid' => $marketID ) ) ;

	}

	public function getInfo(){
		return $this->process('getinfo');
	}

	public function getMarkets(){
		return $this->process('getmarkets');
	}

	public function getWalletStatus(){
		return $this->process('getwalletstatus');
	}

	public function getMyTransactions(){
		return $this->process('mytransactions');
	}

	public function getMarketTrades( $marketID ){
		return $this->process( 'markettrades', array('marketid' => $marketID) );
	}

	public function getMarketOrders( $marketID ){
		return $this->process('marketorders', array('marketid' => $marketID ) );
	}

	public function getMyTrades( $marketID, $limit = 200 ){
		return $this->process('mytrades', array('marketid' => $marketID, 'limit' => $limit ) );
	}

	public function getAllMyTrades( $startDate = null, $endDate = null ){
		
		$data = array();

		if ( !is_null($startDate) ){
			$data['startdate'] = date('Y-m-d', strtotime( $startDate ) );
		}

		if ( !is_null($endDate) ){
			$data['enddate'] = date('Y-m-d', strtotime( $endDate ) );
		}

		return $this->process('allmytrades', $data );
	}

	public function getMyOrders( $marketID ){
		return $this->process( 'myorders', array('marketid' => $marketID ) );
	}

	public function getDepth( $marketID ){
		return $this->process( 'depth', array( 'marketid' => $marketID ) );
	}

	public function getAllMyOrders( ){
		return $this->process( 'allmyorders' );
	}

	public function createOrder( $marketID, $orderType, $quantity, $price ){

		return $this->process( 'createorder', array(
				'marketid' => $marketID,
				'ordertype' => $orderType,
				'quantity' => $quantity,
				'price' => $price
			) 
		);
	}

	public function cancelOrder( $orderID ){

		return $this->process( 'cancelorder', array(
				'orderid' => $orderID
			)
		);
	}

	public function cancelMarketOrders( $marketID ){
		return $this->process( 'cancelmarketorders', array(
				'marketid' => $marketID
			)
		);
	}

	public function cancelAllOrders(){
		return $this->process( 'cancelallorders' );
	}

	public function calculateFees( $orderType, $quantity, $price ){
		return $this->process( 'calculatefees', array(
				'ordertype' => $orderType,
				'quantity' => $quantity,
				'price' => $price
			)
		);
	}

	public function generateNewAddress( $currencyID, $currencyCode ){
		return $this->process('generatenewaddress', array(
				'currencyid' => $currencyID,
				'currencycode' => $currencyCode,
			)
		);
	}

	public function getMyTransfers(){
		return $this->process('mytransfers');
	}

	public function makeWithdrawl( $address, $amount ){
		return $this->process( 'makewithdrawal', array( 
				'address' => $address, 
				'amount' => $amount
			)
		);
	}

	protected function _process( $uri, $params, $getParams = array(), $postParams = array() ){

        // API settings
        $key = ''; // your API-key
        $secret = ''; // your Secret-key
 
        $req['method'] = $method;
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1];
       
        // generate the POST data string
        $postData = http_build_query($req, '', '&');

        $sign = hash_hmac("sha512", $post_data, $secret);
 
        // generate the extra headers
        $headers = array(
                'Sign: '.$sign,
                'Key: '.$key,
        );
 
        // our curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; Cryptsy API PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }

        curl_setopt($ch, CURLOPT_URL, $uri );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 
        // run the query
        $res = curl_exec($ch);

        if ($res === false){
			throw new Exception('Could not get reply: '.curl_error($ch));
        }

        $dec = json_decode($res, true);
        
        if (!$dec){ 
        	throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        }

        return $dec;
	}

	public function process( $method, $params = array() ){
		$params['method'] => $method;
		return $this->_process( 'http://pubapi.cryptsy.com/api.php', array(), $params );
	}

	public function processPublic( $methodName, $params = array() ){
		$params['method'] => $method;
		return $this->_process( 'https://api.cryptsy.com/api', $params, array() );
	}


}