<?php
    require_once 'TabdealAPI.php';
    require_once 'Settings.php';
    
    //var_dump(MarketManager::instance()->getMarket("SHIB_IRT"));
    
    class MarketManager
    {
        private $fpath = __DIR__ . "/../../data/markets.json";
        private $markets = null;
        private static $marketManager = null;
        private function __construct()
        {
            $lastts = Settings::instance()->get('MarketsLastUpdate');
            if($lastts)
            {
                if(Settings::instance()->getCurrentTimestamp() - $lastts < 12 * 60 * 60 * 1000)
                {
                    if(file_exists($this->fpath))
                    {
                        $statisticsJson = file_get_contents($this->fpath);
                        $this->extractData($statisticsJson);
                    }
                    else                
                    {
                        $this->getAllMarkets();
                    }
    
                }
                else
                {
                    $this->getAllMarkets();
                }
            }
            else
            {
                $this->getAllMarkets();
            }
        }
        public static function instance()
        {
            if(self::$marketManager === null)
            {   
                self::$marketManager = new MarketManager();
            }
            return self::$marketManager;
        }
        public function markets()
        {
            return $this->markets;
        }
        public function getMarket($symbol)
        {
            $market = null;
            if(!array_key_exists($symbol, $this->markets))
            {
                // try
                // {
                //     $tapi = new TabdeaAPI();
                //     $mjdata = $tapi->allMarkets($symbol);
                //     $market = json_decode($mjdata);
                //     $this->markets[$symbol] = $market;
                // }
                // catch(Exception $e)
                // {
                //     //echo 'error in get market with symbol : ' . $symbol;
                //     return null;
                // }
                return false;

            }
            return $this->markets[$symbol];
            
        }
        public function getAllMarkets()
        {
            try
            {
                $tapi = new TabdeaAPI();
                $jsdata = $tapi->allMarkets(); 
                file_put_contents($this->fpath, $jsdata);
                $this->extractData($jsdata);
                Settings::instance()->set('MarketsLastUpdate', $tapi->getTimestamp());
            }
            catch(Exception $e)
            {
                //echo 'can not read all markets from tabdeal!';
                return false;
            }

        }
        private function extractData($jsdata)
        {
            $temps = json_decode($jsdata);
            $this->markets = [];
            foreach($temps as $market)
            {
                $this->markets[$market->tabdealSymbol] = $market;
            }
        }
        public function baseAssetPrecision($symbol)
        {
            $mrkt = $this->getMarket($symbol);
            if($mrkt)
            {
                return $mrkt->baseAssetPrecision;
            }
            else return false;
        }
        public function quoteAssetPrecision($symbol)
        {
            $mrkt = $this->getMarket($symbol);
            if($mrkt)
            {
                return $mrkt->quoteAssetPrecision;
            }
            else return false;
        }
        public function baseAssetSymbol($symbol)
        {
            // if(str_ends_with(strtoupper($symbol), "IRT") === true)
            //     return "IRT";
            // else if(str_ends_with(strtoupper($symbol), "USDT") === true)
            //     return "USDT";
            $mrkt = $this->getMarket($symbol);
            if($mrkt)
            {
                return $mrkt->baseAsset;
            }
            return false;
        }
        public function quoteAssetSymbol($symbol)
        {
            // if(strpos($symbol, "_") !== false) {
            //     return  explode("_", $symbol)[0];
            // }
            // $find = null;
            // if(str_ends_with(strtoupper($symbol), "IRT") === true)
            //     $find = "IRT";
            // else if(str_ends_with(strtoupper($symbol), "USDT") === true)
            //     $find = "USDT";
            // if($find != null){
            //     $result = preg_replace(strrev("/$find/"),"",strrev($symbol),1);
            //      return strrev($result);
            // }
            $mrkt = $this->getMarket($symbol);
            if($mrkt)
            {
                return $mrkt->quoteAsset;
            }
          return false;
        }
    }
?>