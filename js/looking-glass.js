function request_doc(query) {
  $.ajax({
    type: 'post',
    url: 'execute.php',
    data: { doc: query, dontlook: '' }
  }).done(function(response) {
    var response = $.parseJSON(response);

    $('#command-help').html(response.query);
    $('#parameter-help').html(response.parameter);
  }).fail(function(xhr) {
    $('#help-content').text('Cannot load documentation...');
  });
}

$(document).ready(function() {
  // hide the optional parameters field
  $('.result').hide();
  $('.loading').hide();
  $('.alert').hide();

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

  // initialize the help modal
  request_doc($('#query').val());

  // update help when a command is selected
  $('#query').on('change', function(e) {
    e.preventDefault();
    request_doc($('#query').val());
  });

  // send an ajax request that will get the info on the router
  $('form').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
      type: 'post',
      url: 'execute.php',
      data: $('form').serialize(),
      beforeSend: function() {
        // show loading bar
        $('#command_properties').attr('disabled', '');
        $('.alert').hide();
        $('.loading').show();
      },
      complete: function() {
        // hide loading bar
        $('#command_properties').removeAttr('disabled');
        $('.loading').hide();
      }
    }).done(function(response) {
      var response = $.parseJSON(response);

      if (response.error) {
        $('#error-text').text(response.error);
        $('.alert').slideDown();
      } else {
        $('#output').html(response.result);
        $('.content').slideUp();
        $('.result').slideDown();
      }
    }).fail(function(xhr) {
      $('#error-text').text(xhr.responseText);
      $('.alert').slideDown();
    });
  });
});
