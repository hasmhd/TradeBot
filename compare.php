<?php
    require  /*$_SERVER['DOCUMENT_ROOT'] .*/'TabdealSite.php';
    //print_r(compareToken("BTC", 0.0001));
    if(isset($_POST['tokenName']) && isset($_POST['tokenCount'])){
         $tokenName = $_POST['tokenName'];
         $tokenCount =  $_POST['tokenCount'];
         $answer = compareToken($tokenName ,$tokenCount);
         if(!isset($_POST['sarmaye'])){
             echo json_encode($answer);
         }
         if(isset($_POST['sarmaye'])){
            $sarmaye =  $_POST['sarmaye'];

            $rettext =  "<tr><td>" . $answer["time"] ."</td>";
            if( $answer["tomanAZusdtSale"] >= $sarmaye){
                if($answer["tomanAZusdtSale"] >= $sarmaye * 2.0){
                    $rettext .= '<td class="alarm" style="color:green;">';    
                }
                else{
                    $rettext .= '<td style="color:green;">';
                }
            }else{
                $rettext .= '<td style="color:red;">';
            }
            $rettext .= number_format($answer["usdtSale"], 4, '.', ',') ."(" . number_format($answer["tomanAZusdtSale"], 0, '.', ',') . ")</td>";
            if( $answer["tomanSale"] >= $sarmaye){
                if($answer["tomanSale"] >= $sarmaye * 2.0){
                    $rettext .= '<td class="alarm" style="color:green;">';    
                }
                else{
                    $rettext .= '<td style="color:green;">';
                }
            }else{
                $rettext .= '<td style="color:red;">';
            }
            $rettext .= number_format($answer["tomanSale"], 0, '.', ',') ."</td>";//</tr>";
            
            
            if( $answer["tomanAZusdtBuy"] <= $sarmaye){
                if($answer["tomanAZusdtBuy"] <= $sarmaye * 0.5){
                    $rettext .= '<td class="alarm" style="color:green;">';    
                }
                else{
                    $rettext .= '<td style="color:green;">';
                }
            }else{
                $rettext .= '<td style="color:red;">';
            }
            $rettext .= number_format($answer["usdtBuy"], 4, '.', ',') ."(" . number_format($answer["tomanAZusdtBuy"], 0, '.', ',') . ")</td>";
            if( $answer["tomanBuy"] <= $sarmaye){
                if($answer["tomanBuy"] <= $sarmaye * 0.5){
                    $rettext .= '<td class="alarm" style="color:green;">';    
                }
                else{
                    $rettext .= '<td style="color:green;">';
                }
            }else{
                $rettext .= '<td style="color:red;">';
            }
            $rettext .= number_format($answer["tomanBuy"], 0, '.', ',') ."</td></tr>";
            echo $rettext;
        }
    }

    function compareToken($tokenName, $tokenCount)
    {
        $ts = new TabdealSite();
        $symbol = $tokenName . "_IRT";
        $answer['time'] =  date('H:i:s');
        if($ts->isMarketExist($symbol)){
            $ordjson = $ts->orderJson($symbol);
            $toman = $ts->sale($symbol, $tokenCount, $ordjson);
            $answer['tomanSale'] = $toman;
            $answer['tomanBuy'] = $ts->buyAmount($symbol, $tokenCount, $ordjson);
        }
        else{
            $answer['tomanSale'] = 0;
            $answer['tomanBuy'] = 0;
        }
        $ordjson = $ts->orderJson($tokenName . "_USDT");
        $usdt = $ts->sale($tokenName . "_USDT", $tokenCount, $ordjson);
        $answer['usdtSale'] = $usdt;
        $answer['usdtBuy'] = $usdtBuy = $ts->buyAmount($tokenName . "_USDT", $tokenCount, $ordjson);

        $ordjson = $ts->orderJson('USDT_IRT');
        $answer['tomanAZusdtSale'] = $ts->sale('USDT_IRT', $usdt, $ordjson);
        $answer['tomanAZusdtBuy'] = $ts->buyAmount('USDT_IRT', $usdtBuy, $ordjson); 
        return $answer;
    }
?>