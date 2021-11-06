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
        localStorage.setItem("admin_access_token", response.access_token);
        localStorage.setItem("administrator_sys_id", response.administrator_sys_id);
        localStorage.setItem("administrator_phone_number", response.administrator_phone_number);
        localStorage.setItem("administrator_user_pottname", response.administrator_user_pottname);
        localStorage.setItem("administrator_firstname", response.administrator_firstname);
        localStorage.setItem("administrator_surname", response.administrator_surname);
        localStorage.setItem("frontend_key", response.frontend_key);
        show_notification("msg_holder", "success", "Success:", "Login successful");
        redirect_to_next_page(admin_web_dashboard_page_url, false);
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
        console.log("admin_api_login_url: " + admin_api_login_url);
        console.log("form data: " + $("#lform").serialize());
        fade_in_loader_and_fade_out_form("loader", "lform");       
        send_request_to_server_from_form("post", admin_api_login_url, $("#lform").serialize(), "json", success_response_function, error_response_function);
    });

});