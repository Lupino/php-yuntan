<?php

namespace Yuntan\Service;

class Coin extends Gateway {
  const COIN_TYPE_INCR = 'Incr';
  const COIN_TYPE_DECR = 'Decr';
  public $bucket = '';

  function __construct($host, $key = '', $secret = '', $bucket = '') {
    parent::__construct($host, $key, $secret);
    if ($bucket) {
      $this -> valid($bucket);
      $this -> bucket = $bucket;
    }
  }

  function get_score($name) {
    $this -> valid($name);
    $path = "/api/coins/{$this->bucket}{$name}/score/";
    $headers = $this -> get_headers(true, ['sign_path' => $path]);
    $req = Requests::get("{$this -> host}{$path}", $headers);
    if ($req -> status_code == 200) {
      $rsp = json_decode($req -> body, true);
      return $rsp['score'];
    }
    $this -> errorResponse($req);
  }

  function get_coin_list($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $path = "/api/coins/{$this->bucket}{$name}/";
    $headers = $this -> get_headers(true, ['from' => $from, 'size' => $size, "sign_path" => $path]);
    $req = Requests::get("{$this -> host}{$path}?from={$from}&size={$size}", $headers);
    if ($req -> status_code == 200) {
      $rsp = json_decode($req -> body, true);
      return $rsp;
    }
    $this -> errorResponse($req);
  }

  function save_coin($name, $score = 0, $desc = '', $type=self::COIN_TYPE_INCR, $created_at = 0) {
    $this -> valid($name);
    $coin = [
      "score"      => $score,
      "desc"       => $desc,
      "type"       => $type,
      "created_at" => $created_at,
    ];

    $path = "/api/coins/{$this->bucket}{$name}/";
    $params = array_copy($coin);
    $params["sign_path"] = $path;
    $headers = $this -> get_headers(true, $params);
    $req = Requests::post("{$this -> host}{$path}", $headers, $coin);
    if ($req -> status_code == 200) {
      $rsp = json_decode($req -> body, true);
      return $rsp['score'];
    }
    $this -> errorResponse($req);
  }

  protected function valid($name) {
    if (!preg_match('/^[a-zA-Z0-9\._=-]+$/', $name)) {
      throw new Exception('Invalid coin name');
    }
  }
}
