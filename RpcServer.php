<?php
/**
 * Created by PhpStorm.
 * User: songyongzhan
 * Date: 2018/11/8
 * Time: 17:28
 * Email: songyongzhan@qianbao.com
 */

class RpcServer {


  protected $server = NULL;

  public function __construct($host, $port, $path) {

    $this->server = stream_socket_server(sprintf("tcp://%s:%s", $host, $port), $errno, $error);

    if (!$this->server) {
      exit("{$errno}:{$error}");
    }

    $realPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $path);
    if ($realPath === FALSE || !file_exists($realPath)) {
      exit("{$realPath} error.\n");
    }

    while (TRUE) {

      $client = @stream_socket_accept($this->server);

      if ($client) {
        //这里为了简单，我们一次性读取
        $buf = fread($client, 2048);
        //解析客户端发送过来的协议
        $classRet = preg_match('/Rpc-Class:\s(.*);[\r\n|\r|\n]?/i', $buf, $class);
        $methodRet = preg_match('/Rpc-Method:\s(.*);[\r\n|\r|\n]?/i', $buf, $method);
        $paramsRet = preg_match('/Rpc-Params:\s(.*);[\r\n|\r|\n]?/i', $buf, $params);

        printf("===================================\n");
        printf("classRet:%s   methodRet:%s  $paramsRet:%s  class:%s\n", $classRet, $methodRet,$paramsRet, json_encode($class));
        printf("===================================\n");
        if ($classRet && $methodRet) {
          $class = ucfirst($class[1]);
          $file = $realPath . '/' . $class . '.php';
          //判断文件是否存在，如果有，则引入文件
          if (file_exists($file)) {
            require_once $file;
            //实例化类，并调用客户端指定的方法
            $obj = new $class();
            //如果有参数，则传入指定参数
            if (!$paramsRet) {
              $data = call_user_func_array([$obj,$method[1]],[]);
            } else {
              $data = call_user_func_array([$obj,$method[1]],json_decode($params[1]));
            }
            //把运行后的结果返回给客户端
            fwrite($client, $data);
          }
        } else {
          fwrite($client, "class or method error\n");
        }
        //关闭客户端
        fclose($client);

      }


    }


  }


  public function __destruct() {
    fclose($this->server);
  }

}


new RpcServer('127.0.0.1', 8888, 'services');
