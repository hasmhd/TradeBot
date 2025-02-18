<?php
//$test = new TabdeaAPI();
//echo $test->servertime();
//echo $test->asksBids("BTCUSDT");
//echo $test->newOrder("SHIBIRT", 2000000.0, 0.4750, 0.0, "BUY", "LIMIT");
//echo $test->newOrder("SHIBIRT", 2000000.0, 0.4090, 0.0, "SELL", "LIMIT");
//

//echo $test->getOpenOrders("SHIBIRT");
//echo $test->cancelOrder("SHIBIRT", 1105890166);
//echo $test->getAllOrders("SHIBIRT");
//echo $test->getMyTrades("SHIBIRT");
//echo $test->trades("BTC_USDT", 500);
//echo $test->allMarkets();
//echo $test->asset();
class TabdeaAPI{
    //'WJCGOVYKX37OGDBLV4HA'
    
    private $apiKey = '';
    private $apiSecret = '';

    public function __construct()
    {

        date_default_timezone_set("Iran");
        $env = parse_ini_file(__DIR__ . '/.env');
        $this->apiKey = $env["API_KEY"];
        $this->apiSecret = $env["API_SECRET"];

    }
    public function getTimestamp(){
        return  floor(microtime(true) * 1000);
    }
    public function getSignature($data, $timed = false){
        $tempstr = http_build_query($data);
        if($timed == true){
            if($tempstr != '')
                $tempstr .= '&timestamp=' . $this->getTimestamp();
            else
                $tempstr = 'timestamp=' . $this->getTimestamp();
        }
        return hash_hmac("sha256", $tempstr, $this->apiSecret);
    }
    public function newOrder($symbol, $quantity, $price = 0.0, $stopPrice = 0.0, $side = "SELL", $type= "MARKET"  ){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        $params['quantity'] = $quantity;
        $params['type'] = $type;
        $params['side'] = $side;
        if($type === "LIMIT"){
            $params['price'] = $price;
        }
        if($type === "STOP_LOSS_LIMIT"){
            $params['stopPrice'] = $stopPrice;
        }
        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        //$content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "POST",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
              'content' => http_build_query($params) 
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/api/v1/order' /* . $content */, false, $context);
          return $data;
    }
    public function getOrder($symbol, $orderId  ){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        if(strpos($orderId, "HMTID_") === false){
            $params['orderId'] = $orderId;    
        }
        else{
            $params['origClientOrderId'] = $orderId;
        }
        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );           
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/order?' . $content, false, $context);
          return $data;
    }
    public function getOpenOrders($symbol = null){
        if($symbol != null){
            if(strpos($symbol, "_") === false){
                $params['symbol'] = $symbol;    
            }
            else{
                $params['tabdealSymbol'] = $symbol;    
            }
        }
        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/openOrders?' . $content, false, $context);
          return $data;
    }
    public function cancelOrder($symbol, $orderId){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        if(strpos($orderId, "HMTID_") === false){
            $params['orderId'] = $orderId;    
        }
        else{
            $params['origClientOrderId'] = $orderId;
        }
        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "DELETE",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );           
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/api/v1/order?' . $content, false, $context);
          return $data;
    }
    public function getAllOrders($symbol, $startTime = null, $endTime = null, $limit = null ){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        if($startTime !== null){
            $params['startTime'] = $startTime;    
        }
        if($endTime !== null){
            $params['endTime'] = $endTime;    
        }
        if($limit !== null){
            $params['limit'] = $limit;    
        }

        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );           
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/allOrders?' . $content, false, $context);
          return $data;
    }
    public function cancelAllOpenOrders($symbol){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "DELETE",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/api/v1/openOrders?' . $content, false, $context);
          return $data;
    }
    public function getMyTrades($symbol, $startTime = null, $endTime = null, $limit = null ){
        if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        if($startTime !== null){
            $params['startTime'] = $startTime;    
        }
        if($endTime !== null){
            $params['endTime'] = $endTime;    
        }
        if($limit !== null){
            $params['limit'] = $limit;    
        }

        //$ctstamp = $this->getTimestamp();
        $params['timestamp'] = $this->getTimestamp();
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );           
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/myTrades?' . $content, false, $context);
          return $data;
    }
    public function acount(){
        try{
        $ctstamp = $this->getTimestamp();
        $params = ['timestamp' => $ctstamp];
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/account?' . $content, false, $context);
          return $data;
        }
        catch(Exception $e){
            throw new Exception("error in fetch data from tabdeal.org!");
        }
    }
    public function newListenkey()
    {
        $ctstamp = $this->getTimestamp();
        $params = ['timestamp' => $ctstamp];
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "POST",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/api/v1/userDataStream?' . $content, false, $context);
          return $data;

    }
    public function renewListenkey($liskey)
    {
        $ctstamp = $this->getTimestamp();
        $params = ['listen_key' => $liskey];
        $params = ['timestamp' => $ctstamp];
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "PUT",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/api/v1/userDataStream?' . $content, false, $context);
          return $data;

    }
    public function asksBids($symbol, $limit = 10){
         if(strpos($symbol, "_") === false){
            $params['symbol'] = $symbol;    
        }
        else{
            $params['tabdealSymbol'] = $symbol;    
        }
        $params['limit'] = $limit;
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/depth?' . $content, false, $context);
          return $data;

    }
    public function allMarkets($symbol = null, $limit = 1000){
        if($symbol !== null){
            if(strpos($symbol, "_") === false){
                $params['symbol'] = $symbol;    
            }
            else{
                $params['tabdealSymbol'] = $symbol;    
            }
        }
        $params['limit'] = $limit;
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/exchangeInfo?' . $content, false, $context);
          return $data;

    }
    public function trades($symbol, $limit = 20, $from = 0){
        if(strpos($symbol, "_") === false){
           $params['symbol'] = $symbol;    
       }
       else{
           $params['tabdealSymbol'] = $symbol;    
       }
       if($from !== 0) $params['from'] = $from;
       else $params['limit'] = $limit;
       $content = http_build_query($params);
       $options = array(
           'http'=>array(
             'method'  => "GET",
           )
         );
         $context=stream_context_create($options);
         $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/trades?' . $content, false, $context);
         return $data;

    }
    public function asset($asset = null){
        if($asset !== null){
            $params['asset'] = $asset;    
        }
        $ctstamp = $this->getTimestamp();
        $params = ['timestamp' => $ctstamp];
        $params['signature'] = $this->getSignature($params);//, true);
        $content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "GET",
              'header'  => "X-MBX-APIKEY: " . $this->apiKey, //."\r\n"
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.tabdeal.ir/r/api/v1/asset/get-funding-asset?' . $content, false, $context);
          return $data;
    }
    public function servertime(){
       //
       $options = array(
           'http'=>array(
             'method'  => "GET",
           )
         );
         $context=stream_context_create($options);
         $data=file_get_contents('https://api1.tabdeal.org/r/api/v1/time' , false, $context);
         $temp = json_decode($data);
         return $temp->serverTime;

    }
}
?>