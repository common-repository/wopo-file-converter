jQuery(document).ready(function($){
    $('[name=file]').on('change',function(){
        var inputExt = $(this).val().split('.').pop().toLowerCase();
        $('#convert_to').removeAttr('disabled').find('option').remove();
        $('#btn_convert').removeAttr('disabled');
        $.each(wopofc_convert_formats.data,function(index, obj){
            if (obj.input_format == inputExt){
                $('#convert_to').append($('<option>',{
                    value: obj.output_format,
                    text: obj.output_format
                }));
            }
        });
    });
    
});