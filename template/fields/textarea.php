      <div class="grid_11 alpha omega field">
         <div class="grid_3 alpha">
            <label><FIELD_NAME_NICE></label>
         </div>
         <div class="grid_4">
            <textarea <VALIDATE>name="<FIELD_NAME>"><?= (isset($<TABLE_NAME>['<FIELD_NAME>']) ? htmlentities($<TABLE_NAME>['<FIELD_NAME>']) : '') ?></textarea>
         </div>
         <div class="grid_4 error omega">
         </div>
      </div>
