<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage couch
 */

class CouchDB {
   function __construct($database, $host = "localhost", $port = 5984) {
      $this->db = $database;
      $this->host = $host;
      $this->port = $port;
   }

   function getAll() {
      $url = "/" . $this->db . "/_all_docs";
      return $this->request($url);
   }

   function getView($design, $view, $limit = null, $descending = null) {
      $url = "/" . $this->db . "/_design/" . $design . "/_view/" . $view;
      return $this->request($url);
   }

   function getOne($id) {
      $url = "/" . $this->db . "/" . $id;
      return $this->request($url);
   }

   function insert($obj) {
      $url = "/" . $this->db;
      $options = array("method" => HTTP_METH_POST,
                       "postdata" => json_encode($obj));
      return $this->request($url, $options);
   }

   function update($id, $obj) {
      $url = "/" . $this->db . "/" . $id;
      $obj['_id'] = $id;
      $options = array("method" => HTTP_METH_PUT,
                       "postdata" => json_encode($obj));
      return $this->request($url, $options);
   }

   function delete($id, $rev) {
      $url = "/" . $this->db . "/" . $id;
      $options = array("method" => HTTP_METH_DELETE,
                       "params" => array("rev" => $rev));
      return $this->request($url, $options);
   }

   function request($url, $options = array()) {
      $url = "http://" . $this->host . ":" . $this->port . $url;
      $method = HTTP_METH_GET;
      $headers = array("Host" => $this->host,
                       "Referer" => "http://localhost/",
                       "Content-Type" => "application/json");
      $params = '';
      foreach ($options as $name => $option) {
         switch ($name) {
            case "method":
               $method = $option;
               break;
            case "headers":
               $headers = array_merge($option, $headers);
               break;
            case "params":
               $params = http_build_query($option);
               break;
            case "postdata":
               $post_data = $option;
               break;
            default:
               trigger_error("Unknown http option: $name", E_USER_WARNING);
         }
      }
      if (!empty($params)) {
         $url = $url . "?" . $params;
      }
      $request = new HttpRequest($url, $method);
      $request->setHeaders($headers);
      if (isset($post_data)) {
         if ($method == HTTP_METH_PUT) {
            $request->addPutData($post_data);
         } else if ($method == HTTP_METH_POST) {
            $request->setRawPostData($post_data);
         }
      }
      $json = $request->send()->getBody();
      $data = json_decode($json, true);
      if (isset($data['rows'])) {
         $data = new CouchResult($data);
      }
      return $data;
   }

}

?>
