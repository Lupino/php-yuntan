<?php

namespace Yuntan\Service;

class Gateway {
  public $host = 'https://gw.huabot.com';
  public $key = '';
  public $secret = '';

  function __construct($host, $key = '', $secret = '') {
    $this -> host = $host;
    $this -> key = $key;
    $this -> secret = $secret;
  }

  protected function signParams($params = array()) {
    $keys = array_keys($params);
    sort($keys);
    $sign_data = "";
    foreach($keys as $key) {
      $sign_data .= "{$key}{$params[$key]}";
    }

    return strtoupper(hash_hmac('sha256', $sign_data, $this -> secret));
  }


  protected function get_headers($sign = false, $params=array()) {
    $now = time();
    if ($sign) {
      $params['key'] = $this -> key;
      $params['timestamp'] = $now;
      $sign = $this -> signParams($params);
      return [
        "User-Agent" => "dispatch-php-1.0.0",
        "X-REQUEST-KEY"  => $this -> key,
        "X-REQUEST-SIGNATURE" => $sign,
        "X-REQUEST-TIME" => $now,
      ];
    } else {
      return [
        "User-Agent" => "dispatch-php-1.0.0",
        "X-REQUEST-KEY"  => $this -> key,
        "X-REQUEST-TIME" => $now,
      ];
    }
  }

  protected function errorResponse($req) {
    $rsp = json_decode($req -> body, true);
    if (isset($rsp['err'])) {
      throw new Exception($rsp['err']);
    }
    throw new Exception("unkonw exception");
  }

  protected function create_nonce($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str ="";
    for ( $i = 0; $i < $length; $i++ )  {
      $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $str;
  }

  public function signature_secret($sign_path, $length = 16) {
    $nonce = $this -> create_nonce($length);
    $timestamp = time();

    $sign = strtoupper(hash_hmac('sha256', "{$this -> secret}{$sign_path}{$timestamp}", $nonce));

    return [
      "secret" => $sign,
      "timestamp" => $timestamp,
      "nonce" => $nonce,
    ];
  }
}
