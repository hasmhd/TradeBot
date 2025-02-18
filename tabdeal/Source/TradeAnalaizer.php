<?php
    require_once 'MarketManager.php';
    // if(isset($_POST['marketSymbol'])){
    //     $symbol = $_POST['marketSymbol'];

    //     $ta = new TradeAnalizer($symbol);
    //     echo json_encode($ta);
    //     //  echo $ta->lastPrice() . "\n";
    //     //  var_dump($ta->getDay());
    //     //  var_dump($ta->getDay(1));
    // }
    //$ta = new TradeAnalizer("SHIBIRT");
    //echo $ta->getDiriction(5, true);
    //$tapi = new TabdeaAPI();
    //$data = file_get_contents(__DIR__ . "/../../data/Analize/SOLUSDT1000.json");// $tapi->trades("SOLUSDT", 1000);
    //$trades = json_decode($data);
    //  foreach($trades as $trade)   {
    //      echo gmdate("Y-m-d H:i:s ", $trade->time / 1000.0) . " --> " . $trade->price ." --> ". $trade->qty ." --> ". $trade->isBuyerMaker."\n"; 
    //          }
    //file_put_contents(__DIR__ . "/../../data/Analize/SOLUSDT1000.json", $data);
    //$symb = "BABYDOGE_IRT"; //"CKB_IRT"; // 
    // $symb = "GROK_IRT"; 
    // $tapi = new TradeData($symb);
    //createImageChart($tapi->lastTrades, "trades.jpeg");
    //createImageChart($tapi->vertixTrades, "vertices.jpeg");
    // echo "\n------------------- min gain >= 0.5% ------------------";
    // $vertices = $tapi->vertices;//(0.005, true);
    // $sood = tradeTest($symb, $vertices, 0.002, false);
    // echo "\ngain = $sood %";
    // $predictor = new VertixPredictor($vertices);
    // $next = $predictor->predict();
    // echo "\nlast trade time : " . date("H:i:s", $tapi->lasttrade()->time / 1000) . " price : " . $tapi->lasttrade()->price;
    // echo "\nlast vertix time : " . date("H:i:s", end($vertices)->time / 1000) . " price : " . end($vertices)->price;
    // echo "\nnext min time : " . date("H:i:s", $next->mintime / 1000) . " price : " . $next->minprice;
    // echo "\nnext max time : " . date("H:i:s", $next->maxtime / 1000) . " price : " . $next->maxprice;
    
    //echo "\n------------------- min gain = 1.0% ------------------";
    //$sood = tradeTest($symb, $tapi->vertices(0.01), 0.002, false);
    //echo "\ngain = $sood %";
    //trait Chart{
    function tradeTest($symbol, $trades, $comission = 0.002, $echoOff = true)
    {
        $retPercent = 0.0;
        $basesymbol = MarketManager::instance()->baseAssetSymbol($symbol);
        $quotesymbol = MarketManager::instance()->quoteAssetSymbol($symbol);
        $val0 = $val = 0.0;
        $qty = 0.0;
        $sym = $quotesymbol;
        $cont = count($trades);
        $trd1 = $trd2 = null;
        if($echoOff !== true)
        echo "\nvertix start time : " . date("d-H:m:s", $trades[0]->time / 1000);
        for($i = 0; $i < $cont - 1; $i++){
            $trd1 = $trades[$i];
            $trd2 = $trades[$i + 1];
            if($trd2->price > $trd1->price){ // buy
                if($i == 0){
                    $val0 = $val = $trd1->price;
                    $sym = $quotesymbol;
                }
                $qty = ($val / $trd1->price) * (1.0 - $comission);
                $sym = $basesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> buy $qty($basesymbol) at $trd1->price  ";
            }
            else { // sell
                if($i == 0){
                    $val0 = $trd1->price;
                    $qty = 1.0;
                    $sym = $basesymbol;
                }
                $val = ($qty * $trd1->price) * (1.0 - $comission);
                $sym = $quotesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> sell $qty($basesymbol) at $trd1->price  ";
            }

        }
        if($trd1 != null && $trd2 != null){
            if($trd2->price > $trd1->price){ // sell
                $val = ($qty * $trd2->price) * (1.0 - $comission);
                $sym = $quotesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> sell $qty($basesymbol) at $trd2->price  ";
                $retPercent = round(100.0 * ($val - $val0) / $val0, 3);
                //MarketManager::instance()->quoteAssetPrecision());
            }
            else{ // buy
                $qty = ($val / $trd2->price) * (1.0 - $comission);
                $sym = $basesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> buy $qty($basesymbol) at $trd2->price  ";
                $retPercent = round(100.0 * ($qty - 1.0), 3);
                //MarketManager::instance()->baseAssetPrecision());
            }
        }
        return $retPercent;
    }
    function createImageChart($trades, $jpegname = null, $showgrid = true, $percision = 2)
    {
        $font = 6;
        $textheight = 15; $axisthickness = 3; $gridthickness = 1;
        $milestonethickness = 2; $chartlinethickness = 2;
        $imgwidth = 800; $rightpanelwidth = 70;
        $imgheight = 600; $buttompanelheight = 70;
        $miny = PHP_FLOAT_MAX; $maxy = 0.0;
        $minx = PHP_FLOAT_MAX; $maxx = 0.0;
        foreach($trades as $trade)
        {
            if($trade->time > $maxx) $maxx = $trade->time;
            if($trade->time < $minx) $minx = $trade->time;
            if($trade->price > $maxy) $maxy = $trade->price;
            if($trade->price < $miny) $miny = $trade->price;
        }
        $chartheight = $imgheight - $buttompanelheight;
        $chartwidth = $imgwidth - $rightpanelwidth;
        $img = imagecreate($imgwidth, $imgheight);
        $backcolor = imagecolorallocate($img, 0,0,0);
        imagefill($img, 0,0, $backcolor);
        $axiscolor = imagecolorallocate($img, 255,255,255);
        imagesetthickness($img, $axisthickness);
        imageline($img,$chartwidth, 0, $chartwidth, $chartheight, $axiscolor);
        imageline($img, $chartwidth, $chartheight, 0, $chartheight, $axiscolor);
        $gridlinecolor = imagecolorallocate($img, 125,125,125);
        ///x axis milestones (time).
        if(count($trades) <= ($chartwidth / $textheight))
        {
            foreach($trades as $trade){
                $x = intval(($trade->time - $minx) * $chartwidth / ($maxx - $minx));
                imagesetthickness($img, $milestonethickness);
                imageline($img, $x, $chartheight, $x, $chartheight - 2, $axiscolor);
                if($showgrid == true){
                    imagesetthickness($img, $gridthickness);
                    imagedashedline($img, $x, $chartheight, $x, 0, $gridlinecolor);
                }
                imagestringup($img, $font, $x , $imgheight, date("H:i:s", $trade->time / 1000), $axiscolor);
            }
        }
        else{
            for($x = 0; $x <= $chartwidth; $x += $textheight){
                imagesetthickness($img, $milestonethickness);
                imageline($img, $x, $chartheight, $x, $chartheight - 2, $axiscolor);
                if($showgrid == true){
                    imagesetthickness($img, $gridthickness);
                    imagedashedline($img, $x, $chartheight, $x, 0, $gridlinecolor);
                }
                $time = $x * ($maxx - $minx) / $chartwidth + $minx;
                imagestringup($img, $font, $x , $imgheight, date("H:i:s", $time / 1000), $axiscolor);
            }
        }
        ///y axis milestones (price).
        for($y = $chartheight; $y >= 0; $y -= $textheight){
            imagesetthickness($img, $milestonethickness);
            imageline($img, $chartwidth, $y, $chartwidth - 2, $y, $axiscolor);
            if($showgrid == true){
                imagesetthickness($img, $gridthickness);
                imagedashedline($img, $chartwidth, $y, 0, $y, $gridlinecolor);
            }
            $price = ($chartheight  - $y) * ($maxy - $miny) / $chartheight + $miny;
            imagestring($img, $font, $chartwidth, $y, round($price, $percision), $axiscolor);
        }
        ///chart line 
        imagesetthickness($img, $chartlinethickness);
        $chartlinecolor = imagecolorallocate($img, 255, 0, 0);
        for($i = 0; $i < count($trades) - 1; $i++){
            $x1 = intval(($trades[$i]->time - $minx) * $chartwidth / ($maxx - $minx));
            $x2 = intval(($trades[$i + 1]->time - $minx) * $chartwidth / ($maxx - $minx));
            $y1 = intval($chartheight - (($trades[$i]->price - $miny) * $chartheight / ($maxy - $miny)));
            $y2 = intval($chartheight - (($trades[$i + 1]->price - $miny) * $chartheight / ($maxy - $miny)));
            imageline($img, $x1, $y1, $x2, $y2, $chartlinecolor);
        }
        //header("Content-type: image/jpeg");
        
        imagejpeg($img, ($jpegname === null) ? "test.jpeg": $jpegname);
        imagedestroy($img);
    }
    class Vertix
    {
        public $price;
        public $time;
        public function __construct($price = 0.0, $time = 0)
        {
            $this->price = $price;
            $this->time = $time;
        }
    }
    class Sarmaye
    {
        public $token;
        public $value;
        public $percision = 2;
    }
    class OLDTradeData
    {
        public $lastTrades = null;
        public $hseeds = null;
        public $dseeds = null;
        private function getHrStart($tstamp)
        { 
            return strtotime(date("Y-m-d H", $tstamp / 1000) . ":00:00") * 1000;
        }
        private function getDayStart($tstamp)
        {
            return strtotime(date("Y-m-d", $tstamp / 1000) . " 00:00:00") * 1000;
        }
        public function getHour($ago = 0)
        {
            return $this->hseeds->byIndex($ago);
        }
        public function getDay($ago = 0)
        {
            return $this->dseeds->byIndex($ago);
        }
        public function getLastMinute($min)
        {
            $now = Settings::instance()->getCurrentTimestamp();
            $st = $now - $min * 60000; 
            $tseed = new TradeSeed($st, $now - $st); 
            foreach($this->lastTrades as $trade)
            {
                $tseed->addTrade($trade);
            }
            return $tseed;
        }
        public function getStepAvrage($step, $justBuyerMakers = false)
        {
            $ans['count'] = count($this->lastTrades);
            $ans['prices'] = array();
            $ans['avrgs'] = array();
            $tcount = 0;
            for($i = 0; $i < $ans['count']; $i++)
            {
                $trade = $this->lastTrades[$ans['count'] - ($i + 1)];
                if($justBuyerMakers && !$trade->isBuyerMaker)
                {
                    continue;
                }
                $ans['prices'][$tcount] = $trade->price;
                $temp = 0.0;
                $cnt = 0;
                for($j = $tcount; $j > $tcount - $step && $j >= 0; $j--)
                {
                    $temp += $ans['prices'][$j];
                    $cnt++;
                }
                $ans['avrgs'][$tcount] = $temp / $cnt;
                $tcount++;
            }
            $ans['count'] = $tcount;
            return $ans;
        }
        public function getDiriction($step = 5, $justBuyerMakers = false)
        {
            $tcount = 0;
            $tp = array();
            for($i = 0; $i < count($this->lastTrades); $i++)
            {
                $trade = $this->lastTrades[$i];
                if($justBuyerMakers && !$trade->isBuyerMaker)
                continue;
                // if($tcount > 0 && $trade->price == $tp[$tcount - 1] )
                // continue;
                $tp[$tcount++] = $trade->price;
                if($tcount > $step)
                break;
            }
            $ans = '';
            for($i = $tcount - 1; $i > 0; $i--)
            {
                if($tp[$i] > $tp[$i - 1]) $ans .= "D";
                else if ($tp[$i] < $tp[$i - 1]) $ans .= "U";
                else  $ans .= "C";
            }
            return $ans;
        }
    }
    class TradeData
    {
        public $symbol;
        public $criticalPercent = 0.005;
        public $last = null; // Vertix
        public $max = null;  // Vertix
        public $min = null;   // Vertix
        public $vertices = null;
        //public $steps = null;
        
        public function __construct($symbol, $criticalPercent = 0.005)
        {
            //date_default_timezone_set("Iran");

            $limit = 1000;
            $this->symbol = $symbol;
            $this->criticalPercent = $criticalPercent;
            $fpath = __DIR__ . "/../../data/Analize/" . $this->symbol . ".json";
            try
            {
                if(file_exists($fpath))
                {
                    $jsdata = file_get_contents($fpath);
                    $temp = json_decode($jsdata);
                    
                    $this->last = $temp->last;
                    $this->max = $temp->max;
                    $this->min = $temp->min;
                    $this->vertices = $temp->vertices;

                    $this->getData($limit, $this->last->time);
                }
                else
                {
                    $this->initialize();
                }
                $this->saveData();
            }
            catch(Exception $e)
            {
                if(file_exists($fpath))
                {
                    unlink($fpath);
                }
                echo $e->getMessage();
            }
        }
        

        private function initialize()
        {
            $this->last = new Vertix();
            $this->max = new Vertix();
            $this->min = new Vertix(PHP_FLOAT_MAX);
            $this->vertices = [];
            //$this->steps = [];
            $this->getData(1000);
        }
        private function getData($limit = 50, $from = 0)
        {
            $tapi = new TabdeaAPI();
            $jsdata = $tapi->trades($this->symbol, $limit, $from);
            $tempLastTrades = json_decode($jsdata);
            
            foreach(array_reverse($tempLastTrades) as $trade)
            {
                if($trade->time > $this->last->time){
                    $this->verticesAddTrade($trade);
                    if($trade->price > $this->max->price)
                        $this->max = new Vertix($trade->price, $trade->time);
                    if($trade->price < $this->min->price)
                        $this->min = new Vertix($trade->price, $trade->time);
                }
            }
            $this->last =  new Vertix($tempLastTrades[0]->price, $tempLastTrades[0]->time);
        }
        private function saveData()
        {
            $fpath = __DIR__ . "/../../data/Analize/" . $this->symbol . ".json";
            $jsdata = json_encode($this);
            file_put_contents($fpath, $jsdata);
        }
        private function verticesAddTrade($trade)
        {
            $cnt = count($this->vertices);
            if($cnt == 0) $this->vertices [] = new Vertix($trade->price, 
                        $trade->time);
            else {
                $lastvertix = $this->vertices[$cnt - 1];
                $m2 = ($trade->price - $lastvertix->price) / $lastvertix->price;
                if($cnt == 1) {
                    if(abs($m2) >= $this->criticalPercent){
                        $this->vertices [] = new Vertix($trade->price, 
                                        $trade->time);
                        //$this->predictdirection = ($m2 < 0) ? 1 : -1;
                    }
                }
                else if($cnt > 1){
                    $beforlastvertix = $this->vertices[$cnt - 2];
                    $m1 = ($lastvertix->price - $beforlastvertix->price) / $beforlastvertix->price;
                    if($m1 * $m2 > 0){
                        $this->vertices[$cnt - 1] = new Vertix($trade->price, $trade->time);
                    }
                    else{
                        if(abs($m2) >= $this->criticalPercent){
                            $this->vertices [] = new Vertix($trade->price, $trade->time);
                            //$this->predictdirection = ($m2 < 0) ? 1 : -1;
                        }
                    }

                }
            }
            
        }

        public function lastPrice()
        {
            if($this->last !== null)
            {
                return $this->last->price;
            }
            else return false;
        }
        public function lasttrade()
        {
            if($this->last !== null) return $this->last;
            return false;

        }
        public function gain($count = 10, $comission = 0.002, $echoOff = true, $roundto = 3)
        {
            $retPercent = 0.0;$val0 = $val = 0.0;$stime = 0;$qty = 0.0;
            $cont = count($this->vertices);
            if($count < $cont) return false;
            $trd1 = $trd2 = null;
            if($echoOff !== true)
                echo "\nvertix start time : " . date("d-H:m:s", $this->vertices[0]->time / 1000);
            for($i = $cont - $count; $i < $cont - 1; $i++){
                $trd1 = $this->vertices[$i];
                $trd2 = $this->vertices[$i + 1];
                if($trd2->price > $trd1->price){ // buy
                    if($i == $cont - $count){
                        $val0 = $val = $trd1->price;
                        $stime = $trd1->time;
                    }
                    $qty = ($val / $trd1->price) * (1.0 - $comission);
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> buy $qty at $trd1->price  ";
                }
                else { // sell
                    if($i == $cont - $count){
                    $val0 = $trd1->price;
                    $qty = 1.0;
                    //$sym = $basesymbol;
                    $stime = $trd1->time;
                }
                $val = ($qty * $trd1->price) * (1.0 - $comission);
                //$sym = $quotesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> sell $qty at $trd1->price  ";
            }

            }
            if($trd1 != null && $trd2 != null){
                if($trd2->price > $trd1->price){ // sell
                    $val = ($qty * $trd2->price) * (1.0 - $comission);
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> sell $qty at $trd2->price  ";
                    $retPercent = round(100.0 * ($val - $val0) / $val0, $roundto);
                }
                else{ // buy
                    $qty = ($val / $trd2->price) * (1.0 - $comission);
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> buy $qty at $trd2->price  ";
                    $retPercent = round(100.0 * ($qty - 1.0), $roundto);
                }
                $difftime = round(($trd2->time - $stime) / 60000, 0); //mili second to minute
                $difftime = ($difftime < 1) ? 1 : $difftime;
                return round(($retPercent / $difftime), 5);

            }
            return false;
        }
        public function setps()
        {
            $steps = [];
            $steps [] = $this->min->price;
            foreach($this->vertices as $vertix){
                    $steps [] = $vertix->price;
            }
            $steps [] = $this->max->price;
            $steps = array_unique($steps);
            return sort($steps, SORT_NUMERIC | SORT_ASC);
        }
    }    
    class TradeSeed
    {
        public $startTime = 0;
        public $msDistance;
        public $totalQty = 0.0;
        public $count = 0;
        
        public $minTrade = null;
        public $maxTrade = null;
        public $openTrade = null;
        public $closeTrade = null;

        public $min = 999999999999.9999;
        public $max = 0.0;
        public $open = 0.0;
        public $close = 0.0;
        public $avr = 0.0;
        public function __construct($stime, $msdis = 3600000, $jsobject = null)
        {
            if($jsobject === null)
            {
                $this->startTime = $stime;
                $this->msDistance = $msdis;
            }
            else  
            {
                $this->startTime = $jsobject->startTime;
                $this->msDistance = $jsobject->msDistance;
                $this->totalQty = $jsobject->totalQty;
                $this->count = $jsobject->count;
                $this->min = $jsobject->min;
                $this->max = $jsobject->max;
                $this->open = $jsobject->open;
                $this->close = $jsobject->close;
                $this->minTrade = $jsobject->minTrade;
                $this->maxTrade = $jsobject->maxTrade;
                $this->openTrade = $jsobject->openTrade;
                $this->closeTrade = $jsobject->closeTrade;
                $this->avr = $jsobject->avr;

            }
        }
        public function addTrade($trade, $checkDistance = true)
        {
            if($trade->time >= $this->startTime)
            {
                if( !$checkDistance || ($checkDistance && $trade->time < $this->startTime + $this->msDistance))
                {
                    $this->count++;
                    if($trade->price > $this->max)                
                    {
                        $this->max = $trade->price;
                        $this->maxTrade = $trade;
                    }
                    if($trade->price < $this->min)                
                    {
                        $this->min = $trade->price;
                        $this->minTrade = $trade;
                    }
                    if($this->openTrade == null || ($this->openTrade != null &&  $trade->time < $this->openTrade->time)) {
                        $this->openTrade = $trade;
                        $this->open = $trade->price;
                    }
                    if($this->closeTrade == null || ($this->closeTrade != null &&  $trade->time > $this->closeTrade->time)) {
                        $this->closeTrade = $trade;
                        $this->close = $trade->price;
                    }
                    if($this->totalQty + $trade->qty > 0)
                        $this->avr = round((($trade->price * $trade->qty) + ($this->avr * $this->totalQty)) / ($this->totalQty + $trade->qty), 8);
                    $this->totalQty += $trade->qty;
                }
            }
        }
    }
    class GroupTradeSeed  
    {
        public $count = 0;
        public $stepMSec = 0;
        public $seeds = null;
        public $currentSeedStartTime = 0;
        public $lastSeedStartTime = 0;

        public function __construct($cnt, $sms, $stime, $jsobject = null)
        {
            if($jsobject === null)
            {
                $this->count = $cnt;
                $this->stepMSec = $sms;
                $this->currentSeedStartTime = $stime;
                $this->init();
            }
            else
            {
                $this->count = $jsobject->count;
                $this->stepMSec = $jsobject->stepMSec;
                $this->currentSeedStartTime = $jsobject->currentSeedStartTime;
                $this->lastSeedStartTime = $jsobject->lastSeedStartTime;
                $this->seeds = array();
                for($i = 0; $i < count($jsobject->seeds); $i++)
                {
                    $this->seeds[$i] = new TradeSeed(0, 0, $jsobject->seeds[$i]);
                }
            }
        }
        private function init()
        {
            $this->seeds = array();
            for($i = 0; $i < $this->count; $i++)
            {
                $this->seeds[$i] = new TradeSeed($this->currentSeedStartTime - ($i * $this->stepMSec), $this->stepMSec);
            }
            $this->lastSeedStartTime = $this->seeds[$this->count - 1]->startTime;
        }
        private function arrangSeeds()
        {
            $now1 = Settings::instance()->getCurrentTimestamp();
            $count1 = 1;
            $tempstart = $this->currentSeedStartTime;
            while($now1 > $this->currentSeedStartTime + $count1 * $this->stepMSec)
            {
                $count1++;
                $tempstart += $this->stepMSec;
            }
            $this->currentSeedStartTime = $tempstart;
            if($count1 >= $this->count - 1)
            {
                $this->init();
            }
            else if ($count1 > 1)
            {
                for($i = $this->count - 1; $i >= $count1; $i--)
                {
                    $this->seeds[$i] = $this->seeds[$i - $count1];
                }
                for($i = 0; $i < $count1; $i++)
                {
                    $this->seeds[$i] = new TradeSeed($this->currentSeedStartTime - ($i * $this->stepMSec), $this->stepMSec);                    
                }
                $this->lastSeedStartTime = $this->seeds[$this->count - 1]->startTime;
            }
        }
        public function addTrade($trade)
        {
            if($trade->time >= $this->lastSeedStartTime)
            {
                if($trade->time >= $this->currentSeedStartTime + $this->stepMSec )
                {
                    $this->arrangSeeds();
                }
                for($i = 0; $i < $this->count; $i++)
                {
                    $tmp = $this->seeds[$i]->startTime;
                    if($trade->time >= $tmp && $trade->time < $tmp + $this->stepMSec)
                    {
                        $this->seeds[$i]->addTrade($trade);
                        break;
                    }
                }
            }
        }
        public function byIndex($ago)
        {
            if($ago >= 0 && $ago < $this->count)
                return $this->seeds[$ago];
            return false;
        }
    }
    class TradeVertixes
    {

        private $maxprice = 0.0;
        private $minprice = 9999999999999.99;
        public $criticalPercent = 0.01;
        public $vertixTrades = array();
        //private $lastVertixCandidate = null;
        public $predictdirection = 0;

        //public function __construct($)
        public function __construct($revTrades, $justBuyerMakers = false, $criticalPercent = 0.01, $isFirstTime = true)
        {
            $this->criticalPercent = $criticalPercent;
            if($isFirstTime === true){
                $minIndex = -1; //$min = 999999999999.999;
                $maxIndex = -1; //$max = 0.0;
                //$lastVertixTrade = null;
                
                for($ind = 0; $ind < count($revTrades); $ind++){
                    if($justBuyerMakers === true && $revTrades[$ind]->isBuyerMaker === false)
                        continue;
                    if($revTrades[$ind]->price > $this->maxprice){
                        $this->maxprice = $revTrades[$ind]->price;
                        $maxIndex = $ind;
                    }
                    if($revTrades[$ind]->price < $this->minprice){
                        $this->minprice = $revTrades[$ind]->price;
                        $minIndex = $ind;
                    }
                }
                if($minIndex < $maxIndex)
                {
                    $startTradeIndex = $minIndex;
                    $this->predictdirection = 1;    
                }
                else{
                    $startTradeIndex = $maxIndex;
                    $this->predictdirection = -1;    
                }
                $this->vertixTrades = array();
                $this->vertixTrades [] = $revTrades[$startTradeIndex];
                
                for($ind = $startTradeIndex + 1; $ind < count($revTrades); $ind++)
                {
                    if($justBuyerMakers === true && $revTrades[$ind]->isBuyerMaker === false)
                        continue;
                    $this->verticesAddTrade($revTrades[$ind]);
                }
            }
            else{
                $this->vertixTrades = array();
                $cnt = count($revTrades);
                for($ind = 0; $ind < $cnt; $ind++){
                    if($revTrades[$ind]->price > $this->maxprice){
                        $this->maxprice = $revTrades[$ind]->price;
                    }
                    if($revTrades[$ind]->price < $this->minprice){
                        $this->minprice = $revTrades[$ind]->price;
                    }
                    $this->vertixTrades [] = $revTrades[$ind];
                }
                if($cnt >= 2){
                    if($this->vertixTrades[$cnt - 1]->price > $this->vertixTrades[$cnt - 2]->price)
                        $this->predictdirection = -1;
                    else $this->predictdirection = 1;

                }
            }
        }
        private function verticesAddTrade($trade)
        {
            $cnt = count($this->vertixTrades);
            if($cnt == 0) $this->vertixTrades [] = $trade;
            else {
                $lastvertix = $this->vertixTrades[$cnt - 1];
                $m2 = ($trade->price - $lastvertix->price) / $lastvertix->price;
                //if(abs($m2) >= $this->criticalPercent){
                    if($cnt == 1) {
                        if(abs($m2) >= $this->criticalPercent){
                            $this->vertixTrades [] = $trade;
                            $this->predictdirection = ($m2 < 0) ? 1 : -1;
                        }
                    }
                    else if($cnt > 1){
                        $beforlastvertix = $this->vertixTrades[$cnt - 2];
                        $m1 = ($lastvertix->price - $beforlastvertix->price) / $beforlastvertix->price;
                        if($m1 * $m2 > 0){
                            $this->vertixTrades[$cnt - 1] = $trade;
                        }
                        else{
                            if(abs($m2) >= $this->criticalPercent){
                                $this->vertixTrades [] = $trade;
                                $this->predictdirection = ($m2 < 0) ? 1 : -1;
                            }
                        }

                    }
                //}
            }
            
        }
        function gain($count = 10, $comission = 0.002, $echoOff = true, $roundto = 3)
        {
            $retPercent = 0.0;$val0 = $val = 0.0;$stime = 0;$qty = 0.0;
            $cont = count($this->vertixTrades);
            if($count < $cont) return false;
            $trd1 = $trd2 = null;
            if($echoOff !== true)
                echo "\nvertix start time : " . date("d-H:m:s", $this->vertixTrades[0]->time / 1000);
            for($i = $cont - $count; $i < $cont - 1; $i++){
                $trd1 = $this->vertixTrades[$i];
                $trd2 = $this->vertixTrades[$i + 1];
                if($trd2->price > $trd1->price){ // buy
                    if($i == $cont - $count){
                        $val0 = $val = $trd1->price;
                        $stime = $trd1->time;
                        //$sym = $quotesymbol;
                    }
                    $qty = ($val / $trd1->price) * (1.0 - $comission);
                    //$sym = $basesymbol;
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> buy $qty at $trd1->price  ";
                }
                else { // sell
                    if($i == $cont - $count){
                    $val0 = $trd1->price;
                    $qty = 1.0;
                    //$sym = $basesymbol;
                    $stime = $trd1->time;
                }
                $val = ($qty * $trd1->price) * (1.0 - $comission);
                //$sym = $quotesymbol;
                if($echoOff !== true)
                    echo "\n" . date("d-H:i:s", $trd1->time / 1000). " ---> sell $qty at $trd1->price  ";
            }

            }
            if($trd1 != null && $trd2 != null){
                if($trd2->price > $trd1->price){ // sell
                    $val = ($qty * $trd2->price) * (1.0 - $comission);
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> sell $qty at $trd2->price  ";
                    $retPercent = round(100.0 * ($val - $val0) / $val0, $roundto);
                }
                else{ // buy
                    $qty = ($val / $trd2->price) * (1.0 - $comission);
                    if($echoOff !== true)
                        echo "\n" . date("d-H:i:s", $trd2->time / 1000). " ---> buy $qty at $trd2->price  ";
                    $retPercent = round(100.0 * ($qty - 1.0), $roundto);
                }
                $difftime = round(($trd2->time - $stime) / 60000, 0); //mili second to minute
                $difftime = ($difftime < 1) ? 1 : $difftime;
                return round(($retPercent / $difftime), 5);

            }
            return false;
        }
    }
    class VertixPredictor
    {
        private $vertices = null;
        private $upmaxpricedist = 0.0;
        private $upmaxtimedist = 0.0;
        private $downmaxpricedist = 0.0;
        private $downmaxtimedist = 0.0;
        private $upminpricedist = PHP_FLOAT_MAX;
        private $upmintimedist = PHP_FLOAT_MAX;
        private $downminpricedist = PHP_FLOAT_MAX;
        private $downmintimedist = PHP_FLOAT_MAX;
        public function __construct($vertices)
        {
            $this->vertices = $vertices;
        }
        private function setminmax($count = 0, $lastvertixconsideration = true){
            $this->upmaxpricedist = 0.0;
            $this->upmaxtimedist = 0.0;
            $this->downmaxpricedist = 0.0;
            $this->downmaxtimedist = 0.0;
            $this->upminpricedist = PHP_FLOAT_MAX;
            $this->upmintimedist = PHP_FLOAT_MAX;
            $this->downminpricedist = PHP_FLOAT_MAX;
            $this->downmintimedist = PHP_FLOAT_MAX;
            
            $cnt = count($this->vertices);
            
            if($cnt > 2 && $cnt >= $count){
                $ind = 0;
                if($count > 0) $ind = $cnt - $count;
                if($lastvertixconsideration === false) {
                    $cnt--;
                    if($ind > 0) $ind--;
                }
                for($i = $ind ; $i < $cnt - 1; $i++){
                    $difprice = $this->vertices[$i + 1]->price - $this->vertices[$i]->price;
                    $diftime = $this->vertices[$i + 1]->time - $this->vertices[$i]->time;
                    if($difprice < 0.0){
                        if(abs($difprice) > $this->downmaxpricedist) $this->downmaxpricedist = abs($difprice);
                        if(abs($difprice) < $this->downminpricedist) $this->downminpricedist = abs($difprice);
                        if(abs($diftime) > $this->downmaxtimedist) $this->downmaxtimedist = abs($diftime);
                        if(abs($diftime) < $this->downmintimedist) $this->downmintimedist = abs($diftime);
                    }
                    else if($difprice > 0.0){
                        if(abs($difprice) > $this->upmaxpricedist) $this->upmaxpricedist = abs($difprice);
                        if(abs($difprice) < $this->upminpricedist) $this->upminpricedist = abs($difprice);
                        if(abs($diftime) > $this->upmaxtimedist) $this->upmaxtimedist = abs($diftime);
                        if(abs($diftime) < $this->upmintimedist) $this->upmintimedist = abs($diftime);
                    }
                }
                return true;
            }
            return false;
        }
        public function predict($cnt = 0, $lastvertixconsideration = true){
            if($this->setminmax($cnt, $lastvertixconsideration)){
                $count = count($this->vertices);
                $lastvertix = $this->vertices[$count - 1];
                $beforelastvertix = $this->vertices[$count - 2];
                if($lastvertixconsideration === false) {
                    $lastvertix = $beforelastvertix;
                    $beforelastvertix = $this->vertices[$count - 3];
                }
                $difprice = $lastvertix->price - $beforelastvertix->price;
                $mindt = $this->downmintimedist;
                $mindp = $this->downminpricedist;
                $maxdt = $this->downmaxtimedist;
                $maxdp = $this->downmaxpricedist;
                if($difprice < 0){
                    $mindt = $this->upmintimedist;
                    $mindp = $this->upminpricedist;
                    $maxdt = $this->upmaxtimedist;
                    $maxdp = $this->upmaxpricedist;
                }
                return new Prediction($lastvertix->time, $lastvertix->price, 
                    $mindt, $mindp, $maxdt, $maxdp, $difprice);
            }
            return false;
        }
    }
    class Trend
    {
        public $from = null;
        public $to = null;
        public function __construct($vertfrom, $vertto){
            $this->from = $vertfrom;
            $this->to = $vertto;
        }
    }
    class Trends
    {
        public $trends = array();
        public function __construct($vertices)
        {
            for($ind = 0; $ind < count($vertices) - 1; $ind = $ind + 2){

            }
        }
        public function addtrend($from, $to)
        {
            $cnt = count($this->trends);
            if($cnt == 0) {
                $this->trends [] = $from;
                $this->trends [] = $to;
            }
            else{
                $lastto = $this->trends[$cnt - 1];
                $lastfrom = $this->trends[$cnt - 2];
                $lastdirection = ($lastto->price > $lastfrom->price) ? 1 : -1;
                if($from->price > $lastfrom->price && $from->price < $lastto->price){
                    //if($to->price > $lasto->price)
                }
            }
        }
    }
    class Prediction
    {
        public $mintime = 0.0;
        public $minprice = 0.0;
        public $maxtime = 0.0;
        public $maxprice = 0.0;
        public function __construct($maintime, $mainprice, $mindt, $mindp, $maxdt, $maxdp, $prevdirection)
        {
            $this->mintime = $maintime + $mindt;
            $this->maxtime = $maintime + $maxdt;
            if($prevdirection > 0){
                $this->minprice = $mainprice - $mindp;
                $this->maxprice = $mainprice - $maxdp;
            }
            else if($prevdirection < 0)
            {
                $this->minprice = $mainprice + $mindp;
                $this->maxprice = $mainprice + $maxdp;
            }
        }

    }

?>