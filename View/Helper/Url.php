<?php

class BTS_View_Helper_Url extends Zend_View_Helper_Abstract {
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true, $absolute = true, $host = null, $shorten = false) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        /* @var $request Zend_Controller_Request_Http */
        $router = Zend_Controller_Front::getInstance()->getRouter();
        /* @var $router Zend_Controller_Router_Rewrite */
        
        if (isset($urlOptions['_reset'])) {
            $reset = $urlOptions['_reset'];
            unset($urlOptions['_reset']);
        }
        else {
            // attempt to intelligently detect whether to reset or not.
            // 
            // take a copy of the options array
            $regularKeys = 0;
            foreach ($urlOptions as $key => $val) {
                // remove keys which translate to actions for the router assembly
                if (substr($key, 0, 1) != "_" && !in_array($key, array("module", "controller", "action"))) {
                    $regularKeys++;
                }
            }
            
            if (!isset($urlOptions['controller']) && !isset($urlOptions['action']) || $regularKeys > 0) {
                // didn't specify a controller or action, or specified other keys (possibly url paramters)
                // so probably wants current url. don't reset
                $reset = false;
                
                if ($router->getCurrentRouteName() != "default") {
                    // reset this if route name is not default, else it might not get re-built correctly
                    $urlOptions['_name'] = $router->getCurrentRouteName();
                }
            }
            else {
                $reset = true;
            }
        }
        
        if (isset($urlOptions['_name'])) {
            $name = $urlOptions['_name'];
            unset($urlOptions['_name']);
        }
        if (isset($urlOptions['_encode'])) {
            $encode = $urlOptions['_encode'];
            unset($urlOptions['_encode']);
        }
        if (isset($urlOptions['_absolute'])) {
            $absolute = $urlOptions['_absolute'];
            unset($urlOptions['_absolute']);
        }
        if (isset($urlOptions['_host'])) {
            $host = $urlOptions['_host'];
            unset($urlOptions['_host']);
        }
        
        if (isset($urlOptions['fragment'])) {
            $fragment = $urlOptions['fragment'];
            unset($urlOptions['fragment']);
        }
        
        if (isset($urlOptions['_removeParam'])) {
            $currentParams = $request->getParams();
            
            if (is_string($urlOptions['_removeParam'])) {
                $urlOptions['_removeParam'] = array($urlOptions['_removeParam']);
            }
            
            foreach ($urlOptions['_removeParam'] as $param) {
                if (isset($currentParams[$param])) {
                    unset($currentParams[$param]);
                }
            }
            unset($urlOptions['_removeParam']);
            
            // force a reset to remove the requested param(s)
            $reset = true;
            
            $urlOptions = $urlOptions + $currentParams;
        }
        
        if (isset($urlOptions['_removeExtraParams'])) {
            $currentParams = $request->getParams();
            
            foreach ($currentParams as $param => $value) {
                if (!in_array($param, array("module", "controller", "action"))) {
                    unset($currentParams[$param]);
                }
            }
            unset($urlOptions['_removeExtraParams']);
            
            // force a reset to remove the requested param(s)
            $reset = true;
            
            $urlOptions = $urlOptions + $currentParams;
        }
        
        if (isset($urlOptions['_shorten'])) {
            $shorten = true;
            unset($urlOptions['_shorten']);
            if (!$absolute) {
                // $host must be forced to true so we get an absolute url, else the
                // shortened url won't work.
                $absolute = true;
            }
        }
        
        if (is_null($name)) {
            $name = "default";
        }
        
        $url = $router->assemble($urlOptions, $name, $reset, $encode);
        if ($absolute) {
            $hostHelper = new BTS_View_Helper_ServerUrl();
            if (!is_null($host)) {
                $hostHelper->setHost($host);
            }
            else {
                $hostHelper->setHost($_SERVER['HTTP_HOST']);
            }
            $url = $hostHelper->serverUrl($url);
        }
        
        if (isset($fragment)) {
            $url .= "#" . $fragment;
        }
        
        if ($shorten) {
            $url = BTS_Service_Bitly::shorten($url);
        }
        
        return $url;
    }
}
