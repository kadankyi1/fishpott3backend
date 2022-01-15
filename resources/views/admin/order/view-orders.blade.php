<?php
$active_page = "Orders";
$page_name = "View Orders";
$page_title = "View orders and take simple actions on them";
?>

@extends('admin.layouts.app')

<!-- SETTING THE CONTENT AS REQUIRED BY THE CORE STRUCTURE OF THE PAGE -->
@section('content')
<div class="pcoded-inner-content">
    <!-- Main-body start -->
    <div class="main-body">
        <div class="page-wrapper">
            <!-- Page-body start -->
            <div class="page-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Note</h5>
                                <span>Simply the, <code>criteria, type keyword</code> and <code>search </code> to find an order</span>
                            </div>
                            <div class="loader-holder offset-md-5" id="loader" style="display: none"><br><br><br><br><br><br><div class="myloader"></div></div>

                            <div class="card-block">
                                <form id="form">
                                    <!-- START OF FIRST COLUMN -->
                                    <input id="administrator_phone_number"  name="administrator_phone_number" required type="hidden" class="form-control">
                                    <input id="administrator_sys_id"  name="administrator_sys_id" required type="hidden" class="form-control">
                                    <input id="frontend_key"  name="frontend_key" required type="hidden" class="form-control">

                                    <h4 class="sub-title">Form</h4>
                                    <div class="form-group row">
                                        <div class="col-sm-7">
                                            <input id="keyword" name="keyword" autocomplete="off" type="text"  required class="form-control" placeholder="Keyword">
                                        </div>
                                        <div class="col-sm-3">
                                            <button class="btn btn-primary waves-effect waves-light">Search</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector">

                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Note</h5>
                                <span>Before you action an order as completed, <code>run it by FinCap Securities</code> to make  <code>sure </code> it is truely completed</span>
                            </div>
                            <div class="loader-holder offset-md-5" id="loadertwo" style="display: none"><br><br><br><br><br><br><div class="myloader"></div></div>

                            <div class="card-block">
                                <form id="formtwo">
                                    <!-- START OF FIRST COLUMN -->
                                    <input id="administrator_phone_number2"  name="administrator_phone_number" required type="hidden" class="form-control">
                                    <input id="administrator_sys_id2"  name="administrator_sys_id" required type="hidden" class="form-control">
                                    <input id="frontend_key2"  name="frontend_key" required type="hidden" class="form-control">

                                    <h4 class="sub-title">Form</h4>
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <select id="action_type" name="action_type" required class="form-control">
                                                <option value="">Choose Action</option>
                                                <option value="1">Processing Complete</option>
                                                <option value="2">Flag</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <input id="order_id" name="order_id" autocomplete="off" type="text"  required class="form-control" placeholder="Order No.">
                                        </div>
                                        <div class="col-sm-3">
                                            <input id="action_info" name="action_info" type="text" class="form-control" placeholder="Action Info">
                                        </div>
                                        <div class="col-sm-2">
                                            <input id="administrator_pin2" name="administrator_pin" autocomplete="off" type="password"  required class="form-control" placeholder="PIN">
                                        </div>
                                        <div class="col-sm-2">
                                            <button class="btn btn-primary waves-effect waves-light">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector">

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-md-12">
                        <div class="card table-card">
                            <div class="card-header">
                                <h5>Projects</h5>
                                <span>Before you action an order as completed, <code>run it by FinCap Securities</code> to make  <code>sure </code> it is truely completed</span>
                                <div class="card-header-right">
                                    <ul class="list-unstyled card-option">
                                        <li><i class="fa fa fa-wrench open-card-option"></i></li>
                                        <li><i class="fa fa-window-maximize full-card"></i></li>
                                        <li><i class="fa fa-minus minimize-card"></i></li>
                                        <li><i class="fa fa-refresh reload-card"></i></li>
                                        <li><i class="fa fa-trash close-card"></i></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-block">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>
                                                <div class="chk-option">
                                                    <div class="checkbox-fade fade-in-primary">
                                                        <label class="check-task">
                                                            <input type="checkbox" value="">
                                                            <span class="cr">
                                                                    <i class="cr-icon fa fa-check txt-default"></i>
                                                                </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                User
                                            </th>
                                            <th>Stock</th>
                                            <th>Per Stock($)/Receiver</th>
                                            <th>Qty</th>
                                            <th>R-I</th>
                                            <th>R-I Fee</th>
                                            <th>Processing</th>
                                            <th>Ttl($ | Local )</th>
                                            <th>Rate</th>
                                            <th>Payout Bank</th>
                                            <th>Payout Account</th>
                                            <th class="text-right">Payment</th>
                                            <th class="text-right">Processed</th>
                                            <th class="text-right">Flagged</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table_body">
                                            <!--
                                                <tr>
                                                <td>TRANSFER</td>
                                                <td>
                                                    <div class="chk-option">
                                                        <div class="checkbox-fade fade-in-primary">
                                                            <i class="fa fa-external-link-square" aria-hidden="true"  style="cursor: pointer"></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-inline-block align-middle">
                                                        <div class="d-inline-block">
                                                            <h6>User Name</h6>
                                                            <p class="text-muted m-b-0">phone|email</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-inline-block align-middle">
                                                        <div class="d-inline-block">
                                                            <h6>Business name</h6>
                                                            <p class="text-muted m-b-0">Find Code | Stock Code</p>
                                                        </div>
                                                    </div>
                                                    
                                                </td>
                                                <td>$0.5</td>
                                                <td>66</td>
                                                <td>100%</td>
                                                <td>$33</td>
                                                <td>$3</td>
                                                <td>$330</td>
                                                <td>$330</td>
                                                <td class="text-right">
                                                    <label class="label label-success">paid</label>
                                                </td>
                                                <td class="text-right">
                                                    <label class="label label-success">pending</label>
                                                </td>
                                                <td class="text-right">
                                                    <label class="label label-danger">Flagged</label>
                                                    <i class="fa fa-flag" aria-hidden="true" style="cursor: pointer"></i>
                                                </td>
                                            </tr>
                                            -->
                                        
    "": "+233553663643",
    "": "annodankyikwaku@gmail.com",
    "": "Time Breakfast",
    "": "20211125-072202",
    "": "$9.00",
    "": 100,
    "": "100% Risk Insurance",
    "": "$90.00",
    "": "999.00",
    "": "6.00",
    "": 1,
    "": 0,
    "": 1,
    "payment_status_text_or_routing_no": "paid",
    "account_no": "NA",
    "": "Gh¢5994.00",
    "created_at": "2021-12-16 11:23:40"

