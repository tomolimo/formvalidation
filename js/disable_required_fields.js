$(document).ajaxComplete(function (event, xhr, settings) {
    //debugger;
    // enable all field with required='required' attr
    //console.log('in disable_required_fields.js');
    var counter = 0;
    setInterval(function () {
        /*debugger; */
        //console.log(counter);
      if (counter < 5) {
          $('#page form input[type=text], #page form textarea, #page form select').attr('required', 'required').each(function (i, requiredField) {
              //console.log(requiredField);
              $(this).removeAttr('required');
              $(this).prop('required', false);
          });
          counter++;
      }
    }, 200);

});