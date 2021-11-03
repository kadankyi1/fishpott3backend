/*
|--------------------------------------------------------------------------
| URLS 
|--------------------------------------------------------------------------
|*/

var host = "http://144.202.111.61";
var host = "http://fishpott.local";

// LOGIN PAGE URLS
var admin_api_login_url = `${host}/api/v1/admin/login`;
var admin_web_login_page_url = `${host}/admin/login`;


/****************************************
    
                URLS

****************************************/

var host = "http://amforex";

// LOGIN PAGE URLS
var admin_api_login_url = `${host}/api/v1/admin/login`;
var admin_web_login_page_url = `${host}/admin/login`;

// PASSCODE VERIFICATION PAGE URL
var admin_api_send_passcode_url = `${host}/api/v1/admin/verification`;
var admin_api_resend_passcode_url = `${host}/api/v1/admin/resend`;
var admin_web_passcode_page_url = `${host}/admin/verification`;

// LOG OUT
var admin_api_logout_url = `${host}/api/v1/admin/logout`;

// CURRENCIES OPTION LINKS
var admin_api_currencies_add_currency_url = `${host}/api/v1/admin/currencies/add`;
var admin_web_currencies_add_page_url = `${host}/admin/currencies/add`;
var admin_api_currencies_get_currency_list_url = `${host}/api/v1/admin/currencies/list`;
var admin_web_currencies_edit_page_url = `${host}/admin/currencies/edit`;
var admin_api_currencies_get_one_currency_url = `${host}/api/v1/admin/currencies/get/?currency_id=`;
var admin_api_currencies_edit_currency_url = `${host}/api/v1/admin/currencies/edit`;
var admin_api_currencies_search_for_currencies_url = `${host}/api/v1/admin/currencies/search/?kw=`;

// RATES 
var admin_web_dashboard_page_url = `${host}/admin/rates/list`;
var admin_api_rates_add_rate_url = `${host}/api/v1/admin/rates/add`;
var admin_api_rates_get_rate_list_url = `${host}/api/v1/admin/rates/list/?page=`;
var admin_api_rates_search_for_rates_url = `${host}/api/v1/admin/rates/search/?kw=`;

//BUREAUS

var show_logging_in_console = true;
var admin_api_bureaus_add_bureau_url = `${host}/api/v1/admin/bureaus/add`;
var admin_api_bureaus_get_bureaus_list_url = `${host}/api/v1/admin/bureaus/list/?page=`;
var admin_api_bureaus_search_for_bureaus_url = `${host}/api/v1/admin/bureaus/search/?kw=`;
var admin_api_bureaus_edit_bureau_url = `${host}/api/v1/admin/bureaus/add`;
var admin_api_bureaus_get_one_bureau_url = `${host}/api/v1/admin/bureaus/get/?bureau_id=`;

// SECURITY
var admin_api_security_change_password_url = `${host}/api/v1/admin/security/password/change`;

// ADMINISTRATOR
var admin_api_administrators_add_administrator_url = `${host}/api/v1/admin/administrators/add`;
var admin_api_admins_get_admins_list_url = `${host}/api/v1/admin/administrators/list/?page=`;
var admin_api_admins_get_one_admin_url = `${host}/api/v1/admin/administrators/get/?admin_id=`;
var admin_api_administrators_edit_administrator_url = `${host}/api/v1/admin/administrators/edit`;

// LOGGING INFORMATION
function show_log_in_console(log){
    if(show_logging_in_console){
        console.log(log);
    }
}


// CHECKING IF USER HAS AN API TOKEN
function user_has_api_token()
{
    if(
        (localStorage.getItem("admin_access_token") != null && localStorage.getItem("admin_firstname") != null && localStorage.getItem("admin_surname") != null ) 
        && 
        (localStorage.getItem("admin_access_token").trim() != "" && localStorage.getItem("admin_firstname").trim() != "" && localStorage.getItem("admin_surname").trim() != ""))
        {
            return true;
        } else {
            return false;
        }
}

// CHECKING IF USER COMPLETED PASSCODE VERIFICATION
function user_has_completed_passcode_verification()
{
    if(
        (localStorage.getItem("admin_passcode_completed") != null && localStorage.getItem("admin_passcode").trim() != null)
        &&
        (localStorage.getItem("admin_passcode_completed") === "1" && localStorage.getItem("admin_passcode").trim() === "149")
        ){
            return true;
        } else {
            return false;
        }
}

// LOGGING USER OUT BY DELETING ACCESS TOKEN
function delete_user_authentication()
{
    localStorage.clear();
    show_log_in_console("user_deleted");
}

function user_token_is_no_longer_valid()
{
    delete_user_authentication();
    redirect_to_next_page(admin_web_login_page_url, false); 
}

function sign_out_success(response)
{
    delete_user_authentication(); 
    user_token_is_no_longer_valid()
}

function sign_out_error(errorThrown)
{
    show_notification("msg_holder", "danger", "Error", errorThrown);
    fade_out_loader_and_fade_in_form("logoutloader", "logoutspan");   
}

function sign_me_out()
{    
    fade_in_loader_and_fade_out_form("logoutloader", "logoutspan");     
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    send_restapi_request_to_server_from_form("get", admin_api_logout_url, bearer, "", "json", sign_out_success, sign_out_error);
}

function hide_notification(){
    document.getElementById('msg_div').style.display = "none";
}

// SHOWING A NOTIFICATION ON THE SCREEN
function show_notification(id, type, title, message)
{
    $('#'+id).html(
        '<div id="msg_div" class="' + type + '"><b>' + title +'</b> '+ message +'<a id="close-bar" onclick="hide_notification();">×</a></div>'
    );
    setTimeout(function(){ $('#close-bar').click(); }, 5000);
}


// SHOWING A LOADER AND DISAPPEARING FORM
function fade_in_loader_and_fade_out_form(loader_id, form_id)
{
    $('#'+form_id).fadeOut();
    $('#'+loader_id).fadeIn();
}

// SHOWING A FORM AND DISAPPEARING LOADER
function fade_out_loader_and_fade_in_form(loader_id, form_id)
{
    $('#'+loader_id).fadeOut();
    $('#'+form_id).fadeIn();
}

// SENDING USER TO NEW PAGE
function redirect_to_next_page(url, can_return_to_page)
{
    if(can_return_to_page){// Simulate a mouse click:
        setTimeout(window.location.href = url, 7000);
    } else {
        setTimeout(window.location.replace(url), 7000);
    }
}

function send_request_to_server_from_form(method, url_to_server, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        data:  form_data,
        dataType: data_type,
        success: function(response){ 
            show_log_in_console(response);
            if(response.status.trim() == "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            show_log_in_console(errorThrown);
            error_response_function(errorThrown);
        }
    });
}

function send_restapi_request_to_server_from_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,headers: {
            'Authorization': authorization
         },
        data:  form_data,
        dataType: data_type,
        success: function(response){ 
            show_log_in_console(response);

            if(response == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            } 
            if(response.status.trim() == "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            show_log_in_console(errorThrown);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(errorThrown);
        }
    });
}
