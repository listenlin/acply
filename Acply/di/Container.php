<?php
/**
 * 此代码文件为Acply框架的一部分，版权和许可证信息请查看LICENSE文件。
 * @copyright listenlin <listenlin521@foxmail.com>
 * @since 1.0
 */
namespace Acply\di;

/**
 * 容器对象，装载所有的对象。
 *
 * @author listenlin <listenlin521@foxmail.com>
 */
class Container
{
    /**
     * 用来解析对象依赖
     *
     * @var ResolveDependencyInterface
     */
    public $resolveDependency;

    private $singletons = [];

    private $singleObjects = [];

    private $definitions = [];

    public function __construct()
    {
        $this->resolveDependency = new ResolveDependency();
    }

    public function set(string $class, array $definition): void
    {
        $this->definitions[$class] = $definition;
    }
    
    public function setSingleton(string $class, array $definition): void
    {
        $this->singletons[$class] = true;
        $this->definitions[$class] = $definition;
    }

    public function get($class)
    {
        if (isset($this->singletons[$class])) {
            return $this->singleObjects[$class];
        }

        $dependency = $this->resolveDependency->resolve($class, $this->definitions[$class]);
        if (isset($this->singletons[$class])) {
            return $this->singleObjects[$class] = $this->new($dependency);
        } else {
            return $this->new($dependency);
        }
    }
}
