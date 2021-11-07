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
                    <div class="col-sm-12">
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
                                    <input id="item_type"  name="item_type" value="2" required type="hidden" class="form-control">

                                    <h4 class="sub-title">Form</h4>
                                    <div class="form-group row">
                                        <div class="col-sm-3">
                                            <select id="business_country" name="business_country" class="form-control">
                                                <option value="1">Choose Criteria </option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <input id="item_identifier" autocomplete="off" name="item_identifier" type="text"  required class="form-control" placeholder="Keyword">
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
                                                Assigned</th>
                                            <th>Name</th>
                                            <th>Due Date</th>
                                            <th class="text-right">Priority</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
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
                                                <div class="d-inline-block align-middle">
                                                    <img src="/images/avatar-4.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">
                                                    <div class="d-inline-block">
                                                        <h6>John Deo</h6>
                                                        <p class="text-muted m-b-0">Graphics Designer</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Able Pro</td>
                                            <td>Jun, 26</td>
                                            <td class="text-right"><label class="label label-danger">Low</label></td>
                                        </tr>
                                        <tr>
                                            <td>
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
                                                <div class="d-inline-block align-middle">
                                                    <img src="/images/avatar-5.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">
                                                    <div class="d-inline-block">
                                                        <h6>Jenifer Vintage</h6>
                                                        <p class="text-muted m-b-0">Web Designer</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Mashable</td>
                                            <td>March, 31</td>
                                            <td class="text-right"><label class="label label-primary">high</label></td>
                                        </tr>
                                        <tr>
                                            <td>
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
                                                <div class="d-inline-block align-middle">
                                                    <img src="/images/avatar-3.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">
                                                    <div class="d-inline-block">
                                                        <h6>William Jem</h6>
                                                        <p class="text-muted m-b-0">Developer</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Flatable</td>
                                            <td>Aug, 02</td>
                                            <td class="text-right"><label class="label label-success">medium</label></td>
                                        </tr>
                                        <tr>
                                            <td>
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
                                                <div class="d-inline-block align-middle">
                                                    <img src="/images/avatar-2.jpg" alt="user image" class="img-radius img-40 align-top m-r-15">
                                                    <div class="d-inline-block">
                                                        <h6>David Jones</h6>
                                                        <p class="text-muted m-b-0">Developer</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Guruable</td>
                                            <td>Sep, 22</td>
                                            <td class="text-right"><label class="label label-primary">high</label></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="text-right m-r-20">
                                        <a href="#!" class=" b-b-primary text-primary">View all Projects</a>
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
            the_model = "business";
        </script>
        <script type="text/javascript" src="/js/custom/suggestion/add-suggestion.js "></script>
    @endsection
