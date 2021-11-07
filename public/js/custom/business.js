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
    if (typeof the_model !== 'undefined'){
        getModelData(the_model);

        /*initiate the autocomplete function on the "business_name" element, and pass along the countries array as possible autocomplete values:*/
        autocomplete(document.getElementById("item_identifier"), models, "item_id");
    }

    $("#administrator_phone_number").val(localStorage.getItem("administrator_phone_number"));
    $("#administrator_sys_id").val(localStorage.getItem("administrator_sys_id"));
    $("#frontend_key").val(localStorage.getItem("frontend_key"));

    if (typeof the_model === 'undefined'){
        $(".theme-loader").animate({
            opacity: "0"
        },1000);
        setTimeout(function() {
            $(".theme-loader").remove();
        }, 800);
    }

    // SUBMITTING THE FORM TO GET API RESPONSE
    $("#form").submit(function (e) 
    { 
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loader", "form"); 
        console.log("form STARTED");
        var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
        console.log("admin_api_add_business_url: " + admin_api_add_business_url);
        console.log("Token: " + bearer);
        var form = $("#form");
        var form_data = new FormData(form[0]);
        send_file_upload_restapi_request_to_server_from_form("post", admin_api_add_business_url, bearer, form_data, "", successResponseFunction, errorResponseFunction);
    });
    
        // SUBMITTING THE FORM TO GET API RESPONSE
        $("#sform").submit(function (e) 
        { 
            e.preventDefault(); 
            fade_in_loader_and_fade_out_form("loader", "form"); 
            console.log("form STARTED");
            var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
            console.log("admin_api_add_new_stock_value_url: " + admin_api_add_new_stock_value_url);
            console.log("Token: " + bearer);
            var form = $("#sform");
            var form_data = new FormData(form[0]);
            send_restapi_request_to_server_from_form("post", admin_api_add_new_stock_value_url, bearer, form_data, "", successResponseFunction, errorResponseFunction);
        });
    
});


// RESENDING THE PASSCODE
function successResponseFunction(response)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    $('#form')[0].reset();
    show_notification("msg_holder", "success", "Success:", response.message);
}

function errorResponseFunction(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}

