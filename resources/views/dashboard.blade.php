<?php
$active_page = "dashboard";
$page_name = "Dashboard";
$page_title = "Welcome FishPott Admin";
?>

@extends('layouts.app')

<!-- SETTING THE CONTENT AS REQUIRED BY THE CORE STRUCTURE OF THE PAGE -->
                      @section('content')
                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <!-- Page-body start -->
                                    <div class="page-body">
                                        <div class="row">
                                            <!-- task, page, download counter  start -->
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                @yield('components.headersidebar')
                                                                <h4 class="text-c-purple" id="users_total_count"></h4>
                                                                <h6 class="text-muted m-b-0">Users</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="fa fa-users f-28"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-c-purple">
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="users_today_count">Users Today : </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="users_months_count">Users Last Month : </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
            
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-c-green" id="suggestions_active_total_count"></h4>
                                                                <h6 class="text-muted m-b-0">Active Suggestions</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="fa fa-file-text-o f-28"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-c-green">
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="suggestions_active_drills_total_count">Active Drill Suggestion. : </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="suggestions_active_businesses_total_count">Active Business Suggestion : </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-c-red" id="businesses_total_count"></h4>
                                                                <h6 class="text-muted m-b-0">Businesses</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="fa fa-home f-28"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-c-red">
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="businesses_listed_total_count">Listed Businesses: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="businesses_non_listed_total_count">Non-listed Businesses: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-md-6">
                                                <div class="card">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-c-blue" id="orders_pending_total_count"></h4>
                                                                <h6 class="text-muted m-b-0">Pending Paid Orders</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="fa fa-list-alt f-28"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-c-blue">
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="orders_months_total_count">Paid Month Orders: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="orders_months_profit_total_count">Unpaid Month Orders: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-6 col-md-6">
                                                <div class="card">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-c-blue" id="drillanswers_today_count"></h4>
                                                                <h6 class="text-muted m-b-0">Drills Answers Today</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="fa fa-list-alt f-28"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-c-orenge">
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="drillanswers_months_count">Drills Answers Month: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center">
                                                            <div class="col-9">
                                                                <p class="text-white m-b-0" id="drillanswers_year_count">Drills Answers Year: </p>
                                                            </div>
                                                            <div class="col-3 text-right">
                                                                <i class="fa fa-line-chart text-white f-16"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-md-12">
                                                <div class="card quater-card">
                                                    <div class="card-block">
                                                        <h5 class="text-muted m-b-15">Notes</h5>
                                                        <h6>Inquiries</h6>
                                                        <p class="text-muted">Go to the <b><span id="contact_email"></span></b> to keep track of all enquiries from users</p>
                                                        <h6>Payments & Finances</h6>
                                                        <p class="text-muted">Go to <b><span id="payment_gateway_provider_name"></span></b> to get any needed information regarding payments. Login at <b><a id="payment_gateway_provider_url" href=""></a></b></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--  project and team member end -->
                                        </div>
                                    </div>
                                    <!-- Page-body end -->
                                </div>
                                <div id="styleSelector"> </div>
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
        <script type="text/javascript" src="/js/vanilla-material-notifications.js"></script>
        <script type="text/javascript" src="/js/custom/config.js "></script>
        <script type="text/javascript" src="/js/custom/dashboard.js "></script>
    @endsection
</body>

</html>
