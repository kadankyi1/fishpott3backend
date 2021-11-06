// CHECKING IF USER IS LOGGED IN
if(!user_has_api_token()){
    redirect_to_next_page(admin_web_login_page_url, false);
    //return;
}

$(document).ready(function () 
{
    $(".theme-loader").animate({
        opacity: "0"
    },1000);
    setTimeout(function() {
        $(".theme-loader").remove();
    }, 800);

    // SUBMITTING THE FORM TO GET API RESPONSE
    $("#form").submit(function (e) 
    { 
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loader", "form"); 
        console.log("form STARTED");
        var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
        console.log("admin_api_add_drill_url: " + admin_api_add_drill_url);
        console.log("Token: " + bearer);
        var data = {
            'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
            'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
            'frontend_key': localStorage.getItem("frontend_key"),
            'administrator_pin': $("#administrator_pin").val(),
            'drill_question': $("#drill_question").val(),
            'drill_answer_1': $("#drill_answer_1").val(),
            'drill_answer_2': $("#drill_answer_2").val(),
            'drill_answer_3': $("#drill_answer_3").val(),
            'drill_answer_4': $("#drill_answer_4").val()
        };

        //var data = new FormData($("#form"));
        //data.append('administrator_phone_number', localStorage.getItem("administrator_phone_number"));
        //data.append('administrator_sys_id', localStorage.getItem("administrator_sys_id"));
        //data.append('frontend_key', localStorage.getItem("frontend_key"));
        console.log(data);
        send_restapi_request_to_server_from_form("post", admin_api_add_drill_url, bearer, data, "json", successResponseFunction, errorResponseFunction);
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

