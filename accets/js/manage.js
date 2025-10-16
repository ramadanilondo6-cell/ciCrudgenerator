function delRow() {
  var confrm = confirm("Are you sure you want to delete?");
  if (confrm) {
    var ids = values();
    if (ids.length === 0) {
      alert("Please select at least one record to delete.");
      return;
    }
    $.ajax({
      type: "POST",
      url: deleteAllUrl,
      data: {
        allIds: ids,
        [csrfTokenName]: csrfHash
      },
      success: function() {
        location.reload();
      },
      error: function(xhr, status, error) {
        alert("An error occurred: " + error);
        location.reload();
      }
    });
  }
}

$(document).ready(function() {
  $('#per_page_select').on('change', function() {
    this.form.submit();
  });

  $('input[type="checkbox"]').on('change', function() {
    if ($('input[type="checkbox"]:checked').length > 0) {
      $('.recycle-bin').show();
    } else {
      $('.recycle-bin').hide();
    }
  });
});