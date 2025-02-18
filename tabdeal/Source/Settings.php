<?php
    class Settings
    {
        private static $settings = null;
        
        private $variables;
        private $fpath = __DIR__ . "/../../data/settings.json";
        
        private function __construct()
        {
            if(file_exists($this->fpath))
            {
                $statisticsJson = file_get_contents($this->fpath);    
                $this->variables = json_decode($statisticsJson, true);
            }
        }
        public static function instance()
        {
            if(self::$settings === null)
            {   
                self::$settings = new Settings();
            }
            return self::$settings;
        }
        public function get($varname)
        {
            if(array_key_exists($varname, $this->variables))
            {
                return $this->variables[$varname];
            }
            else
            {
                return false;
            }
            
        }
        public function set($varname, $value)
        {
            $this->variables[$varname] = $value;
            $statisticsJson = json_encode($this->variables);
            file_put_contents($this->fpath, $statisticsJson);
        }
        public function getCurrentTimestamp()
        {
            return  floor(microtime(true) * 1000);
        }
    }
?>