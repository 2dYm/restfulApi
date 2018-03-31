<?php
/**
 * author: Nick
 */

try{
    $pdo = new PDO('mysql:localhost;dbname=restfulapi','root','123');
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e){
    echo '数据库连接失败'.$e->getMessage();
}

return $pdo;