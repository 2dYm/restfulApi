<?php
/**
 * author: Nick
 */

$pdo = require __DIR__ . '/lib/DB.php';
require __DIR__ . '/lib/User.php';
require __DIR__ . '/lib/Article.php';

//$user = new User($pdo);
//$user->login('admin', '0192023a7bbd73250516f069df18b500');

$article = new Article($pdo);
//$result = $article->create('aaa', 'aaaaadfasdf', '1');
$result = $article->list(1);

var_dump($result);