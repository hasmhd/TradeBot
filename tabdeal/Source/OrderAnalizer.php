
    <?php
    require_once 'TradeAnalaizer.php';
    //require 'MarketManager.php';

    //$oa = new OrderAnalizer("SHIB_IRT");
    //var_dump($oa);
    class OrderAnalizer
    {
        //private $jsdata = null;
        public $symbol;
        public $maxBuyPrice = 0.0;
        public $buyWeight = 0.0;
        public $minSellPrice = 9999999999999.9999;
        public $sellWeight = 0.0;
        public function __construct($symbol, $jsdata = null)
        {
            $this->symbol = $symbol;
            if($jsdata === null)
            {
                $tapi = new TabdeaAPI();
                $jsdata = $tapi->asksBids($this->symbol, 10);
            }
            //$this->jsdata = $jsdata;
            $this->getData($jsdata);
        }
        private function getData($jsdata)
        {
            $totalBCount = 0.0;
            $totalSCount = 0.0;

            $temps = json_decode($jsdata);
            $temp = 0.0;
            foreach($temps->bids as $bid) 
            {
                if($bid[0] > $this->maxBuyPrice){
                    $this->maxBuyPrice = $bid[0];
                }
                $totalBCount += $bid[1];
                $temp += ($bid[1] * $bid[0]);
            }
            $this->buyWeight = round($temp / $totalBCount, MarketManager::instance()->quoteAssetPrecision($this->symbol));
            $temp = 0.0;
            foreach($temps->asks as $ask) 
            {
                if($ask[0] < $this->minSellPrice){
                    $this->minSellPrice = $ask[0];
                }
                $totalSCount += $ask[1];
                $temp += $ask[1] * $ask[0];
            }
            $this->sellWeight = round($temp / $totalSCount, MarketManager::instance()->quoteAssetPrecision($this->symbol));

        }

    }
?>