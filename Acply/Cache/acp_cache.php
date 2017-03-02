<?php

/**
 * 文件级缓存类
 * @author pxl
 */
class Acp_cache extends Acp_base
{

    protected $_cache_path;

    public function __construct()
    {
        parent::__construct();

        $path = $GLOBALS['whole']['app_root'] . DIRECTORY_SEPARATOR . $GLOBALS['whole']['config']->cache_path;

        $this->_cache_path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    // ------------------------------------------------------------------------

    /**
     * 获取缓存信息
     *
     * @param  mixed $id 缓存名
     * @return mixed     缓存内容
     */
    public function get($id)
    {
        if (! file_exists($this->_cache_path.$id)) {
            return false;
        }

        $data = file_get_contents($this->_cache_path.$id);
        $data = unserialize($data);

        if (time() >  $data['time'] + $data['ttl']) {
            unlink($this->_cache_path.$id);
            return false;
        }

        return $data['data'];
    }

    // ------------------------------------------------------------------------

    /**
     * 保存缓存信息
     *
     * @param   string      缓存名称
     * @param   mixed       缓存数据
     * @param   int         缓存生存时间
     *                          - 默认是60秒
     * @return  boolean     是否保存成功
     */
    public function save($id, $data, $ttl = 60)
    {
        $contents = array(
                'time'      => time(),
                'ttl'       => $ttl,
                'data'      => $data
            );

        if (file_put_contents($this->_cache_path.$id, serialize($contents))) {
            @chmod($this->_cache_path.$id, 0777);
            return true;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * 删除缓存
     *
     * @param   mixed       缓存文件名
     * @return  boolean     是否删除成功
     */
    public function delete($id)
    {
        return unlink($this->_cache_path.$id);
    }

    // ------------------------------------------------------------------------

    /**
     * 清理缓存
     *
     * @return  boolean     是否成功清理
     */
    public function clean()
    {
        return $this->_delete_files($this->_cache_path);
    }

    /**
     * 删除文件夹里面的内容
     * @param  string  $path    文件夹路劲
     * @param  boolean $del_dir 是否删除这个文件夹
     * @param  integer $level   文件夹层数
     * @return boolean          是否成功
     */
    function _delete_files($path, $del_dir = false, $level = 0)
    {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (! $current_dir = @opendir($path)) {
            return false;
        }

        while (false !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path.DIRECTORY_SEPARATOR.$filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        $this->_delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
                    }
                } else {
                    unlink($path.DIRECTORY_SEPARATOR.$filename);
                }
            }
        }
        @closedir($current_dir);

        if ($del_dir == true and $level > 0) {
            return @rmdir($path);
        }

        return true;
    }
}
