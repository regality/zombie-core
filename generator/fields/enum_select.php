      <div class="grid_11 alpha omega field">
         <div class="grid_3 alpha">
            <label><FIELD_NAME_NICE></label>
         </div>
         <div class="grid_4">
            <select <VALIDATE>name="<FIELD_NAME>">
               <option value=""></option>
<ENUM_OPTIONS>
            </select>
            <?php if (isset($<TABLE_NAME>)): ?>
               <script type="text/javascript">
               $("select[name='<FIELD_NAME>'] option[value='<?= $<TABLE_NAME>['<FIELD_NAME>'] ?>']").attr("selected","selected");
               </script>
            <?php endif ?>
         </div>
         <div class="grid_4 error omega">
         </div>
      </div>
