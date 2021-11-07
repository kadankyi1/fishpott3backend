  /*An array containing all the country names in the world:*/
  var models = [];
  var model_ids = [];

// CHECKING IF USER IS LOGGED IN
if(!user_has_api_token()){
    redirect_to_next_page(admin_web_login_page_url, false);
    //return;
}

$(document).ready(function () 
{

    getModelData(the_model);

    $("#administrator_phone_number").val(localStorage.getItem("administrator_phone_number"));
    $("#administrator_sys_id").val(localStorage.getItem("administrator_sys_id"));
    $("#frontend_key").val(localStorage.getItem("frontend_key"));

    /*initiate the autocomplete function on the "business_name" element, and pass along the countries array as possible autocomplete values:*/
    autocomplete(document.getElementById("item_identifier"), models, "item_id");

    // SUBMITTING THE FORM TO GET API RESPONSE
    $("#form").submit(function (e) 
    { 
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loader", "form"); 
        console.log("form STARTED");
        var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
        console.log("admin_api_add_drill_url: " + admin_api_add_suggestion_url);
        console.log("Token: " + bearer);
        var form = $("#form");
        var form_data = new FormData(form[0]);
        send_restapi_request_to_server_from_form("post", admin_api_add_suggestion_url, bearer, form_data, "", successResponseFunction2, errorResponseFunction2);
    });
    
});


// RESENDING THE PASSCODE
function successResponseFunction2(response)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    $('#form')[0].reset();
    show_notification("msg_holder", "success", "Success:", response.message);
}

function errorResponseFunction2(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}

/*
|--------------------------------------------------------------------------
| FUNCTIONS FOR FETCHING DASHBOARD DATA
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
*/
function getModelData(model)
{
    console.log("getDashboardData STARTED");
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    console.log("admin_api_get_dashboard_data_url: " + admin_api_get_dashboard_data_url);
    console.log("Token: " + bearer);
    var data = {
        'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
        'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
        'frontend_key': localStorage.getItem("frontend_key"),
        'model': model
    };
    console.log(data);
    send_restapi_request_to_server_no_form("post", admin_api_add_search_model_url, bearer, data, "json", successResponseFunction, errorResponseFunction);
}

// RESENDING THE PASSCODE
function successResponseFunction(response)
{
    $.each(response.data, function(key,value) {
        if(the_model == "business"){
            console.log(value.business_full_name);
            models.push(value.business_full_name); 
            model_ids.push(value.business_sys_id); 
        } else if(the_model == "drill"){
            console.log(value.drill_question);
            models.push(value.drill_question); 
            model_ids.push(value.drill_sys_id); 
        } 
    }); 

    $(".theme-loader").animate({
        opacity: "0"
    },1000);
    setTimeout(function() {
        $(".theme-loader").remove();
    }, 800);

    show_notification("msg_holder", "success", "Success:", "Fetch Successful");
}

function errorResponseFunction(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}

