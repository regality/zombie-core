$(document).ready(function() {
   $(".blog-delete").live('click', function(e) {
      e.preventDefault();
      var row = $(this).parents("tr");
      $.ajax({"data":{"app":"blog",
                      "action":"delete",
                      "_id":$(this).attr("blog_id"),
                      "_rev":$(this).attr("blog_rev")},
              "success":function(data) {
                  if (data.status == "success") {
                     row.remove();
                  } else {
                     zs.ui.error("Could not delete blog.");
                  }
              }
      });
   });

   $(".blog-create").live('click', function() {
      var form = $(this).parents("form");
      if (!zs.ui.verifyForm(form)) {
         return false;
      }
      $.ajax({"data":{"app":"blog",
                      "title":form.find("input[name=title]").val(),
                      "content":form.find("textarea[name=content]").val(),
                      "action":"create"},
              "success":function(data) {
                  if (data.status == "success") {
                     zs.stack.pop("blog");
                     zs.stack.refresh("blog");
                  } else {
                     zs.ui.error("Could not create blog.");
                  }
              }
      });
      return false;
   });

   $(".blog-update").live('click', function() {
      var form = $(this).parents("form");
      if (!zs.ui.verifyForm(form)) {
         return false;
      }
      $.ajax({"data":{"app":"blog",
                      "_id":form.find("input[name=_id]").val(),
                      "_rev":form.find("input[name=_rev]").val(),
                      "title":form.find("input[name=title]").val(),
                      "content":form.find("textarea[name=content]").val(),
                      "action":"update"},
              "success":function(data) {
                  if (data.status == "success") {
                     zs.stack.pop("blog");
                     zs.stack.refresh("blog");
                  } else {
                     zs.ui.error("Could not update blog.");
                  }
              }
      });
      return false;
   });

});
