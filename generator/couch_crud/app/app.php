<?php

class <CLASS_NAME> extends Controller {

   /*********************************************
    * run functions
    *********************************************/

   public function indexRun($request) {
      $<DATABASE_NAME>_model = new <MODEL_CLASS_NAME>();
      $this->data['<DATABASE_NAME>'] = $<DATABASE_NAME>_model->getAll();
   }

   public function editRun($request) {
      $<DATABASE_NAME>_model = new <MODEL_CLASS_NAME>();
      $this->data['<DATABASE_NAME>'] = $<DATABASE_NAME>_model->getOne($request['_id']);
      $this->data['form_action'] = 'update';
   }

   public function newRun($request) {
      $this->view = 'edit';
      $this->data['form_action'] = 'create';
   }

   public function updateRun($request) {
   }

   public function deleteRun($request) {
   }

   public function createRun($request) {
   }

   /*********************************************
    * save functions
    *********************************************/

   public function createSave($request) {
      $<DATABASE_NAME>_model = new <MODEL_CLASS_NAME>();
      $status = $<DATABASE_NAME>_model->insert($request);
      $this->data['status'] = ($status ? "success" : "failed");
   }

   public function updateSave($request) {
      $<DATABASE_NAME>_model = new <MODEL_CLASS_NAME>();
      $status = $blog_model->update($request['_id'], $request['_rev'], $request);
      $this->data['status'] = ($status ? "success" : "failed");
   }

   public function deleteSave($request) {
      $<DATABASE_NAME>_model = new <MODEL_CLASS_NAME>();
      $status = $blog_model->delete($request['_id'], $request['_rev']);
      $this->data['status'] = ($status ? "success" : "failed");
   }

}

?>