<tr data-tranid="' + value.transaction_sys_id + '" id="' + value.transaction_ref_id + '">
    <td>' + value.transaction_type + '</td>
    <td>
        <div class="chk-option">
            <div class="checkbox-fade fade-in-primary">
                <i class="fa fa-external-link-square" aria-hidden="true"  style="cursor: pointer"></i>
            </div>
        </div>
        <div class="d-inline-block align-middle">
            <div class="d-inline-block">
                <h6>' + value.user_fullname + '</h6>
                <p class="text-muted m-b-0">' + value.user_phone + '|' + value.user_email + '</p>
            </div>
        </div>
    </td>
    <td>
        <div class="d-inline-block align-middle">
            <div class="d-inline-block">
                <h6>' + value.stock_name + '</h6>
                <p class="text-muted m-b-0">' + value.stock_business_fincode + '</p>
            </div>
        </div>
        
    </td>
    <td>' + value.stock_price_usd_or_receiver_pottname_or_buyback_offer + '</td>
    <td>' + value.stocks_quantity + '</td>
    <td>' + value.risk_insurance + '</td>
    <td>' + value.risk_insurance_fee_or_account_name + '</td>
    <td>' + value.risk_insurance + '</td>
    <td>' + value.total_fees_usd + '</td>
    <td>
        (' + value.rate_usd_to_local + ') 
        <p class="text-muted m-b-0">' + value.total_fee_local_or_total_payout_local + '</p>
    </td>
    <td class="text-right">
        <label class="label label-success">paid</label>
    </td>
    <td class="text-right">
        <label class="label label-success">pending</label>
    </td>
    <td class="text-right">
        <label class="label label-danger">Flagged</label>
        <i class="fa fa-flag" aria-hidden="true" style="cursor: pointer"></i>
    </td>
</tr>
                                        </tbody>
                                    </table>
                                    <div class="text-right m-r-20">
                                        <span class=" b-b-primary text-primary">Contact A Super-Admin If Anything is off</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Page-body end -->
        </div>
    </div>
</div>
@endsection


    @section('bottom-scripts');    
        <!-- Required Jquery -->
        <script type="text/javascript" src="/js/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="/js/jquery-ui/jquery-ui.min.js "></script>
        <script type="text/javascript" src="/js/popper.js/popper.min.js"></script>
        <script type="text/javascript" src="/js/bootstrap/js/bootstrap.min.js "></script>
        <script type="text/javascript" src="/pages/widget/excanvas.js "></script>
        <!-- waves js -->
        <script src="/pages/waves/js/waves.min.js"></script>
        <!-- jquery slimscroll js -->
        <script type="text/javascript" src="/js/jquery-slimscroll/jquery.slimscroll.js "></script>
        <!-- modernizr js -->
        <script type="text/javascript" src="/js/modernizr/modernizr.js "></script>
        <!-- slimscroll js -->
        <script type="text/javascript" src="/js/SmoothScroll.js"></script>
        <script src="/js/jquery.mCustomScrollbar.concat.min.js "></script>
        <!-- Chart js -->
        <script type="text/javascript" src="/js/chart.js/Chart.js"></script>
        <!-- amchart js -->
        <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
        <script src="/pages/widget/amchart/gauge.js"></script>
        <script src="/pages/widget/amchart/serial.js"></script>
        <script src="/pages/widget/amchart/light.js"></script>
        <script src="/pages/widget/amchart/pie.min.js"></script>
        <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
        <!-- menu js -->
        <script src="/js/pcoded.min.js"></script>
        <script src="/js/vertical-layout.min.js "></script>
        <!-- custom js -->
        <script type="text/javascript" src="/pages/dashboard/custom-dashboard.js"></script>
        <script type="text/javascript" src="/js/script.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.7/packaged/jquery.noty.packaged.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css">
        <script type="text/javascript" src="/js/custom/config.js "></script>
        <script type="text/javascript">
            the_model = "orders";
        </script>
        <script type="text/javascript" src="/js/custom/order/view-orders.js"></script>
    @endsection
