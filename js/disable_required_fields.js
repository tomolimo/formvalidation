$(document).ajaxComplete(function (event, xhr, settings) {
    //debugger;

    var counter = 0;
    var intervalId  = setInterval(function () {
        /*debugger; */
      if (counter < 300) {
          $('#page form input[type=text], #page form textarea, #page form select').attr('required', 'required').each(function (i, requiredField) {
              $(this).removeAttr('required');
              $(this).prop('required', false);
          });
          $('#page form .mce-edit-area.required').each(function (i, requiredField) {
              $(this).removeClass('required');
          });
          counter++;
      } else {
          clearInterval(intervalId);
      }
    }, 10);

});