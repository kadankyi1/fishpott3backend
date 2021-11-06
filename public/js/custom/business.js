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
        console.log("admin_api_add_business_url: " + admin_api_add_business_url);
        console.log("Token: " + bearer);
        var data = {
            'administrator_phone_number': localStorage.getItem("administrator_phone_number"),
            'administrator_sys_id': localStorage.getItem("administrator_sys_id"),
            'frontend_key': localStorage.getItem("frontend_key"),
            'administrator_pin': $("#administrator_pin").val(),
            'business_pottname' : $("#business_pottname").val(),
            'business_registration_number' : $("#business_registration_number").val(),
            'business_type' : $("#business_type").val(),
            //'business_logo_file' : $("#business_logo_file").val(),
            'business_full_name' : $("#business_full_name").val(),
            'business_stockmarket_shortname' : $("#business_stockmarket_shortname").val(),
            'business_descriptive_bio' : $("#business_descriptive_bio").val(),
            'business_address' : $("#business_address").val(),
            'business_country' : $("#business_country").val(),
            'business_start_date' : $("#business_start_date").val(),
            'business_website' : $("#business_website").val(),
            'business_pitch_text' : $("#business_pitch_text").val(),
            //'business_pitch_video' : $("#business_pitch_video").val(),
            'business_lastyr_revenue_usd' : $("#business_lastyr_revenue_usd").val(),
            'business_lastyr_profit_or_loss_usd' : $("#business_lastyr_profit_or_loss_usd").val(),
            'business_debt_usd' : $("#business_debt_usd").val(),
            'business_cash_on_hand_usd' : $("#business_cash_on_hand_usd").val(),
            'business_net_worth_usd' : $("#business_net_worth_usd").val(),
            'business_price_per_stock_usd' : $("#business_price_per_stock_usd").val(),
            'business_investments_amount_needed_usd' : $("#business_investments_amount_needed_usd").val(),
            'business_maximum_number_of_investors_allowed' : $("#business_maximum_number_of_investors_allowed").val(),
            'business_current_shareholders' : $("#business_current_shareholders").val(),
            //'business_full_financial_report_pdf_url' : $("#business_full_financial_report_pdf_url").val(),
            'business_descriptive_financial_bio' : $("#business_descriptive_financial_bio").val(),
            'business_executive1_firstname' : $("#business_executive1_firstname").val(),
            'business_executive1_lastname' : $("#business_executive1_lastname").val(),
            'business_executive1_position' : $("#business_executive1_position").val(),
            'business_executive2_firstname' : $("#business_executive2_firstname").val(),
            'business_executive2_lastname' : $("#business_executive2_lastname").val(),
            'business_executive2_position' : $("#business_executive2_position").val(),        
        };

        //var data = new FormData($("#form"));
        //data.append('administrator_phone_number', localStorage.getItem("administrator_phone_number"));
        //data.append('administrator_sys_id', localStorage.getItem("administrator_sys_id"));
        //data.append('frontend_key', localStorage.getItem("frontend_key"));
        console.log(data);
        send_restapi_request_to_server_from_form("post", admin_api_add_business_url, bearer, data, "json", successResponseFunction, errorResponseFunction);
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

