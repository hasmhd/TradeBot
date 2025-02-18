<?php
class Monitor{
    private const minute = 60 * 1000000;
    private $market = null;
    private $sleep = 5;
    public function __construct($market , $minute = 5)
    {
        $this->market = $market;
        $this->sleep = $minute * self::minute;
    }
    

}