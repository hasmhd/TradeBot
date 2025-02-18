<?php
require_once 'OrderAnalizer.php';
require_once 'marketmanager.php';
if(isset($_POST['marketSymbol'])){
    $symbol = $_POST['marketSymbol'];
    $ta = new TradeAnalizer($symbol);
    $td = new TraderData($symbol, $ta);

    echo json_encode($td);
    //  echo $ta->lastPrice() . "\n";
    //  var_dump($ta->getDay());
    //  var_dump($ta->getDay(1));
}
// $maxBuypercent = null;
// $mrks =  MarketManager::instance()->markets();
// foreach($mrks as $sym  => $market )
// {
//     $td = new TraderData($sym); 
//     var_dump($td);
//     if($maxBuypercent === null)
//         $maxBuypercent = $td;
//     else if($td->buyPercent > $maxBuypercent->buyPercent)
//         $maxBuypercent = $td;
// }
// echo "------------------------------------------------\n";
// echo "best market for buy : " . $maxBuypercent->symbol . "\n";
// var_dump($maxBuypercent);
//$td = new TraderData("SHIB_IRT");
//var_dump($td);
class TraderData
{
    public $symbol;
    public $time;
    public $last60min = null;
    public $last24hr = null;
    public $orderData = null;
    public $lastPrice = 0.0;
    public $typicalDirection;
    public $buyerMakerDirection;
    public $sellPercent = 0.0;
    public $buyPercent = 0.0;
    public function __construct($markSymbol, $tradeAnalizer = null)
    {

        $this->symbol = $markSymbol;
        $ta = $tradeAnalizer;
        if($tradeAnalizer === null)
            $ta = new TradeAnalizer($this->symbol);
        $this->time = date("Y-m-d H:i:s", Settings::instance()->getCurrentTimestamp() / 1000);
        $this->last60min = $ta->getLastMinute(60);
        $this->last24hr = $ta->getLastMinute(24 * 60);
        $this->orderData = new OrderAnalizer($this->symbol);
        $this->lastPrice = $ta->lastPrice();
        $this->typicalDirection = $ta->getDiriction();
        $this->buyerMakerDirection = $ta->getDiriction(5, true);
        $this->buyPercent = round((1 - ($this->orderData->minSellPrice - $this->last24hr->min) / ($this->last24hr->max - $this->last24hr->min)) * 100.0, 2);
        $this->sellPercent = round(($this->orderData->maxBuyPrice - $this->last24hr->min ) / ($this->last24hr->max - $this->last24hr->min) * 100.0, 2);
    }

}
class Trader
{
    public function tradeOnMovingAvrag()
    {
        $step = 5;
        $sarmaye['SHIB'] = 20000000;
        $sarmaye['IRT'] = 5000000;
        $ta = new TradeAnalizer("SHIB_IRT");
        $initBalance = $balance = $currentBalance = $sarmaye['IRT'] + ($sarmaye['SHIB'] * $ta->lastPrice()) * (1 - 0.0022);
        $ans = $ta->getStepAvrage($step, true);
        echo 'total count : ' . $ans['count'] . "\n";
        $oldStatus = $status = 'start';
        for($i = $step - 1; $i < $ans['count']; $i++)
        {
            $dis = $ans['prices'][$i] - $ans['avrgs'][$i];
            $currentBalance = $sarmaye['IRT'] + ($sarmaye['SHIB'] * $ans['prices'][$i]) * (1 - 0.0022);
            // if($oldStatus === 'start')
            // {
            //     $oldStatus = $dis;
            // }
            //else 
            {
                //if(abs($dis) > 0.1 * $ans['prices'][$i])
                {
                    if($dis > 0.0)
                    {
                        //if($dis)
                        $status = 'BUY';
                        if($sarmaye['IRT'] > 0.0)// $currentBalance > $balance)
                        {
                            $temp  = $sarmaye['IRT'] / $ans['prices'][$i];
                            $sarmaye['SHIB'] += ($temp * (1 - 0.0022));
                            $sarmaye['IRT'] = 0.0;
                            $balance = $currentBalance;
                        }
                    }
                    else if($dis < 0.0)
                    {
                        $status = 'SELL';
                        if($currentBalance < ($balance * 0.98))
                        {
                            $temp  = $sarmaye['SHIB'] * $ans['prices'][$i];
                            $sarmaye['IRT'] += ($temp * (1 - 0.0022));
                            $sarmaye['SHIB'] = 0.0;
                            $balance = $currentBalance;
                        }
                    }
                    //$oldStatus = $dis;
                }
            }
            // else 
            // {
            //     if(abs($dis) > 0.009 * $ans['prices'][$i])
            //     {
            //         if($dis * $oldStatus < 0.0)
            //         {
            //             if($dis > 0.0)
            //             {
            //                 $status = 'BUY';
            //                 $temp  = $sarmaye['IRT'] / $ans['prices'][$i];
            //                 $sarmaye['SHIB'] += ($temp * (1 - 0.0022));
            //                 $sarmaye['IRT'] = 0.0;
            //             }
            //             else
            //             {
            //                 $status = 'SELL';
            //                 $temp  = $sarmaye['SHIB'] * $ans['prices'][$i];
            //                 $sarmaye['IRT'] += ($temp * (1 - 0.0022));
            //                 $sarmaye['SHIB'] = 0.0;
            //             }
            //             $oldStatus = $dis;
            //         }
            //         else
            //         {
            //             $status = 'HOLD';
            //         }
            //     }
            // }
            echo "--------------------------------------------------------\n";
            echo 'Status : '. $status . "\n";
            echo 'Price : '. $ans['prices'][$i] . "\n";
            echo 'IRT : '. round($sarmaye['IRT']) . "\n";
            echo 'SHIB : '. round($sarmaye['SHIB']) . "\n";
        }

    }
}
?>