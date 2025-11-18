<?php

namespace Services;

/**
 * 数据库服务类
 * 提供通用的数据库操作方法
 */
class Database {
    
    private $db;
    private $f3;
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->f3 = \Base::instance();
        $this->db = $this->f3->get('DB');
    }
    
    /**
     * 执行自定义 SQL 查询
     * 
     * @param string $sql SQL 查询语句
     * @param array $values 绑定参数
     * @return array 查询结果
     */
    public function query($sql, $values = []) {
        try {
            return $this->db->exec($sql, $values);
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 插入数据
     * 
     * @param string $table 表名
     * @param array $data 要插入的数据（关联数组）
     * @return int 插入的记录 ID
     */
    public function insert($table, $data) {
        try {
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
            
            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );
            
            $params = [];
            foreach ($data as $key => $value) {
                $params[':' . $key] = $value;
            }
            
            $this->db->exec($sql, $params);
            return $this->db->lastInsertId();
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 更新数据
     * 
     * @param string $table 表名
     * @param array $data 要更新的数据（关联数组）
     * @param string $where WHERE 条件（例如："id = :id"）
     * @param array $whereParams WHERE 条件的绑定参数
     * @return int 受影响的行数
     */
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $sets = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $sets[] = "$key = :set_$key";
                $params[':set_' . $key] = $value;
            }
            
            // 合并 WHERE 参数
            $params = array_merge($params, $whereParams);
            
            $sql = sprintf(
                "UPDATE %s SET %s WHERE %s",
                $table,
                implode(', ', $sets),
                $where
            );
            
            $this->db->exec($sql, $params);
            return $this->db->count();
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 删除数据
     * 
     * @param string $table 表名
     * @param string $where WHERE 条件（例如："id = :id"）
     * @param array $whereParams WHERE 条件的绑定参数
     * @return int 受影响的行数
     */
    public function delete($table, $where, $whereParams = []) {
        try {
            $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
            $this->db->exec($sql, $whereParams);
            return $this->db->count();
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 查询数据
     * 
     * @param string $table 表名
     * @param string $where WHERE 条件（可选）
     * @param array $whereParams WHERE 条件的绑定参数
     * @param string $orderBy 排序条件（可选，例如："created_at DESC"）
     * @param int $limit 限制返回数量（可选）
     * @return array 查询结果
     */
    public function select($table, $where = null, $whereParams = [], $orderBy = null, $limit = null) {
        try {
            $sql = "SELECT * FROM " . $table;
            
            if ($where) {
                $sql .= " WHERE " . $where;
            }
            
            if ($orderBy) {
                $sql .= " ORDER BY " . $orderBy;
            }
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            return $this->db->exec($sql, $whereParams);
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 查询单条数据
     * 
     * @param string $table 表名
     * @param string $where WHERE 条件
     * @param array $whereParams WHERE 条件的绑定参数
     * @return array|null 查询结果（单条记录）
     */
    public function selectOne($table, $where, $whereParams = []) {
        $result = $this->select($table, $where, $whereParams, null, 1);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * 检查记录是否存在
     * 
     * @param string $table 表名
     * @param string $where WHERE 条件
     * @param array $whereParams WHERE 条件的绑定参数
     * @return bool 是否存在
     */
    public function exists($table, $where, $whereParams = []) {
        try {
            $sql = sprintf("SELECT COUNT(*) as count FROM %s WHERE %s", $table, $where);
            $result = $this->db->exec($sql, $whereParams);
            return $result[0]['count'] > 0;
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 获取记录总数
     * 
     * @param string $table 表名
     * @param string $where WHERE 条件（可选）
     * @param array $whereParams WHERE 条件的绑定参数
     * @return int 记录总数
     */
    public function count($table, $where = null, $whereParams = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM " . $table;
            
            if ($where) {
                $sql .= " WHERE " . $where;
            }
            
            $result = $this->db->exec($sql, $whereParams);
            return (int)$result[0]['count'];
            
        } catch (\PDOException $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * 开始事务
     */
    public function beginTransaction() {
        $this->db->begin();
    }
    
    /**
     * 提交事务
     */
    public function commit() {
        $this->db->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollback() {
        $this->db->rollback();
    }
    
    /**
     * 处理数据库错误
     * 
     * @param \PDOException $e PDO 异常
     * @throws \Exception
     */
    private function handleError(\PDOException $e) {
        // 记录错误日志
        error_log('Database Error: ' . $e->getMessage());
        
        // 抛出友好的错误信息
        throw new \Exception('データベースエラーが発生しました', 500);
    }
}
