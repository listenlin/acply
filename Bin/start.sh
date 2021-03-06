#!/bin/bash
# 使用docker启动开发环境，或者建立开发环境
# listenlin <listenlin521@foxmail.com>

docker -v > /dev/null
if [ $? -ne 0 ]; then
    echo "请先安装docker.."
    exit
fi

docker images | grep "acply" > /dev/null
if [ $? -ne 0 ]; then
    docker build -t acply ./docker
fi

# 获取项目根目录路径
path=`dirname $0`
path=`dirname $path`
cd $path
path=`pwd`

docker run -v $path:/var/www -p 80:80 acply