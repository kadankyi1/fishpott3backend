// CHECKING IF USER IS LOGGED IN
if(!user_has_api_token()){
    redirect_to_next_page(admin_web_login_page_url, false);
    //return;
}

$(document).ready(function () 
{
    // GETTING THE DASHBOARD PAGE
    getDashboardData();

});

/*
|--------------------------------------------------------------------------
| FUNCTIONS FOR FETCHING DASHBOARD DATA
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
*/

function getDashboardData()
{
    console.log("getDashboardData STARTED");
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    $form_data = "administrator_phone_number=" + localStorage.getItem("administrator_phone_number") + "&administrator_sys_id=" + localStorage.getItem("administrator_sys_id") + "&frontend_key=" + localStorage.getItem("frontend_key");
    show_log_in_console("form_data: " + form_data);
    send_restapi_request_to_server_from_form("post", admin_api_get_dashboard_data_url, bearer, "", "json", successResponseFunction, errorResponseFunction);
}

// RESENDING THE PASSCODE
function successResponseFunction(response)
{
    $(".theme-loader").animate({
        opacity: "0"
    },1000);
    setTimeout(function() {
        $(".theme-loader").remove();
    }, 800);

    show_notification("msg_holder", "success", "Success:", "Fetch Successful");
    //fade_out_loader_and_fade_in_form("loader", "form"); 
}

function errorResponseFunction(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}
