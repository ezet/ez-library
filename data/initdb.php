<?php

/*
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

// set error levels
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

session_start();
session_destroy();
include '../application/lib/config.class.php';
include '../application/lib/database.class.php';
$dbh = new \ezmvc\lib\Database;
$dbh = $dbh->lazyConnect();

$sql = explode(";", file_get_contents('mysql.sql'));
$sql = array_filter($sql);
foreach ($sql as $query) {
    $query = trim($query);
    $dbh->exec($query);
}

echo '<h3>Tables successfully reset!</h3>';

$sql = explode(";", file_get_contents('sampledata.sql'));
$sql = array_filter($sql);


foreach ($sql as $query) {
    $query = trim($query);
    if (!empty($query)) {
        $dbh->exec($query);
    }
}

echo '<h3>Sampledate successfully loaded!</h3>';


//if (isset($_SERVER['HTTP_REFERER'])) {
//    header('Location:' . $_SERVER['HTTP_REFERER']);
//}
exit(1);