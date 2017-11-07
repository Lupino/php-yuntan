<?php

namespace Yuntan\Service;

class Gateway {
  private $host = 'https://gw.huabot.com';
  private $key = '';
  private $secret = '';
  private $secure = '';

  function __construct($host, $key = '', $secret = '', $secure = false) {
    $this -> host = $host;
    $this -> key = $key;
    $this -> secret = $secret;
    $this -> secure = $secure;
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
        "User-Agent" => "yuntan-php-1.0.0",
        "X-REQUEST-KEY"  => $this -> key,
        "X-REQUEST-SIGNATURE" => $sign,
        "X-REQUEST-TIME" => $now,
      ];
    } else {
      return [
        "User-Agent" => "yuntan-php-1.0.0",
        "X-REQUEST-KEY"  => $this -> key,
        "X-REQUEST-TIME" => $now,
      ];
    }
  }


  protected function request($pathname, $method=Requests::GET, $data=null) {
    $params = ["pathname" => $pathname];
    if ($data) {
      foreach($data as $key => $value) {
        $params[$key] = $value;
      }
    }

    $url = "{$this -> host}{$pathname}";
    $secure = $this -> secure;
    if ($method == Requests::GET) {
      if ($data) {
        $data = urlencode($data);
        $url = "{$url}?{$data}";
        $data = null;
      }
    } else {
      $secure = true;
    }

    $headers = $this -> get_headers($secure, $params);

		return Requests::request($url, $headers, $data, $method);
  }

  protected function requestJSON($pathname, $method=Requests::GET, $data=null) {
    $req = $this -> request($pathname, $method, $data);
    if ($req -> status_code == 200) {
      $rsp = json_decode($req -> body, true);
      $keys = array_keys($rsp);
      if (count($keys) == 1) {
        return $rsp[$keys[0]];
      }
      return $rsp;
    }
    $this -> errorResponse($req);
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

  public function signature_secret($method, $pathname, $length = 16) {
    $nonce = $this -> create_nonce($length);
    $timestamp = time();

    $sign = strtoupper(hash_hmac('sha256', "{$this -> secret}{$method}{$pathname}{$timestamp}", $nonce));

    return [
      "secret" => $sign,
      "timestamp" => $timestamp,
      "nonce" => $nonce,
      "method" => $method,
    ];
  }
}
