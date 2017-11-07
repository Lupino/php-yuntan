<?php

namespace Yuntan\Service;

class Coin extends Gateway {
  const COIN_TYPE_INCR = 'Incr';
  const COIN_TYPE_DECR = 'Decr';
  public $bucket = '';

  function __construct($host, $key = '', $secret = '', $bucket = '') {
    parent::__construct($host, $key, $secret, true);
    if ($bucket) {
      $this -> valid($bucket);
      $this -> bucket = $bucket;
    }
  }

  function get_score($name) {
    $this -> valid($name);
    $pathname = "/api/coins/{$this->bucket}{$name}/score/";
    return $this -> requestJSON($pathname);
  }

  function get_coin_list($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $pathname = "/api/coins/{$this->bucket}{$name}/";
    return $this -> requestJSON($pathname, Requests::GET, ["from" => $from, "size" => $size]);
  }

  function save_coin($name, $score = 0, $desc = '', $type=self::COIN_TYPE_INCR, $created_at = 0) {
    $this -> valid($name);
    $coin = [
      "score"      => $score,
      "desc"       => $desc,
      "type"       => $type,
      "created_at" => $created_at,
    ];

    $pathname = "/api/coins/{$this->bucket}{$name}/";
    return $this -> requestJSON($pathname, Requests::POST, $coin);
  }

  protected function valid($name) {
    if (!preg_match('/^[a-zA-Z0-9\._=-]+$/', $name)) {
      throw new Exception('Invalid coin name');
    }
  }
}
