// URL
var email_alerts_update_url = `${host}/api/v1/user/email-alerts`;

$(document).ready(function() {
    
    $(".theme-loader").animate({
        opacity: "0"
    },1000);
    setTimeout(function() {
        $(".theme-loader").remove();
    }, 800);
    
});

$(document).ready(function () 
{
    // RESENDING THE PASSCODE
    function success_response_function(response)
    {
        fade_out_loader_and_fade_in_form("loader", ""); 
    }

    function error_response_function(errorThrown)
    {
        fade_out_loader_and_fade_in_form("loader", "lform"); 
        if(errorThrown.message == null){
            show_notification("msg_holder", "error", "Error", errorThrown);
        } else {
            show_notification("msg_holder", "error", "Error", errorThrown.message);
        }
    }

    // SUBMITTING THE LOGIN FORM TO GET API TOKEN
    $("#lform").submit(function (e) 
    { 
        e.preventDefault(); 
        console.log("email_alerts_update_url: " + email_alerts_update_url);
        console.log("form data: " + $("#lform").serialize());
        fade_in_loader_and_fade_out_form("loader", "lform");       
        send_request_to_server_from_form("post", email_alerts_update_url, $("#lform").serialize(), "json", success_response_function, error_response_function);
    });

});