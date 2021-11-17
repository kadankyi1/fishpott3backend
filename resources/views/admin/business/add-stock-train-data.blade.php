<?php
$active_page = "Business";
$page_name = "Add Stock Train Data";
$page_title = "Based on factual information, add a new stock training data";
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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Note</h5>
                                <span>Make sure the  <code>data is certified correct</code> before  <code>entering to the AI</code>  sub-system</span>
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
                                        <label class="col-sm-2 col-form-label">7 Stock Values Separated By #</label>
                                        <div class="col-sm-10">
                                            <input id="value_per_stock_usd_seven_inputs" name="value_per_stock_usd_seven_inputs" type="text" required class="form-control" placeholder="7 Stock Values Separated By ">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">7 Stock Changes Separated By #</label>
                                        <div class="col-sm-10">
                                            <input id="value_change_seven_inputs" name="value_change_seven_inputs" type="text" required class="form-control" placeholder="7 Stock Changes Separated By #">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">7 Stock Volume Separated By #</label>
                                        <div class="col-sm-10">
                                            <input id="volume_seven_inputs" name="volume_seven_inputs" type="text" required class="form-control" placeholder="7 Stock Volume Separated By #">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Output for Openness to experience</label>
                                        <div class="col-sm-10">
                                            <input id="expected_output_o" name="expected_output_o" type="number" min="-1" max="1"  required class="form-control" placeholder="1 or -1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Output for Conscientiousness</label>
                                        <div class="col-sm-10">
                                            <input id="expected_output_c" name="expected_output_c" type="number" min="-1" max="1"  required class="form-control" placeholder="1 or -1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Output for Extraversion</label>
                                        <div class="col-sm-10">
                                            <input id="expected_output_e" name="expected_output_e" type="number" min="-1" max="1"  required class="form-control" placeholder="1 or -1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Output for Agreeableness</label>
                                        <div class="col-sm-10">
                                            <input id="expected_output_a" name="expected_output_a" type="number" min="-1" max="1" required class="form-control" placeholder="1 or -1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Output for Neuroticism</label>
                                        <div class="col-sm-10">
                                            <input id="expected_output_n" name="expected_output_n" type="number" min="-1" max="1"  required class="form-control" placeholder="1 or -1">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">PIN</label>
                                        <div class="col-sm-10">
                                            <input id="administrator_pin" name="administrator_pin" type="password" required class="form-control" placeholder="PIN">
                                        </div>
                                    </div>
                                    <div class="row m-t-30">
                                        <div class="offset-md-4 col-md-4">
                                            <input type="submit" value="Save" class="btn btn-primary btn-md btn-block waves-effect waves-light text-center m-b-20"/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector">

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
        <script type="text/javascript">
            the_model = "business";
        </script>
        <script type="text/javascript" src="/js/custom/config.js "></script>
        <script type="text/javascript" src="/js/custom/business/add-stock-train-data.js "></script>
    @endsection
