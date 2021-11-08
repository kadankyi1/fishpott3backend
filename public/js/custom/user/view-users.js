// CHECKING IF USER IS LOGGED IN
if(!user_has_api_token()){
    redirect_to_next_page(admin_web_login_page_url, false);
    //return;
}

$(document).ready(function () 
{
    // GETTING THE DASHBOARD PAGE
    getOrders();

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
        send_restapi_request_to_server_from_form("post", admin_api_search_orders_url, bearer, form_data, "", getOrdersSuccessResponseFunction, getOrdersErrorResponseFunction);
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
function getOrders()
{
    console.log("getModelData STARTED");
    var bearer = "Bearer " + localStorage.getItem("admin_access_token"); 
    console.log("admin_api_search_orders_url: " + admin_api_search_orders_url);
    console.log("Token: " + bearer);
    var data = {
        'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
        'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
        'frontend_key': localStorage.getItem("frontend_key")
    };
    console.log(data);
    send_restapi_request_to_server_no_form("post", admin_api_search_orders_url, bearer, data, "json", getOrdersSuccessResponseFunction, getOrdersErrorResponseFunction);
}

// RESENDING THE PASSCODE
function getOrdersSuccessResponseFunction(response)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    //$("#table_body").html('');
    $.each(response.data, function(key,value) {
        console.log(value.stockpurchase_payment_gateway_info);
        if(value.stockpurchase_payment_gateway_status === 1){
            payment_status_class = "success";
            payment_status = "paid";
        } else if(value.stockpurchase_payment_gateway_status === 0){
            payment_status_class = "danger";
            payment_status = "unpaid";
        } else {
            payment_status_class = "warning";
            payment_status = "unknown";
        }
        if(value.stockpurchase_processed === 1){
            processing_status_class = "success";
            processing_status = "completed";
        } else if(value.stockpurchase_processed === 0){
            processing_status_class = "warning";
            processing_status = "pending";
        } else {
            processing_status_class = "danger";
            processing_status = "denied";
        }
        if(value.stockpurchase_flagged === 1){
            flagged_status_class = "danger";
            flagged_status = "Flagged";
        } else if(value.stockpurchase_flagged === 0){
            flagged_status_class = "success";
            flagged_status = "No";
        } else {
            flagged_status_class = "warning";
            flagged_status = "unknown";
        }
        $('#table_body').append('<tr><td><div class="chk-option"><div class="checkbox-fade fade-in-primary">' + value.stockpurchase_id + '</div></div><div class="d-inline-block align-middle"><img src="/images/avatar-4.jpg" alt="user image" class="img-radius img-40 align-top m-r-15"><div class="d-inline-block"><h6>' + value.user_surname + ' ' + value.user_firstname + '</h6><p class="text-muted m-b-0">' + value.user_phone_number + ' | ' + value.user_email + '</p></div></div></td><td><div class="d-inline-block align-middle"><div class="d-inline-block"><h6>' + value.business_full_name + '</h6><p class="text-muted m-b-0">' + value.business_find_code + '</p></div></div></td><td>$' + value.stockpurchase_price_per_stock_usd + '</td><td>' + value.stockpurchase_stocks_quantity + '</td><td>' + value.risk_type_shortname + '</td><td>$' + value.stockpurchase_risk_insurance_fee_usd + '</td><td>$' + value.stockpurchase_processing_fee_usd + '</td><td>$' + value.stockpurchase_total_price_with_all_fees_usd + '</td><td>' + value.stockpurchase_rate_of_dollar_to_currency_paid_in + '</td><td class="text-right">    <label class="label label-' + payment_status_class + '">' + payment_status + '</label></td><td class="text-right"><label class="label label-' + processing_status_class + '">' + processing_status + '</label></td><td class="text-right"><label class="label label-' + flagged_status_class + '">' + flagged_status + '</label><i class="fa fa-flag" aria-hidden="true" style="cursor: pointer"></i></td></tr>');
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

function getOrdersErrorResponseFunction(errorThrown)
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


