<?php
namespace Yuntan\Service;

class BaseShare extends Gateway {
  function __construct($host, $key = '', $secret = '', $bucket = '') {
    parent::__construct($host, $key, $secret, true);
  }
  function create_share($name, $share_name) {
    $this -> valid($name);
    $this -> valid($share_name);
    $share = [
      "name"      => $name,
      "sharename" => $share_name,
    ];
    $pathname = "/api/shares/";
    return $this -> requestJSON($pathname, Requests::POST, $share);
  }

  function create_share_history($name, $score, $summary) {
    $this -> valid($name);
    $hist = [
      "score"   => ceil($score),
      "summary" => $summary,
    ];
    $pathname = "/api/shares/{$name}/hists/";
    return $this -> requestJSON($pathname, Requests::POST, $hist);
  }

  function save_config($key, $value) {
    $this -> valid($key);
    $config = [
      "value"   => $value,
    ];
    $pathname = "/api/config/{$key}/";
    return $this -> requestJSON($pathname, Requests::POST, $config);
  }

  function get_config($key) {
    $this -> valid($key);
    $pathname = "/api/config/{$key}/";
    return $this -> requestJSON($pathname, Requests::GET);
  }

  function get_share($name) {
    $this -> valid($name);
    $pathname = "/api/shares/{$name}/";
    return $this -> requestJSON($pathname, Requests::GET);
  }

  function get_share_childs($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $pathname = "/api/shares/{$name}/childs/";
    return $this -> requestJSON($pathname, Requests::GET, ["from" => $from, "size" => $size]);
  }

  function get_share_hists($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $pathname = "/api/shares/{$name}/hists/";
    return $this -> requestJSON($pathname, Requests::GET, ["from" => $from, "size" => $size]);
  }

  function get_share_patch($name, $start_time = 0, $end_time = 0) {
    $this -> valid($name);
    $pathname = "/api/shares/{$name}/patch/";
    return $this -> requestJSON($pathname, Requests::GET, ["start_time" => $start_time, "end_time" => $end_time]);
  }

  function get_share_list($from = 0, $size = 10) {
    $pathname = "/api/shares/{$name}/";
    return $this -> requestJSON($pathname, Requests::GET, ["from" => $from, "size" => $size]);
  }

  function get_statistic_share_history($start_time, $end_time, $from = 0, $size = 10) {
    $pathname = "/api/statistic/";
    return $this -> requestJSON($pathname, Requests::GET, ["start_time" => $start_time, "end_time" => $end_time, "from" => $from, "size" => $size]);
  }
}

class Share {

  protected $share = null;
  protected $coin  = null;

  function __construct($host, $key = '', $secret = '', $coin = null) {
    $this -> share = new BaseShare($host, $key, $secret);
    $this -> coin = $coin;
  }

  function create_share($name, $share_name) {
    return $this -> share -> create_share($name, $share_name);
  }

  function create_share_history($name, $score, $summary) {
    $patchs = $this -> share -> create_share_history($name, $score, $summary);
    foreach ($patchs as $patch) {
      $patch_name = $patch['name'];
      $patch_score = $patch['patch_score'];
      $this -> coin -> save_coin($patch_name, $patch_score, $summary);
    }
    return $patchs;
  }
  function save_config($key, $value) {
    return $this -> share -> save_config($key, $value);
  }

  function get_config($key) {
    return $this -> share -> get_config($key);
  }

  function get_share($name) {
    $share = $this -> share -> get_share($name);
    $share['remain_score'] = $this -> coin -> get_score($name);
    return $share;
  }

  function get_share_childs($name, $from = 0, $size = 10) {
    return $this -> share -> get_share_childs($name, $from, $size);
  }

  function get_share_hists($name, $from = 0, $size = 10) {
    return $this -> share -> get_share_hists($name, $from, $size);
  }

  function get_share_patch($name, $start_time = 0, $end_time = 0) {
    return $this -> share -> get_share_patch($name, $start_time, $end_time);
  }

  function get_share_list($from = 0, $size = 10) {
    return $this -> share -> get_share_list($from, $size);
  }

  function get_statistic_share_history($start_time, $end_time, $from = 0, $size = 10) {
    return $this -> share -> get_statistic_share_history($start_time, $end_time
      , $from, $size);
  }

  function get_coin() {
    return $this -> coin;
  }

}
