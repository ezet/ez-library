<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

namespace ezmvc\lib;

/**
 * Description of Gbook
 *
 * @author Lars Kristian Dahl <http://www.krisd.com>
 */
class GBook {

    private $_apiurl = "http://books.google.com/books/feeds/volumes";
    private $_apikey = 'ABQIAAAAxLUY4IMs9aUZnipANmGKsRR1tdvgEygv2uq6rMwoCLkTMQJr0RRim9vW8CI6htHnRr5qVz2D7Y7q9Q';

    public function jsonFind($value) {
        $url = 'https://ajax.googleapis.com/ajax/services/search/books?' .
                'v=1.0&q=' . urlencode((string) $value) . '&key=' . $this->_apikey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
//        curl_setopt($ch, CURLOPT_REFERER, 'set this when live');
        curl_exec($ch);
//        var_dump(curl_getinfo($ch));
        curl_close($ch);
    }

    public function findByIsbn($isbn) {
        $isbn = $this->strip($isbn);
        $url = $this->_apiurl . '?q=' . ($isbn);
        $xml = simplexml_load_file($url);
        $ns = $xml->getNamespaces(true);
        $book = null;
        foreach ($xml->entry as $entry) {
            $dc = $entry->children($ns['dc']);
            foreach ($dc->identifier as $id) {
                if ('ISBN:' . $isbn == $id && $book === NULL) {
                    $book = $this->parseEntry($dc);
                    $book['isbn'] = \str_replace('ISBN:', '', $id);
                    break;
                }
            }
        }
        return $book;
    }

    public function parseEntry($dc) {
        $book['title'] = $this->parseTitle($dc);
        $book['author'] = (string) $dc->creator;
        $book['publisher'] = (string) $dc->publisher;
        $book['datePublished'] = (string) $dc->date;
        $book['category'] = $this->parseSubjects($dc);
        $book['synopsis'] = (string) $dc->description;
        return $book;
    }

    private function parseTitle($dc) {
        $title = '';
        foreach ($dc->title as $str) {
            if (!empty($title)) {
                $title .= ', ';
            }
            $title .= $str;
        }
        return $title;
    }

    private function parseSubjects($dc) {
        $subjects = array();
        foreach ($dc->subject as $subject) {
            $subjects[] = (string) $subject;
        }
        return $subjects;
    }

    private function strip($isbn) {
        $isbn = preg_replace('/[^0-9]/', '', $isbn);
        return $isbn;
    }

}