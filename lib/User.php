<?php
/**
 * author: Nick
 */
require_once __DIR__ . '/ErrorCode.php';
class User
{
    private $_DB;

    public function __construct($DB)
    {
        $this->_DB = $DB;
    }

    public function login($username, $password)
    {

        if (empty($username)){
            throw new Exception('用户名不能为空', ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)){
            throw new Exception('密码不能为空', ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        $sql = 'select * from restfulapi.users WHERE name = :username AND password = :password';
        $password = $this->_md5($password);
        $stmt = $this->_DB->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        if (!$stmt->execute()){
            throw new Exception('服务器内部错误', ErrorCode::SERVER_INTERNAL_ERROR);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)){
            throw new Exception('用户名或密码错误', ErrorCode::USERNAME_OR_PASSWORD_ERROR);
        }
        unset($result['password']);
        return $result;
    }

    public function register($username, $password)
    {

        if (empty($username)){
            throw new Exception('用户名不能为空', ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)){
            throw new Exception('密码不能为空', ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        if ($this->_isUsernameExists($username)){
            throw new Exception('用户名已存在', ErrorCode::USERNAME_EXISTS);
        }
        $sql = 'insert into restfulapi.users (name, password) VALUES (:username, :password)';
        $password = $this->_md5($password);
        $stmt = $this->_DB->prepare($sql);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if (!$stmt->execute()){
            throw new Exception('注册失败', ErrorCode::REGISTER_FAIL);
        }
        return [
            'userId' => $this->_DB->lastInsertId(),
            'username' => $username
        ];
    }

    private function _md5($password, $key = '123')
    {
        return md5($password . $key);
    }

    private function _isUsernameExists($username)
    {
        $sql = 'select * from restfulapi.users where restfulapi.users.name = :username';
        $stmt = $this->_DB->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result);
    }
}