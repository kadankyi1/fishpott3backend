/*
|--------------------------------------------------------------------------
| URLS 
|--------------------------------------------------------------------------
|*/

var host = "https://app.fishpott.com/";
//var host = "http://fishpott.local";

// LOGIN PAGE URLS
var admin_web_login_page_url = `${host}/admin/login`;
var admin_api_login_url = `${host}/api/v1/admin/login`;

// LOGOUT URL
var admin_api_logout_url = `${host}/api/v1/admin/logout`;

// DASHBOARD URL
var admin_web_dashboard_page_url = `${host}/admin/dashboard`;
var admin_api_get_dashboard_data_url = `${host}/api/v1/admin/get-dashboard-data`;

// ADD DRILL
var admin_web_add_drill_page_url = `${host}/admin/drills/add`;
var admin_api_add_drill_url = `${host}/api/v1/admin/add-drill`;

// ADD BUSINESS
var admin_web_add_business_page_url = `${host}/admin/business/add`;
var admin_api_add_business_url = `${host}/api/v1/admin/add-business`;

// SEARCH MODEL
var admin_api_add_search_model_url = `${host}/api/v1/admin/search-model`;

// SEARCH ORDERS
var admin_api_search_orders_url = `${host}/api/v1/admin/search-orders`;

// SEARCH ORDERS
var admin_api_update_order_url = `${host}/api/v1/admin/update-order`;

// SEARCH USERS
var admin_api_search_users_url = `${host}/api/v1/admin/search-users`;

// UPDATE USER
var admin_api_update_user_url = `${host}/api/v1/admin/update-user`;

// ADD SUGGESTION
var admin_api_add_suggestion_url = `${host}/api/v1/admin/add-suggestion`;

// ADD NEW STOCK VALUE
var admin_api_add_new_stock_value_url = `${host}/api/v1/admin/add-new-stock-value`;

// ADD NEW STOCK TRAIN DATA
var admin_api_add_new_stock_train_data_url = `${host}/api/v1/admin/add-stock-train-data`;


