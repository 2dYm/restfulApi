<?php
/**
 * author: Nick
 */
require_once __DIR__ . '/ErrorCode.php';
class Article
{
    /**
     * 数据库句柄
     * @var
     */
    private $_db;

    /**
     * 构造方法
     * @param PDO　$db 连接数据库
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }

    /**
     * 创建文章
     * @param $title
     * @param $content
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function create($title, $content, $userId)
    {
        if (empty($title)){
            throw new Exception('文章标题不能为空', ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY);
        }
        if (empty($content)){
            throw new Exception('文章内容不能为空', ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY);
        }
        $sql = 'insert into restfulapi.article(title, content, user_id) VALUES (:title, :content, :user_id)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':user_id', $userId);
        if (!$stmt->execute()){
            throw new Exception('发表文章失败', ErrorCode::ARTICLE_CREATE_FAIL);
        }
        return [
            'articleId' => $this->_db->lastInsertId(),
            'title'     => $title,
            'content'   => $content,
            'userId'    => $userId
        ];
    }

    /**
     * 查看文章
     * @param $articleId
     * @return
     * @throws Exception
     */
    public function view($articleId)
    {
        if (empty($articleId)){
            throw new Exception('文章ID不能为空', ErrorCode::ARTICLE_ID_CANNOT_EMPTY);
        }
        $sql = 'select * from restfulapi.article WHERE id = :articleId';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':articleId', $articleId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)){
            throw new Exception('文章不存在', ErrorCode::ARTICLE_NOT_EXISTS);
        }
        return $result;
    }

    /**
     * 编辑文章
     * @param $articleId
     * @param $title
     * @param $content
     * @param $userId
     * @return mixed
     * @throws Exception
     */
    public function edit($articleId, $title, $content, $userId)
    {
        $result = $this->view($articleId);
        if ($result['id'] !== $userId){
            throw new Exception('您无权编辑文章', ErrorCode::PERMISSION_DENIED);
        }
        $title = empty($title) ? $result['title'] : $title;
        $content = empty($content) ? $result['content'] : $content;
        if ($title === $result['title'] && $content === $result['content']){
            return $result;
        }
        $sql = 'update restfulapi.article set title = :title, content = :content WHERE id = :id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $articleId);
        if (!$stmt->execute()){
           throw new Exception('文章跟新失败', ErrorCode::ARTICLE_UPDATE_FAIL);
        }
        return [
            'articleId' => $articleId,
            'title' => $title,
            'content' => $content,
        ];
    }

    /**
     * 删除文章
     * @param $articleId
     * @param $userId
     */
    public function delete($articleId, $userId)
    {
        $result = $this->view($articleId);
        if ($result['user_id'] !== $userId){
            throw new Exception('您无权操作', ErrorCode::PERMISSION_DENIED);
        }
        $sql = 'delete from restfulapi.article WHERE id = :articleId AND user_id = :userId';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':articleId', $articleId);
        $stmt->bindParam(':userId', $userId);
        if (false === $stmt->execute()){
            throw new Exception('文章删除失败', ErrorCode::ARTICLE_DELETE_FAIL);
        }
        return true;
    }

    /**
     * @param $userId
     * @param int $page
     * @param int $size
     * @return
     * @throws Exception
     */
    public function list($userId, $page = 1, $size = 10)
    {
        if ($size > 100){
            throw new Exception('分页大小最大为100', ErrorCode::PAGE_SIZE_TO_BIG);
        }
        $sql = 'select * from restfulapi.article WHERE user_id = :userId LIMIT :limit, :offest';
        $limit = ($page - 1) * $size;
        $limit = $limit < 0 ? 0 : $limit;
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':limit', $limit);
        $stmt->bindParam(':offest', $size);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
}