<?php
/**
 * Created by PhpStorm.
 * User: songyongzhan
 * Date: 2018/11/8
 * Time: 17:43
 * Email: songyongzhan@qianbao.com
 */

class Test {
  public function hehe($name, $age) {
    return sprintf('hello %s age:%d', $name, $age);
  }

  public function hehe2($params) {
    return json_encode($params);
  }
}