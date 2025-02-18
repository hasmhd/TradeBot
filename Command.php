<?php
//require_once 'tabdeal\source\trader.php';
require_once 'tabdealSite.php';
if(isset($_POST['cmd'])){
    $commandText = $_POST['cmd'];
    //$temps [] = preg_split('@ @', $commandText, -1, PREG_SPLIT_NO_EMPTY);
    echo json_encode(doThis($commandText));
    //echo json_encode($td);
    //  echo $ta->lastPrice() . "\n";
    //  var_dump($ta->getDay());
    //  var_dump($ta->getDay(1));
}
//$t = doThis("chart BTC_USDT 500");
// $ts = new TabdealSite();
//             $trades = $ts->tradesForOnMarket("BTC_USDT", 50);
//             var_dump($trades);
//             $dataPoints = array();
//             foreach($trades as $trade){
//                 $dataPoints [] = array("y" => $trade->price, "x" => $trade->time);
//             }
// var_dump($dataPoints);
function bestMarket()
{
    $maxBuypercent = null;
    $mrks =  MarketManager::instance()->markets();
    echo "------------------------------------------------\n";
    foreach($mrks as $sym  => $market )
    {
        if(strpos($sym, "_IRT") !== false)
        {
            $td = new TraderData($sym); 
            //var_dump($td);
            //echo $td->symbol . ' : ' . $td->last24hr->min + ' - ' + $td->last24hr->max + '(' + $td->last24hr->count  + ')';
            //echo ' : ' + $td->orderData->maxBuyPrice + '(' + $td->orderData->buyWeight + ') - ' + $td->orderData->minSellPrice + '(' + $td->orderData->sellWeight + ')';
            echo $td->symbol . ' : ' . $td->sellPercent . ' - ' . $td->buyPercent . ' - ' . $td->typicalDirection . ' - ' . $td->buyerMakerDirection . ' - ' . $td->lastPrice . "\n";
            if($maxBuypercent === null)
                $maxBuypercent = $td;
            else if($td->buyPercent > $maxBuypercent->buyPercent)
                $maxBuypercent = $td;
        }
    }
    echo "------------------------------------------------\n";
    echo "best market for buy : " . $maxBuypercent->symbol . "\n";
}
//var_dump($maxBuypercent);
function doThis($strtext){
$temps [] = preg_split('@ @', $strtext, -1, PREG_SPLIT_NO_EMPTY);
$firstString = $temps[0][0];
    if($temps){
        if(strtolower($firstString) === "sale"){
            if(sizeof($temps) > 1)
            {
                $ts = new TabdealSite();
                return $ts->sale($temps[0][1], $temps[0][2], null, ($temps[0][3] === "true") ? true: false);
            }
        }
        if(strtolower($firstString) === "chart"){ // chart BTC_USDT 500
            $ans["type"] = "chart";
            $ans["title"] = $temps[0][1];
            $chartData["type"] = "line";
		    $chartData["xValueFormatString"] = "HH:m";
            $chartData["xValueType"] = "dateTime";
            $ts = new TabdealSite();
            $trades = $ts->tradesForOnMarket($temps[0][1], $temps[0][2]);
            $dataPoints = array();
            foreach($trades as $trade){
                $dataPoints [] = array("y" => $trade->price, "x" => $trade->time);
            }
            
		    $chartData["dataPoints"] = $dataPoints;
            $ans["data"] = $chartData;
            //echo json_encode($ans);
            return $ans;
        }
    }

}