<?php

class <MODEL_CLASS_NAME> extends ModelBase {
   public function getAll() {
      $couch = new CouchDB("<DATABASE_NAME>");
      return $couch->getView("<DATABASE_NAME>", "default_view");
   }

   public function getOne($id) {
      $couch = new CouchDB("<DATABASE_NAME>");
      return $couch->getOne($id);
   }

   public function delete($id, $rev) {
      $couch = new CouchDB("<DATABASE_NAME>");
      $response = $couch->delete($id, $rev);
      return (isset($response['ok']) && $response['ok'] == true);
   }

   public function insert($object) {
      $couch = new CouchDB("<DATABASE_NAME>");
      $response = $couch->insert($object);
      return (isset($response['ok']) && $response['ok'] == true);
   }

   public function update($id, $rev, $object) {
      $couch = new CouchDB("<DATABASE_NAME>");
      $object['_id'] = $id;
      $object['_rev'] = $rev;
      $response = $couch->update($id, $obj);
      return (isset($response['ok']) && $response['ok'] == true);
   }
}

?>
