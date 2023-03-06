<?php


namespace Gjc\ThinkPHP5\Container\Service\Command;

use think\App;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Service extends Command
{
    protected $type = [
        'controller',
        'model',
        'service',
    ];

    protected function configure()
    {
        $this->setName('make:service')->setDescription('本命令可一键创建FastAdmin api的controller，model，service');
        $this->addArgument('name', Argument::REQUIRED, "The name of the class");
    }

    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));

        $className = $this->getClassName($name);

        if ($this->fileExists($name)) {
            return false;
        };

        foreach ($this->type as $type) {
            $pathname = $this->getPathName($className, $type);
            if (!is_dir(dirname($pathname))) {
                mkdir(dirname($pathname), 0755, true);
            }

            file_put_contents($pathname, $this->buildClassContent($className, $type));
            $output->writeln("创建 $type 成功");
        }

        $this->updateBaseServiceCode($className);
        $output->writeln('更新 BaseService 成功');
        $output->writeln('操作完成');
    }

    protected function fileExists($className)
    {
        foreach ($this->type as $type) {
            $pathname = $this->getPathName($className, $type);
            if (is_file($pathname)) {
                $this->output->writeln('<error>' . $className . ' ' . ucwords($type) . ' already exists!</error>');
                return true;
            }
        }

        return false;
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

    protected function getPathName($className, $type)
    {
        switch ($type) {
            case 'controller':
                return APP_PATH . 'api/controller/' . $className . '.php';
                break;
            case 'model':
                return APP_PATH . 'common/model/' . $className . '.php';
                break;
            case 'service':
                return APP_PATH . 'services/' . $className. 'Service.php';
                break;
        }
    }

    protected function buildClassContent($name, $type)
    {
        switch ($type) {
            case 'controller':
                return $this->buildControllerClass($name);
                break;
            case 'model':
                return $this->buildModelClass($name);
                break;
            case 'service':
                return $this->buildServiceClass($name);
                break;
        }
    }

    protected function buildModelClass($name)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/model.stub');
        return str_replace(['{%className%}'], [
            $name,
        ], $stub);
    }

    protected function buildServiceClass($name)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/service.stub');
        return str_replace(['{%className%}'], [
            $name,
        ], $stub);
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

        $path = APP_PATH . 'services/BaseService.php';
        if (!is_file($path)) {
            $this->buildBaseService($path);
        }

        $service = file_get_contents($path);
        $content = str_replace(['//{%add use model%}', '//{%add function code%}'], [
            $useCode,
            $functionCode,
        ], $service);

        file_put_contents($path, $content);
    }

    protected function buildBaseService($pathName)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/baseService.stub');
        file_put_contents($pathName, $stub);

        return true;
    }
}
