<?php
/**
 * 此代码文件为Acply框架的一部分，版权和许可证信息请查看LICENSE文件。
 * @copyright listenlin <listenlin521@foxmail.com>
 * @since 1.0
 */
namespace Acply\di;

/**
 * 实例化某个对象时，解析其依赖。
 *
 * @author listenlin <listenlin521@foxmail.com>
 */
class ResolveDependency implements ResolveDependencyInterface
{
    /**
     * 缓存某个类的依赖配置信息
     * [
     *     'class' => 'someNameapce\someClassName',
     *     ''
     * ]
     *
     * @var array
     */
    protected $dependencies = [];

    public function resolve(string $class, array $definition)
    {

    }
}
