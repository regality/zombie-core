      <div class="grid_11 alpha omega field">
         <div class="grid_3 alpha">
            <label><FIELD_NAME_NICE></label>
         </div>
         <div class="grid_4">
            <select <VALIDATE>name="<FIELD_NAME>">
               <option value=''></option>
               <?php foreach ($<OTHER_TABLE_NAME> as $option): ?>
                  <?php $selected = ((isset($<TABLE_NAME>) && $option['id'] == $<TABLE_NAME>['<OTHER_TABLE_NAME>_id']) ? "selected" : "") ?>
                  <option value="<?= $option['id'] ?>" <?= $selected ?>><?= $option['<JOIN_FIELD>'] ?></option>
               <?php endforeach ?>
            </select>
         </div>
         <div class="grid_4 error omega">
         </div>
      </div>
