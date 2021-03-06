<?php

class BTS_Version {
    
    const BTS_VERSION = "1.0.5";
    
    public static function getVersion() {
        $list = explode(".", self::BTS_VERSION);
        $version = array(
            "major" => $list[0],
            "minor" => $list[1],
            "revision" => $list[2]
        );
        if (count($list) == 4) {
            $version['maintenance'] = $list[3];
        }
        return $version;
    }
    
    public static function getAppVersion() {
        if (get_called_class() == "BTS_Version") {
            throw new Exception("Unable to get Application Version details");
        }
        
        $class = get_called_class();
        $list = explode(".", $class::APP_VERSION);
        $version = array(
            "major" => $list[0],
            "minor" => $list[1],
            "revision" => $list[2],
        );
        if (count($list) == 4) {
            $version['maintenance'] = $list[3];
        }
        return $version;
    }
    
    public static function getVCSRevision() {
        $svnDir = dirname(APPLICATION_PATH) . "/.svn/";
        $gitDir = dirname(APPLICATION_PATH) . "/.git/";
        
        if (file_exists($svnDir) && is_dir($svnDir)) {
            $str = "svn info --xml " . dirname(APPLICATION_PATH);
            $xml = simplexml_load_string(`$str`);
            if ($xml) {
                return "r" . (string)$xml->entry->attributes()->revision;
            }
        }
        else if (file_exists($gitDir) && is_dir($gitDir)) {
            $str = `git rev-parse HEAD`;
            if ($str && $str != "") {
                $str = preg_replace("/[\r|\n]/", "", $str);
                return $str;
            }
        }
        else {
            return false;
        }
    }
}
