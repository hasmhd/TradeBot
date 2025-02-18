<?php

        //$params['limit'] = $limit;
        ///$content = http_build_query($params);
        $options = array(
            'http'=>array(
              'method'  => "POST",
            )
          );
          $context=stream_context_create($options);
          $data=file_get_contents('https://api.nobitex.ir/market/global-stats' , false, $context);
          ///var_dump(
                $nbtx =  json_decode($data);
                echo 'shib  : ' . $nbtx->markets->binance->shib  . '\n';
                echo 'sol   : ' . $nbtx->markets->binance->sol;


?>