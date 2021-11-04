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

//LOGOUT URL
var admin_api_logout_url = `${host}/api/v1/admin/logout`;

//LOGOUT URL
var admin_web_dashboard_page_url = `${host}/api/v1/admin/dashboard`;


// CHECKING IF USER HAS AN API TOKEN
function user_has_api_token()
{
    if(
        localStorage.getItem("admin_access_token") != null 
        && localStorage.getItem("administrator_sys_id") != null 
        && localStorage.getItem("administrator_user_pottname") != null 
        && localStorage.getItem("administrator_firstname") != null
        && localStorage.getItem("administrator_surname") != null
        && localStorage.getItem("admin_access_token").trim() != "" 
        && localStorage.getItem("administrator_sys_id").trim() != "" 
        && localStorage.getItem("administrator_user_pottname").trim() != ""
        && localStorage.getItem("administrator_firstname").trim() != "" 
        && localStorage.getItem("administrator_surname").trim() != ""
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
    console.log("user_deleted");
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
            console.log(response);
            if(response.status.trim() == "success"){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(errorThrown);
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
            console.log(response);

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
            console.log(errorThrown);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(errorThrown);
        }
    });
}
