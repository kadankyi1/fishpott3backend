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
    console.log("admin_api_get_dashboard_data_url: " + admin_api_get_dashboard_data_url);
    console.log("Token: " + bearer);
    var data = {
        'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
        'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
        'frontend_key': localStorage.getItem("frontend_key")
    };
    console.log(data);
    send_restapi_request_to_server_from_form("post", admin_api_get_dashboard_data_url, bearer, data, "json", successResponseFunction, errorResponseFunction);
}

// RESENDING THE PASSCODE
function successResponseFunction(response)
{
    $("#users_total_count").html(response.data.users_total_count);
    $("#users_today_count").html(response.data.users_today_count);
    $("#users_months_count").html(response.data.users_thirtydays_count);


    $("#suggestions_active_total_count").html(response.data.suggestions_active);
    $("#suggestions_active_drills_total_count").html(response.data.suggestions_active_drill);
    $("#suggestions_active_businesses_total_count").html(response.data.suggestions_active_business);

    $("#businesses_total_count").html(response.data.businesses_all);
    $("#businesses_listed_total_count").html(response.data.businesses_not_listed);
    $("#businesses_non_listed_total_count").html(response.data.businesses_not_listed);

    $("#orders_pending_total_count").html(response.data.orders_paid_pending);
    $("#orders_months_total_count").html(response.data.orders_paid_thirty_days);
    $("#orders_months_profit_total_count").html(response.data.orders_unpaid_thirty_days);


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
