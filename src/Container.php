<?php


namespace Gjc\ThinkPHP5\Container;

use ReflectionClass;
use ReflectionMethod;

class Container
{
    /**
     * @var Container
     */
    private static $instance;

    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];

    /**
     * 获取容器中的对象实例
     * @access public
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     */
    public static function get($abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 获取当前容器的实例（单例）
     * @access public
     * @return Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 创建类的实例
     * @access public
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     */
    public function make($abstract, $vars = [], $newInstance = false)
    {
        if (true === $vars) {
            // 总是创建新的实例化对象
            $newInstance = true;
            $vars        = [];
        }

        //单例
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        $reflectionClass = new ReflectionClass($abstract);

        $constructor = $reflectionClass->getConstructor();

        $args = $constructor ? $this->getParameters($constructor, $vars) : [];

        $object = $reflectionClass->newInstanceArgs($args);

        $this->instances[$abstract] = $object;

        return $object;
    }

    /**
     * @param ReflectionMethod $reflect
     * @param array $vars
     * @return array
     * @throws \ReflectionException
     */
    public function getParameters(ReflectionMethod $reflect, $vars = [])
    {
        $result = [];
        if (0 === $reflect->getNumberOfParameters()) {
            return [];
        }

        $parameters = $reflect->getParameters();

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            //检测此参数是否有默认值
            if (isset($vars[$name])) {
                $result[] = $vars[$name];
            } else if ($parameter->isDefaultValueAvailable()) {
                $result[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception('parameter ' . $name . ' is required');
            }
        }

        return $result;
    }

}









