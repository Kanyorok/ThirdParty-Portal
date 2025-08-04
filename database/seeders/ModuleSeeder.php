<?php

namespace Database\Seeders;

use App\Enums\Core\ModulesEnum;
use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fresh = DB::table('t_Modules')->count() === 0;
        if ($fresh) {
            DB::table(config('permission.table_names.role_has_permissions'))->delete();
            DB::table(config('permission.table_names.permissions'))->delete();
        }

        $this->_seed($this->_thirdParty($fresh));
        $this->_seed($this->_crm($fresh));
        $this->_seed($this->_procurement($fresh));
        $this->_seed($this->_inventory($fresh));
        $this->_seed($this->_propertyManagement($fresh));
        $this->_seed($this->_fleetManagement($fresh));
        $this->_seed($this->_documentManagement($fresh));
        $this->_seed($this->_legal($fresh));
        $this->_seed($this->_insurance($fresh));
        $this->_seed($this->_hrm($fresh));
        $this->_seed($this->_finance($fresh));
        $this->_seed($this->_settings($fresh));
        $this->_seed($this->_myAccount($fresh));
        $this->_seed($this->_budgetline($fresh));
    }

    protected function _thirdParty(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 100000, 'Name' => ModulesEnum::ThirdParty->description(), 'Icon' => '<i data-feather="users"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 101000, 'Name' => 'Parties', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 100000],

        ]);
        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _crm(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 200000, 'Name' => ModulesEnum::CRM->description(), 'Icon' => '<i data-feather="share-2"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 200100, 'Name' => 'Third Parties', 'Icon' => '<i data-feather="users"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 200000],

            ['ModuleID' => 200110, 'Name' => 'Clients', 'Icon' => null, 'Description' => '', 'Route' => 'clients.index', 'ParentID' => 200100],
            ['ModuleID' => 200120, 'Name' => 'Leads', 'Icon' => null, 'Description' => '', 'Route' => 'leads.index', 'ParentID' => 200100],
            ['ModuleID' => 200130, 'Name' => 'Board', 'Icon' => null, 'Description' => '', 'Route' => 'board.index', 'ParentID' => 200100],

            ['ModuleID' => 201000, 'Name' => 'Mailbox', 'Icon' => '<i class="fas fa-envelope"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'email-conversations.index'],

            ['ModuleID' => 202000, 'Name' => 'Marketing Planner', 'Icon' => '<i class=" fa-solid fa-seedling"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'marketing-planner.index'],
            ['ModuleID' => 203000, 'Name' => 'Marketing Lists', 'Icon' => '<i class="fas fa-list-dots"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'marketing-list.index'],
            ['ModuleID' => 204000, 'Name' => 'Campaigns', 'Icon' => '<i class="fas fa-copyright"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'campaigns.index'],
            ['ModuleID' => 205000, 'Name' => 'Competitors', 'Icon' => '<i class="fas fa-face-rolling-eyes"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'competitors.index'],
            ['ModuleID' => 206000, 'Name' => 'Social Media', 'Icon' => '<i class="fa-solid fa-icons"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'socials.index'],

            ['ModuleID' => 207000, 'Name' => 'Feedback', 'Icon' => null, 'Description' => '', 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 207100, 'Name' => 'Surveys', 'Icon' => '<i class="fa-regular fa-circle-check"></i>', 'Description' => '', 'ParentID' => 207000, 'Route' => 'surveys.index'],
            ['ModuleID' => 207200, 'Name' => 'Reviews', 'Icon' => '<i class="fa-regular fa-comment"></i>', 'Description' => '', 'ParentID' => 207000, 'Route' => 'reviews.index'],

            ['ModuleID' => 208000, 'Name' => 'Product Development', 'Icon' => '<i class="fa-solid fa-cubes"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'product-development.index'],

            ['ModuleID' => 209000, 'Name' => 'Debt Collection', 'Icon' => null, 'Description' => '', 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 209100, 'Name' => 'Notifications', 'Icon' => '<i class="fas fa-comment-dollar"></i>', 'Description' => '', 'ParentID' => 209000, 'Route' => 'debt-notification.index'],
            ['ModuleID' => 209200, 'Name' => 'Loans', 'Icon' => '<i class="fas fa-hands-helping"></i>', 'Description' => '', 'ParentID' => 209000, 'Route' => null],
            ['ModuleID' => 209210, 'Name' => 'Lists', 'Icon' => null, 'Description' => '', 'ParentID' => 209200, 'Route' => 'loans-list.index'],
            ['ModuleID' => 209220, 'Name' => 'Loans', 'Icon' => null, 'Description' => '', 'ParentID' => 209200, 'Route' => 'debt-collection.index'],

            ['ModuleID' => 299000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'Description' => '', 'ParentID' => 200000, 'Route' => 'crm-reports.index'],
        ]);


        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _procurement(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 300000, 'Name' => ModulesEnum::Procurement->description(), 'Icon' => '<i class="fas fa-layer-group"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 301000, 'Name' => 'Procurement Plan', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 301100, 'Name' => 'Department Needs', 'Icon' => null, 'Description' => '', 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301110, 'Name' => 'Raise Needs', 'Icon' => null, 'Description' => '', 'ParentID' => 301100, 'Route' => 'procurementdepartmentalplan.index'],
            ['ModuleID' => 301120, 'Name' => 'Approve Needs', 'Icon' => null, 'Description' => '', 'ParentID' => 301100, 'Route' => 'department-need-approval.index'],
            ['ModuleID' => 301200, 'Name' => 'Plan Consolidation', 'Icon' => null, 'Description' => '', 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301210, 'Name' => 'Consolidated Needs', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'consolidated.index'],
            ['ModuleID' => 301220, 'Name' => 'Plan Management', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'procurementplanmaintain.index'],
            ['ModuleID' => 301250, 'Name' => 'Set Method', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'procurement-set-method.index'],
            ['ModuleID' => 301270, 'Name' => 'Submit Plan', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'Procurement-Plan-Submission.index'],
            ['ModuleID' => 301280, 'Name' => 'Approve Plan', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'procurementplanapproval.index'],
            ['ModuleID' => 301260, 'Name' => 'Schedule Plan', 'Icon' => null, 'Description' => '', 'ParentID' => 301200, 'Route' => 'Procurement-Plan-Schedule.index'],
            ['ModuleID' => 302000, 'Name' => 'Purchase Requisition', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 302100, 'Name' => 'Requisition List', 'Icon' => null, 'Description' => '', 'ParentID' => 302000, 'Route' => 'requisition.create'],
            ['ModuleID' => 302300, 'Name' => 'Priority List', 'Icon' => null, 'Description' => '', 'ParentID' => 302000, 'Route' => 'requisitionItem.index'],
            ['ModuleID' => 303000, 'Name' => 'Suppliers', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 303100, 'Name' => 'Suppliers List', 'Icon' => null, 'Description' => '', 'ParentID' => 303000, 'Route' => 'suppliers.index'],
            ['ModuleID' => 303200, 'Name' => 'Prequalification', 'Icon' => null, 'Description' => '', 'ParentID' => 303000, 'Route' => null],
            ['ModuleID' => 303210, 'Name' => 'Prequalification Periods', 'Icon' => null, 'Description' => '', 'ParentID' => 303200, 'Route' => 'preqrounds.index'],
            ['ModuleID' => 303220, 'Name' => 'Prequalification Criteria', 'Icon' => null, 'Description' => '', 'ParentID' => 303200, 'Route' => 'preqcriteria.index'],
            ['ModuleID' => 303230, 'Name' => 'Supplier Applications', 'Icon' => null, 'Description' => '', 'ParentID' => 303200, 'Route' => 'preqapplications.index'],
            ['ModuleID' => 303240, 'Name' => 'Evaluation & Approval', 'Icon' => null, 'Description' => '', 'ParentID' => 303200, 'Route' => 'preqevaluation.index'],
            ['ModuleID' => 303250, 'Name' => 'Prequalified Suppliers', 'Icon' => null, 'Description' => '', 'ParentID' => 303200, 'Route' => 'preqsuppliers.index'],
            ['ModuleID' => 305000, 'Name' => 'Tendering', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],

            // Tender Setup
            ['ModuleID' => 305100, 'Name' => 'Tender Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305110, 'Name' => 'Tender Initiation', 'Icon' => null, 'Description' => '', 'ParentID' => 305100, 'Route' => 'initiatetender.index'],
            //['ModuleID' => 305120, 'Name' => 'Initiation Approval', 'Icon' => null, 'Description' => '', 'ParentID' => 305100, 'Route' => 'initiateapprove.index'],
            ['ModuleID' => 305130, 'Name' => 'Tender Category', 'Icon' => null, 'Description' => '', 'ParentID' => 305100, 'Route' => 'tendercategory.index'],
            ['ModuleID' => 305140, 'Name' => 'Tender Type', 'Icon' => null, 'Description' => '', 'ParentID' => 305100, 'Route' => 'tendertype.index'],
            ['ModuleID' => 305160, 'Name' => 'Tender Criteria Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 305100, 'Route' => 'tenderevaluations.index'],

            // Suppliers
            ['ModuleID' => 305200, 'Name' => 'Suppliers', 'Icon' => null, 'Description' => '', 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305210, 'Name' => 'Response Tracking', 'Icon' => null, 'Description' => '', 'ParentID' => 305200, 'Route' => 'tenderresponse.index'],
            ['ModuleID' => 305220, 'Name' => 'Clarifications', 'Icon' => null, 'Description' => '', 'ParentID' => 305200, 'Route' => 'tenderclarification.index'],
            ['ModuleID' => 305230, 'Name' => 'Submission', 'Icon' => null, 'Description' => '', 'ParentID' => 305200, 'Route' => 'tendersubmission.index'],

            // Opening
            ['ModuleID' => 305300, 'Name' => 'Opening', 'Icon' => null, 'Description' => '', 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305310, 'Name' => 'Opening', 'Icon' => null, 'Description' => '', 'ParentID' => 305300, 'Route' => 'tenderopening.index'],

            ['ModuleID' => 305350, 'Name' => 'Responsiveness Check', 'Icon' => null, 'Description' => '', 'ParentID' => 305000, 'Route' => 'bidresponsiveness.index'],

            // Evaluation
            ['ModuleID' => 305400, 'Name' => 'Evaluation', 'Icon' => null, 'Description' => '', 'ParentID' => 305000, 'Route' => null],
            //['ModuleID' => 305410, 'Name' => 'Appoint Committee', 'Icon' => null, 'Description' => '', 'ParentID' => 305400, 'Route' => 'tendercommittee.index'],
            //['ModuleID' => 305430, 'Name' => 'Assign Roles', 'Icon' => null, 'Description' => '', 'ParentID' => 305400, 'Route' => 'assignrole.index'],
            ['ModuleID' => 305440, 'Name' => 'Evaluators Dashboard', 'Icon' => null, 'Description' => '', 'ParentID' => 305400, 'Route' => 'evaluationdashboard.index'],
            ['ModuleID' => 305450, 'Name' => 'Consolidated Scores', 'Icon' => null, 'Description' => '', 'ParentID' => 305400, 'Route' => 'bidscores.index'],

            ['ModuleID' => 306000, 'Name' => 'Quotations', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 306100, 'Name' => 'View Quotations', 'Icon' => null, 'Description' => '', 'ParentID' => 306000, 'Route' => 'rfqs.index'],
            ['ModuleID' => 306200, 'Name' => 'Quotation Responses', 'Icon' => null, 'Description' => '', 'ParentID' => 306000, 'Route' => 'rfqresponses.index'],
            ['ModuleID' => 306400, 'Name' => 'Quotation Criteria Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 306000, 'Route' => 'rfqcriteriasetup.evaluations'],
            ['ModuleID' => 306300, 'Name' => 'Quotation Evaluation', 'Icon' => null, 'Description' => '', 'ParentID' => 306000, 'Route' => 'evaluations.index'],

            //Awards
            ['ModuleID' => 306500, 'Name' => 'Awards', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => 'procawards.index'],

            ['ModuleID' => 307000, 'Name' => 'Purchase Order', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 307100, 'Name' => 'View Purchase Orders', 'Icon' => null, 'Description' => '', 'ParentID' => 307000, 'Route' => 'purchaseOrder.index'],
           // ['ModuleID' => 307200, 'Name' => 'Create Purchase Orders', 'Icon' => null, 'Description' => '', 'ParentID' => 307000, 'Route' => 'purchaseOrder.create'],
           // ['ModuleID' => 307300, 'Name' => 'Link RFQ to Purchase Order', 'Icon' => null, 'Description' => '', 'ParentID' => 307000, 'Route' => 'purchaseOrder.linkRFQ'],
    //            ['ModuleID' => 307400, 'Name' => 'Purchase Order Approval', 'Icon' => null,'Description' => '', 'ParentID' => 307000, 'Route' => 'purchaseOrder.approval'],

            ['ModuleID' => 308000, 'Name' => 'Good Receipt', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 308100, 'Name' => 'View Good Receipt', 'Icon' => null, 'Description' => '', 'ParentID' => 308000, 'Route' => 'procurementreceipts.index'],
            ['ModuleID' => 308200, 'Name' => 'Create Good Receipt', 'Icon' => null, 'Description' => '', 'ParentID' => 308000, 'Route' => 'procurementreceipts.create'],

            ['ModuleID' => 398000, 'Name' => 'Settings', 'Icon' => null, 'Description' => '', 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 398100, 'Name' => 'Criteria Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'sections.index'],
            ['ModuleID' => 398400, 'Name' => 'RFQ Criteria Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'rfqsettingsections.index'],
            ['ModuleID' => 398200, 'Name' => 'Methods Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'procurement-modes.index'],
            ['ModuleID' => 398300, 'Name' => 'Approval Setup', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'approval-setup.index'],
            ['ModuleID' => 398500, 'Name' => 'Appoint Committee', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'tendercommittee.index'],
            ['ModuleID' => 398600, 'Name' => 'Member Response', 'Icon' => null, 'Description' => '', 'ParentID' => 398000, 'Route' => 'memberresponse.index'],
            ['ModuleID' => 399000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'Description' => '', 'ParentID' => 300000, 'Route' => 'procurement-reports.index'],

        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _inventory(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 400000, 'Name' => ModulesEnum::Inventory->description(), 'Icon' => '<i data-feather="archive"></i>', 'Description' => 'Inventory Management Module', 'Route' => null, 'ParentID' => null],
            ['ModuleID' => 401000, 'Name' => 'Item Master', 'Icon' => null, 'Description' => 'Item Master Management', 'Route' => null, 'ParentID' => 400000],

            ['ModuleID' => 401100, 'Name' => 'Item Master List', 'Icon' => null, 'Description' => 'Item Master List', 'Route' => 'itemmaster.index', 'ParentID' => 401000],
            ['ModuleID' => 401200, 'Name' => 'Stock Item', 'Icon' => null, 'Description' => 'Stock Item Management', 'Route' => 'sku.index', 'ParentID' => 401000],
            ['ModuleID' => 402000, 'Name' => 'InterBranch Requisition', 'Icon' => null, 'Description' => 'InterBranch Requisition Management', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 402100, 'Name' => 'New Requisition', 'Icon' => null, 'Description' => 'Create New Requisition', 'Route' => 'interbranchrequisition.index', 'ParentID' => 402000],
            ['ModuleID' => 402200, 'Name' => 'Requisition Approval', 'Icon' => null, 'Description' => 'Requisition Approval', 'Route' => 'interbranchrequisitionapproval.index', 'ParentID' => 402000],
            //['ModuleID' => 403000, 'Name' => 'Inventory Dashboard', 'Icon' => null, 'Description' => 'Inventory Dashboard', 'Route' => null, 'ParentID' => 400000],
            //['ModuleID' => 403100, 'Name' => 'By Branch or Store List', 'Icon' => null, 'Description' => 'Inventory Dashboard By Branch or Store', 'Route' => 'inventorydashboard.index', 'ParentID' => 403000],
            // ['ModuleID' => 403200, 'Name' => 'Movement Dashboard', 'Icon' => null, 'Description' => 'Movement Dashboard', 'Route' => 'movementdashboard.index', 'ParentID' => 403000],
            ['ModuleID' => 404000, 'Name' => 'Transactions', 'Icon' => null, 'Description' => 'Inventory Transactions', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 404100, 'Name' => 'Stock Issue', 'Icon' => null, 'Description' => 'Stock Issue', 'Route' => 'stockissue.index', 'ParentID' => 404000],
            ['ModuleID' => 404200, 'Name' => 'Stock Receipts', 'Icon' => null, 'Description' => 'Transaction Receipts', 'Route' => 'transactionsreceipts.index', 'ParentID' => 404000],
            ['ModuleID' => 404300, 'Name' => 'Stock Transfers', 'Icon' => null, 'Description' => 'Stock Transfers', 'Route' => 'transactionstransfers.index', 'ParentID' => 404000],
            ['ModuleID' => 404400, 'Name' => 'Stock Adjustments', 'Icon' => null, 'Description' => 'Stock Adjustments', 'Route' => 'transactionsadjustment.index', 'ParentID' => 404000],
            ['ModuleID' => 404500, 'Name' => 'Transactions Approval', 'Icon' => null, 'Description' => 'Transaction Approvals', 'Route' => 'transactionsapproval.index', 'ParentID' => 404000],
            ['ModuleID' => 405000, 'Name' => 'Stock Management', 'Icon' => null, 'Description' => 'Stock Management', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 405100, 'Name' => 'Stock Take', 'Icon' => null, 'Description' => 'Stock Take Management', 'Route' => 'stocktake.index', 'ParentID' => 405000],
            ['ModuleID' => 407000, 'Name' => 'Price Management', 'Icon' => null, 'Description' => 'Items Prices', 'Route' => 'pricemanagement.index', 'ParentID' => 400000],

            ['ModuleID' => 498000, 'Name' => 'Settings', 'Icon' => null, 'Description' => 'Inventory Settings', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 498100, 'Name' => 'Item Master Settings', 'Icon' => null, 'Description' => 'Item Master Settings', 'Route' => null, 'ParentID' => 498000],
            ['ModuleID' => 498101, 'Name' => 'Item Category', 'Icon' => null, 'Description' => 'Item Category Management', 'Route' => 'itemcategory.index', 'ParentID' => 498100],
            ['ModuleID' => 498102, 'Name' => 'Item Type', 'Icon' => null, 'Description' => 'Item Type Management', 'Route' => 'itemtype.index', 'ParentID' => 498100],
            ['ModuleID' => 498103, 'Name' => 'Inventory Type', 'Icon' => null, 'Description' => 'Inventory Type Management', 'Route' => 'inventorytype.index', 'ParentID' => 498100],
            ['ModuleID' => 498104, 'Name' => 'Unit Of Measure', 'Icon' => null, 'Description' => 'Unit of Measure Management', 'Route' => 'unitofmeasure.index', 'ParentID' => 498100],
            ['ModuleID' => 498105, 'Name' => 'Conversion Mapping', 'Icon' => null, 'Description' => 'UOM Conversion Mapping', 'Route' => 'uomconversion.index', 'ParentID' => 498100],
            ['ModuleID' => 498001, 'Name' => 'Stores', 'Icon' => null, 'Description' => 'List of Stores', 'Route' => 'stores.index', 'ParentID' => 498000],
            ['ModuleID' => 498200, 'Name' => 'Load Opening Stock', 'Icon' => null, 'Description' => 'Load Opening Stock', 'Route' => 'openingstock.create', 'ParentID' => 498000],

            ['ModuleID' => 499000, 'Name' => 'Reports', 'Icon' => null, 'Description' => 'Inventory Reports', 'Route' => 'inventory-reports.index', 'ParentID' => 400000],




        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _budgetline(bool $fresh): Collection
    {
        $values = collect([
            // Root Module
            ['ModuleID' => 1200000, 'Name' => ModulesEnum::BudgetLine->description(), 'Icon' => '<i data-feather="bar-chart-2"></i>', 'Description' => 'Budget Line Module', 'Route' => null, 'ParentID' => null],

            // Setup & Structure
            ['ModuleID' => 1201000, 'Name' => 'Setup & Structure', 'Icon' => null, 'Description' => 'Setup Budget Line', 'Route' => null, 'ParentID' => 1200000],
            //['ModuleID' => 1201100, 'Name' => 'New Budget', 'Icon' => null, 'Description' => 'New Budget with Period definition', 'Route' => 'budgetperiod.index', 'ParentID' => 1201000],
            //['ModuleID' => 1201200, 'Name' => 'Scenario Planning', 'Icon' => null, 'Description' => 'Scenario Planning Budget', 'Route' => 'budgetscenerios.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201300, 'Name' => 'CBS Product View', 'Icon' => null, 'Description' => 'Product Master Management', 'Route' => 'budgetproductmaster.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201400, 'Name' => 'CBS GL View', 'Icon' => null, 'Description' => 'GL Mapping Setup', 'Route' => 'budgetglmapping.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201410, 'Name' => 'Budget Line Categories', 'Icon' => null, 'Description' => 'Budget Lines Categories', 'Route' => 'budgetlinecategories.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201415, 'Name' => 'Budget Lines', 'Icon' => null, 'Description' => 'Budget Lines Management', 'Route' => 'budgetlinemapping.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201420, 'Name' => 'Activity Master', 'Icon' => null, 'Description' => 'Budget Activity Master', 'Route' => 'activitymaster.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201425, 'Name' => 'Rates', 'Icon' => null, 'Description' => 'Budget Rates', 'Route' => 'rates.index', 'ParentID' => 1201000],
            //['ModuleID' => 1201700, 'Name' => 'Drivers Master', 'Icon' => null, 'Description' => 'Driver Setup', 'Route' => 'budgetdrivers.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201800, 'Name' => 'Products Rates', 'Icon' => null, 'Description' => 'Products Rates Setup', 'Route' => 'yieldexpenserate.index', 'ParentID' => 1201000],
            //moved this to workspace ['ModuleID' => 1201900, 'Name' => 'Activities Master', 'Icon' => null, 'Description' => 'Budget Activity Setup', 'Route' => 'budgetactivities.index', 'ParentID' => 1201000],

            // Budgeting Workspace
            ['ModuleID' => 1202000, 'Name' => 'Budgeting Workspace', 'Icon' => null, 'Description' => 'Workspace for Budgeting Activities', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1202040, 'Name' => 'New Budget', 'Icon' => null, 'Description' => 'New Budget with Period definition', 'Route' => 'budgetperiod.index', 'ParentID' => 1202000],
            //['ModuleID' => 1202100, 'Name' => 'Driver Projections', 'Icon' => null, 'Description' => 'Driver Projections', 'Route' => 'budgetprojections.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202050, 'Name' => 'Budget Activities', 'Icon' => null, 'Description' => 'Budget Activity Setup', 'Route' => 'budgetactivities.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202200, 'Name' => 'Budget Projections', 'Icon' => null, 'Description' => 'Budget Projections', 'Route' => 'budgetprojections.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202300, 'Name' => 'Entry By Lines', 'Icon' => null, 'Description' => 'Entry by GL Lines', 'Route' => 'entrybyglline.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202400, 'Name' => 'Submit For Approval', 'Icon' => null, 'Description' => 'Submit Budget for Approval', 'Route' => 'submitapproval.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202500, 'Name' => 'Approve Branch Budgets', 'Icon' => null, 'Description' => 'Branch Budget Approval', 'Route' => 'budgetapproval.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202600, 'Name' => 'Top-Down Budget', 'Icon' => null, 'Description' => 'Top-Down Allocation Tool', 'Route' => 'topdownallocation.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202700, 'Name' => 'Budget Consolidation', 'Icon' => null, 'Description' => 'Consolidate Budgets', 'Route' => 'budgetconsolidation.index', 'ParentID' => 1202000],

            // Monitoring & Execution
            ['ModuleID' => 1203000, 'Name' => 'Monitoring & Execution', 'Icon' => null, 'Description' => 'Monitor & Execute Budget', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1203100, 'Name' => 'Plan vs Actual Monitoring', 'Icon' => null, 'Description' => 'Plan vs Actual Dashboard', 'Route' => 'budgetvsactualdashboard.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203200, 'Name' => 'Variance Analysis', 'Icon' => null, 'Description' => 'Analyse Variances', 'Route' => 'budgetvarianceanalysis.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203300, 'Name' => 'KPI Scorecards', 'Icon' => null, 'Description' => 'KPI Dashboard', 'Route' => 'kpiscorecards.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203400, 'Name' => 'Deviation Alerts', 'Icon' => null, 'Description' => 'Monitor Deviations', 'Route' => 'submitapproval.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203500, 'Name' => 'Completion Rate View', 'Icon' => null, 'Description' => 'View Completion Rates', 'Route' => 'submitapproval.index', 'ParentID' => 1203000],

            // Business Intelligence & Deep Analytics
            ['ModuleID' => 1204000, 'Name' => 'Business Intelligence & Deep Analytics', 'Icon' => null, 'Description' => 'Performance, Risk, and Compliance Insights', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1204100, 'Name' => 'BI & Analytics Dashboard', 'Icon' => null, 'Description' => 'Analytics Dashboard', 'Route' => 'analyticsdashboard.index', 'ParentID' => 1204000],

            // Admin & Integration
            ['ModuleID' => 1205000, 'Name' => 'Admin & Integration', 'Icon' => null, 'Description' => 'System Controls and CBS Integration', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1205100, 'Name' => 'CBS Data Sync', 'Icon' => null, 'Description' => 'CBS Product Auto Sync', 'Route' => 'cbssync.index', 'ParentID' => 1205000],
            ['ModuleID' => 1205200, 'Name' => 'Data Sync Logs', 'Icon' => null, 'Description' => 'CBS & System Sync Logs', 'Route' => 'datasynclogs.index', 'ParentID' => 1205000],
             //Settings
           // ['ModuleID' => 1206000, 'Name' => 'Settings', 'Icon' => null, 'Description' => 'Budget Settings', 'Route' => null, 'ParentID' => 1200000],
            //['ModuleID' => 1206040, 'Name' => 'Budget Line Categories', 'Icon' => null, 'Description' => 'Budget Lines Categories', 'Route' => 'budgetlinecategories.index', 'ParentID' => 1206000],
            //['ModuleID' => 1206050, 'Name' => 'Activity Master', 'Icon' => null, 'Description' => 'Budget Activity Master', 'Route' => 'activitymaster.index', 'ParentID' => 1206000],
            //['ModuleID' => 1206100, 'Name' => 'Rates', 'Icon' => null, 'Description' => 'Budget Rates', 'Route' => 'rates.index', 'ParentID' => 1206000],
           // ['ModuleID' => 1206200, 'Name' => 'Perod Types', 'Icon' => null, 'Description' => 'Budget Period Types', 'Route' => 'periodtypes.index', 'ParentID' => 1206000],
            //['ModuleID' => 1206300, 'Name' => 'Planning Methods', 'Icon' => null, 'Description' => 'Budget Planning Methods', 'Route' => 'planningmethods.index', 'ParentID' => 1206000],
            //['ModuleID' => 1206400, 'Name' => 'Budget Drivers', 'Icon' => null, 'Description' => 'Budet Drivers', 'Route' => 'budgetdriverssetup.index', 'ParentID' => 1206000],
        ]);

        if ($fresh) {
            return $values;
        }

        $data = collect();
        foreach ($values as $value) {
            if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                $data->add($value);
            }
        }
        return $data;
    }



    protected function _propertyManagement(bool $fresh): Collection
    {
        $values = collect([
            // Main module
            ['ModuleID' => 500000, 'Name' => ModulesEnum::Property->description(), 'Icon' => '<i data-feather="home"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            // First level - Property Registry
            ['ModuleID' => 501000, 'Name' => 'Property Registry', 'Icon' => '<i class="fas fa-building"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 501100, 'Name' => 'Add Property', 'Icon' => null, 'Description' => '', 'ParentID' => 501000, 'Route' => 'PropertyRegistry.index'],
            ['ModuleID' => 501200, 'Name' => 'Property Type', 'Icon' => null, 'Description' => '', 'ParentID' => 501000, 'Route' => 'propertytype.index'],
            ['ModuleID' => 501300, 'Name' => 'Property Category', 'Icon' => null, 'Description' => '', 'ParentID' => 501000, 'Route' => 'propertycategory.index'],
            ['ModuleID' => 501400, 'Name' => 'Property Attachments', 'Icon' => null, 'Description' => '', 'ParentID' => 501000, 'Route' => 'attachments.index'],

            // Structural Mapping (under Property Registry)
            ['ModuleID' => 501500, 'Name' => 'Structural Mapping', 'Icon' => '<i class="fas fa-sitemap"></i>', 'Description' => '', 'ParentID' => 501000, 'Route' => null],
            ['ModuleID' => 501510, 'Name' => 'Add Block', 'Icon' => null, 'Description' => '', 'ParentID' => 501500, 'Route' => 'addblock.index'],
            ['ModuleID' => 501520, 'Name' => 'Add Floor', 'Icon' => null, 'Description' => '', 'ParentID' => 501500, 'Route' => 'addfloor.index'],
            ['ModuleID' => 501530, 'Name' => 'Add Unit', 'Icon' => null, 'Description' => '', 'ParentID' => 501500, 'Route' => 'addunit.index'],

            // First level - Tenant & Lease
            ['ModuleID' => 502000, 'Name' => 'Tenant & Lease', 'Icon' => '<i class="fas fa-users"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 502100, 'Name' => 'Tenant Maintenance', 'Icon' => null, 'Description' => '', 'ParentID' => 502000, 'Route' => 'addtenant.index'],
            ['ModuleID' => 502200, 'Name' => 'Tenant Clearance', 'Icon' => null, 'Description' => '', 'ParentID' => 502000, 'Route' => 'tenantclearance.index'],

            // Lease Management (under Tenant & Lease)
            ['ModuleID' => 502300, 'Name' => 'Lease Management', 'Icon' => '<i class="fas fa-file-contract"></i>', 'Description' => '', 'ParentID' => 502000, 'Route' => null],
            ['ModuleID' => 502310, 'Name' => 'Lease Maintenance', 'Icon' => null, 'Description' => '', 'ParentID' => 502300, 'Route' => 'addlease.index'],
            ['ModuleID' => 502320, 'Name' => 'Lease Schedule', 'Icon' => null, 'Description' => '', 'ParentID' => 502300, 'Route' => 'schedulelease.index'],
            ['ModuleID' => 502330, 'Name' => 'Lease Renewal', 'Icon' => null, 'Description' => '', 'ParentID' => 502300, 'Route' => 'renewlease.index'],
            ['ModuleID' => 502340, 'Name' => 'Lease Termination', 'Icon' => null, 'Description' => '', 'ParentID' => 502300, 'Route' => 'terminatelease.index'],
            //['ModuleID' => 502350, 'Name' => 'Payment Frequency', 'Icon' => null, 'Description' => '', 'ParentID' => 502300, 'Route' => 'paymentfrequency.index'],

            // First level - Billing & Receipting
            ['ModuleID' => 503000, 'Name' => 'Billing & Receipting', 'Icon' => '<i class="fas fa-file-invoice-dollar"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 503100, 'Name' => 'Invoicing', 'Icon' => null, 'Description' => '', 'ParentID' => 503000, 'Route' => 'rentinvoice.index'],
            ['ModuleID' => 503200, 'Name' => 'Receipting', 'Icon' => null, 'Description' => '', 'ParentID' => 503000, 'Route' => 'rentreceipt.index'],
            ['ModuleID' => 503300, 'Name' => 'Tenant Ledger', 'Icon' => null, 'Description' => '', 'ParentID' => 503000, 'Route' => 'tenantledger.index'],
            ['ModuleID' => 503400, 'Name' => 'Rent Dashboard', 'Icon' => null, 'Description' => '', 'ParentID' => 503000, 'Route' => 'rentdashboard.index'],

            // First level - Maintenance & Issues
            ['ModuleID' => 504000, 'Name' => 'Maintenance & Issues', 'Icon' => '<i class="fas fa-tools"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 504100, 'Name' => 'Maintenance request', 'Icon' => null, 'Description' => '', 'ParentID' => 504000, 'Route' => 'maintenancerequest.index'],
            ['ModuleID' => 504200, 'Name' => 'Assign', 'Icon' => null, 'Description' => '', 'ParentID' => 504000, 'Route' => 'assignrequest.index'],
            ['ModuleID' => 504300, 'Name' => 'Dashboard', 'Icon' => null, 'Description' => '', 'ParentID' => 504000, 'Route' => 'maintenancedashboard.index'],
            ['ModuleID' => 504400, 'Name' => 'Work Completion', 'Icon' => null, 'Description' => '', 'ParentID' => 504000, 'Route' => 'workcompletion.index'],

            // First level - Property Management Settings
            ['ModuleID' => 505000, 'Name' => 'Settings', 'Icon' => '<i class="fas fa-cog"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 505100, 'Name' => 'Property Settings', 'Icon' => null, 'Description' => '', 'ParentID' => 505000, 'Route' => 'propertysettings.index'],

            // First level - Reports
            ['ModuleID' => 599000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-chart-bar"></i>', 'Description' => '', 'ParentID' => 500000, 'Route' => 'propertyreports.index'],
            /* ['ModuleID' => 50510, 'Name' => 'Reports', 'Icon' => null, 'Description' => '', 'ParentID' => 50500, 'Route' =>],
             ['ModuleID' => 50520, 'Name' => 'Analytics', 'Icon' => null, 'Description' => '', 'ParentID' => 50500, 'Route' => 'propertyanalytics.index'],*/
        ]);
        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _fleetManagement(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 600000, 'Name' => ModulesEnum::Fleet->description(), 'Icon' => '<i data-feather="truck"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 601000, 'Name' => 'Driver Management', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'drivermanagement.index'],
            ['ModuleID' => 602000, 'Name' => 'Vehicle Registry', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'vehicle-registry.index'],
            ['ModuleID' => 603000, 'Name' => 'Trip Management', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'tripmanagement.index'],
            ['ModuleID' => 604000, 'Name' => 'Fuel Management', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'fuelmanagement.index'],
            ['ModuleID' => 605000, 'Name' => 'Compliance', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'complianceanddocumentation.index'],
            ['ModuleID' => 606000, 'Name' => 'Fleet Disposal', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'fleetprocurementanddisposal.index'],
            ['ModuleID' => 607000, 'Name' => 'Inventory Of Spare Parts', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'inventoryofspareparts.index'],
            ['ModuleID' => 608000, 'Name' => 'Utilization & Costing', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'utilization.index'],
            ['ModuleID' => 609000, 'Name' => 'Service Tracking', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'servicetracking.index'],
            ['ModuleID' => 610000, 'Name' => 'GPS Integration', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'gps.index'],

            ['ModuleID' => 699000, 'Name' => 'Reports', 'Icon' => null, 'Description' => '', 'ParentID' => 600000, 'Route' => 'reports.index'],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _documentManagement(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 700000, 'Name' => ModulesEnum::DMS->description(), 'Icon' => '<i data-feather="file-text"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 701000, 'Name' => 'Recent Documents', 'Icon' => null, 'Description' => '', 'ParentID' => 700000, 'Route' => 'repo.recent'],
            ['ModuleID' => 702000, 'Name' => 'Repository', 'Icon' => null, 'Description' => '', 'ParentID' => 700000, 'Route' => 'repo.index'],
            ['ModuleID' => 703000, 'Name' => 'Tags', 'Icon' => null, 'Description' => '', 'ParentID' => 700000, 'Route' => 'file-tags.index'],
            ['ModuleID' => 704000, 'Name' => 'Bulk Upload', 'Icon' => null, 'Description' => '', 'ParentID' => 700000, 'Route' => 'files.upload'],

            ['ModuleID' => 799000, 'Name' => 'Reports', 'Icon' => null, 'Description' => '', 'ParentID' => 700000, 'Route' => 'dms-reports.index'],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _legal(bool $fresh): Collection
    {
        $values = collect([
            // Main module - Legal (800000)
            ['ModuleID' => 800000, 'Name' => ModulesEnum::Legal->description(), 'Icon' => '<i data-feather="briefcase"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            // First level - Case Management (801000)
            ['ModuleID' => 801000, 'Name' => 'Case Management', 'Icon' => '<i class="fas fa-gavel"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 801100, 'Name' => 'Case Register', 'Icon' => null, 'Description' => '', 'ParentID' => 801000, 'Route' => 'caseregister.index'],
            ['ModuleID' => 801200, 'Name' => 'Case details', 'Icon' => null, 'Description' => '', 'ParentID' => 801000, 'Route' => 'casedetails.index'],
            ['ModuleID' => 801300, 'Name' => 'Case Hearing', 'Icon' => null, 'Description' => '', 'ParentID' => 801000, 'Route' => 'hearing.index'],
            ['ModuleID' => 801400, 'Name' => 'Case Documents', 'Icon' => null, 'Description' => '', 'ParentID' => 801000, 'Route' => 'casedocuments.index'],
            ['ModuleID' => 801500, 'Name' => 'Case Notes', 'Icon' => null, 'Description' => '', 'ParentID' => 801000, 'Route' => 'casenotes.index'],

            // First level - Contract Management (802000)
            ['ModuleID' => 802000, 'Name' => 'Contract Management', 'Icon' => '<i class="fas fa-file-contract"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 802100, 'Name' => 'Contract Approval', 'Icon' => null, 'Description' => '', 'ParentID' => 802000, 'Route' => 'contractapproval.index'],
            ['ModuleID' => 802200, 'Name' => 'Contract Drafting', 'Icon' => null, 'Description' => '', 'ParentID' => 802000, 'Route' => 'contractdrafting.index'],
            ['ModuleID' => 802300, 'Name' => 'Contract Repository', 'Icon' => null, 'Description' => '', 'ParentID' => 802000, 'Route' => 'contractrepository.index'],
            ['ModuleID' => 802400, 'Name' => 'Obligation Tracker', 'Icon' => null, 'Description' => '', 'ParentID' => 802000, 'Route' => 'obligationtracker.index'],
            ['ModuleID' => 802500, 'Name' => 'Expiry Alerts & Renewals', 'Icon' => null, 'Description' => '', 'ParentID' => 802000, 'Route' => 'renewals.index'],

            // First level - Compliance Management (803000)
            ['ModuleID' => 803000, 'Name' => 'Compliance Management', 'Icon' => '<i class="fas fa-clipboard-check"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 803100, 'Name' => 'Regulatory Checklist', 'Icon' => null, 'Description' => '', 'ParentID' => 803000, 'Route' => 'regulatorychecklist.index'],
            ['ModuleID' => 803200, 'Name' => 'Compliance calendar', 'Icon' => null, 'Description' => '', 'ParentID' => 803000, 'Route' => 'compliancecalendar.index'],
            ['ModuleID' => 803300, 'Name' => 'Filing Tracker', 'Icon' => null, 'Description' => '', 'ParentID' => 803000, 'Route' => 'fillingtracker.index'],
            ['ModuleID' => 803400, 'Name' => 'Non-compliance Register', 'Icon' => null, 'Description' => '', 'ParentID' => 803000, 'Route' => 'noncomplianceregister.index'],

            // First level - Legal Dashboard & Reports (804000)
            ['ModuleID' => 804000, 'Name' => 'Legal Dashboard & Reports', 'Icon' => '<i class="fas fa-chart-line"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 804100, 'Name' => 'Open vs Closed Cases Summary', 'Icon' => null, 'Description' => '', 'ParentID' => 804000, 'Route' => 'casessummary.index'],
            ['ModuleID' => 804200, 'Name' => 'Legal Expenses by Case or Department', 'Icon' => null, 'Description' => '', 'ParentID' => 804000, 'Route' => 'legalexpenses.index'],
            ['ModuleID' => 804300, 'Name' => 'Upcoming Hearings Calendar', 'Icon' => null, 'Description' => '', 'ParentID' => 804000, 'Route' => 'hearings.index'],

            // First level - Intellectual Property (805000)
            ['ModuleID' => 805000, 'Name' => 'Intellectual Property', 'Icon' => '<i class="fas fa-lightbulb"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 805100, 'Name' => 'License Agreement', 'Icon' => null, 'Description' => '', 'ParentID' => 805000, 'Route' => 'copyrightlicenseagreement.index'],
            ['ModuleID' => 805200, 'Name' => 'Patent Tracking', 'Icon' => null, 'Description' => '', 'ParentID' => 805000, 'Route' => 'patenttracking.index'],
            ['ModuleID' => 805300, 'Name' => 'Trademark Register', 'Icon' => null, 'Description' => '', 'ParentID' => 805000, 'Route' => 'trademarkregister.index'],

            // First level - Legal Notices (806000)
            ['ModuleID' => 806000, 'Name' => 'Legal Notices', 'Icon' => '<i class="fas fa-exclamation-circle"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 806100, 'Name' => 'Correspondance', 'Icon' => null, 'Description' => '', 'ParentID' => 806000, 'Route' => 'archive.index'],
            ['ModuleID' => 806200, 'Name' => 'Notice Register', 'Icon' => null, 'Description' => '', 'ParentID' => 806000, 'Route' => 'register.index'],
            ['ModuleID' => 806300, 'Name' => 'Response Tracker', 'Icon' => null, 'Description' => '', 'ParentID' => 806000, 'Route' => 'responsetracker.index'],

            // First level - Lawyer Management (807000)
            ['ModuleID' => 807000, 'Name' => 'Lawyer Management', 'Icon' => '<i class="fas fa-user-tie"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 807100, 'Name' => 'External Directory', 'Icon' => null, 'Description' => '', 'ParentID' => 807000, 'Route' => 'externaldirectory.index'],
            ['ModuleID' => 807200, 'Name' => 'Fee Tracker', 'Icon' => null, 'Description' => '', 'ParentID' => 807000, 'Route' => 'feetracker.index'],
            ['ModuleID' => 807300, 'Name' => 'Performance Log', 'Icon' => null, 'Description' => '', 'ParentID' => 807000, 'Route' => 'performancelog.index'],


            ['ModuleID' => 899000, 'Name' => 'Legal Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'Description' => '', 'ParentID' => 800000, 'Route' => 'compliancestatus.index'],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _insurance(bool $fresh): Collection
    {
        $values = collect([
            // Main module - Insurance
            ['ModuleID' => 900000, 'Name' => ModulesEnum::Insurance->description(), 'Icon' => '<i data-feather="shield"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            // First level children
            // ['ModuleID' => 901000, 'Name' => 'Provider Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'providermanagement.index'],
            // ['ModuleID' => 902000, 'Name' => 'Insurance Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'insurancetypemanagement.index'],
            // ['ModuleID' => 903000, 'Name' => 'Policy Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'insurancepolicymanagement.index'],
            // ['ModuleID' => 904000, 'Name' => 'Asset Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'coveredassetmanagement.index'],
            // ['ModuleID' => 905000, 'Name' => 'Premium Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'premiumpaymentmanagement.index'],
            // ['ModuleID' => 906000, 'Name' => 'Claims Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'claimsmanagement.index'],
            // ['ModuleID' => 907000, 'Name' => 'Renewal Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'renewalmanagement.index'],
            // ['ModuleID' => 908000, 'Name' => 'Report Management', 'Icon' => null, 'Description' => '', 'ParentID' => 900000, 'Route' => 'reportmanagement.index'],
            //['ModuleID' => 900000, 'Name' => 'Insurance', 'Icon' => '<i data-feather="shield"></i>', 'Description' => '', 'Route' => null, 'ParentID' => null],
            ['ModuleID' => 901000, 'Name' => 'Bank Staff Workspace', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 901100, 'Name' => 'Referrals & Tracking', 'Icon' => null, 'Description' => '', 'Route' => 'bancassurance.referrals.index', 'ParentID' => 901000],
            ['ModuleID' => 901200, 'Name' => 'Referral Assignment', 'Icon' => 'fas fa-user-check', 'Description' => '', 'Route' => 'bancassurance.referrals.assign.list', 'ParentID' => 901000],
            ['ModuleID' => 901300, 'Name' => 'Referral Performance', 'Icon' => 'fas fa-chart-bar', 'Description' => 'View referral counts and conversion rates', 'Route' => 'bancassurance.referrals.performance', 'ParentID' => 901000],
            ['ModuleID' => 901400, 'Name' => 'Commission Statement View', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 901000],
            ['ModuleID' => 902000, 'Name' => 'Customers', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 902100, 'Name' => 'Customer Profile & KYC', 'Icon' => 'fas fa-user-plus', 'Description' => 'Create customer profile with KYC', 'Route' => 'bancassurance.customers.check', 'ParentID' => 902000],
            ['ModuleID' => 902300, 'Name' => 'Customer Listing', 'Icon' => 'fas fa-user-friends', 'Description' => 'Add or edit customer beneficiaries', 'Route' => 'bancassurance.customers.index', 'ParentID' => 902000],
            ['ModuleID' => 902400, 'Name' => 'Customer Communications', 'Icon' => 'fas fa-comments', 'Description' => 'Track calls, emails, visits and SMS logs', 'Route' =>'bancassurance.customers.communication.index', 'ParentID' => 902000],
            ['ModuleID' => 903000, 'Name' => 'Policy Management', 'Icon' => null, 'Description' => null, 'Route' => 'bancassurance.policies.create', 'ParentID' => 900000],
            ['ModuleID' => 903100, 'Name' => 'Policy Proposals', 'Icon' => null, 'Description' => 'List and manage policy proposals', 'Route' => 'bancassurance.policies.index', 'ParentID' => 903000],
            ['ModuleID' => 903200, 'Name' => 'Proposal Review', 'Icon' => null, 'Description' => 'Review policy proposals and make underwriting decisions', 'Route' => 'bancassurance.policies.reviewIndex', 'ParentID' => 903000],
            ['ModuleID' => 903300, 'Name' => 'Underwriting Feedback', 'Icon' => null, 'Description' => 'Capture and track underwriter decisions and comments for submitted proposals', 'Route' => 'bancassurance.policies.feedback.list', 'ParentID' => 903000],
            ['ModuleID' => 903400, 'Name' => 'Policy Issuance', 'Icon' => null, 'Description' => 'List of policies approved for issuance', 'Route' => 'bancassurance.policies.issuance.list', 'ParentID' => 903000],
            ['ModuleID' => 903500, 'Name' => 'Policy Register', 'Icon' => null, 'Description' => 'View list of all issued policies', 'Route' => 'bancassurance.policies.register', 'ParentID' => 903000],
            ['ModuleID' => 903600, 'Name' => 'Renewal Management', 'Icon' => null, 'Description' => 'View and manage renewable policies', 'Route' => 'bancassurance.policies.renewals.index', 'ParentID' => 903000],
            ['ModuleID' => 904000, 'Name' => 'Premium Management', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 904100, 'Name' => 'Premium Payments', 'Icon' => null, 'Description' => 'View all premium payments made by customers', 'Route' => 'bancassurance.premiums.index', 'ParentID' => 904000],
            ['ModuleID' => 905000, 'Name' => 'Claims Management', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 905100, 'Name' => 'Claims Register', 'Icon' => null, 'Description' => 'View all submitted claims', 'Route' => 'bancassurance.claims.index', 'ParentID' => 905000],
            ['ModuleID' => 905200, 'Name' => 'Claims Assessment', 'Icon' => null, 'Description' => 'Assess and decide on initiated claims', 'Route' => 'bancassurance.claims.index', 'ParentID' => 905000],
            ['ModuleID' => 905300, 'Name' => 'Claims Approval', 'Icon' => null, 'Description' => 'List claims awaiting approval', 'Route' => 'bancassurance.claims.approvalQueue', 'ParentID' => 905000],
            ['ModuleID' => 905400, 'Name' => 'Claim Payments', 'Icon' => null, 'Description' => 'View and track settled claim payments', 'Route' => 'bancassurance.claims.payments.index', 'ParentID' => 905000],
            ['ModuleID' => 905500, 'Name' => 'Closed Claims', 'Icon' => null, 'Description' => 'View Closed Claims', 'Route' => 'bancassurance.claims.closed', 'ParentID' => 905000],
            ['ModuleID' => 906000, 'Name' => 'Commissions', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 906100, 'Name' => 'Commission Rules', 'Icon' => null, 'Description' => 'View commissions', 'Route' => 'commissions.rules.index', 'ParentID' => 906000],
            ['ModuleID' => 906200, 'Name' => 'Commission Earned', 'Icon' => null, 'Description' => 'View commissions earned', 'Route' => 'bancassurance.commissions.earned.index', 'ParentID' => 906000],
            ['ModuleID' => 906300, 'Name' => 'Commission PaidOut', 'Icon' => null, 'Description' => 'View commissions Paid', 'Route' => 'bancassurance.commissions.payouts.index', 'ParentID' => 906000],
            ['ModuleID' => 907000, 'Name' => 'Providers & Products', 'Icon' => null, 'Description' => '', 'Route' => null, 'ParentID' => 900000],
            ['ModuleID' => 907100, 'Name' => 'Insurance Providers', 'Icon' => null, 'Description' => 'View commissions', 'Route' => 'bancassurance.insurers.index', 'ParentID' => 907000],
            ['ModuleID' => 907200, 'Name' => 'Insurance Products', 'Icon' => null, 'Description' => 'View commissions', 'Route' => 'bancassurance.products.index', 'ParentID' => 907000],
            ['ModuleID' => 907300, 'Name' => 'Mapped Products', 'Icon' => null, 'Description' => 'Map products to providers', 'Route' => 'bancassurance.products.mapped.index', 'ParentID' => 907000],
            ['ModuleID' => 907400, 'Name' => 'Riders & Add-ons', 'Icon' => null, 'Description' => 'Riders and Addons', 'Route' => 'bancassurance.riders.index', 'ParentID' => 907000],
            ['ModuleID' => 907500, 'Name' => 'Pricing Rules', 'Icon' => null, 'Description' => 'Define and manage premium pricing rules for mapped provider products', 'Route' => 'bancassurance.pricing.index', 'ParentID' => 907000],
            ['ModuleID' => 907600, 'Name' => 'Product Lifecycle', 'Icon' => null, 'Description' => '', 'Route' => 'bancassurance.lifecycle.index', 'ParentID' => 907000],
            ['ModuleID' => 908000, 'Name' => 'Settings & Access', 'Icon' => null, 'Description' => 'Bancassurance Settings', 'Route' => 'bancassurance.settings.index', 'ParentID' => 900000],
            ['ModuleID' => 999000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 900000],



            //['ModuleID' => 999000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'Description' => '', 'ParentID' => 900000, 'Route' => null],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    private function _seed(Collection $modules): void
    {
        $actor = SystemHelper::user();
        $dated = now()->toDateTimeString();

        // Seed parent modules
        $modules = $modules->map(function ($module) use ($actor, $dated) {
            $module['CreatedOn'] = $dated;
            $module['ModifiedOn'] = $dated;
            $module['CreatedBy'] = $actor->Id;
            $module['ModifiedBy'] = $actor->Id;
            return $module;
        });

        // dd($modules->toArray());
        DB::table('t_Modules')->insert($modules->toArray());
    }

    protected function _finance(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 1100000, 'Name' => ModulesEnum::Finance->description(), 'Icon' => '<i data-feather="dollar-sign"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 1100100, 'Name' => 'Chart Of Accounts', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1100105, 'Name' => 'Segment Config', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'segments.index', 'ParentID' => 1100100],
            ['ModuleID' => 1100110, 'Name' => 'Chart Of Accounts', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'chartofaccounts.index', 'ParentID' => 1100100],
            //['ModuleID' => 1100120, 'Name' => 'Hierarchy Viewer', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'hierachyviewer.index', 'ParentID' => 1100100],


            ['ModuleID' => 1100400, 'Name' => 'General Ledger', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1100410, 'Name' => 'Journal Entry', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'journalentry.index', 'ParentID' => 1100400],
            ['ModuleID' => 1100420, 'Name' => 'Recurrent Journals', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'recurrentjournal.index', 'ParentID' => 1100400],
            ['ModuleID' => 1100430, 'Name' => 'Reversing Journals', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'reversingjournal.index', 'ParentID' => 1100400],
            ['ModuleID' => 1100440, 'Name' => 'GL Reporting', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100400],
            ['ModuleID' => 1100441, 'Name' => 'Trial Balance', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'trialbalance.index', 'ParentID' => 1100440],
            ['ModuleID' => 1100442, 'Name' => 'GL Report', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'ledgerreporting.index', 'ParentID' => 1100440],
            ['ModuleID' => 1100443, 'Name' => 'Balance Sheet', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'balancesheet.index', 'ParentID' => 1100440],
            ['ModuleID' => 1100444, 'Name' => 'Income Statement', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => 'incomestatement.index', 'ParentID' => 1100440],

            ['ModuleID' => 1101000, 'Name' => 'Accounts Payable', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1101100, 'Name' => 'Vendor Master', 'Icon' => null, 'Description' => '', 'Route' => 'vendormaster.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101200, 'Name' => 'Invoice Entry', 'Icon' => null, 'Description' => '', 'Route' => 'invoiceentry.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101250, 'Name' => 'Invoice Approval', 'Icon' => null, 'Description' => '', 'Route' => 'invoiceapproval.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101300, 'Name' => 'Credit/Debit Note', 'Icon' => null, 'Description' => '', 'Route' => 'creditnote.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101400, 'Name' => 'Payment Processing', 'Icon' => null, 'Description' => '', 'Route' => 'paymentprocessing.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101500, 'Name' => 'Payment Voucher', 'Icon' => null, 'Description' => '', 'Route' => 'paymentvoucher.index', 'ParentID' => 1101000],
            ['ModuleID' => 1101600, 'Name' => 'Aging Report', 'Icon' => null, 'Description' => '', 'Route' => 'agingreport.index', 'ParentID' => 1101000],
            ['ModuleID' => 1102000, 'Name' => 'Accounts Receivable', 'Icon' => '<i class="fas fa-money-check-alt"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1102100, 'Name' => 'Customer Master', 'Icon' => null, 'Description' => '', 'Route' => 'customermaster.index', 'ParentID' => 1102000],
            ['ModuleID' => 1102200, 'Name' => 'Invoice Generation', 'Icon' => null, 'Description' => '', 'Route' => 'invoicegeneration.index', 'ParentID' => 1102000],
            ['ModuleID' => 1102300, 'Name' => 'Receipts Posting', 'Icon' => null, 'Description' => '', 'Route' => 'receiptsposting.index', 'ParentID' => 1102000],
            ['ModuleID' => 1102400, 'Name' => 'Credit Management', 'Icon' => null, 'Description' => '', 'Route' => 'creditmanagement.index', 'ParentID' => 1102000],
            ['ModuleID' => 1102500, 'Name' => 'Aging Report', 'Icon' => null, 'Description' => '', 'Route' => 'agingreportar.index', 'ParentID' => 1102000],
            ['ModuleID' => 1102600, 'Name' => 'Customer Statement', 'Icon' => null, 'Description' => '', 'Route' => 'customerstatement.index', 'ParentID' => 1102000],


            ['ModuleID' => 1104000, 'Name' => 'Tax Management', 'Icon' => '<i class="fas fa-exchange-alt"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1104040, 'Name' => 'Jurisdiction Setup', 'Icon' => null, 'Description' => '', 'Route' => 'taxjurisdiction.index', 'ParentID' => 1104000],
            ['ModuleID' => 1104050, 'Name' => 'Tax Types', 'Icon' => null, 'Description' => '', 'Route' => 'taxtypes.index', 'ParentID' => 1104000],
            ['ModuleID' => 1104100, 'Name' => 'Tax Rule Configuration', 'Icon' => null, 'Description' => '', 'Route' => 'taxruleconfig.index', 'ParentID' => 1104000],
            ['ModuleID' => 1104300, 'Name' => 'Tax Summary Report', 'Icon' => null, 'Description' => '', 'Route' => 'taxsummaryreport.index', 'ParentID' => 1104000],
            ['ModuleID' => 1104400, 'Name' => 'Tax Return Generator', 'Icon' => null, 'Description' => '', 'Route' => 'taxreturngenerator.index', 'ParentID' => 1104000],
            ['ModuleID' => 1104500, 'Name' => 'e-Filing Integration Panel', 'Icon' => null, 'Description' => '', 'Route' => 'efiling.index', 'ParentID' => 1104000],


            ['ModuleID' => 1106000, 'Name' => 'Bank Reconciliation', 'Icon' => '<i class="fas fa-check-double"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1106100, 'Name' => 'Statement Upload', 'Icon' => null, 'Description' => '', 'Route' => 'reconuploads.index', 'ParentID' => 1106000],
            ['ModuleID' => 1106200, 'Name' => 'ReconDashboard', 'Icon' => null, 'Description' => '', 'Route' => 'recondashboard.index', 'ParentID' => 1106000],


            ['ModuleID' => 1107000, 'Name' => 'Period Management', 'Icon' => '<i class="fas fa-calendar-alt"></i>', 'Description' => '', 'Route' => 'periodmanagement.index', 'ParentID' => 1100000],


            ['ModuleID' => 1108000, 'Name' => 'Bank Management', 'Icon' => '<i class="fas fa-university"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1108100, 'Name' => 'Account Setup', 'Icon' => null, 'Description' => '', 'Route' => 'bankaccountsetup.index', 'ParentID' => 1108000],
            ['ModuleID' => 1108200, 'Name' => 'Cash Book', 'Icon' => null, 'Description' => '', 'Route' => 'cashbook.index', 'ParentID' => 1108000],
            ['ModuleID' => 1108300, 'Name' => 'Cash Management', 'Icon' => null, 'Description' => '', 'Route' => 'cashmanagement.index', 'ParentID' => 1108000],
            ['ModuleID' => 1108400, 'Name' => 'Cheque Management', 'Icon' => null, 'Description' => '', 'Route' => 'chequemanagement.index', 'ParentID' => 1108000],
            ['ModuleID' => 1108500, 'Name' => 'Vouchers', 'Icon' => null, 'Description' => '', 'Route' => 'paymentandreceiptvouchers.index', 'ParentID' => 1108000],

            ['ModuleID' => 1109000, 'Name' => 'Finance Settings', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1100000],
            ['ModuleID' => 1109100, 'Name' => 'Transaction Types', 'Icon' => '<i class="fas fa-exchange-alt"></i>', 'Description' => '', 'Route' => 'transactiontypes.index', 'ParentID' => 1109000],
            ['ModuleID' => 1109200, 'Name' => 'GL Posting Map', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => 'glpostingmap.index', 'ParentID' => 1109000],
            ['ModuleID' => 1109300, 'Name' => 'PO to Invoice Sync Setup', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => 'integration.po_invoice_sync.index', 'ParentID' => 1109000],
            ['ModuleID' => 1109400, 'Name' => 'Salary Journal Template Setup', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => 'salary-journal-templates.index', 'ParentID' => 1109000],
            ['ModuleID' => 1109500, 'Name' => 'CBS GL Mapping', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1109000],
            ['ModuleID' => 1109600, 'Name' => 'CBS Sync Log Viewer', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => null, 'ParentID' => 1109000],


            ['ModuleID' => 1199000, 'Name' => 'Financial Reports', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'Route' => 'balancesheet.index', 'ParentID' => 1100000],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _hrm(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 1000000, 'Name' => ModulesEnum::HRM->description(), 'Icon' => '<i data-feather="users"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 1001000, 'Name' => 'Employees', 'Icon' => '<i class="fas fa-user-tie"></i>', 'Description' => '', 'ParentID' => 1000000, 'Route' => null],
            ['ModuleID' => 1001100, 'Name' => 'New Employee', 'Icon' => null, 'Description' => '', 'ParentID' => 1001000, 'Route' => 'employees.create'],
            ['ModuleID' => 1001200, 'Name' => 'Employees List', 'Icon' => null, 'Description' => '', 'ParentID' => 1001000, 'Route' => 'employees.index'],

            ['ModuleID' => 1002000, 'Name' => 'Departments', 'Icon' => '<i class="fas fa-building"></i>', 'Description' => '', 'ParentID' => 1000000, 'Route' => 'departments.index'],
            ['ModuleID' => 1003000, 'Name' => 'Committees', 'Icon' => '<i class="fas fa-building"></i>', 'Description' => '', 'ParentID' => 1000000, 'Route' => 'tendercommittee.index'],
            ['ModuleID' => 1099000, 'Name' => 'Financial Reports', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'Description' => '', 'ParentID' => 1000000, 'Route' => null],
        ]);
        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }


    protected function _settings(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 9800000, 'Name' => ModulesEnum::Settings->description(), 'Icon' => '<i class="fas fa-cogs"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 9800100, 'Name' => 'Users', 'Icon' => '<i class="fas fa-user"></i>', 'Description' => '', 'ParentID' => 9800000, 'Route' => 'users.index'],
            ['ModuleID' => 9800200, 'Name' => 'Roles', 'Icon' => '<i class="fas fa-user-tag"></i>', 'Description' => '', 'ParentID' => 9800000, 'Route' => 'roles.index'],
            ['ModuleID' => 9800300, 'Name' => 'Branches', 'Icon' => '<i class="fas fa-code-branch"></i>', 'Description' => '', 'ParentID' => 9800000, 'Route' => 'branches.index'],
            ['ModuleID' => 98004000, 'Name' => 'Code Details', 'Icon' => null, 'Description' => '', 'ParentID' => 9800000, 'Route' => 'settings.lists'],
            ['ModuleID' => 98005000, 'Name' => 'Integrations', 'Icon' => null, 'Description' => '', 'ParentID' => 9800000, 'Route' => 'settings.integrations'],
        ]);
        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }

    protected function _myAccount(bool $fresh): Collection
    {
        $values = collect([
            ['ModuleID' => 9900000, 'Name' => ModulesEnum::MyAccount->description(), 'Icon' => '<i class="fas fa-user"></i>', 'Description' => '', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 9900100, 'Name' => 'Schedule', 'Icon' => '<i class=" fas fa-calendar"></i>', 'Description' => '', 'ParentID' => 9900000, 'Route' => 'schedule.index'],
            ['ModuleID' => 9900200, 'Name' => 'Tickets', 'Icon' => '<i data-feather="check-square"></i>', 'Description' => '', 'ParentID' => 9900000, 'Route' => 'tickets.index'],
        ]);

        if ($fresh) {
            $data = $values;
        } else {
            $data = collect();
            foreach ($values as $value) {
                if (!DB::table('t_Modules')->where('ModuleID', $value['ModuleID'])->exists()) {
                    $data->add($value);
                }
            }
        }
        return $data;
    }
}
