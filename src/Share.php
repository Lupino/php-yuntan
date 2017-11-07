<?php
namespace Yuntan\Service;

class BaseShare extends Gateway {
  function create_share($name, $share_name) {
    $this -> valid($name);
    $this -> valid($share_name);
    $share = [
      "name"      => $name,
      "sharename" => $share_name,
    ];

    $params = array_copy($share);
    $params["sign_path"] = "/api/shares/";
    $headers = $this -> get_headers(true, $params);
    $req = Requests::post("{$this -> host}/api/shares/", $headers, $share);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function create_share_history($name, $score, $summary) {
    $this -> valid($name);
    $hist = [
      "score"   => ceil($score),
      "summary" => $summary,
    ];

    $params = array_copy($hist);
    $params["sign_path"] = "/api/shares/{$name}/hists/";
    $headers = $this -> get_headers(true, $params);
    $req = Requests::post("{$this -> host}/api/shares/{$name}/hists/", $headers, $hist);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function save_config($key, $value) {
    $this -> valid($key);
    $config = [
      "value"   => $value,
    ];

    $params = [
      "value"   => $value,
      "sign_path" => "/api/config/{$key}/",
    ];
    $headers = $this -> get_headers(true, $params);
    $req = Requests::post("{$this -> host}/api/config/{$key}/", $headers, $config);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_config($key) {
    $this -> valid($key);
    $params = [
      "sign_path" => "/api/config/{$key}/",
    ];
    $headers = $this -> get_headers(true, $params);
    $req = Requests::get("{$this -> host}/api/config/{$key}/", $headers);
    if ($req -> status_code == 200) {
      $rsp = json_decode($req -> body, true);
      return $rsp['value'];
    }
    $this -> errorResponse($req);
  }

  function get_share($name) {
    $this -> valid($name);
    $params = [
      "sign_path" => "/api/shares/{$name}/",
    ];
    $headers = $this -> get_headers(true, $params);
    $req = Requests::get("{$this -> host}/api/shares/{$name}/", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_share_childs($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $headers = $this -> get_headers(true, ['from' => $from, 'size' => $size,
        "sign_path" => "/api/shares/{$name}/childs/"]);
    $req = Requests::get("{$this -> host}/api/shares/{$name}/childs/?from={$from}&size={$size}", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_share_hists($name, $from = 0, $size = 10) {
    $this -> valid($name);
    $headers = $this -> get_headers(true, ['from' => $from, 'size' => $size,
        "sign_path" => "/api/shares/{$name}/hists/"]);
    $req = Requests::get("{$this -> host}/api/shares/{$name}/hists/?from={$from}&size={$size}", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_share_patch($name, $start_time = 0, $end_time = 0) {
    $this -> valid($name);
    $headers = $this -> get_headers(true, ['start_time' => $start_time, 'end_time' => $end_time,
        "sign_path" => "/api/shares/{$name}/patch/"]);
    $req = Requests::get("{$this -> host}/api/shares/{$name}/patch/?start_time={$start_time}&end_time={$end_time}", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_share_list($from = 0, $size = 10) {
    $headers = $this -> get_headers(true, ['from' => $from, 'size' => $size,
        "sign_path" => "/api/shares/"]);
    $req = Requests::get("{$this -> host}/api/shares/?from={$from}&size={$size}", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
  }

  function get_statistic_share_history($start_time, $end_time, $from = 0, $size = 10) {
    $headers = $this -> get_headers(true, ['start_time' => $start_time
      , 'end_time' => $end_time, 'from' => $from, 'size' => $size,
        "sign_path" => "/api/statistic/"]);
    $req = Requests::get("{$this -> host}/api/statistic/?from={$from}&size={$size}&start_time={$start_time}&end_time={$end_time}", $headers);
    if ($req -> status_code == 200) {
      return json_decode($req -> body, true);
    }
    $this -> errorResponse($req);
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
