(function () {
    'use strict'

    var base_url = 'http://localhost/md_backend';

    $("#login").submit(function(e) {

        e.preventDefault(); 
    
        var form = $(this);
        var url = base_url+'/login';
        $.ajax({
            type: "POST",
            url: url,
            cache: false,
            data: form.serialize(), 
            success: function(data)
            {
                console.log(data); 
                if(data.error_code == 401) {
                    var myAlert = document.getElementById('liveToast');
                    $('#liveToast').find('.toast-body').html(data.error_description);
                    var bsAlert = new bootstrap.Toast(myAlert);
                    bsAlert.show();
                }
                else if(data.success == "true") {
                    window.localStorage.setItem('token', data.data.token);
                    window.location = '../dashboard';
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                alert("Status: " + textStatus); alert("Error: " + errorThrown); 
            }
        });
    
        
    });
  })()
  