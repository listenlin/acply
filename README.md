Acply PHP框架
============

## 框架来由

应该是在2013年下半年时，在开源中国里，读了教如何写PHP框架的系列博客。

冲动就此产生，开始动手撸自己的框架。

在有了一个雏形后，恰好当时处于创业初期，自己是产品主力开发，就直接把框架套进去使用了，后面边开发边改进。

此框架在线上运行了一年多后，因公司扩大，产品需求增加，没有精力维护，而且使用起来也并不方便。

就逐渐的将技术栈转移到Yii2框架上。就此成为历史。

最近(2017.3.1)心血来潮，又将这个框架从历史尘埃里翻了出来，修改后将其开源。

主要打算基于此造一些感兴趣的轮子。

## 框架概述

学会使用PHP开发不久，甚至还没学过任何一门框架，就开始了此框架的开发，所以各种烂代码一堆堆的在里面。

准备：

1. 将代码按照PSR2规范调整。
2. 优化各个模块，调整命名、梳理依赖结构、日志规范调整为PSR3、将类自动加载规范为PSR4等等。
3. 改为使用php7.1及以上的新特性开发，不兼容5.x和7.x及以下版本。
4. 完善一个小案例。
5. ……

## 使用Docker搭建运行环境

安装Docker后，可简单执行`./Bin/start.sh`脚本即可。

也可手动去Docker目录下，使用`docker build`建立配套的镜像，再启动容器。
```
cd Docker
docker build -t acply ./
docker run -v your_acply_path:/var/www/ -p 80:80 acply
```