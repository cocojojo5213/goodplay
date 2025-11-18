<?php
/**
 * 基底リポジトリクラス
 * 
 * 全てのリポジトリの共通機能を提供
 */

abstract class BaseRepository {
    protected $db;
    protected $table;
    
    public function __construct($db = null) {
        if ($db === null) {
            $f3 = F3();
            $db = $f3->get('DB');
        }
        $this->db = $db;
    }
    
    /**
     * 全件取得
     */
    public function findAll($columns = '*', $orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT {$columns} FROM {$this->table} ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * IDで検索
     */
    public function findById($id, $columns = '*') {
        $sql = "SELECT {$columns} FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * 条件付き検索
     */
    public function findWhere($conditions, $params = [], $columns = '*', $orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$conditions} ORDER BY {$orderBy} {$order}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * 単一レコード条件付き検索
     */
    public function findOneWhere($conditions, $params = [], $columns = '*') {
        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$conditions} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * ページング付き検索
     */
    public function findWithPagination($where = '', $params = [], $page = 1, $limit = 20, $orderBy = 'id', $order = 'DESC', $columns = '*') {
        $offset = ($page - 1) * $limit;
        
        // カウント取得
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $countSql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // データ取得
        $sql = "SELECT {$columns} FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy} {$order} LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * 挿入
     */
    public function insert($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 更新
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = ?",
            $this->table,
            implode(', ', $fields)
        );
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 削除
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * 件数カウント
     */
    public function count($where = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    /**
     * 存在チェック
     */
    public function exists($conditions, $params = []) {
        return $this->count($conditions, $params) > 0;
    }
    
    /**
     * PDOインスタンスを取得
     */
    public function getDb() {
        return $this->db;
    }
}
