<?php
    //require   $_SERVER['DOCUMENT_ROOT'] .  '/tabdeal/source/TabdealAPI.php';
    require  'TabdealAPI.php';
    
    //if(isset($_POST['listenkey']))
    {
         //$lk = $_POST['listenkey'];
         $ta = new TabdeaAPI();
         //if($lk === null or $lk == null)
         {
             echo $ta->newListenkey();
            //return $ta->newListenkey();
         }
         //else
         {
            // return $ta->renewListenkey($lk);
         }
    }
?>