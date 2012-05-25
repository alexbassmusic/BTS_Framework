<?php

/**
 * Inflector was an idea I had/took from Kohana (where most of this code can actually be found). Kohana as
 * this neat class which can pluralize or singularize (amongst other things) words. Projects I was working
 * on needed this exactly functionality, so I merged Kohana's inflector class, merged it into this Zend
 * extension, and made it available as both a statically available singleton, or via a ViewHelper 
 * (BTS_View_Helper_Inflector).
 *
 * Usage:
 * $inflector = new BTS_Inflector();
 * echo $inflector->pluralize("car"); // outputs "cars"
 * echo $inflector->pluralize("story"); // outputs "stories"
 * echo $inflector->singularize("stories"); // outputs "story"
 * echo $inflector->singularize("sheep"); // outputs "sheep"
 *
 * It can also be called with BTS_Inflector::instance()->pluralize("...");
 *
 * Functions:
 * pluralize: make plural a singular word passed (car => cars)
 * singularize: make singular a plural word passed (cars => car)
 * wrapInPTags: given a whole bunch of text, strips unnecessary line endings, and wraps somewhat sanely
 *              with <p></p> tags for decent front-end display
 */

class BTS_Inflector {
    
    private static $_instance;
    
    /**
      * Get a singleton instance.
      * @return \BTS_Inflector
      */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
      * Get inflector configuration file/data
      * @return \Zend_Config_Xml
      */
    public function getInflectorConfig() {
        $cache = false;
        if($cache === false) {
            $element = new Zend_Config_Xml(dirname(__FILE__) . "/Data/inflector.xml");
            return $element;
        }
        return $cache;
    }
    
    public function isUncountable($str) {
        if (isset($this->getInflectorConfig()->uncountable->$str)) {
            return true;
        }
        else {
            return false;
        }
    }
    public function isIrregular($str) {
        if ($this->getIrregular($str) != $str) {
            return true;
        }
        else {
            return false;
        }
    }
    public function getIrregular($str) {
        if (isset($this->getInflectorConfig()->irregular->$str)) {
            return $this->getInflectorConfig()->irregular->$str;
        }
        else {
            return $str;
        }
    }
    
    public function singularize($str) {
        // Remove garbage
        $str = strtolower(trim($str));

        if ($this->IsUncountable($str)) {
            return $str;
        }

        if ($this->isIrregular($str)) {
            $str = $this->getIrregular($str);
        }
        elseif (preg_match('/us$/', $str)) {
            // http://en.wikipedia.org/wiki/Plural_form_of_words_ending_in_-us
            // Already singular, do nothing
        }
        elseif (preg_match('/[sxz]es$/', $str) OR preg_match('/[^aeioudgkprt]hes$/', $str)) {
            // Remove "es"
            $str = substr($str, 0, -2);
        }
        elseif (preg_match('/[^aeiou]ies$/', $str)) {
            // Replace "ies" with "y"
            $str = substr($str, 0, -3).'y';
        }
        elseif (substr($str, -1) === 's' AND substr($str, -2) !== 'ss') {
            // Remove singular "s"
            $str = substr($str, 0, -1);
        }

        return $str;
    }
    
    public function pluralize($str, $count = null) {
        if (!is_null($count) && $count == 1) {
            return $str;
        }
        
        // Remove garbage
        $str = trim($str);

        // Check uppercase
        $is_uppercase = ctype_upper($str);

        if ($this->isUncountable($str)) {
            return $str;
        }
        
        if ($this->isIrregular($str)) {
            $str = $this->getIrregular($str);
        }
        elseif (preg_match('/[sxz]$/', $str) OR preg_match('/[^aeioudgkprt]h$/', $str)) {
            $str .= 'es';
        }
        elseif (preg_match('/[^aeiou]y$/', $str)) {
            // Change "y" to "ies"
            $str = substr_replace($str, 'ies', -1);
        }
        else {
            $str .= 's';
        }

        // Convert to uppsecase if nessasary
        if ($is_uppercase) {
            $str = strtoupper($str);
        }
        
        return $str;
    }
    
    public function wrapInPTags($text) {                                                                                                                                                        
                                                                                                                                                                                                
        $text = str_replace(array("<p>", "</p>"), array("", "<br />"), $text);                                                                                                                  
        $text = trim(str_replace(array("<br />", "<br>", "\r"), "\n", $text));                                                                                                                  
                                                                                                                                                                                                
        $lines = explode("\n", $text);                                                                                                                                                          
                                                                                                                                                                                                
        $finalText = "";
        foreach ($lines as $line) {
            if ($line == "") {
                continue;
            }
            
            $finalText .= "<p>" . $line . "</p>\n";
        }
        
        return $finalText;
    }
}
