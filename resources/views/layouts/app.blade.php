<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts._partials._head')
    <title>{{ config('app.name') }} - @yield('title')</title>
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr"
      data-pc-theme_contrast="" data-pc-theme="light">
<div class="loader-bg">
    <div class="loader-track">
        <div class="loader-fill"></div>
    </div>
</div>
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('home') }}" class="b-brand text-primary d-flex align-items-center">
                <img src="{{ asset('assets/img/carft.png') }}" class="img-fluid" alt="logo" width="58" height="48">
                <div class="ms-3">
                    <div class="h2 mb-0 text-decoration-none">
                        {{ config('app.name') }}
                    </div>
                    <div class="small text-muted text-center">Thinking.Crafting.Transforming</div>
                </div>
            </a>
        </div>

        <div class="navbar-content">
            <div class="card pc-user-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 ">
                            {!! auth()->user()->getImage('class="avatar-1 user-avtar wid-45 hei-45 rounded-circle"
                            alt="user-image"') !!}
                        </div>
                        <div class="flex-grow-1 ms-3 me-2">
                            <h6 class="mb-0">{{ auth()->user()->UserID }}</h6>
                            <small data-i18n="Administrator">{{ auth()->user()->role()?->name }}</small><br>
                            <small data-i18n="Administrator">
                                {{ session('LoginBranchName') ? 'Branch: ' . session('LoginBranchName') : 'No branch selected' }}
                            </small>
                        </div>
                        <a class="btn btn-icon btn-link-secondary avtar collapsed" data-bs-toggle="collapse"
                           href="#pc_sidebar_userlink" aria-expanded="false">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sort-outline"></use>
                            </svg>
                        </a>
                    </div>
                    <div class="pc-user-links collapse" id="pc_sidebar_userlink">
                        <div class="pt-3">
                            <a href="{{ route('profile') }}"><i class="ti ti-user"></i> <span>My Account</span>
                            </a>
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti ti-power"></i> <span data-i18n="Logout">Logout</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts._partials._navbar')

            {{--
            <ul class="pc-navbar">
                <li class="pc-item {{ request()->is('/')?'active':'' }}">
                    <a href="{{ route('home') }}" class="pc-link">
                            <span class="pc-micon"><i data-feather="home" class="pc-icon"></i>
                                <use xlink:href="#custom-fatrows"></use>
                            </span><span class="pc-mtext fw-bold" data-i18n="Data">Home</span></a>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link"><span class="pc-micon"><svg class="pc-icon">
                                    <use xlink:href="#custom-layer"></use>
                                </svg> </span><span class="pc-mtext" data-i18n="Online Courses">Procurement </span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Procurement Plan
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                    Department Needs
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                      <ul class="pc-submenu">
                                      <li class="pc-item"><a class="pc-link" href="{{ route('procurementdepartmentalplan.index') }}" data-i18n="Procurement List">Raise Needs</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('needsapproval.index') }}" data-i18n="Procurement List">Approve Needs</a></li>
                              </ul>
                                        </li>
                                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                    Plan Consolidation
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                      <ul class="pc-submenu">
                                      <li class="pc-item"><a class="pc-link" href="{{ route('consolidated.index') }}" data-i18n="Procurement List">Consolidated Needs</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('procurementplanmaintain.index') }}" data-i18n="Procurement List">New Plan</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('editplan.index') }}" data-i18n="Procurement List">Ammend Plan</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('maptobudget.index') }}" data-i18n="Procurement List">Link Budget Lines</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('procurementassignitem.index') }}" data-i18n="Procurement List">Set Method</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('procurementplanquaterly.index') }}" data-i18n="Procurement List">Schedule Plan</a></li>

                                    </ul>
                                        </li>

                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                    Dashboard
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                      <ul class="pc-submenu">
                                       <li class="pc-item"><a class="pc-link" href="{{ route('procurementplandetails.index') }}" data-i18n="Procurement List">Plan View</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('plantimeline.index') }}" data-i18n="Procurement List">Timeline</a></li>
                                      <li class="pc-item"><a class="pc-link" href="{{ route('calenderbased.index') }}" data-i18n="Procurement List">Calender Based</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('delayeditems.index') }}" data-i18n="Procurement List">Flagged Items</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('planvsactual.index') }}" data-i18n="Procurement List">Plan vs Actual</a></li>
                                    </ul>
                                        </li>

                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                    Approval
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                      <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('submitplan.index') }}" data-i18n="Procurement List">Submit For Approval</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('approvalinbox.index') }}" data-i18n="Procurement List">Approval Inbox</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{ route('procurementplanapproval.index') }}" data-i18n="Procurement List">Approve Plan</a></li>
                                    </ul>
                                        </li>

                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                    Plan Execution
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                      <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('executiondashboard.index') }}" data-i18n="Procurement List">Execution Dashboard</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('approvalinbox.index') }}" data-i18n="Procurement List">Pending Execution</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('submitplan.index') }}" data-i18n="Procurement List">Execution Calendar</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('approvalinbox.index') }}" data-i18n="Procurement List">Deviations</a></li>
                                    </ul>
                                        </li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="javascript:void(0)">
                                    <span data-i18n="Requisitions">Purchase Requisition
                                    </span>
                                <span class="pc-arrow">
                                        <i data-feather="chevron-right">
                                        </i>
                                    </span>
                            </a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{route('requisition.create')}}"
                                                       data-i18n="List">Requisition List</a></li>

                                <li class="pc-item"><a class="pc-link" href="{{route('requisition.index')}}"
                                                       data-i18n="Add">Requisition Approval</a></li>

                                <li class="pc-item"><a class="pc-link" href="{{route('requisitionItem.index')}}"
                                                       data-i18n="Apply">Priority List</a></li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Teacher">Items
                                        Catalogue</span> <span class="pc-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('items.create') }}" data-i18n="List">Add New
                                        Item</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('items.index') }}" data-i18n="Apply">Manage
                                        Items</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('categories.index') }}" data-i18n="Add">Item
                                        Categories</a>
                                </li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Teacher">
                                        Procurement Modes
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('procurement-modes.index') }}"
                                       data-i18n="List">List Modes</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('procurement-modes.create') }}"
                                       data-i18n="Apply">Add Mode</a>
                                </li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Teacher">
                            Tendering
                            </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">

                            <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="#!"> <span data-i18n="Auditors">Tender Setup</span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                            <li class="pc-item"> <a class="pc-link" href="{{ route('initiatetender.index') }}" data-i18n="Create Tender">Tender Initiation</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('initiateapprove.index') }}" data-i18n="Create Tender">Initiation Approval</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tendercategory.index') }}" data-i18n="Tender List">Tender Category</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tendertype.index') }}" data-i18n="Tender List">Tender Type</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('evaluationcriteria.index') }}" data-i18n="Tender List">Criteria Setup</a></li>

                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="#!"> <span data-i18n="Auditors">Suppliers</span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tenderresponse.index') }}" data-i18n="Create Tender">Response Tracking</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tenderclarification.index') }}" data-i18n="Create Tender">Clarifications</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tendersubmission.index') }}" data-i18n="Tender List">Submission</a></li>

                            </ul>
                        </li>

                                <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="#!"> <span data-i18n="Auditors">Opening</span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tenderopening.index') }}" data-i18n="Create Tender">Opening</a></li>
                            </ul>
                        </li>

                                    <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="#!"> <span data-i18n="Auditors">Evaluation</span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                            <li class="pc-item"> <a class="pc-link" href="{{ route('tendercommittee.index') }}" data-i18n="Create Tender">Appoint Committee</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('memberresponse.index') }}" data-i18n="Create Tender">Member Response</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('assignrole.index') }}" data-i18n="Create Tender">Assign Roles</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('evaluationdashboard.index') }}" data-i18n="Create Tender">Evaluators Dashboard</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('bidscores.index') }}" data-i18n="Create Tender">Consolidated Scores</a></li>
                        </ul>
                        </li>

                    <li class="pc-item pc-hasmenu">
                            <a class="pc-link" href="#!"> <span data-i18n="Auditors">Auditors</span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                            <li class="pc-item"> <a class="pc-link" href="{{ route('sasra-auditors.index') }}" data-i18n="SASRA List">SASRA Auditor List</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('sasra-auditors.import') }}" data-i18n="Upload">Upload Auditor List</a></li>
                            <li class="pc-item"> <a class="pc-link" href="{{ route('engaged-auditors.index') }}" data-i18n="Engaged">Engaged Auditors</a></li>
                        </ul>
                        </li>
                    </ul>
                        </li>

                        <li class="pc-item pc
                    </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Teacher">

                                        RFQS
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('rfqs.index') }}" data-i18n="View RFQs">View
                                        RFQs</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('rfqresponses.index') }}"
                                       data-i18n="RFQ Response">RFQ Responses</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('evaluations.index') }}"
                                       data-i18n="RFQ Evaluation">RFQ Evaluation</a>
                                </li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Teacher">
                                        Purchase Order
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('purchaseOrder.index') }}"
                                       data-i18n="View RFQs">View Orders</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('purchaseOrder.create') }}"
                                       data-i18n="RFQ Response">Create Orders</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="#" data-i18n="RFQ Evaluation">Order Approval</a>
                                </li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Teacher">
                                        Sales Order
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('salesOrder.index') }}"
                                       data-i18n="View RFQs">View Orders</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="{{ route('salesOrder.create') }}"
                                       data-i18n="RFQ Response">Create Orders</a>
                                </li>
                                <li class="pc-item">
                                    <a class="pc-link" href="#" data-i18n="RFQ Evaluation">Order Approval</a>
                                </li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Suppliers
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('suppliers.index') }}"
                                                       data-i18n="List">Supplier List</a></li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Receipts">
                                        Good Receipts
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('procurementreceipts.index') }}" data-i18n="List">Good
                                        Receipts</a></li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('procurementreports.index') }}"
                                                       data-i18n="Procurement List">Reports</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="archive" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Inventory">Inventory</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>

                    <ul class="pc-submenu">
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Item Master
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('itemmaster.index') }}"
                                                       data-i18n="Procurement List">Item Master List</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('sku.index') }}"
                                                       data-i18n="Procurement List">Stock Item</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('itemcategory.index') }}"
                                                       data-i18n="Procurement List">Item Category</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('itemsubcategory.index') }}"
                                                       data-i18n="Procurement List">Item Sub Category</a></li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        InterBranch Requisition
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('interbranchrequisition.index') }}"
                                                       data-i18n="Procurement List">New Requisition</a></li>
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('interbranchrequisitionapproval.index') }}"
                                                       data-i18n="Procurement List">Requisition Approval</a></li>

                            </ul>
                        </li>


                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Inventory Dashboard
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('inventorydashboard.index') }}"
                                                       data-i18n="Procurement List">By Branch or Store List</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('movementdashboard.index') }}"
                                                       data-i18n="Procurement List">Movement Dashboard</a></li>

                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Transactions
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('transactionsreceipts.index') }}"
                                                       data-i18n="Procurement List">Receipts</a></li>
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('transactionstransfers.index') }}"
                                                       data-i18n="Procurement List">Transfers</a></li>
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('transactionsadjustment.index') }}"
                                                       data-i18n="Procurement List">Adjustments</a></li>
                            </ul>

                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Stock Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('stocktake.index') }}"
                                                       data-i18n="Procurement List">Stock Take</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('openingstock.index') }}"
                                                       data-i18n="Procurement List">Load Opening Stock</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('bintracking.index') }}"
                                                       data-i18n="Procurement List">Location Tracking</a></li>
                                <li class="pc-item"><a class="pc-link"
                                                       href="{{ route('stockvaluationhistory.index') }}"
                                                       data-i18n="Procurement List">Stock Valuation</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('expirytracking.index') }}"
                                                       data-i18n="Procurement List">Expiry Batch Tracking</a></li>
                            </ul>

                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Conversion Mapping
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('uomconversion.index') }}"
                                                       data-i18n="Procurement List">Conversion Mapping</a></li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('reports.index') }}"
                                                       data-i18n="Procurement List">Reports</a></li>
                            </ul>
                        </li>

                    </ul>

                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="file-text" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Document Management">D.M.S.</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('drepositorymanagement.index') }}"


                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="truck" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Fleet Management">Fleet Management</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('drivermanagement.index') }}"
                                               data-i18n="Driver Management">Driver Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('vehicle-registry.index') }}"
                                               data-i18n="fleetmanagement">Vehicle Registry</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('tripmanagement.index') }}"
                                               data-i18n="fleetmanagement">Trip Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('fuelmanagement.index') }}"
                                               data-i18n="fleetmanagement">Fuel Management</a></li>
                        <li class="pc-item"><a class="pc-link"
                                               href="{{ route('complianceanddocumentation.index') }}"
                                               data-i18n="fleetmanagement">Compliance</a></li>
                        <li class="pc-item"><a class="pc-link"
                                               href="{{ route('fleetprocurementanddisposal.index') }}"
                                               data-i18n="fleetmanagement">Fleet Disposal</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('inventoryofspareparts.index') }}"
                                               data-i18n="fleetmanagement">Inventory Of Spare Parts</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('utilization.index') }}"
                                               data-i18n="fleetmanagement">Utilization & Costing</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('servicetracking.index') }}"
                                               data-i18n="fleetmanagement">Service Tracking</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('gps.index') }}"
                                               data-i18n="fleetmanagement">GPS Integration</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('reports.index') }}"
                                               data-i18n="fleetmanagement">Reports</a></li>
                    </ul>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="home" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Property Management">Property Management</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Propery Registry
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('PropertyRegistry.index') }}"
                                                       data-i18n="Property List">Add Property</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('propertytype.index') }}"
                                                       data-i18n="Property List">Property Type</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('propertycategory.index') }}"
                                                       data-i18n="Property List">Property Category</a></li>
                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Structural Mapping
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                    <ul class="pc-submenu">
                                        <li class="pc-item"><a class="pc-link" href="{{ route('addblock.index') }}"
                                                               data-i18n="Procurement List">Add Block</a></li>
                                        <li class="pc-item"><a class="pc-link" href="{{ route('addfloor.index') }}"
                                                               data-i18n="Procurement List">Add Floor</a></li>
                                        <li class="pc-item"><a class="pc-link" href="{{ route('addunit.index') }}"
                                                               data-i18n="Procurement List">Add Unit</a></li>
                                    </ul>
                                </li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('attachments.index') }}"
                                                       data-i18n="Property List">Property Attachments</a></li>
                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Tenant & Lease
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('addtenant.index') }}"
                                                       data-i18n="Procurement List">Tenant Maintenance</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('tenantclearance.index') }}"
                                                       data-i18n="Procurement List">Tenant Clearance</a></li>
                                <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Lease Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>

                                    <ul class="pc-submenu">
                                        <li class="pc-item"><a class="pc-link" href="{{ route('addlease.index') }}"
                                                               data-i18n="Procurement List">Lease Maintenance</a></li>
                                        <li class="pc-item"><a class="pc-link" href="{{ route('schedulelease.index') }}"
                                                               data-i18n="Procurement List">Lease Schedule</a></li>
                                        <li class="pc-item"><a class="pc-link" href="{{ route('renewlease.index') }}"
                                                               data-i18n="Procurement List">Lease Renewal</a></li>
                                        <li class="pc-item"><a class="pc-link"
                                                               href="{{ route('terminatelease.index') }}"
                                                               data-i18n="Procurement List">Lease Termination</a></li>
                                        <li class="pc-item"><a class="pc-link"
                                                               href="{{ route('paymentfrequency.index') }}"
                                                               data-i18n="Procurement List">Payment Frequency</a></li>
                                    </ul>
                                </li>
                            </ul>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                            Billing & Receipting
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('rentinvoice.index') }}"
                                                       data-i18n="Procurement List">Invoicing</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('rentreceipt.index') }}"
                                                       data-i18n="Procurement List">Receipting</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('tenantledger.index') }}"
                                                       data-i18n="Procurement List">Tenant Ledger</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('rentdashboard.index') }}"
                                                       data-i18n="Procurement List">Rent Dashboard</a></li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                            Maintenance & Issues
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('maintenancerequest.index') }}"
                                                       data-i18n="Procurement List">Maintenance request</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('assignrequest.index') }}"
                                                       data-i18n="Procurement List">Assign</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('maintenancedashboard.index') }}"
                                                       data-i18n="Procurement List">Dashboard</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('workcompletion.index') }}"
                                                       data-i18n="Procurement List">Work Completion</a></li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                            Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('propertyreports.index') }}"
                                                       data-i18n="Procurement List">Reports</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('propertyanalytics.index') }}"
                                                       data-i18n="Procurement List">Analytics</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="shield" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Insurance">Insurance</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('providermanagement.index') }}"
                                               data-i18n="Provider Management">Provider Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('insurancetypemanagement.index') }}"
                                               data-i18n=" insurancetypeManagement">Insurance Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('insurancepolicymanagement.index') }}"
                                               data-i18n=" insurancepolicymanagement">policy Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('coveredassetmanagement.index') }}"
                                               data-i18n=" coveredassetmanagement">Asset Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('premiumpaymentmanagement.index') }}"
                                               data-i18n=" premiumpaymentmanagement">Premium Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('claimsmanagement.index') }}"
                                               data-i18n=" claimsmanagement">Claims Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('renewalmanagement.index') }}"
                                               data-i18n=" renewalmanagement">Renewal Management</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('reportmanagement.index') }}"
                                               data-i18n=" reportmanagement">Report Management</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon"><i data-feather="briefcase" class="pc-icon"></i></span>
                            <span class="pc-mtext" data-i18n="Legal">Legal</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu">
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Case Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('caseregister.index') }}"
                                            data-i18n="case register">Case Register</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{ route('casedetails.index') }}"
                                            data-i18n="case details">Case details</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{ route('hearing.index') }}"
                                            data-i18n="case hearing">Case Hearing</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{ route('casedocuments.index') }}"
                                            data-i18n="case documents">Case Documents</a></li>
                                    <li class="pc-item"><a class="pc-link" href="{{ route('casenotes.index') }}"
                                            data-i18n="case details">Case Notes</a></li>
                                </ul>
                            </li>
                             <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Contract Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('contractapproval.index') }}"
                                            data-i18n="Contract Approval">Contract Approval</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('contractdrafting.index') }}"
                                            data-i18n="Contract Drafting">Contract Drafting</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('contractrepository.index') }}"
                                            data-i18n="Contract Repository">Contract Repository</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('obligationtracker.index') }}"
                                            data-i18n="Obligation Tracker">Obligation Tracker</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('renewals.index') }}"
                                            data-i18n="Expiry Alerts & Renewals">Expiry Alerts & Renewals</a></li>

                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Compliance Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('regulatorychecklist.index') }}"
                                            data-i18n="Regulatory Checklist">Regulatory Checklist</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('compliancecalendar.index') }}"
                                            data-i18n="Compliance calendar">Compliance calendar</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('fillingtracker.index') }}"
                                            data-i18n="Filling Tracker">Filing Tracker</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('noncomplianceregister.index') }}"
                                            data-i18n="Non-compliance Register">Non-compliance Register</a></li>

                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Legal Dashboard & Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                    <li class="pc-item"><a class="pc-link" href="{{ route('casessummary.index') }}"
                                            data-i18n="Cases  Summary">Open vs Closed Cases Summary</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('legalexpenses.index') }}"
                                            data-i18n="Legal Expenses">Legal Expenses by Case or Department</a></li>
                                    <li class="pc-item"><a class="pc-link"href="{{ route('hearings.index') }}"
                                            data-i18n="hearings">Upcoming Hearings Calendar</a></li>

                                </ul>
                            </li>
                            <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                         Intellectual Property
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('copyrightlicenseagreement.index') }}" data-i18n="legal">License Agreement</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('patenttracking.index') }}" data-i18n="legal">Patent Tracking</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('trademarkregister.index') }}" data-i18n="legal">Trademark Register</a></li>
                                    </ul>
                            </li>
                             <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                         Legal Notices
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('archive.index') }}" data-i18n="legal">Correspondance </a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('register.index') }}" data-i18n="legal">Notice Register</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('responsetracker.index') }}" data-i18n="legal">Response Tracker</a></li>
                                    </ul>
                             </li>
                             <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                     Lawyer Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('externaldirectory.index') }}" data-i18n="legal">External Directory </a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('feetracker.index') }}" data-i18n="legal">Fee Tracker</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('performancelog.index') }}" data-i18n="legal">Performance Log</a></li>
                                    </ul>
                            </li>
                             <li class="pc-item pc-hasmenu"><a class="pc-link" href="#!"><span data-i18n="Supplier">
                                        Legal Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                                <ul class="pc-submenu">
                                     <li class="pc-item"><a class="pc-link" href="{{ route('compliancestatus.index') }}" data-i18n="legal">Compliance Status</a></li>
                                     <li class="pc-item"><a class="pc-link" href="{{ route('pendingcontracts.index') }}" data-i18n="legal">Pending Contracts</a></li>                                    </ul>
                            </li>


                        </ul>


                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="dollar-sign" class="pc-icon"></i></span>
                        <span class="pc-mtext" data-i18n="Budgeting">Finance</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Accounts Payable
                                </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('vendormaster.index') }}"
                                                       data-i18n="Finance List">Vendor Master</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('invoiceentry.index') }}"
                                                       data-i18n="Finance List">Invoice Entry</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('creditnote.index') }}"
                                                       data-i18n="Finance List">Credit/Debit Note</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('paymentprocessing.index') }}"
                                                       data-i18n="Finance List">Payment Processing</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('paymentvoucher.index') }}"
                                                       data-i18n="Finance List">Payment Voucher</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('agingreport.index') }}"
                                                       data-i18n="Finance List">Aging Report</a></li>
                            </ul>
                        </li>

                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">

                                                Accounts Receivable
                                        </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('customermaster.index') }}"
                                                       data-i18n="Finance">Customer Master</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('invoicegeneration.index') }}"
                                                       data-i18n="Finance">Invoice Generation</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('receiptsposting.index') }}"
                                                       data-i18n="Finance">Receipts Posting</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('creditmanagement.index') }}"
                                                       data-i18n="Finance">Credit Management</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('agingreportar.index') }}"
                                                       data-i18n="Finance">Aging Report</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('customerstatement.index') }}"
                                                       data-i18n="Finance">Customer Statement</a></li>
                            </ul>
                        </li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('journalbatch.index') }}"
                                               data-i18n="Procurement List">Journal Batch</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('ledgeraccounts.index') }}"
                                               data-i18n="Finance">Ledger Accounts</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('transactiontypes.index') }}"
                                               data-i18n="Finance">Transaction Types</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('bankreconciliation.index') }}"
                                               data-i18n="Finance">Bank Reconciliation</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('periodmanagement.index') }}"
                                               data-i18n="Finance">Period Mananagement</a></li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                        Bank Management
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('bankaccountsetup.index') }}"
                                                       data-i18n="finance">Account Setup</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('cashbook.index') }}"
                                                       data-i18n="finance">Cash Book</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('cashmanagement.index') }}"
                                                       data-i18n="finance">Cash Management</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('chequemanagement.index') }}"
                                                       data-i18n="finance">Cheque Management</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('paymentandreceiptvouchers.index') }}"
                                                       data-i18n="finance">Vouchers</a></li>

                            </ul>
                        </li>
                        <li class="pc-item pc-hasmenu"><a class="pc-link" href="javascript:void(0)"><span data-i18n="Supplier">
                                         Financial Reports
                                    </span> <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item"><a class="pc-link" href="{{ route('balancesheet.index') }}"
                                                       data-i18n="finance">Balance Sheet</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('cashflowstatement.index') }}"
                                                       data-i18n="finance">Cash Flow Statement</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('consolidationreports.index') }}"
                                                       data-i18n="finance">Consolidation Reports</a></li>
                                <li class="pc-item"><a class="pc-link" href="{{ route('incomestatement.index') }}"
                                                       data-i18n="finance">Income Statement</a></li>
                            </ul>
                        </li>

                    </ul>
                </li>

                <li class="pc-item pc-hasmenu {{ request()->is('hrm*')?'active pc-trigger':'' }}">
                    <a href="javascript:void(0)" class="pc-link">
                        <span class="pc-micon"><i data-feather="users" class="pc-icon"></i></span>
                        <span class="pc-mtext" >H.R.M</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item pc-hasmenu {{ request()->is('hrm/employees*')?'active pc-trigger':'' }}"><a
                                class="pc-link" href="javascript:void(0)">
                                         Employees
                                 <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                            <ul class="pc-submenu">
                                <li class="pc-item {{ request()->is('hrm/employees/create')?'active pc-trigger':'' }}">
                                    <a class="pc-link" href="{{ route('employees.create') }}">New Employee</a></li>
                                <li class="pc-item {{ request()->is('hrm/employees/e*')?'active pc-trigger':'' }}"><a
                                        class="pc-link" href="{{ route('employees.index') }}">Employees</a></li>
                            </ul>
                        </li>
                        <li class="pc-item">
                            <a class="pc-link {{ request()->is('hrm/departments*')?'active pc-trigger':'' }}"
                               href="{{ route('departments.index') }}">Departments</a>
                        </li>
                        <li class="pc-item">
                            <a class="pc-link {{ request()->is('hrm/committees*')?'active pc-trigger':'' }}"
                               href="{{ route('hrms.committees.index') }}">Committees</a>
                        </li>

                    </ul>
                </li>
                <li class="pc-item pc-caption"><label data-i18n="Widget">Settings</label>
                    <svg class="pc-icon">
                        <use xlink:href="#custom-presentation-chart"></use>
                    </svg>
                </li>
                <li class="pc-item pc-hasmenu {{ request()->is(['settings/users*', 'settings/roles*'])?'active pc-trigger':'' }}">
                    <a href="javascript:void(0)" class="pc-link">
                            <span class="pc-micon">
                                <i class="fas fa-users"></i>
                            </span>
                        <span class="pc-mtext" data-i18n="">Users and Roles </span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item {{ request()->is(['settings/users*'])?'active ':'' }}">
                            <a class="pc-link" href="{{ route('users.index') }}">Users</a>
                        </li>
                        <li class="pc-item  {{ request()->is(['settings/roles*'])?'active ':'' }}"><a
                                class="pc-link" href="{{ route('roles.index') }}">Roles</a></li>
                        <li class="pc-item  {{ request()->is(['settings/branches*'])?'active ':'' }}"><a
                                class="pc-link" href="{{ route('branches.index') }}">Branches</a></li>

                    </ul>
                </li>
                <li class="pc-item {{ request()->is('settings/lists*')?'active':'' }}">
                    <a href="{{ route('settings.lists') }}" class="pc-link">
                            <span class="pc-micon"><i data-feather="settings" class="pc-icon"></i>
                                <use xlink:href="#custom-fatrows"></use>
                            </span><span class="pc-mtext" data-i18n="Data">Code Details</span></a>
                </li>
            </ul>

             --}}
        </div>
    </div>
