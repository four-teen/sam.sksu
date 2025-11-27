function refresh_user_card() {
  $.ajax({
    type: "POST",
    url: "counter.php",
    data: { refresh_user_count: "1" },
    success: function (response) {
      $("#get_count").html(response);
    }
  });
}


function all_doc_counts() {
  $.ajax({
    type: "POST",
    url: "counter.php",
    data: { count_all_docs: "1" },
    success: function (response) {
      $("#all_doc_counts").html(response);
    }
  });
}
