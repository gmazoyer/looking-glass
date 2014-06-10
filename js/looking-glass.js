$(document).ready(function() {
  // hide the optional parameters field
  $('.result').hide();
  $('.loading').hide();

  // show and hide loading bar
  $(document).ajaxStart(function() {
    $('#command_properties').attr('disabled', '');
    $('.loading').show();
  });
  $(document).ajaxStop(function() {
    $('#command_properties').removeAttr('disabled');
    $('.loading').hide();
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
    }).done(function(response, state, xhr) {
      $('#output').text(response);
      $('.content').slideUp();
      $('.result').slideDown();
    }).fail(function(xhr, state, error) {
      alert('The following error occured: ' + state, error);
    });
  });
});
