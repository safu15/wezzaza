(function ($) {

    $('#bank_name').on('change', function (e) {

        var bank_id = $(this).val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var _token = $('input[name="_token"]').val();
        var html = "";
        $.ajax({
            type: "post",
            url: URL_BASE + "/getSwift",
            datatype: "json",
            data: {bank_id: bank_id, _token: _token},
            success: function (response) {
                if (response.message == "success") {
                    $('#bic').val(response.bic);

                    html += '<option value="" disabled selected> Select Branch </option>';
                    
                    $.each(response.branch, function (key, value) {
                        
                        html += '<option value="' + value.id + ' ">' + value.branch + '</option>';
                        
                    });
                    
                    $("#branch").html(html);
                }
            }
        });


    });
    
 
})(jQuery);

 function isNumber(evt)
  {
     var charCode = (evt.which) ? evt.which : event.keyCode
     if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;

     return true;
  }