// CHECKING IF USER HAS AN API TOKEN
function user_has_api_token()
{
    if(
        localStorage.getItem("admin_access_token") != null 
        && localStorage.getItem("administrator_sys_id") != null 
        && localStorage.getItem("administrator_phone_number") != null 
        && localStorage.getItem("administrator_user_pottname") != null 
        && localStorage.getItem("administrator_firstname") != null
        && localStorage.getItem("administrator_surname") != null
        && localStorage.getItem("frontend_key") != null
        && localStorage.getItem("admin_access_token").trim() != "" 
        && localStorage.getItem("administrator_sys_id").trim() != "" 
        && localStorage.getItem("administrator_phone_number").trim() != ""
        && localStorage.getItem("administrator_user_pottname").trim() != ""
        && localStorage.getItem("administrator_firstname").trim() != "" 
        && localStorage.getItem("administrator_surname").trim() != ""
        && localStorage.getItem("administrator_firstname").trim() != "" 
        && localStorage.getItem("frontend_key").trim() != ""
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
    send_restapi_request_to_server_no_form("post", admin_api_get_dashboard_data_url, bearer, data, "json", sign_out_success, sign_out_error);

}

function hide_notification(){
    document.getElementById('msg_div').style.display = "none";
}

// SHOWING A NOTIFICATION ON THE SCREEN
function show_notification(id, type, title, message)
{
    noty({
        text: message,
        type: type, 
        layout: "topRight", timeout: 60000, 
        animation: {
            open: 'animated bounceInRight', // in order to use this you'll need animate.css
            close: 'animated bounceOutRight',
            easing: 'swing',
            speed: 500
        }
    });
}

/*
|--------------------------------------------------------------------------
| FUNCTIONS FOR FETCHING MODEL DATA
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
|
*/
function getModelData(model)
{
    console.log("getModelData STARTED");
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
    send_restapi_request_to_server_no_form("post", admin_api_add_search_model_url, bearer, data, "json", getModelDataSuccessResponseFunction, getModelDataErrorResponseFunction);
}

// RESENDING THE PASSCODE
function getModelDataSuccessResponseFunction(response)
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

function getModelDataErrorResponseFunction(errorThrown)
{
    fade_out_loader_and_fade_in_form("loader", "form"); 
    show_notification("msg_holder", "danger", "Error", errorThrown);
}



// MAKING AUTO COMPLETE SUGGESTIONS
function autocomplete(inp, arr, item_id) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false;}
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {

          /*check if the item starts with the same letters as the text field value:*/
          if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            /*create a DIV element for each matching element:*/
            b = document.createElement("DIV");
            /*make the matching letters bold:*/
            b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
            b.innerHTML += arr[i].substr(val.length);
            /*insert a input field that will hold the current array item's value:*/
            b.innerHTML += "<input type='hidden' value='" + arr[i] + "' data-index='" + i + "'>";
            /*execute a function when someone clicks on the item value (DIV element):*/
            b.addEventListener("click", function(e) {
                /*insert the value for the autocomplete text field:*/
                inp.value = this.getElementsByTagName("input")[0].value;
                console.log("array index: " + this.getElementsByTagName("input")[0].getAttribute("data-index"));
                document.getElementById(item_id).value = model_ids[this.getElementsByTagName("input")[0].getAttribute("data-index")];
                /*close the list of autocompleted values,
                (or any other open lists of autocompleted values:*/
                closeAllLists();
            });
            a.appendChild(b);
          }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
          /*If the arrow DOWN key is pressed,
          increase the currentFocus variable:*/
          currentFocus++;
          /*and and make the current item more visible:*/
          addActive(x);
        } else if (e.keyCode == 38) { //up
          /*If the arrow UP key is pressed,
          decrease the currentFocus variable:*/
          currentFocus--;
          /*and and make the current item more visible:*/
          addActive(x);
        } else if (e.keyCode == 13) {
          /*If the ENTER key is pressed, prevent the form from being submitted,*/
          e.preventDefault();
          if (currentFocus > -1) {
            /*and simulate a click on the "active" item:*/
            if (x) x[currentFocus].click();
          }
        }
    });
    function addActive(x) {
      /*a function to classify an item as "active":*/
      if (!x) return false;
      /*start by removing the "active" class on all items:*/
      removeActive(x);
      if (currentFocus >= x.length) currentFocus = 0;
      if (currentFocus < 0) currentFocus = (x.length - 1);
      /*add class "autocomplete-active":*/
      x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
      /*a function to remove the "active" class from all autocomplete items:*/
      for (var i = 0; i < x.length; i++) {
        x[i].classList.remove("autocomplete-active");
      }
    }
    function closeAllLists(elmnt) {
      /*close all autocomplete lists in the document,
      except the one passed as an argument:*/
      var x = document.getElementsByClassName("autocomplete-items");
      for (var i = 0; i < x.length; i++) {
        if (elmnt != x[i] && elmnt != inp) {
          x[i].parentNode.removeChild(x[i]);
        }
      }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
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
            if(response.status == 1){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.responseText);
            error_response_function(XMLHttpRequest.responseText);
        }
    });
}

function send_restapi_request_to_server_no_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        headers: {
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
            if(response.status == 1){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.responseText);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(XMLHttpRequest.responseText);
        }
    });
}

function send_restapi_request_to_server_from_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        headers: {
            'Authorization': authorization
         },
        data:  form_data,
        contentType: false,
        processData: false,
        async: true,
        cache: false,
        success: function(response){ 
            console.log(response);

            if(response == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            } 
            if(response.status == 1){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.responseText);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(XMLHttpRequest.responseText);
        }
    });
}

function send_file_upload_restapi_request_to_server_from_form(method, url_to_server, authorization, form_data, data_type, success_response_function, error_response_function)
{
    $.ajax({
        type: method,
        url: url_to_server,
        headers: {
            'Authorization': authorization
         },
        data:  form_data,
        contentType: false,
        processData: false,
        async: true,
        cache: false,
        timeout: 600000,
        success: function(response){ 
            console.log(response);

            if(response == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            } 
            if(response.status == 1){
                success_response_function(response);
            } else {
                error_response_function(response.message);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(XMLHttpRequest.responseText);
            if(errorThrown == "Unauthorized"){
                user_token_is_no_longer_valid();
                return;
            }
            error_response_function(XMLHttpRequest.responseText);
        }
    });
}