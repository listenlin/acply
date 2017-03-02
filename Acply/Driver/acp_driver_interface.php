<?php
/**
 *@copyright Copyright 2014 listenlin. All rights reserved.
 *@author listenlin <listenlin521@foxmail.com>
 *@version 1.0
 *@package Acply\acp_driver_interface
 *
 * 所有数据库驱动都必须实现的接口。
 */
interface Acp_driver_interface
{
    public function query($sql); // 直接输入SQL语句接口
    public function select(array $table_name, array $sql); // 查询接口
    public function insert(array $table_name, array $sql); // 插入接口
    public function update(array $table_name, array $sql); // 更新接口
    public function delete(array $table_name, array $sql); // 删除接口
    public function lastid(); // 获取最后插入主键值
    public function affectRows(); // 获取受SQL操作后受影响的条数
    public function splitPage($p, $l, $t, $s); // 分页的实现
    public function getResult(); // 返回结果集(类Acp_result的实例)
    public function beginTransaction(); // 启动数据库的事务
    public function commit(); // 提交事务
    public function rollBack(); // 回滚事务
    public function inTransaction();//是否在一个事务里面，也就是当前是否开启事务
    public function getError(); // 获取错误信息。
    public function getLastSql();//获取最近执行的SQL语句
    public function changeUser($qx); // 改变数据库用户，等同于更改权限
    public function getConnect(); // 获取PDO对象
}
