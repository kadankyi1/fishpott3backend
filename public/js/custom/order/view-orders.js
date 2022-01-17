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
        send_restapi_request_to_server_from_form("post", admin_api_update_order_url, bearer, form_data, "", successResponseFunction2, errorResponseFunction2);
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
    $("#table_body").html('');
    $.each(response.data, function(key,value) {
        if(value.processing_status === 1){
            processing_status_class = "success";
            processing_status = "completed";
        } else if(value.processing_status === 0){
            processing_status_class = "warning";
            processing_status = "pending";
        } else {
            processing_status_class = "danger";
            processing_status = "unavailable";
        }

        if(value.payment_status === 1){
            payment_status_class = "success";
            payment_status = "paid";
        } else if(value.payment_status === 0){
            payment_status_class = "danger";
            payment_status = "unpaid";
        } else {
            payment_status_class = "warning";
            payment_status = "unknown";
        }
        
        if(value.flagged_status === 1){
            flagged_status_class = "danger";
            flagged_status = "Flagged";
        } else if(value.flagged_status === 0){
            flagged_status_class = "success";
            flagged_status = "No";
        } else {
            flagged_status_class = "warning";
            flagged_status = "unknown";
        }
        $('#table_body').append('<tr><td>' + value.transaction_type + '</td><td><div class="chk-option"><div class="checkbox-fade fade-in-primary"><i class="fa fa-external-link-square" aria-hidden="true"  style="cursor: pointer"></i></div></div><div class="d-inline-block align-middle"><div class="d-inline-block"><h6>' + value.user_fullname + '</h6><p class="text-muted m-b-0">' + value.user_phone + ' | ' + value.user_email + '</p></div></div></td><td><div class="d-inline-block align-middle"><div class="d-inline-block"><h6>' + value.stock_name + '</h6><p class="text-muted m-b-0">' + value.stock_business_fincode + '</p></div></div></td><td>' + value.stock_price_usd_or_receiver_pottname_or_buyback_offer + '</td><td>' + value.stocks_quantity + '</td><td>' + value.risk_insurance + '</td><td>' + value.risk_insurance_fee + '</td><td>' + value.ADD_PROCESSING_FEE + '</td><td>(' + value.total_fees_usd + ') <p class="text-muted m-b-0">' + value.total_fee_local_or_total_payout_local + '</p></td><td>' + value.rate_usd_to_local + '</td><td>(' + value.networkname + ') <p class="text-muted m-b-0">RN: ' + value.routing_no + '</p></td><td>(' + value.account_name + ') <p class="text-muted m-b-0">AN: ' + value.account_no + '</p></td><td>' + value.created_at + '</td><td class="text-right"><label class="label label-' + payment_status_class + '">' + payment_status + '</label></td><td class="text-right"><label class="label label-' + processing_status_class + '">' + processing_status + '</label><i class="fa fa-arrow-circle-right" aria-hidden="true" style="cursor: pointer" onclick="toggleTransactionProcessedStatus()" data-tranid="' + value.transaction_sys_id + '" id="' + value.transaction_ref_id + '"></i><p class="text-muted m-b-0"  style="display:none;" id="processloader' + value.transaction_id + '">Working..</p></td><td class="text-right"><label class="label label-' + flagged_status_class + '">' + flagged_status + '</label><i class="fa fa-flag" aria-hidden="true" style="cursor: pointer" onclick="toggleTransactionProcessedStatus()" data-tranid="' + value.transaction_sys_id + '" id="' + value.transaction_ref_id + '"></i><p class="text-muted m-b-0" style="display:none;" id="flagloader' + value.transaction_id + '">Working..</p></td></tr>');
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


