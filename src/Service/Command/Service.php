<?php


namespace Gjc\ThinkPHP5\Container\Command;

use think\App;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Service extends Command
{
    protected function configure()
    {
        $this->setName('make:service')->setDescription('本命令可一键创建api的controller，model，service');
        $this->addArgument('name', Argument::REQUIRED, "The name of the class");
    }

    protected function execute(Input $input, Output $output)
    {

        $name = trim($input->getArgument('name'));

        $className = $this->getClassName($name);
        file_put_contents($this->buildControllerPath($className), $this->buildControllerClass($className));
        $output->writeln("创建 controller/{$name}.php 成功");

        file_put_contents($this->buildModelPath($className), $this->buildModelClass($className));
        $output->writeln("创建 model/{$name}.php 成功");

        file_put_contents($this->buildServicePath($className), $this->buildServiceClass($className));
        $output->writeln("创建 services/{$name}Service.php 成功");

        $this->updateBaseServiceCode($className);
        $output->writeln('更新 BaseService 成功');
        $output->writeln('操作完成');
    }

    protected function getClassName($name)
    {
        return ucwords($name);
    }

    protected function buildControllerClass($name)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/controller.stub');
        return str_replace(['{%className%}'], [
            $name,
        ], $stub);
    }

    protected function buildControllerPath($className)
    {
        return APP_PATH . '/api/controller/' . $className . '.php';
    }

    protected function buildModelClass($name)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/model.stub');
        return str_replace(['{%className%}'], [
            $name,
        ], $stub);
    }

    protected function buildModelPath($className)
    {
        return APP_PATH . '/common/model/' . $className . '.php';
    }

    protected function buildServiceClass($name)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/service.stub');
        return str_replace(['{%className%}'], [
            $name,
        ], $stub);
    }

    protected function buildServicePath($className)
    {
        return APP_PATH . '/services/' . $className. 'Service.php';
    }

    /**
     * 更新 BaseService 中的代码
     * @param $className
     */
    protected function updateBaseServiceCode($className)
    {
        $useCode = "use app\common\model\\{$className};
//{%add use model%}";

        $functionCode = "/**
     * @return {$className}Service
     */
    protected function get{$className}Service()
    {
        return app({$className}Service::class);
    }

    /**
     * @return {$className}
     */
    protected function get{$className}Model()
    {
        return app({$className}::class);
    }

    //{%add function code%}";

        $path = APP_PATH . '/services/BaseService.php';
        $service = file_get_contents($path);
        $content = str_replace(['//{%add use model%}', '//{%add function code%}'], [
            $useCode,
            $functionCode,
        ], $service);

        file_put_contents($path, $content);
    }
}
