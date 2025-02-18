<?php
    require_once 'MarketManager.php';
    //require_once 'TabdealAPI.php';
    
    if(isset($_POST['AcountType']))
    {
        $acountType = $_POST['AcountType'];
        //$acountType = 'Data';
        try{
            $acnt  = new Acount();
            if($acountType === 'Data'){
                $ans['commission'] = $acnt->comission();
                $ret = [];
                foreach($acnt->assets() as $aset)
                {
                    
                    $ast['symbol'] = $aset->asset;
                    $ast['freeze'] = $aset->freeze;
                    $ast['qty'] = $aset->freeze + $aset->free;
                    if($aset->asset === "USDT"){
                        $ast['usdtPercision'] = false;
                        $ast['irtPercision'] = MarketManager::instance()->quoteAssetPrecision($aset->asset. "_IRT");
                    }
                    else if($aset->asset === "IRT"){
                        $ast['usdtPercision'] = 4;
                        $ast['irtPercision'] = 0;
                    }
                    else{
                        $ast['usdtPercision'] = MarketManager::instance()->quoteAssetPrecision($aset->asset. "_USDT");
                        $ast['irtPercision'] = MarketManager::instance()->quoteAssetPrecision($aset->asset. "_IRT");
                    }
                    $ret [] = $ast;
                }
                $ans['asset'] = $ret;
                echo json_encode($ans);
            }
        }
        catch(Exception $e){
            $ans['commission'] = -1;
            $ans['error'] = $e->getMessage();
            echo json_encode($ans);
        }
    }
    else if(isset($_POST['OpenOrders'])){
        $ans["count"] = 0;
        $openOrders = $_POST['OpenOrders'];
        try{
            $api  = new TabdeaAPI();
            if($openOrders === 'Data'){
                $ans["orders"] = json_decode($api->getOpenOrders());
                $ans["count"] = count($ans["orders"]);
            }
            echo json_encode($ans);
        }
        catch(Exception $e){
            $ans["count"] = -1;
            $ans["error"] = $e->getMessage();
            echo json_encode($ans);
        }
    }
    else if(isset($_POST['CancelOrder'])){
        $ans["count"] = 1;
        $orderId = $_POST['CancelOrder'];
        $symbol = $_POST['Symbol'];
        try{
            $api  = new TabdeaAPI();
            //if($openOrders === 'Data'){
             $ans["order"] = json_decode($api->cancelOrder($symbol, $orderId));// getOpenOrders());
                ///$ans["count"] = count($ans["orders"]);
            //}
            echo json_encode($ans);
        }
        catch(Exception $e){
            $ans["count"] = -1;
            $ans["error"] = $e->getMessage();
            echo json_encode($ans);
        }
    }
    // $symbol = "LUNC_USDT";
    // $api  = new TabdeaAPI();
    // $ans = $api->newOrder($symbol, 2000000, 0.0003, 0.0, "SELL", "LIMIT");
    // echo $ans;
    //echo ($api->cancelOrder($symbol, 1162722465));
    // $acnt = new Acount();
    // echo $acnt->comission();
    // var_dump($acnt->assets());
    class Acount
    {
        private static $fpath = __DIR__ . "/../../data/Acount.json";
        private $_acount = null;
        public function __construct()
        {
            try{
                // $lastts = Settings::instance()->get('AcountLastUpdate');
                // if($lastts)
                // {
                //     if(Settings::instance()->getCurrentTimestamp() - $lastts < 24 * 60 * 60 * 1000)
                //     {
                //         if(file_exists(self::$fpath))
                //         {
                //             $statisticsJson = file_get_contents(self::$fpath);
                //             $this->_acount = json_decode($statisticsJson);
                //         }
                //         else                
                //         {
                //             $this->getAcount();
                //         }
        
                //     }
                //     else
                //     {
                    //     $this->getAcount();
                    // }
                // }
                // else
                {
                    $this->getAcount();
                }
            }
            catch(Exception $e)
            {
                throw new Exception($e->getMessage());
            }
        }
        private function getAcount()
        {
            try
            {
                $tapi = new TabdeaAPI();
                $jsdata = $tapi->acount(); 
                file_put_contents(self::$fpath, $jsdata);
                $this->_acount = json_decode($jsdata);
                Settings::instance()->set('AcountLastUpdate', $tapi->getTimestamp());
            }
            catch(Exception $e)
            {
                echo 'can not read acount from tabdeal!';
                return false;
            }

        }
        public static function refresh()
        {
            unlink(self::$fpath);
        }
        public function getFullAcount()
        {
            return $this->_acount;
        }
        public function assets()
        {
            $ret = [];
            foreach($this->_acount->balances as $blnc)
            {
                if(($blnc->free > 0.0 or $blnc->freeze > 0.0))// && $blnc->asset != 'BABYDOGE')
                {
                    $ret[] = $blnc;
                }
            }
            return $ret;
        }
        public function comission()
        {
            $temp [] = $this->_acount->makerCommission;// ["makerCommission"];
            $temp [] = $this->_acount->takerCommission;
            return max($temp) / 100.0;
        }

    }

?>