function request_doc(query) {
  $.ajax({
    type: 'post',
    url: 'execute.php',
    data: { doc: query, dontlook: '' }
  }).done(function(response) {
    var response = $.parseJSON(response);

    $('#command-reminder').text(response.command);
    $('#description-help').html(response.description);
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

  // clear the form and page
  $('#clear').click(function(e) {
    $('.alert').slideUp();

    e.preventDefault();

    // reset the parameter field if it was marked as error
    $('#input-param').parent().removeClass('has-error');

    // reset the form and update the doc modal
    $(this).closest('form').get(0).reset();
    request_doc($('#query').val());
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

  // if the field has been completed, turn it back to normal
  $('#input-param').change(function() {
    $('#input-param').parent().removeClass('has-error');
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
      if (!response || (response.length === 0)) {
        // no parameter given
        $('#error-text').text('No parameter given.');
        $('#input-param').focus().parent().addClass('has-error');
        $('.alert').slideDown();
      } else {
        var response = $.parseJSON(response);

        if (response.error) {
          $('#error-text').text(response.error);
          $('.alert').slideDown();
        } else {
          $('#output').html(response.result);
          $('.content').slideUp();
          $('.result').slideDown();
        }
      }
    }).fail(function(xhr) {
      $('#error-text').text(xhr.responseText);
      $('.alert').slideDown();
    });
  });
});
