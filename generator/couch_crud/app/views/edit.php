<form>
   <div class="grid_7 form alpha omega form">
      <div class="grid_11 field">
         <?php if ($form_action == "update"): ?>
            <h3>Updating <DATABASE_NAME> <?= $<DATABASE_NAME>['_id'] ?></h3>
         <?php else: ?>
            <h3>Creating New <DATABASE_NAME></h3>
         <?php endif ?>
      </div>

      <div class="grid_7 alpha omega">
         <hr />
      </div>

      <div class="grid_11 alpha omega field">
         <div class="grid_3 alpha">
            <label>field name</label>
         </div>
         <div class="grid_4">
            <input validate="" type="text" name="field" value="<?= (isset($<DATABASE_NAME>['title']) ? $<DATABASE_NAME>['title'] : '') ?>" />
         </div>
         <div class="grid_4 error omega">
         </div>
      </div>

      <div class="grid_11 alpha omega field">
         <div class="grid_1 prefix_2 suffix_1 alpha">
            <button class="<SLUG>-<?= $form_action ?>"><?= ucwords($form_action) ?></button>
         </div>

         <div class="grid_1 suffix_6 omega">
            <button class="pop-active">Cancel</button>
         </div>
      </div>

      <?php if (isset($<DATABASE_NAME>['_id'])): ?>
         <input type="hidden" value="<?= $<DATABASE_NAME>['_id'] ?>" name="_id" />
         <input type="hidden" value="<?= $<DATABASE_NAME>['_rev'] ?>" name="_rev" />
      <?php endif ?>
   </div>
</form>
<script type="text/javascript">
zs.util.require("<SLUG>/main");
</script>
