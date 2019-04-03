$(function() {
    // hide all checkboxes to start
    //$('input[type="checkbox"][states!="1"]').parent().parent().parent().hide();
    $('[name="state"]').on('change', function() {
        // hide all checkboxes first
        $('input[type="checkbox"][states!="' + $(this).val() + '"]').parent().parent().parent().hide();
        $('#id_charlieannunziata').parent().parent().parent().show();
        //console.log($(this).val());
        // show the checkboxes in the state selected
        //console.log($('input[states*="' + $(this).val() + '"]'));
        $('input[type="checkbox"][states*="' + $(this).val() + '"]').parent().parent().parent().show();
    });
    
    $('[name="state"]').trigger('change');
    
    $('.checkboxgroup1').on('change', function() {
        if ($(this).val() !== '') {
            // save current credit hours for calculation
            var total_hours = parseInt($('#id_credithrs').val());
            if (isNaN(total_hours)) {
                total_hours = 0;
            }
            var course_str = $('[name="courses"]').val();
            var courses = [];
            if (course_str !== '') {
                courses = course_str.split(',');
            }
            
            // action on the courses array is dependent on if the
            // item is checked
            if ($(this).prop('checked') === true) {
                console.log('checked');
                console.log($(this).val());
                // add the item to the array
                courses.push($(this).val());
                // add to the total hours
                total_hours += parseInt($(this).attr('hours'));
            } else {
                // remove the item from the array
                console.log('unchecked');
                console.log($(this).val());
                courses.splice($.inArray($(this).val(), courses), 1);
                // subtract from the total hours
                total_hours -= parseInt($(this).attr('hours'));
            }
            
            // convert the courses array back into a string
            $('[name="courses"]').val(courses.toString()); 
            
            // save the total hours
            $('#id_credithrs').val(total_hours);
        }
    });
});