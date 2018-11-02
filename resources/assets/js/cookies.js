function acceptCookies() {
    var link_to = window.location.origin + "/data/protection/accept";
    $.ajax({
      headers: { 'X-CSRF-Token' : $('meta[name="csrf"]').attr('content') },
      type: "POST",
      url: link_to,
      success: function () {
        $('.cookie-banner').remove();
      }
    });
  }