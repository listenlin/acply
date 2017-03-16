# 运行Acply框架，并且使用php7.1.x。

FROM php:7.1-apache

MAINTAINER listenlin <listenlin521@foxmail.com>

LABEL Name=Acply Version=1.0

# 删除默认虚拟主机配置，增加自定义配置。主要是不会用到html文件夹，而是Public。
RUN rm -f /etc/apache2/sites-enabled/000-default.conf
COPY ./conf/vhost.conf /etc/apache2/sites-enabled/
# 把重写功能加上
COPY ./conf/rewrite.load /etc/apache2/mods-enabled/

VOLUME ["/var/www"]  

EXPOSE 80

CMD ["apache2-foreground"]
