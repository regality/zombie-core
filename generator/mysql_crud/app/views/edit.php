<form>
   <div class="grid_7 form alpha omega form">
      <div class="grid_11 field">
         <?php if ($form_action == "update"): ?>
            <h3>Updating <TABLE_NAME> <?= $<TABLE_NAME>['id'] ?></h3>
         <?php else: ?>
            <h3>Creating New <TABLE_NAME></h3>
         <?php endif ?>
      </div>

      <div class="grid_7 alpha omega">
         <hr />
      </div>

      <HTML_EDIT_FIELDS>

      <div class="grid_11 alpha omega field">
         <div class="grid_1 prefix_2 suffix_1 alpha">
            <button class="<SLUG>-<?= $form_action ?>"><?= ucwords($form_action) ?></button>
         </div>

         <div class="grid_1 suffix_6 omega">
            <button class="pop-active">Cancel</button>
         </div>
      </div>

      <?php if (isset($<TABLE_NAME>['id'])): ?>
         <input type="hidden" value="<?= $<TABLE_NAME>['id'] ?>" name="id" />
      <?php endif ?>
   </div>
</form>
<script type="text/javascript">
zs.util.require("<SLUG>/main");
</script>