</nav>
<header class="pc-header">
    <div class="header-wrapper">
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item pc-sidebar-collapse"><a href="#" class="pc-head-link ms-0" id="sidebar-hide"><i
                            class="ti ti-menu-2"></i></a></li>
                <li class="pc-h-item pc-sidebar-popup"><a href="#" class="pc-head-link ms-0" id="mobile-collapse"><i
                            class="ti ti-menu-2"></i></a></li>
                <li class="pc-h-item d-none d-md-inline-flex">
                    @yield('search-form')
                </li>

            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-sun-1"></use>
                        </svg>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown"><a href="javascript:void(0)"
                                                                                  class="dropdown-item"
                                                                                  onclick="layout_change('dark')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-moon"></use>
                            </svg>
                            <span>Dark</span> </a><a href="javascript:void(0)" class="dropdown-item"
                                                     onclick="layout_change('light')">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sun-1"></use>
                            </svg>
                            <span>Light</span> </a><a href="javascript:void(0)" class="dropdown-item"
                                                      onclick="layout_change_default()">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-setting-2"></use>
                            </svg>
                            <span>Default</span></a></div>
                </li>
                <li class="pc-h-item">
                    <a href="#" class="pc-head-link me-0" data-bs-toggle="offcanvas" data-bs-target="#announcement"
                       aria-controls="announcement">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-flash"></use>
                        </svg>
                    </a>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        <svg class="pc-icon">
                            <use xlink:href="#custom-notification"></use>
                        </svg>
                        {{-- <span class="badge bg-success pc-h-badge">3</span>--}}
                    </a>
                    <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header d-flex align-items-center justify-content-between">
                            <h5 class="m-0">Notifications</h5><a href="javascript:void(0)"
                                                                 class="btn btn-link btn-sm disabled">Mark all
                                read</a>
                        </div>
                        <div class="dropdown-body text-wrap header-notification-scroll position-relative">
                            <p class="text-span text-center my-3">No Notifications here</p>
                        </div>
                        <div class="text-center py-2"><a href="javascript:void(0)"
                                                         class="link-danger disabled">Clear
                                all Notifications</a>
                        </div>
                    </div>
                </li>
                <li class="dropdown pc-h-item">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                       role="button" aria-haspopup="false" aria-expanded="false">
                        {!! auth()->user()->getImage('class="avatar-1 user-avtar" alt="user-image"') !!}
                        <svg class="pc-icon">
                            <use xlink:href="#custom-setting-2"></use>
                        </svg>

                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <i class="ti ti-user"></i> <span>My Account</span>
                        </a>
                        <a href="javascript:void(0)" class="dropdown-item"><i class="ti ti-headset"></i>
                            <span>Support</span>
                        </a>

                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="dropdown-item">
                            <i class="ti ti-power"></i> <span data-i18n="Logout">Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>

<div class="pc-container">
    <div class="pc-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-6">
                        <div class="page-header-title"><h3 class="mb-0">@yield('title')</h3></div>
                    </div>
                    <div class="col-12 col-sm-6">
                        @hasSection('title')
                            <ul class="breadcrumb float-end">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                @yield('breadcrumbs')
                                <li class="breadcrumb-item" aria-current="page">@yield('title')</li>
                            </ul>
                        @endif
                    </div>


                </div>
            </div>
        </div>
        @yield('content')
    </div>
</div>
<footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
        <div class="row">
            <div class="col my-1">
                <p class="m-0">@include('layouts._partials._copyright')</p>
            </div>
            <div class="col-auto my-1">
                {{--<ul class="list-inline footer-link mb-0">
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://ableproadmin.com/index.html">Home</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.gitbook.io/able-pro/"
                            target="_blank">Documentation</a></li>
                    <li class="list-inline-item"><a
                            href="../../external.html?link=https://phoenixcoded.authordesk.app/"
                            target="_blank">Support</a></li>
                </ul>--}}

            </div>
        </div>
    </div>
</footer>
@include('layouts._partials._scripts')

@stack('scripts')
</body>

</html>
