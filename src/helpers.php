<?php
if (!function_exists('app')) {
    /**
     * 快速获取容器中的实例
     * @param string    $name 类名
     * @param array|bool     $args 参数
     * @param bool      $newInstance    是否每次创建新的实例
     */
    function app(string $name, $args = [], bool $newInstance = false)
    {
        return \Gjc\ThinkPHP5\Container\Container::get($name, $args, $newInstance);
    }
}
