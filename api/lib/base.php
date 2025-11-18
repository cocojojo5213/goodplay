<?php
/**
 * Fat-Free Framework (F3) - 基础框架文件
 * 
 * 这是一个简化版本的 F3 框架文件
 * 完整版本请从 https://github.com/bcosca/fatfree 下载
 */

// F3 基础类
class Base {
    private static $instance;
    public $hive;
    
    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->hive = [
            'BASE' => '/',
            'DEBUG' => 0,
            'UI' => './',
            'IMPORTS' => [],
            'LOCALES' => './',
            'FALLBACK' => 'en',
            'LANG' => 'en',
            'ENCODING' => 'UTF-8',
            'CASELESS' => TRUE,
            'CACHE' => FALSE,
            'JAR' => [
                'expire' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => FALSE,
                'httponly' => TRUE
            ],
            'PACKAGE' => '',
            'TEMP' => 'temp/',
            'UNLOAD' => [],
            'ERROR' => NULL,
            'EXCEPTION' => NULL,
            'ONERROR' => NULL,
            'PREROUTE' => NULL,
            'POSTROUTE' => NULL,
            'AUTOLOAD' => './',
            'PLUGINS' => '',
            'ROUTES' => []
        ];
    }
    
    public function set($key, $val) {
        $this->hive[$key] = $val;
        return $val;
    }
    
    public function get($key) {
        return $this->hive[$key] ?? NULL;
    }
    
    public function route($pattern, $handler) {
        $this->hive['ROUTES'][$pattern] = $handler;
    }
    
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->hive['ROUTES'] as $pattern => $handler) {
            if ($this->match($pattern, $path)) {
                if (is_callable($handler)) {
                    call_user_func($handler);
                } elseif (is_string($handler) && class_exists($handler)) {
                    $controller = new $handler();
                    if (method_exists($controller, 'beforeRoute')) {
                        $controller->beforeRoute();
                    }
                    if (method_exists($controller, $method)) {
                        $controller->$method();
                    }
                    if (method_exists($controller, 'afterRoute')) {
                        $controller->afterRoute();
                    }
                }
                return;
            }
        }
        
        // 404 处理
        header('HTTP/1.1 404 Not Found');
        echo '404 - Page not found';
    }
    
    private function match($pattern, $path) {
        // 简单的路由匹配逻辑
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = preg_replace('/\{(\w+)\}/', '([^\/]+)', $pattern);
        return preg_match('/^' . $pattern . '$/', $path);
    }
}

// 全局函数
function F3() {
    return Base::instance();
}