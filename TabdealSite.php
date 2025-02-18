<?php
    //require 'PageScraper.php';
    require 'Tabdeal\Source\tabdealAPI.php';
    //$test = new TabdealSite();
    //var_dump($test->trades(["BTC_USDT"])); //["BTC_USDT"]
    //var_dump($test->price());
    class TabdealSite
    {
        private $tapi = null;
        //private $apiAddress = 'https://api.tabdeal.org:8443';
        public $markets = null;
        public $curency = null;
        public $info = null;
        
        public function __construct()
        {
            //$this->casper = $casper;
            date_default_timezone_set("Iran");
            $this->tapi = new TabdeaAPI();
            $this->makeMarkets();
            //$this->makeCurency();
        }
        public function marketID($marketSymbol)
        {

            $market = $this->markets[$marketSymbol];
            if($market) return $market->market_id;
            return -1;
        }
        private function makeCurency()
        {
            if($this->markets != null){
                $this->curency = [];
                foreach($this->markets as $market){
                    $sym = $market->tabdealSymbol;

                    if (!in_array($sym,$this->curency, TRUE)){
                        $this->curency[$sym] = $market->first_currency;
                    }
                    $sym = $market->second_currency->symbol;
                    if (!in_array($sym,$this->curency, TRUE)){
                        $this->curency[$sym] = $market->second_currency;
                    }
                }
            }
        }        
        public function makeMarkets()
        {
            $statisticsJson = "";
            $fpath = __DIR__ . "/api_output/r_markets.json";
            if(file_exists($fpath)){
                //echo "make markets!";
                $statisticsJson = file_get_contents($fpath);    
            }else{
                $statisticsJson = $this->tapi->allMarkets(); // file_get_contents($this->apiAddress . '/r/markets');
                file_put_contents($fpath, $statisticsJson);
            }
            $temps = json_decode($statisticsJson);
            if($temps != null){
                foreach($temps as $market){
                    $this->markets[$market->tabdealSymbol] = $market;
                }
            }
        }
        public function isMarketExist($tabdealSymbol)
        {
            return array_key_exists($tabdealSymbol, $this->markets);
        }
        public function orderJson($marketSymbol)
        {
            $orderJson = $this->tapi->asksBids($marketSymbol, 20);
            return $orderJson;
        }
        public function bids($marketSymbol, $count = 0.0, $orderJson = null, $money = 0.0)
        {
            $retArray = [];
            if($orderJson == null)
                $orderJson = $this->orderJson($marketSymbol);
            if($orderJson != null){
                $temps = json_decode($orderJson);
                if($temps != null) {
                    if($count === 0.0) {
                        if($money === 0.0){
                            return $temps->bids;
                        } else{
                            foreach($temps->bids as $bid) {
                                if($money > 0.0) { 
                                    $retArray[] = $bid;
                                    $money -= ($bid[1] * $bid[0]);
                                }
                                else break;
                            }
                        }
                    }
                    else {
                        foreach($temps->bids as $bid) {
                            if($count > 0.0) { 
                                $retArray[] = $bid;
                                $count -= $bid[1];
                            }
                            else break;
                        }
                    }
                }
            }
            return $retArray;
        }
        public function sale($marketSymbol, $count, $orderJson = null, $echoOff = true)
        {
            $inputCount = $count;
            $totalPrice = 0.0;
            $bids = $this->bids($marketSymbol, $count, $orderJson);
            foreach($bids as $bid){
                if($count <= $bid[1]){
                    $totalPrice += $count * $bid[0];
                    break;
                }else{
                    $totalPrice += $bid[1] * $bid[0];
                    $count -= $bid[1];
                }
            }
            $karmozd = /* $this->markets[$marketSymbol]->market_commission */ 0.004 * $totalPrice;
            $tempret = $totalPrice - $karmozd;
            if(!$echoOff){
                echo "\r\n" . date('H:i:s') . " $marketSymbol sale : $inputCount => $tempret";
            }

            return $tempret;
        }
        public function saleMoney($marketSymbol, $money, $orderJson = null, $echoOff = true)
        {
            $count = 0.0;
            $tPrice = $money / (1.0 - 0.004);// $this->markets[$marketSymbol]->market_commission);
            $bids = $this->bids($marketSymbol, 0.0, $orderJson, $money);
            foreach($bids as $bid){
                if($tPrice <= $bid[1] * $bid[0]){
                    $tPrice -= $bid[1] * $bid[0];
                    $count += $tPrice / $bid[0];
                    break;
                }else{
                    $tPrice -= $bid[1] * $bid[0];
                    $count += $bid[1];
                }
            }
            if(!$echoOff){
                echo "\r\n" . date('H:i:s') . " $marketSymbol sale : $count => $money";
            }

            return $count;
        }
        public function asks($marketSymbol, $money = 0.0, $orderJson = null, $amount = 0.0)
        {
            $retArray = [];
            if($orderJson == null)
                $orderJson = $this->orderJson($marketSymbol);
            if($orderJson != null){
                $temps = json_decode($orderJson);
                if($temps != null) {
                    if($money === 0.0){ 
                        if($amount === 0.0){
                            return $temps->asks;
                        } else{
                            foreach($temps->asks as $ask) {
                                if($amount > 0.0) { 
                                    $retArray[] = $ask;
                                    $amount -=  $ask[1];
                                }
                                else break;
                            }
                        }
                    }
                    else {
                        foreach($temps->asks as $ask) {
                            if($money > 0.0) { 
                                $retArray[] = $ask;
                                $cnt = $money / $ask[0];
                                if($cnt > $ask[1])
                                    $money -= ($ask[1] * $ask[0]);
                                else
                                    $money = 0.0;
                            }
                            else break;
                        }
                    }
                }
            }
            return $retArray;
        }
        public function buy($marketSymbol, $money, $orderJson = null, $echoOff = true)
        {
            $inputMoney = $money;
            $count = 0.0;
            $asks = $this->asks($marketSymbol, $money, $orderJson);
            foreach($asks as $ask){
                if($money > 0.0){
                    $cnt = $money / $ask[0];
                    if($cnt > $ask[1]){
                        $count += $ask[1];
                        $money -= $ask[1] * $ask[0];                        
                    }else{
                        $count += $cnt;
                        $money = 0.0;//-= $cnt * $ask[0];
                        break;
                    }
                }
            }
            $karmozd = /* $this->markets[$marketSymbol]->market_commission */ 0.004 * $count;
            $tempret = $count - $karmozd;
            if(!$echoOff){
                echo "\r\n" . date('H:i:s') . " $marketSymbol buy : $tempret => $inputMoney";
            }
            return $tempret;
        }
        public function buyAmount($marketSymbol, $amount, $orderJson = null, $echoOff = true)
        {
            $realAmount = $amount / (1.0 - 0.004);// $this->markets[$marketSymbol]->market_commission);
            $money = 0.0;
            $asks = $this->asks($marketSymbol, 0.0, $orderJson, $realAmount);
            foreach($asks as $ask){
                if($realAmount > 0.0){
                    if($realAmount >= $ask[1]){
                        $realAmount -= $ask[1];
                        $money += $ask[1] * $ask[0];                        
                    }else{
                        $money += $realAmount * $ask[0];
                        break;
                    }
                }
            }
            if(!$echoOff){
                echo "\r\n" . date('H:i:s') . " $marketSymbol buy : $amount => $money";
            }
            return $money;
        }
        
        public function marketsInfo($symbols = null)
        {
            $retArray = [];
            $statisticsJson = $this->tapi->allMarkets();
            // file_get_contents($this->apiAddress . '/plots/market_information/');
            $temps = json_decode($statisticsJson);
            if($temps != null){
                foreach($temps as $market_info){
                    $this->info[$market_info->tabdealSymbol] = $market_info;
                    if($symbols == null){
                        $retArray[$market_info->tabdealSymbol] = $market_info;
                    }
                    else{
                        if(in_array($market_info->tabdealSymbol, $symbols)){
                            $retArray[$market_info->tabdealSymbol] = $market_info;
                        }
                    }
                }
            }
            return $retArray;
        }
        public function trades($symbols = null, $count = 20)
        {
            $retArray = [];
            if($symbols != null)
            {
                foreach($symbols as $symbol){
                    $statisticsJson = $this->tapi->trades($symbol, $count);
                    $retArray[$symbol] = json_decode($statisticsJson);
                }
            }
            else{
                foreach($this->markets as $market){
                    $statisticsJson = $this->tapi->trades($market->tabdealSymbol, $count);
                    $retArray[$market->tabdealSymbol] = json_decode($statisticsJson);
                }
            }
            return $retArray;            
        }
        public function tradesForOnMarket($symbol, $count = 20)
        {
            $statisticsJson = $this->tapi->trades($symbol, $count);
            return json_decode($statisticsJson);
        }
        public function price($symbols = null)
        {
            $retArray = [];
            $trades = $this->trades($symbols, 1);
            //print_r($trades);
            foreach($trades as $symbol => $trade){
                $retArray[$symbol] = $trade[0]->price;
                
            }
            return $retArray;
        }
        public function beep ($int_beeps = 1) 
        {
            $string_beeps = "";
            for ($i = 0; $i < $int_beeps; $i++): $string_beeps .= "\x07"; endfor;
            isset ($_SERVER['SERVER_PROTOCOL']) ? false : print $string_beeps;
        }
        public function saveCurrencyIcon()
        {
            if($this->curency != null)
            {
                foreach($this->curency as $symbol => $curreny)
                {
                    $iconFile = 'https://tabdeal.org/coin-icons/' . $symbol .'-icon.svg';
                    $content = file_get_contents($iconFile);
                    file_put_contents($this->currencyIcon($symbol), $content);
                }
            }
        }
        public function currencyIcon($symbol)
        {
            return  __DIR__ . "/tabdeal/images/" . $symbol . "-icon.svg";
        }
    }
?>