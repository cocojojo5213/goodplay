<?php

namespace Services;

/**
 * 错误处理服务类
 * 统一处理和格式化错误响应
 */
class ErrorHandler {
    
    private $f3;
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->f3 = \Base::instance();
    }
    
    /**
     * 返回成功响应
     * 
     * @param mixed $data 响应数据
     * @param string $message 成功消息（可选）
     * @param int $code HTTP 状态码
     */
    public function success($data = null, $message = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true
        ];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * 返回错误响应
     * 
     * @param string $message 错误消息
     * @param string $code 错误代码
     * @param int $httpCode HTTP 状态码
     * @param array $details 详细错误信息（可选）
     */
    public function error($message, $code = 'ERROR', $httpCode = 400, $details = null) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $message,
            'code' => $code
        ];
        
        if ($details !== null && $this->f3->get('DEBUG') >= 3) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * 验证错误（400）
     * 
     * @param string $message 错误消息
     * @param array $fields 字段错误详情（可选）
     */
    public function validationError($message = '入力データが無効です', $fields = null) {
        $details = $fields ? ['fields' => $fields] : null;
        $this->error($message, 'VALIDATION_ERROR', 400, $details);
    }
    
    /**
     * 未找到错误（404）
     * 
     * @param string $message 错误消息
     */
    public function notFound($message = '要求されたリソースが見つかりません') {
        $this->error($message, 'NOT_FOUND', 404);
    }
    
    /**
     * 未授权错误（401）
     * 
     * @param string $message 错误消息
     */
    public function unauthorized($message = '認証が必要です') {
        $this->error($message, 'UNAUTHORIZED', 401);
    }
    
    /**
     * 禁止访问错误（403）
     * 
     * @param string $message 错误消息
     */
    public function forbidden($message = 'アクセスが拒否されました') {
        $this->error($message, 'FORBIDDEN', 403);
    }
    
    /**
     * 服务器错误（500）
     * 
     * @param string $message 错误消息
     * @param mixed $details 错误详情（可选）
     */
    public function serverError($message = 'サーバーエラーが発生しました', $details = null) {
        $this->error($message, 'SERVER_ERROR', 500, $details);
    }
    
    /**
     * 数据库错误（500）
     * 
     * @param string $message 错误消息
     */
    public function databaseError($message = 'データベースエラーが発生しました') {
        $this->error($message, 'DATABASE_ERROR', 500);
    }
    
    /**
     * 冲突错误（409）
     * 
     * @param string $message 错误消息
     */
    public function conflict($message = 'リソースが既に存在します') {
        $this->error($message, 'CONFLICT', 409);
    }
    
    /**
     * 请求过大错误（413）
     * 
     * @param string $message 错误消息
     */
    public function tooLarge($message = 'リクエストが大きすぎます') {
        $this->error($message, 'TOO_LARGE', 413);
    }
    
    /**
     * 方法不允许错误（405）
     * 
     * @param string $message 错误消息
     */
    public function methodNotAllowed($message = '許可されていないメソッドです') {
        $this->error($message, 'METHOD_NOT_ALLOWED', 405);
    }
    
    /**
     * 验证必填字段
     * 
     * @param array $data 要验证的数据
     * @param array $requiredFields 必填字段列表
     * @return array|null 如果验证失败返回错误字段数组，成功返回 null
     */
    public function validateRequired($data, $requiredFields) {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = $field . 'は必須項目です';
            }
        }
        
        return empty($errors) ? null : $errors;
    }
    
    /**
     * 验证邮箱格式
     * 
     * @param string $email 邮箱地址
     * @return bool 是否有效
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 验证数字范围
     * 
     * @param mixed $value 要验证的值
     * @param int $min 最小值
     * @param int $max 最大值
     * @return bool 是否在范围内
     */
    public function validateRange($value, $min, $max) {
        return is_numeric($value) && $value >= $min && $value <= $max;
    }
    
    /**
     * 验证字符串长度
     * 
     * @param string $value 要验证的字符串
     * @param int $min 最小长度
     * @param int $max 最大长度
     * @return bool 是否符合长度要求
     */
    public function validateLength($value, $min, $max = null) {
        $length = mb_strlen($value, 'UTF-8');
        
        if ($length < $min) {
            return false;
        }
        
        if ($max !== null && $length > $max) {
            return false;
        }
        
        return true;
    }
}
