<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/25
 * Time: 15:43
 */

namespace Home\Controller;

/**
 * Class CacheController
 * 
 * @package Home\Controller
 * 
 * 
 * @discribe :此类用于管理缓存
 */
class CacheController extends CommonController
{

    //cache clear
    public function clear(){
       $admin_cache_path = dirname(dirname(dirname(__FILE__))).'/Runtime';
       $this->delFileByDir($admin_cache_path);
       $index_cache_path = dirname(dirname(dirname(dirname(__FILE__)))).'/App/Runtime';
       $this->delFileByDir($index_cache_path);
       $this->success("缓存清除成功");
    }

    private function delFileByDir($dir) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
           if ($file != "." && $file != "..") {
     
              $fullpath = $dir . "/" . $file;
              if (is_dir($fullpath)) {
                 $this->delFileByDir($fullpath);
              } else {
                 unlink($fullpath);
              }
           }
        }
        closedir($dh);
     }
}