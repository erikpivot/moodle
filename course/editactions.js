$(function() {
     // hide all text boxes and show the default one
    $("#id_stateapprovenos input[type='text']").parent().parent().hide();
    $("#id_alapprovalno").parent().parent().show();
    
    $("#id_approvalnolist").on('change', function() {
        // hide all text boxes and show the selected one
        $("#id_stateapprovenos input[type='text']").parent().parent().hide();
        $("#id_" + $(this).val() + "approvalno").parent().parent().show();
    });
});