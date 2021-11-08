// CHECKING IF USER IS LOGGED IN
if(!user_has_api_token()){
    redirect_to_next_page(admin_web_login_page_url, false);
    //return;
}

$(document).ready(function () 
{
    // GETTING THE DASHBOARD PAGE
     getUsers();

    $("#administrator_phone_number").val(localStorage.getItem("administrator_phone_number"));
    $("#administrator_sys_id").val(localStorage.getItem("administrator_sys_id"));
    $("#frontend_key").val(localStorage.getItem("frontend_key"));

    $("#administrator_phone_number2").val(localStorage.getItem("administrator_phone_number"));
    $("#administrator_sys_id2").val(localStorage.getItem("administrator_sys_id"));
    $("#frontend_key2").val(localStorage.getItem("frontend_key"));

    // SUBMITTING THE FORM TO GET API RESPONSE
    $("#form").submit(function (e) 
    { 
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loader", "form"); 
        var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
        var form = $("#form");
        var form_data = new FormData(form[0]);
        send_restapi_request_to_server_from_form("post", admin_api_search_users_url, bearer, form_data, "",  getUsersSuccessResponseFunction,  getUsersErrorResponseFunction);
    });

    // SUBMITTING THE FORM TO GET API RESPONSE
    $("#formtwo").submit(function (e) 
    { 
        e.preventDefault(); 
        fade_in_loader_and_fade_out_form("loadertwo", "formtwo"); 
        var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
        var form = $("#formtwo");
        var form_data = new FormData(form[0]);
        send_restapi_request_to_server_from_form("post", admin_api_update_user_url, bearer, form_data, "", successResponseFunction2, errorResponseFunction2);
    });
    
});

/*
|--------------------------------------------------------------------------
| FUNCTIONS FOR FETCHING MODEL DATA
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
*/
function  getUsers()
{
    console.log("getModelData STARTED");
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    console.log("admin_api_search_users_url: " + admin_api_search_users_url);
    console.log("Token: " + bearer);
    var data = {
        'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
        'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
        'frontend_key': localStorage.getItem("frontend_key")
    };
    console.log(data);
    send_restapi_request_to_server_no_form("post", admin_api_search_users_url, bearer, data, "json",  getUsersSuccessResponseFunction,  getUsersErrorResponseFunction);
}

// RESENDING THE PASSCODE
function  getUsersSuccessResponseFunction(response)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    //$("#table_body").html('');
    $.each(response.data, function(key,value) {
        console.log(value.user_flagged);
        if(value.user_flagged === 1){
            flagged_status_class = "danger";
            flagged_status = "Flagged";
        } else if(value.user_flagged === 0){
            flagged_status_class = "success";
            flagged_status = "No";
        } else {
            flagged_status_class = "warning";
            flagged_status = "unknown";
        }
        if(value.user_profile_picture === ""){
            pott_pic = "/images/fishpott_icon_circle.png";
        } else {
            pott_pic = "/uploads/images/" + value.user_profile_picture;
        }
        $('#table_body').append('<tr><td><div class="chk-option"><div class="checkbox-fade fade-in-primary">' + value.user_id + '</div></div><div class="d-inline-block align-middle"><img src="' + pott_pic + '" alt="user image" class="img-radius img-40 align-top m-r-15"><div class="d-inline-block"><h6>' + value.user_surname + ' ' + value.user_firstname + '</h6><p class="text-muted m-b-0">@' + value.user_pottname + '</p></div></div></td><td><div class="d-inline-block align-middle"><div class="d-inline-block"><h6>' + value.user_phone_number + '</h6><p class="text-muted m-b-0">' + value.user_email + '</p></div></div></td><td>$' + value.user_net_worth_usd + '</td><td>' + value.user_pott_intelligence + '</td><td>' + value.user_pott_position + '</td><td>' + value.country_nice_name + '</td><td>' + value.user_dob + '</td><td>' + value.last_online + '</td><td>' + value.gender_name + '</td><td class="text-right"><label class="label label-' + flagged_status_class + '">' + flagged_status + '</label><i class="fa fa-flag" aria-hidden="true" style="cursor: pointer"></i></td></tr>');
        //models.push(value.business_full_name); 
    }); 

    $(".theme-loader").animate({
        opacity: "0"
    },1000);
    setTimeout(function() {
        $(".theme-loader").remove();
    }, 800);

    show_notification("msg_holder", "success", "Success:", "Fetch Successful");
}

function  getUsersErrorResponseFunction(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}

// RESENDING THE PASSCODE
function successResponseFunction2(response)
{
    fade_out_loader_and_fade_in_form("loadertwo", "formtwo"); 
    $('#formtwo')[0].reset();
    show_notification("msg_holder", "success", "Success:", response.message);
}

function errorResponseFunction2(errorThrown)
{
    fade_out_loader_and_fade_in_form("loadertwo", "formtwo"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}


