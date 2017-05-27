<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * Description of Response
 *
 * @author Lars Kristian Dahl <http://www.krisd.com>
 */
class Response {

    private $_content;
    private $_format = 'text/html';

    public static function factory() {
        return new self();
    }

    public function __construct() {

    }

    public function setFormat($format) {
        $this->_format = $format;
    }

    public function setResponse($content) {
        $this->_content = $content;
    }

    public function send() {
        echo $this->_content->render();
    }

    /**
     * Performs a redirect to the requested url
     * @param <type> $url
     */
    public function redirect($url) {
        header('Location:' . $url);
        exit(1);
    }

    /**
     * Redirects to the HTTP referrer if the request came from us,
     * otherwise redirects to the index
     * @param <type> $anchor
     */
    public function backward($anchor=null) {
        $returnurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if ($anchor)
            $returnurl .= '#' . $anchor;
        if (strpos($returnurl, $_SERVER['HTTP_HOST'])) {
            $this->redirect($returnurl);
        } else {
            $this->redirect(__PUBLIC_PATH);
        }
        exit(1);
    }

    /**
     * Redirects to given url after a specified delay
     * @param <type> $delay
     * @param <type> $url
     */
    public function refresh($delay=5, $url='#') {
        header('Refresh:' . $delay . '; Url=' . __PUBLIC_PATH . '/' . $url);
        exit(1);
    }

}