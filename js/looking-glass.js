$(document).ready(function() {
  // hide the optional parameters field
  $('.result').hide();
  $('.loading').hide();
  $('.alert').hide();

  // show and hide loading bar
  $(document).ajaxStart(function() {
    $('#command_properties').attr('disabled', '');
    $('.alert').hide();
    $('.loading').show();
  });
  $(document).ajaxStop(function() {
    $('#command_properties').removeAttr('disabled');
    $('.loading').hide();
  });

  // close the alert bar
  $('.close').click(function() {
    $('.alert').slideUp();
  });
  $('#clear').click(function() {
    $('.alert').slideUp();
  });

  // reset the view to the default one
  $('#backhome').click(function() {
    $('.content').slideDown();
    $('.result').slideUp();
  });

  // send an ajax request that will get the info on the router
  $('form').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: 'post',
      url: 'execute.php',
      data: $('form').serialize()
    }).done(function(response) {
      var response = $.parseJSON(response);

      if (response.error) {
        $('#error-text').text(response.error);
        $('.alert').slideDown();
      } else {
        $('#output').text(response.result);
        $('.content').slideUp();
        $('.result').slideDown();
      }
    }).fail(function(xhr) {
      $('#error-text').text(xhr.responseText);
      $('.alert').slideDown();
    });
  });
});
