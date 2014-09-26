<?php
/**
 *  Discogs PHP API interface
 *
 *  @cecekpawon - thrsh.net
 */

class Discogs {
  protected $api_url = "http://api.discogs.com/";
  private $_user_agent = "yod-php-discogs-api/1.0.0 +thrsh.net";

  var
    $only_success = FALSE,
    $as_array = FALSE,
    $debug = FALSE,
    $native_user_agent = TRUE;

  /**
   * [_parse description]
   *
   * @param  string $json_str []
   *
   * @return [array / object] []
   */
  private function _parse($json_str) {
    $result = FALSE;

    $json = json_decode($json_str, $this->as_array);

    if (json_last_error() == JSON_ERROR_NONE) {
      $result = $json;
    }

    return $result;
  }

  /**
   * [_curl description]
   *
   * @param  string $url      []
   * @param  string $postdata []
   *
   * @return array            []
   */
  private function _curl($url, $postdata = "") {
    $curl = curl_init();
    $curl_opt_post_arr = array();

    $curl_opt_array = array(
      CURLOPT_USERAGENT => $this->_user_agent,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_VERBOSE => 0,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_URL => $url
    );

    if ($postdata) {
      $curl_opt_post_arr = array(
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => $postdata,
        CURLOPT_HTTPHEADER => array('Content-Type: text/xml; charset=utf8', 'Content-length: ' . strlen($postdata))
      );
    }

    $curl_opt_array += $curl_opt_post_arr;

    curl_setopt_array($curl, $curl_opt_array);

    $http_result = curl_exec($curl);
    $http_code   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    $res = array(
      "code" => $http_code,
      "res" => $http_result,
      "url" => $url
    );

    return $res;
  }

  /**
   * [_get description]
   *
   * @param  string $path      []
   * @param  array  $param     []
   * @param  array  $query_arr []
   *
   * @return array             []
   */
  private function _get($path, $param = array(), $query_arr = array()) {
    if (is_array($param) && count($param)) {
      $param = rtrim(implode("/", $param), "/");
    }

    if (!is_array($query_arr)) $query_arr = array();

    if (count($query_arr)) {
      $param .= "?" . http_build_query($query_arr);
    }

    $url = $this->api_url . $path . '/' . $param;

    $res = $this->_curl($url);

    return $this->_output($res);
  }

  /**
   * [_output description]
   *
   * @param  array $res []
   *
   * @return [string / array / object]
   */
  private function _output($res) {
    // return string
    if ($this->debug) {
      return json_encode($res);
    }

    // return array / object
    $no_result = $this->as_array ? array() : new stdClass();
    $result = $this->_parse($res["res"]);
    $is_success = $result && in_array($res["code"], range(200, 202));

    if (!$result || ($this->only_success && !$is_success)) {
      $result = $no_result;
    }

    return $result;
  }

  /**
   * [_get_bool description]
   *
   * @param  [string / integer] $val
   *
   * @return bool
   */
  private function _get_bool($val) {
    return preg_match("#(true|1)#i", $val) ? TRUE : FALSE;
  }

  /**
   * [__construct description]
   *
   * @param array $options [
     * $only_success = FALSE,
     * $as_array = FALSE,
     * $debug = FALSE,
     * $native_user_agent = TRUE;
   * ]
   */
  public function __construct($options = array()) {
    $reflect = new ReflectionClass($this);

    foreach ($options as $key => $val) {
      $key = strtolower(trim((string) $key));
      $val = $this->_get_bool($val);

      if(property_exists($this, $key)) {
        if (!$reflect->getProperty($key)->isPublic()) continue;
        $this->{$key} = $val;
      }
    }

    $this->only_success = $this->debug ? FALSE : $this->only_success;
    $this->_user_agent = $this->native_user_agent ? $this->_user_agent : $_SERVER['HTTP_USER_AGENT'];
  }

  /**
   * [releases description]
   *
   * @param  integer $release_id [required]
   *
   * @return [array / object]    []
   */
  public function releases($release_id = 0) {
    return $this->_get("releases", $release_id);
  }

  /**
   * [masters description]
   *
   * @param  integer $master_id [required]
   * @param  boolean $versions  [optional]
   * @param  array   $param     [optional]
   *
   * @return [array / object]   []
   */
  public function masters($master_id = 0, $versions = FALSE, $param = array()) {
    if (!is_array($param)) $param = array();
    return $this->_get("masters", array($master_id, $this->_get_bool($versions) ? "versions" : FALSE), $param);
  }

  /**
   * [artists description]
   *
   * @param  integer $artist_id [required]
   * @param  boolean $releases  [optional]
   *
   * @return [array / object]   []
   */
  public function artists($artist_id = 0, $releases = FALSE) {
    return $this->_get("artists", array($artist_id, $this->_get_bool($releases) ? "releases" : FALSE));
  }

  /**
   * [labels description]
   *
   * @param  integer $label_id [required]
   * @param  boolean $releases [optional]
   * @param  array   $param    [optional]
   *
   * @return [array / object]  []
   */
  public function labels($label_id = 0, $releases = FALSE, $param = array()) {
    if (!is_array($param)) $param = array();
    return $this->_get("labels", array($label_id, $this->_get_bool($releases) ? "releases" : FALSE), $param);
  }

  /**
   * [search description]
   *
   * @param  array  $param [required]
   * @param  string $query [optional]
   *
   * @return [array / object] []
   */
  public function search($param = array(), $query = "") {
    if (!is_array($param)) $param = array();
    if ($query = (string) trim($query)) $param = array("q" => $query) + $param;

    return $this->_get("database", array("search"), $param);
  }
}
?>