$(document).ready(function() {
   $(".<SLUG>-delete").live('click', function(e) {
      e.preventDefault();
      var row = $(this).parents("tr");
      $.ajax({"data":{"app":"<SLUG>",
                      "action":"delete",
                      "id":$(this).attr("<TABLE_NAME>_id")},
              "success":function(data) {
                  if (data.status == "success") {
                     row.remove();
                  } else {
                     zs.ui.error("Could not delete <TABLE_NAME>.");
                  }
              }
      });
   });

   $(".<SLUG>-create").live('click', function() {
      var form = $(this).parents("form");
      if (!zs.ui.verifyForm(form)) {
         return false;
      }
      $.ajax({"data":{"app":"<SLUG>",
<AJAX_COMMA_SEP_FIELDS>
                      "action":"create"},
              "success":function(data) {
                  if (data.status == "success") {
                     zs.stack.pop("<SLUG>");
                     zs.stack.refresh("<SLUG>");
                  } else {
                     zs.ui.error("Could not create <TABLE_NAME>.");
                  }
              }
      });
      return false;
   });

   $(".<SLUG>-update").live('click', function() {
      var form = $(this).parents("form");
      if (!zs.ui.verifyForm(form)) {
         return false;
      }
      $.ajax({"data":{"app":"<SLUG>",
<AJAX_COMMA_SEP_FIELDS_WID>
                      "action":"update"},
              "success":function(data) {
                  if (data.status == "success") {
                     zs.stack.pop("<SLUG>");
                     zs.stack.refresh("<SLUG>");
                  } else {
                     zs.ui.error("Could not update <TABLE_NAME>.");
                  }
              }
      });
      return false;
   });

});
