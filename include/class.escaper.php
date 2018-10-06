<?php
    class escaper {
        
        /**
         * Escapes text for safe use in HTML.
         * 
         * @param string $text Text to escape
         * @return string
         */
        public static function escape_html($text) {
            return htmlspecialchars($text, ENT_QUOTES);
        }
        
        /**
         * Escapes keys and values for safe use in HTML.
         * 
         * @param array $arr Array with values to escape
         * @return array|boolean
         */
        public static function escape_html_array($arr) {
            if (!is_array($arr)) {
                return false;
            }
            
            $output = array();
            
            foreach ($arr as $k => $v) {
                $k = self::escape_html($k);
                if (is_array($v)) {
                    $v = self::escape_html_array($v);
                } else {
                    $v = self::escape_html($v);
                }
                
                $output[$k] = $v;
            }
            
            return $output;
        }
    }