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
            ['ModuleID' => 102000, 'Name' => 'Clients', 'Icon' => null, 'Description' => '', 'Route' => 'clients.index', 'ParentID' => 100000],
            ['ModuleID' => 103000, 'Name' => 'Leads', 'Icon' => null, 'Description' => '', 'Route' => 'leads.index', 'ParentID' => 100000],
            ['ModuleID' => 109000, 'Name' => 'Board', 'Icon' => null, 'Description' => '', 'Route' => 'board.index', 'ParentID' => 100000],
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
            ['ModuleID' => 200000, 'Name' => ModulesEnum::CRM->description(), 'Icon' => '<i data-feather="share-2"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 201000, 'Name' => 'Mailbox', 'Icon' => '<i class="fas fa-envelope"></i>', 'ParentID' => 200000, 'Route' => 'email-conversations.index'],

            ['ModuleID' => 202000, 'Name' => 'Marketing Planner', 'Icon' => '<i class=" fa-solid fa-seedling"></i>', 'ParentID' => 200000, 'Route' => 'marketing-planner.index'],
            ['ModuleID' => 203000, 'Name' => 'Marketing Lists', 'Icon' => '<i class="fas fa-list-dots"></i>', 'ParentID' => 200000, 'Route' => 'marketing-list.index'],
            ['ModuleID' => 204000, 'Name' => 'Campaigns', 'Icon' => '<i class="fas fa-copyright"></i>', 'ParentID' => 200000, 'Route' => 'campaigns.index'],
            ['ModuleID' => 205000, 'Name' => 'Competitors', 'Icon' => '<i class="fas fa-face-rolling-eyes"></i>', 'ParentID' => 200000, 'Route' => 'competitors.index'],
            ['ModuleID' => 206000, 'Name' => 'Social Media', 'Icon' => '<i class="fa-solid fa-icons"></i>', 'ParentID' => 200000, 'Route' => 'socials.index'],

            ['ModuleID' => 207000, 'Name' => 'Feedback', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 207100, 'Name' => 'Surveys', 'Icon' => '<i class="fa-regular fa-circle-check"></i>', 'ParentID' => 207000, 'Route' => 'surveys.index'],
            ['ModuleID' => 207200, 'Name' => 'Reviews', 'Icon' => '<i class="fa-regular fa-comment"></i>', 'ParentID' => 207000, 'Route' => 'reviews.index'],

            ['ModuleID' => 208000, 'Name' => 'Product Development', 'Icon' => '<i class="fa-solid fa-cubes"></i>', 'ParentID' => 200000, 'Route' => 'product-development.index'],

            ['ModuleID' => 209000, 'Name' => 'Debt Collection', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 209100, 'Name' => 'Notifications', 'Icon' => '<i class="fas fa-comment-dollar"></i>', 'ParentID' => 209000, 'Route' => 'debt-notification.index'],
            ['ModuleID' => 209200, 'Name' => 'Loans', 'Icon' => '<i class="fas fa-hands-helping"></i>', 'ParentID' => 209000, 'Route' => null],
            ['ModuleID' => 209210, 'Name' => 'Lists', 'Icon' => null, 'ParentID' => 209200, 'Route' => 'loans-list.index'],
            ['ModuleID' => 209220, 'Name' => 'Loans', 'Icon' => null, 'ParentID' => 209200, 'Route' => 'debt-collection.index'],

            ['ModuleID' => 299000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'ParentID' => 200000, 'Route' => 'crm-reports.index'],
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
            ['ModuleID' => 300000, 'Name' => ModulesEnum::Procurement->description(), 'Icon' => '<i class="fas fa-layer-group"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 301000, 'Name' => 'Procurement Plan', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 301100, 'Name' => 'Department Needs', 'Icon' => null, 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301110, 'Name' => 'Raise Needs', 'Icon' => null, 'ParentID' => 301100, 'Route' => 'procurementdepartmentalplan.index'],
            ['ModuleID' => 301120, 'Name' => 'Approve Needs', 'Icon' => null, 'ParentID' => 301100, 'Route' => 'department-need-approval.index'],
            ['ModuleID' => 301200, 'Name' => 'Plan Consolidation', 'Icon' => null, 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301210, 'Name' => 'Consolidated Needs', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'consolidated.index'],
            ['ModuleID' => 301220, 'Name' => 'New Plan', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'procurementplanmaintain.index'],
            ['ModuleID' => 301230, 'Name' => 'Amend Plan', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'ammendplan.index'],
            ['ModuleID' => 301240, 'Name' => 'Link Budget Lines', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'maptobudget.index'],
            ['ModuleID' => 301250, 'Name' => 'Set Method', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'procurement-set-method.index'],
            ['ModuleID' => 301270, 'Name' => 'Submit Plan', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'Procurement-Plan-Submission.index'],
            ['ModuleID' => 301280, 'Name' => 'Approve Plan', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'procurementplanapproval.index'],
            ['ModuleID' => 301260, 'Name' => 'Schedule Plan', 'Icon' => null, 'ParentID' => 301200, 'Route' => 'Procurement-Plan-Schedule.index'],
            ['ModuleID' => 301300, 'Name' => 'Dashboard', 'Icon' => null, 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301320, 'Name' => 'Timeline', 'Icon' => null, 'ParentID' => 301300, 'Route' => 'plantimeline.index'],
            ['ModuleID' => 301330, 'Name' => 'Calender Based', 'Icon' => null, 'ParentID' => 301300, 'Route' => 'calenderbased.index'],
            ['ModuleID' => 301340, 'Name' => 'Flagged Items', 'Icon' => null, 'ParentID' => 301300, 'Route' => 'delayeditems.index'],
            ['ModuleID' => 301350, 'Name' => 'Plan vs Actual', 'Icon' => null, 'ParentID' => 301300, 'Route' => 'planvsactual.index'],
            ['ModuleID' => 301400, 'Name' => 'Approval', 'Icon' => null, 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301410, 'Name' => 'Submit For Approval', 'Icon' => null, 'ParentID' => 301400, 'Route' => 'submitplan.index'],
            ['ModuleID' => 301420, 'Name' => 'Approval Inbox', 'Icon' => null, 'ParentID' => 301400, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 301430, 'Name' => 'Approve Plan', 'Icon' => null, 'ParentID' => 301400, 'Route' => 'procurementplanapproval.index'],
            ['ModuleID' => 301500, 'Name' => 'Plan Execution', 'Icon' => null, 'ParentID' => 301000, 'Route' => null],
            ['ModuleID' => 301510, 'Name' => 'Execution Dashboard', 'Icon' => null, 'ParentID' => 301500, 'Route' => 'submitplan.index'],
            ['ModuleID' => 301520, 'Name' => 'Pending Execution', 'Icon' => null, 'ParentID' => 301500, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 301530, 'Name' => 'Execution Calendar', 'Icon' => null, 'ParentID' => 301500, 'Route' => 'submitplan.index'],
            ['ModuleID' => 301540, 'Name' => 'Deviations', 'Icon' => null, 'ParentID' => 301500, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 302000, 'Name' => 'Purchase Requisition', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 302100, 'Name' => 'Requisition List', 'Icon' => null, 'ParentID' => 302000, 'Route' => 'requisition.create'],
            ['ModuleID' => 302200, 'Name' => 'Requisition Approval', 'Icon' => null, 'ParentID' => 302000, 'Route' => 'requisition.index'],
            ['ModuleID' => 302300, 'Name' => 'Priority List', 'Icon' => null, 'ParentID' => 302000, 'Route' => 'requisitionItem.index'],
            ['ModuleID' => 303000, 'Name' => 'Suppliers', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 303100, 'Name' => 'Suppliers List', 'Icon' => null, 'ParentID' => 303000, 'Route' => 'suppliers.index'],
            ['ModuleID' => 304000, 'Name' => 'Procurement Modes', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 304100, 'Name' => 'List Modes', 'Icon' => null, 'ParentID' => 304000, 'Route' => 'procurement-modes.index'],
            ['ModuleID' => 304200, 'Name' => 'Add Mode', 'Icon' => null, 'ParentID' => 304000, 'Route' => 'procurement-modes.create'],
            ['ModuleID' => 305000, 'Name' => 'Tendering', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],

            // Tender Setup
            ['ModuleID' => 305100, 'Name' => 'Tender Setup', 'Icon' => null, 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305110, 'Name' => 'Tender Initiation', 'Icon' => null, 'ParentID' => 305100, 'Route' => 'initiatetender.index'],
            ['ModuleID' => 305120, 'Name' => 'Initiation Approval', 'Icon' => null, 'ParentID' => 305100, 'Route' => 'initiateapprove.index'],
            ['ModuleID' => 305130, 'Name' => 'Tender Category', 'Icon' => null, 'ParentID' => 305100, 'Route' => 'tendercategory.index'],
            ['ModuleID' => 305140, 'Name' => 'Tender Type', 'Icon' => null, 'ParentID' => 305100, 'Route' => 'tendertype.index'],
            ['ModuleID' => 305160, 'Name' => 'Tender Criteria Setup', 'Icon' => null, 'ParentID' => 305100, 'Route' => 'tenderevaluations.index'],

            // Suppliers
            ['ModuleID' => 305200, 'Name' => 'Suppliers', 'Icon' => null, 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305210, 'Name' => 'Response Tracking', 'Icon' => null, 'ParentID' => 305200, 'Route' => 'tenderresponse.index'],
            ['ModuleID' => 305220, 'Name' => 'Clarifications', 'Icon' => null, 'ParentID' => 305200, 'Route' => 'tenderclarification.index'],
            ['ModuleID' => 305230, 'Name' => 'Submission', 'Icon' => null, 'ParentID' => 305200, 'Route' => 'tendersubmission.index'],

            // Opening
            ['ModuleID' => 305300, 'Name' => 'Opening', 'Icon' => null, 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305310, 'Name' => 'Opening', 'Icon' => null, 'ParentID' => 305300, 'Route' => 'tenderopening.index'],

            // Evaluation
            ['ModuleID' => 305400, 'Name' => 'Evaluation', 'Icon' => null, 'ParentID' => 305000, 'Route' => null],
            ['ModuleID' => 305410, 'Name' => 'Appoint Committee', 'Icon' => null, 'ParentID' => 305400, 'Route' => 'tendercommittee.index'],
            ['ModuleID' => 305420, 'Name' => 'Member Response', 'Icon' => null, 'ParentID' => 305400, 'Route' => 'memberresponse.index'],
            ['ModuleID' => 305430, 'Name' => 'Assign Roles', 'Icon' => null, 'ParentID' => 305400, 'Route' => 'assignrole.index'],
            ['ModuleID' => 305440, 'Name' => 'Evaluators Dashboard', 'Icon' => null, 'ParentID' => 305400, 'Route' => 'evaluationdashboard.index'],
            ['ModuleID' => 305450, 'Name' => 'Consolidated Scores', 'Icon' => null, 'ParentID' => 305400, 'Route' => 'bidscores.index'],

            ['ModuleID' => 306000, 'Name' => 'Quotations', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 306100, 'Name' => 'View Quotations', 'Icon' => null, 'ParentID' => 306000, 'Route' => 'rfqs.index'],
            ['ModuleID' => 306200, 'Name' => 'Quotation Responses', 'Icon' => null, 'ParentID' => 306000, 'Route' => 'rfqresponses.index'],
            ['ModuleID' => 306300, 'Name' => 'Quotation Evaluation', 'Icon' => null, 'ParentID' => 306000, 'Route' => 'evaluations.index'],

            ['ModuleID' => 307000, 'Name' => 'Purchase Order', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 307100, 'Name' => 'View Purchase Orders', 'Icon' => null, 'ParentID' => 307000, 'Route' => 'purchaseOrder.index'],
            ['ModuleID' => 307200, 'Name' => 'Create Purchase Orders', 'Icon' => null, 'ParentID' => 307000, 'Route' => 'purchaseOrder.create'],
            ['ModuleID' => 307300, 'Name' => 'Link RFQ to Purchase Order', 'Icon' => null, 'ParentID' => 307000, 'Route' => 'purchaseOrder.linkRFQ'],

            ['ModuleID' => 308000, 'Name' => 'Good Receipt', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 308100, 'Name' => 'View Good Receipt', 'Icon' => null, 'ParentID' => 308000, 'Route' => 'procurementreceipts.index'],
            ['ModuleID' => 308200, 'Name' => 'Create Good Receipt', 'Icon' => null, 'ParentID' => 308000, 'Route' => 'procurementreceipts.create'],

            ['ModuleID' => 398000, 'Name' => 'Settings', 'Icon' => null, 'ParentID' => 300000, 'Route' => null],
            ['ModuleID' => 398100, 'Name' => 'Criteria Setup', 'Icon' => null, 'ParentID' => 398000, 'Route' => 'sections.index'],

            ['ModuleID' => 399000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'ParentID' => 300000, 'Route' => 'procurement-reports.index'],
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
                       ['ModuleID' => 401300, 'Name' => 'Item Category', 'Icon' => null, 'Description' => 'Item Category Management', 'Route' => 'itemcategory.index', 'ParentID' => 401000],
                       ['ModuleID' => 401700, 'Name' => 'Item Type', 'Icon' => null, 'Description' => 'Item Type Management', 'Route' => 'itemtype.index', 'ParentID' => 401000],
                       ['ModuleID' => 401500, 'Name' => 'Inventory Type', 'Icon' => null, 'Description' => 'Inventory Type Management', 'Route' => 'inventorytype.index', 'ParentID' => 401000],
                       ['ModuleID' => 401600, 'Name' => 'Unit Of Measure', 'Icon' => null, 'Description' => 'Unit of Measure Management', 'Route' => 'unitofmeasure.index', 'ParentID' => 401000],
            ['ModuleID' => 402000, 'Name' => 'InterBranch Requisition', 'Icon' => null, 'Description' => 'InterBranch Requisition Management', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 402100, 'Name' => 'New Requisition', 'Icon' => null, 'Description' => 'Create New Requisition', 'Route' => 'interbranchrequisition.index', 'ParentID' => 402000],
            ['ModuleID' => 402200, 'Name' => 'Requisition Approval', 'Icon' => null, 'Description' => 'Requisition Approval', 'Route' => 'interbranchrequisitionapproval.index', 'ParentID' => 402000],
            ['ModuleID' => 403000, 'Name' => 'Inventory Dashboard', 'Icon' => null, 'Description' => 'Inventory Dashboard', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 403100, 'Name' => 'By Branch or Store List', 'Icon' => null, 'Description' => 'Inventory Dashboard By Branch or Store', 'Route' => 'inventorydashboard.index', 'ParentID' => 403000],
            ['ModuleID' => 403200, 'Name' => 'Movement Dashboard', 'Icon' => null, 'Description' => 'Movement Dashboard', 'Route' => 'movementdashboard.index', 'ParentID' => 403000],
            ['ModuleID' => 404000, 'Name' => 'Transactions', 'Icon' => null, 'Description' => 'Inventory Transactions', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 404100, 'Name' => 'Receipts', 'Icon' => null, 'Description' => 'Transaction Receipts', 'Route' => 'transactionsreceipts.index', 'ParentID' => 404000],
            ['ModuleID' => 404200, 'Name' => 'Transfers', 'Icon' => null, 'Description' => 'Transaction Transfers', 'Route' => 'transactionstransfers.index', 'ParentID' => 404000],
            ['ModuleID' => 404300, 'Name' => 'Adjustments', 'Icon' => null, 'Description' => 'Transaction Adjustments', 'Route' => 'transactionsadjustment.index', 'ParentID' => 404000],
            ['ModuleID' => 405000, 'Name' => 'Stock Management', 'Icon' => null, 'Description' => 'Stock Management', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 405100, 'Name' => 'Stock Take', 'Icon' => null, 'Description' => 'Stock Take Management', 'Route' => 'stocktake.index', 'ParentID' => 405000],
            ['ModuleID' => 405200, 'Name' => 'Load Opening Stock', 'Icon' => null, 'Description' => 'Load Opening Stock', 'Route' => 'openingstock.index', 'ParentID' => 405000],
            ['ModuleID' => 405300, 'Name' => 'Location Tracking', 'Icon' => null, 'Description' => 'Location Tracking', 'Route' => 'bintracking.index', 'ParentID' => 405000],
            ['ModuleID' => 405400, 'Name' => 'Stock Valuation', 'Icon' => null, 'Description' => 'Stock Valuation History', 'Route' => 'stockvaluationhistory.index', 'ParentID' => 405000],
            ['ModuleID' => 405500, 'Name' => 'Expiry Batch Tracking', 'Icon' => null, 'Description' => 'Expiry Batch Tracking', 'Route' => 'expirytracking.index', 'ParentID' => 405000],
            ['ModuleID' => 406000, 'Name' => 'Conversion Mapping', 'Icon' => null, 'Description' => 'Conversion Mapping Management', 'Route' => null, 'ParentID' => 400000],
            ['ModuleID' => 406100, 'Name' => 'Conversion Mapping', 'Icon' => null, 'Description' => 'UOM Conversion Mapping', 'Route' => 'uomconversion.index', 'ParentID' => 406000],
            ['ModuleID' => 408000, 'Name' => 'Stores', 'Icon' => null, 'Description' => 'List of Stores', 'Route' => 'stores.index', 'ParentID' => 400000],

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
            ['ModuleID' => 1201100, 'Name' => 'Budget Period', 'Icon' => null, 'Description' => 'Budget Line Period', 'Route' => 'budgetperiod.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201200, 'Name' => 'Scenario Planning', 'Icon' => null, 'Description' => 'Scenario Planning Budget', 'Route' => 'budgetscenerios.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201300, 'Name' => 'Product Master', 'Icon' => null, 'Description' => 'Product Master Management', 'Route' => 'budgetproductmaster.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201400, 'Name' => 'Item Sub Category', 'Icon' => null, 'Description' => 'Item Sub Category Management', 'Route' => 'itemsubcategory.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201500, 'Name' => 'Budget Lines', 'Icon' => null, 'Description' => 'Budget Lines Management', 'Route' => 'budgetlinemapping.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201600, 'Name' => 'GL Mapping View', 'Icon' => null, 'Description' => 'GL Mapping Setup', 'Route' => 'budgetglmapping.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201700, 'Name' => 'Drivers Master', 'Icon' => null, 'Description' => 'Driver Setup', 'Route' => 'budgetdrivers.index', 'ParentID' => 1201000],
            ['ModuleID' => 1201800, 'Name' => 'Formula Setup', 'Icon' => null, 'Description' => 'Budget Formula Setup', 'Route' => 'budgetformula.index', 'ParentID' => 1201000],

            // Budgeting Workspace
            ['ModuleID' => 1202000, 'Name' => 'Budgeting Workspace', 'Icon' => null, 'Description' => 'Workspace for Budgeting Activities', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1202100, 'Name' => 'Entry Productwise', 'Icon' => null, 'Description' => 'Entry by Product', 'Route' => 'entrybyproduct.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202200, 'Name' => 'Entry By Lines', 'Icon' => null, 'Description' => 'Entry by GL Lines', 'Route' => 'entrybyglline.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202300, 'Name' => 'Submit For Approval', 'Icon' => null, 'Description' => 'Submit Budget for Approval', 'Route' => 'submitapproval.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202400, 'Name' => 'Approve Branch Budgets', 'Icon' => null, 'Description' => 'Branch Budget Approval', 'Route' => 'budgetapproval.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202500, 'Name' => 'Top-Down Budget', 'Icon' => null, 'Description' => 'Top-Down Allocation Tool', 'Route' => 'topdownallocation.index', 'ParentID' => 1202000],
            ['ModuleID' => 1202600, 'Name' => 'Budget Consolidation', 'Icon' => null, 'Description' => 'Consolidate Budgets', 'Route' => 'budgetconsolidation.index', 'ParentID' => 1202000],

            // Monitoring & Execution
            ['ModuleID' => 1203000, 'Name' => 'Monitoring & Execution', 'Icon' => null, 'Description' => 'Monitor & Execute Budget', 'Route' => null, 'ParentID' => 1200000],
            ['ModuleID' => 1203100, 'Name' => 'Plan vs Actual Monitoring', 'Icon' => null, 'Description' => 'Plan vs Actual Dashboard', 'Route' => 'budgetvsactualdashboard.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203200, 'Name' => 'Variance Analysis', 'Icon' => null, 'Description' => 'Analyse Variances', 'Route' => 'budgetvarianceanalysis.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203300, 'Name' => 'KPI Scorecards', 'Icon' => null, 'Description' => 'KPI Dashboard', 'Route' => 'kpiscorecards.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203400, 'Name' => 'Deviation Alerts', 'Icon' => null, 'Description' => 'Monitor Deviations', 'Route' => 'submitapproval.index', 'ParentID' => 1203000],
            ['ModuleID' => 1203500, 'Name' => 'Completion Rate View', 'Icon' => null, 'Description' => 'View Completion Rates', 'Route' => 'submitapproval.index', 'ParentID' => 1203000],
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
            ['ModuleID' => 500000, 'Name' => ModulesEnum::Property->description(), 'Icon' => '<i data-feather="home"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Property Registry
            ['ModuleID' => 501000, 'Name' => 'Property Registry', 'Icon' => '<i class="fas fa-building"></i>', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 501100, 'Name' => 'Add Property', 'Icon' => null, 'ParentID' => 501000, 'Route' => 'PropertyRegistry.index'],
            ['ModuleID' => 501200, 'Name' => 'Property Type', 'Icon' => null, 'ParentID' => 501000, 'Route' => 'propertytype.index'],
            ['ModuleID' => 501300, 'Name' => 'Property Category', 'Icon' => null, 'ParentID' => 501000, 'Route' => 'propertycategory.index'],
            ['ModuleID' => 501400, 'Name' => 'Property Attachments', 'Icon' => null, 'ParentID' => 501000, 'Route' => 'attachments.index'],

            // Structural Mapping (under Property Registry)
            ['ModuleID' => 501500, 'Name' => 'Structural Mapping', 'Icon' => '<i class="fas fa-sitemap"></i>', 'ParentID' => 501000, 'Route' => null],
            ['ModuleID' => 501510, 'Name' => 'Add Block', 'Icon' => null, 'ParentID' => 501500, 'Route' => 'addblock.index'],
            ['ModuleID' => 501520, 'Name' => 'Add Floor', 'Icon' => null, 'ParentID' => 501500, 'Route' => 'addfloor.index'],
            ['ModuleID' => 501530, 'Name' => 'Add Unit', 'Icon' => null, 'ParentID' => 501500, 'Route' => 'addunit.index'],

            // First level - Tenant & Lease
            ['ModuleID' => 502000, 'Name' => 'Tenant & Lease', 'Icon' => '<i class="fas fa-users"></i>', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 502100, 'Name' => 'Tenant Maintenance', 'Icon' => null, 'ParentID' => 502000, 'Route' => 'addtenant.index'],
            ['ModuleID' => 502200, 'Name' => 'Tenant Clearance', 'Icon' => null, 'ParentID' => 502000, 'Route' => 'tenantclearance.index'],

            // Lease Management (under Tenant & Lease)
            ['ModuleID' => 502300, 'Name' => 'Lease Management', 'Icon' => '<i class="fas fa-file-contract"></i>', 'ParentID' => 502000, 'Route' => null],
            ['ModuleID' => 502310, 'Name' => 'Lease Maintenance', 'Icon' => null, 'ParentID' => 502300, 'Route' => 'addlease.index'],
            ['ModuleID' => 502320, 'Name' => 'Lease Schedule', 'Icon' => null, 'ParentID' => 502300, 'Route' => 'schedulelease.index'],
            ['ModuleID' => 502330, 'Name' => 'Lease Renewal', 'Icon' => null, 'ParentID' => 502300, 'Route' => 'renewlease.index'],
            ['ModuleID' => 502340, 'Name' => 'Lease Termination', 'Icon' => null, 'ParentID' => 502300, 'Route' => 'terminatelease.index'],
            ['ModuleID' => 502350, 'Name' => 'Payment Frequency', 'Icon' => null, 'ParentID' => 502300, 'Route' => 'paymentfrequency.index'],

            // First level - Billing & Receipting
            ['ModuleID' => 503000, 'Name' => 'Billing & Receipting', 'Icon' => '<i class="fas fa-file-invoice-dollar"></i>', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 503100, 'Name' => 'Invoicing', 'Icon' => null, 'ParentID' => 503000, 'Route' => 'rentinvoice.index'],
            ['ModuleID' => 503200, 'Name' => 'Receipting', 'Icon' => null, 'ParentID' => 503000, 'Route' => 'rentreceipt.index'],
            ['ModuleID' => 503300, 'Name' => 'Tenant Ledger', 'Icon' => null, 'ParentID' => 503000, 'Route' => 'tenantledger.index'],
            ['ModuleID' => 503400, 'Name' => 'Rent Dashboard', 'Icon' => null, 'ParentID' => 503000, 'Route' => 'rentdashboard.index'],

            // First level - Maintenance & Issues
            ['ModuleID' => 504000, 'Name' => 'Maintenance & Issues', 'Icon' => '<i class="fas fa-tools"></i>', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 504100, 'Name' => 'Maintenance request', 'Icon' => null, 'ParentID' => 504000, 'Route' => 'maintenancerequest.index'],
            ['ModuleID' => 504200, 'Name' => 'Assign', 'Icon' => null, 'ParentID' => 504000, 'Route' => 'assignrequest.index'],
            ['ModuleID' => 504300, 'Name' => 'Dashboard', 'Icon' => null, 'ParentID' => 504000, 'Route' => 'maintenancedashboard.index'],
            ['ModuleID' => 504400, 'Name' => 'Work Completion', 'Icon' => null, 'ParentID' => 504000, 'Route' => 'workcompletion.index'],

            // First level - Reports
            ['ModuleID' => 599000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-chart-bar"></i>', 'ParentID' => 500000, 'Route' => 'propertyreports.index'],
            /* ['ModuleID' => 50510, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 50500, 'Route' =>],
             ['ModuleID' => 50520, 'Name' => 'Analytics', 'Icon' => null, 'ParentID' => 50500, 'Route' => 'propertyanalytics.index'],*/
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
            ['ModuleID' => 600000, 'Name' => ModulesEnum::Fleet->description(), 'Icon' => '<i data-feather="truck"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 601000, 'Name' => 'Driver Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'drivermanagement.index'],
            ['ModuleID' => 602000, 'Name' => 'Vehicle Registry', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'vehicle-registry.index'],
            ['ModuleID' => 603000, 'Name' => 'Trip Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'tripmanagement.index'],
            ['ModuleID' => 604000, 'Name' => 'Fuel Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'fuelmanagement.index'],
            ['ModuleID' => 605000, 'Name' => 'Compliance', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'complianceanddocumentation.index'],
            ['ModuleID' => 606000, 'Name' => 'Fleet Disposal', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'fleetprocurementanddisposal.index'],
            ['ModuleID' => 607000, 'Name' => 'Inventory Of Spare Parts', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'inventoryofspareparts.index'],
            ['ModuleID' => 608000, 'Name' => 'Utilization & Costing', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'utilization.index'],
            ['ModuleID' => 609000, 'Name' => 'Service Tracking', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'servicetracking.index'],
            ['ModuleID' => 610000, 'Name' => 'GPS Integration', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'gps.index'],

            ['ModuleID' => 699000, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'reports.index'],
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
            ['ModuleID' => 700000, 'Name' => ModulesEnum::DMS->description(), 'Icon' => '<i data-feather="file-text"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 701000, 'Name' => 'Repository Management', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'drepositorymanagement.index'],
            ['ModuleID' => 702000, 'Name' => 'Setup Management', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'dtypessetupmanagement.index'],
            ['ModuleID' => 703000, 'Name' => 'Categories Management', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'categoriesmanagement.index'],
            ['ModuleID' => 704000, 'Name' => 'Version Control', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'versioncontrolmanagement.index'],
            ['ModuleID' => 705000, 'Name' => 'Search Management', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'searchmanagement.index'],
            ['ModuleID' => 706000, 'Name' => 'Renewal Management', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'renewalmanagement.index'],
            ['ModuleID' => 707000, 'Name' => 'Access Control', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'accessmanagement.index'],
            ['ModuleID' => 708000, 'Name' => 'Audit Trail', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'trailmanagement.index'],
            ['ModuleID' => 709000, 'Name' => 'Bulk Upload', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'uploadmanagement.index'],

            ['ModuleID' => 799000, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 700000, 'Route' => 'reports.index'],
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
            ['ModuleID' => 800000, 'Name' => ModulesEnum::Legal->description(), 'Icon' => '<i data-feather="briefcase"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Case Management (801000)
            ['ModuleID' => 801000, 'Name' => 'Case Management', 'Icon' => '<i class="fas fa-gavel"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 801100, 'Name' => 'Case Register', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'caseregister.index'],
            ['ModuleID' => 801200, 'Name' => 'Case details', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'casedetails.index'],
            ['ModuleID' => 801300, 'Name' => 'Case Hearing', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'hearing.index'],
            ['ModuleID' => 801400, 'Name' => 'Case Documents', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'casedocuments.index'],
            ['ModuleID' => 801500, 'Name' => 'Case Notes', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'casenotes.index'],

            // First level - Contract Management (802000)
            ['ModuleID' => 802000, 'Name' => 'Contract Management', 'Icon' => '<i class="fas fa-file-contract"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 802100, 'Name' => 'Contract Approval', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'contractapproval.index'],
            ['ModuleID' => 802200, 'Name' => 'Contract Drafting', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'contractdrafting.index'],
            ['ModuleID' => 802300, 'Name' => 'Contract Repository', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'contractrepository.index'],
            ['ModuleID' => 802400, 'Name' => 'Obligation Tracker', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'obligationtracker.index'],
            ['ModuleID' => 802500, 'Name' => 'Expiry Alerts & Renewals', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'renewals.index'],

            // First level - Compliance Management (803000)
            ['ModuleID' => 803000, 'Name' => 'Compliance Management', 'Icon' => '<i class="fas fa-clipboard-check"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 803100, 'Name' => 'Regulatory Checklist', 'Icon' => null, 'ParentID' => 803000, 'Route' => 'regulatorychecklist.index'],
            ['ModuleID' => 803200, 'Name' => 'Compliance calendar', 'Icon' => null, 'ParentID' => 803000, 'Route' => 'compliancecalendar.index'],
            ['ModuleID' => 803300, 'Name' => 'Filing Tracker', 'Icon' => null, 'ParentID' => 803000, 'Route' => 'fillingtracker.index'],
            ['ModuleID' => 803400, 'Name' => 'Non-compliance Register', 'Icon' => null, 'ParentID' => 803000, 'Route' => 'noncomplianceregister.index'],

            // First level - Legal Dashboard & Reports (804000)
            ['ModuleID' => 804000, 'Name' => 'Legal Dashboard & Reports', 'Icon' => '<i class="fas fa-chart-line"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 804100, 'Name' => 'Open vs Closed Cases Summary', 'Icon' => null, 'ParentID' => 804000, 'Route' => 'casessummary.index'],
            ['ModuleID' => 804200, 'Name' => 'Legal Expenses by Case or Department', 'Icon' => null, 'ParentID' => 804000, 'Route' => 'legalexpenses.index'],
            ['ModuleID' => 804300, 'Name' => 'Upcoming Hearings Calendar', 'Icon' => null, 'ParentID' => 804000, 'Route' => 'hearings.index'],

            // First level - Intellectual Property (805000)
            ['ModuleID' => 805000, 'Name' => 'Intellectual Property', 'Icon' => '<i class="fas fa-lightbulb"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 805100, 'Name' => 'License Agreement', 'Icon' => null, 'ParentID' => 805000, 'Route' => 'copyrightlicenseagreement.index'],
            ['ModuleID' => 805200, 'Name' => 'Patent Tracking', 'Icon' => null, 'ParentID' => 805000, 'Route' => 'patenttracking.index'],
            ['ModuleID' => 805300, 'Name' => 'Trademark Register', 'Icon' => null, 'ParentID' => 805000, 'Route' => 'trademarkregister.index'],

            // First level - Legal Notices (806000)
            ['ModuleID' => 806000, 'Name' => 'Legal Notices', 'Icon' => '<i class="fas fa-exclamation-circle"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 806100, 'Name' => 'Correspondance', 'Icon' => null, 'ParentID' => 806000, 'Route' => 'archive.index'],
            ['ModuleID' => 806200, 'Name' => 'Notice Register', 'Icon' => null, 'ParentID' => 806000, 'Route' => 'register.index'],
            ['ModuleID' => 806300, 'Name' => 'Response Tracker', 'Icon' => null, 'ParentID' => 806000, 'Route' => 'responsetracker.index'],

            // First level - Lawyer Management (807000)
            ['ModuleID' => 807000, 'Name' => 'Lawyer Management', 'Icon' => '<i class="fas fa-user-tie"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 807100, 'Name' => 'External Directory', 'Icon' => null, 'ParentID' => 807000, 'Route' => 'externaldirectory.index'],
            ['ModuleID' => 807200, 'Name' => 'Fee Tracker', 'Icon' => null, 'ParentID' => 807000, 'Route' => 'feetracker.index'],
            ['ModuleID' => 807300, 'Name' => 'Performance Log', 'Icon' => null, 'ParentID' => 807000, 'Route' => 'performancelog.index'],


            ['ModuleID' => 899000, 'Name' => 'Legal Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'ParentID' => 800000, 'Route' => 'compliancestatus.index'],
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
            ['ModuleID' => 900000, 'Name' => ModulesEnum::Insurance->description(), 'Icon' => '<i data-feather="shield"></i>', 'ParentID' => null, 'Route' => null],

            // First level children
            ['ModuleID' => 901000, 'Name' => 'Provider Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'providermanagement.index'],
            ['ModuleID' => 902000, 'Name' => 'Insurance Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'insurancetypemanagement.index'],
            ['ModuleID' => 903000, 'Name' => 'Policy Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'insurancepolicymanagement.index'],
            ['ModuleID' => 904000, 'Name' => 'Asset Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'coveredassetmanagement.index'],
            ['ModuleID' => 905000, 'Name' => 'Premium Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'premiumpaymentmanagement.index'],
            ['ModuleID' => 906000, 'Name' => 'Claims Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'claimsmanagement.index'],
            ['ModuleID' => 907000, 'Name' => 'Renewal Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'renewalmanagement.index'],
            ['ModuleID' => 908000, 'Name' => 'Report Management', 'Icon' => null, 'ParentID' => 900000, 'Route' => 'reportmanagement.index'],

            ['ModuleID' => 999000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'ParentID' => 900000, 'Route' => null],
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
            ['ModuleID' => 1100000, 'Name' => ModulesEnum::Finance->description(), 'Icon' => '<i data-feather="dollar-sign"></i>', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 1101000, 'Name' => 'Accounts Payable', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'ParentID' => 1100000, 'Route' => null],
            ['ModuleID' => 1101100, 'Name' => 'Vendor Master', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'vendormaster.index'],
            ['ModuleID' => 1101200, 'Name' => 'Invoice Entry', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'invoiceentry.index'],
            ['ModuleID' => 1101300, 'Name' => 'Credit/Debit Note', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'creditnote.index'],
            ['ModuleID' => 1101400, 'Name' => 'Payment Processing', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'paymentprocessing.index'],
            ['ModuleID' => 1101500, 'Name' => 'Payment Voucher', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'paymentvoucher.index'],
            ['ModuleID' => 1101600, 'Name' => 'Aging Report', 'Icon' => null, 'ParentID' => 1101000, 'Route' => 'agingreport.index'],

            ['ModuleID' => 1102000, 'Name' => 'Accounts Receivable', 'Icon' => '<i class="fas fa-money-check-alt"></i>', 'ParentID' => 1100000, 'Route' => null],
            ['ModuleID' => 1102100, 'Name' => 'Customer Master', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'customermaster.index'],
            ['ModuleID' => 1102200, 'Name' => 'Invoice Generation', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'invoicegeneration.index'],
            ['ModuleID' => 1102300, 'Name' => 'Receipts Posting', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'receiptsposting.index'],
            ['ModuleID' => 1102400, 'Name' => 'Credit Management', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'creditmanagement.index'],
            ['ModuleID' => 1102500, 'Name' => 'Aging Report', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'agingreportar.index'],
            ['ModuleID' => 1102600, 'Name' => 'Customer Statement', 'Icon' => null, 'ParentID' => 1102000, 'Route' => 'customerstatement.index'],

            ['ModuleID' => 1103000, 'Name' => 'Journal Batch', 'Icon' => '<i class="fas fa-book"></i>', 'ParentID' => 1100000, 'Route' => 'journalbatch.index'],
            ['ModuleID' => 1104000, 'Name' => 'Ledger Accounts', 'Icon' => '<i class="fas fa-list-alt"></i>', 'ParentID' => 1100000, 'Route' => 'ledgeraccounts.index'],
            ['ModuleID' => 1105000, 'Name' => 'Transaction Types', 'Icon' => '<i class="fas fa-exchange-alt"></i>', 'ParentID' => 1100000, 'Route' => 'transactiontypes.index'],
            ['ModuleID' => 1106000, 'Name' => 'Bank Reconciliation', 'Icon' => '<i class="fas fa-check-double"></i>', 'ParentID' => 1100000, 'Route' => 'bankreconciliation.index'],
            ['ModuleID' => 1107000, 'Name' => 'Period Management', 'Icon' => '<i class="fas fa-calendar-alt"></i>', 'ParentID' => 1100000, 'Route' => 'periodmanagement.index'],

            ['ModuleID' => 1108000, 'Name' => 'Bank Management', 'Icon' => '<i class="fas fa-university"></i>', 'ParentID' => 1100000, 'Route' => null],
            ['ModuleID' => 1108100, 'Name' => 'Account Setup', 'Icon' => null, 'ParentID' => 1108000, 'Route' => 'bankaccountsetup.index'],
            ['ModuleID' => 1108200, 'Name' => 'Cash Book', 'Icon' => null, 'ParentID' => 1108000, 'Route' => 'cashbook.index'],
            ['ModuleID' => 1108300, 'Name' => 'Cash Management', 'Icon' => null, 'ParentID' => 1108000, 'Route' => 'cashmanagement.index'],
            ['ModuleID' => 1108400, 'Name' => 'Cheque Management', 'Icon' => null, 'ParentID' => 1108000, 'Route' => 'chequemanagement.index'],
            ['ModuleID' => 1108500, 'Name' => 'Vouchers', 'Icon' => null, 'ParentID' => 1108000, 'Route' => 'paymentandreceiptvouchers.index'],

            ['ModuleID' => 1199000, 'Name' => 'Financial Reports', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'ParentID' => 1100000, 'Route' => 'balancesheet.index'],
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
            ['ModuleID' => 1000000, 'Name' => ModulesEnum::HRM->description(), 'Icon' => '<i data-feather="users"></i>', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 1001000, 'Name' => 'Employees', 'Icon' => '<i class="fas fa-user-tie"></i>', 'ParentID' => 1000000, 'Route' => null],
            ['ModuleID' => 1001100, 'Name' => 'New Employee', 'Icon' => null, 'ParentID' => 1001000, 'Route' => 'employees.create'],
            ['ModuleID' => 1001200, 'Name' => 'Employees List', 'Icon' => null, 'ParentID' => 1001000, 'Route' => 'employees.index'],

            ['ModuleID' => 1002000, 'Name' => 'Departments', 'Icon' => '<i class="fas fa-building"></i>', 'ParentID' => 1000000, 'Route' => 'departments.index'],
            ['ModuleID' => 1003000, 'Name' => 'Committees', 'Icon' => '<i class="fas fa-building"></i>', 'ParentID' => 1000000, 'Route' => 'tendercommittee.index'],
            ['ModuleID' => 1099000, 'Name' => 'Financial Reports', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'ParentID' => 1000000, 'Route' => null],
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
            ['ModuleID' => 9800000, 'Name' => ModulesEnum::Settings->description(), 'Icon' => '<i class="fas fa-cogs"></i>', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 9800100, 'Name' => 'Users', 'Icon' => '<i class="fas fa-user"></i>', 'ParentID' => 9800000, 'Route' => 'users.index'],
            ['ModuleID' => 9800200, 'Name' => 'Roles', 'Icon' => '<i class="fas fa-user-tag"></i>', 'ParentID' => 9800000, 'Route' => 'roles.index'],
            ['ModuleID' => 9800300, 'Name' => 'Branches', 'Icon' => '<i class="fas fa-code-branch"></i>', 'ParentID' => 9800000, 'Route' => 'branches.index'],
            ['ModuleID' => 98004000, 'Name' => 'Code Details', 'Icon' => null, 'ParentID' => 9800000, 'Route' => 'settings.lists'],
            ['ModuleID' => 98005000, 'Name' => 'Integrations', 'Icon' => null, 'ParentID' => 9800000, 'Route' => 'settings.integrations'],
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
            ['ModuleID' => 9900000, 'Name' => ModulesEnum::MyAccount->description(), 'Icon' => '<i class="fas fa-user"></i>', 'ParentID' => null, 'Route' => null],

            ['ModuleID' => 9900100, 'Name' => 'Schedule', 'Icon' => '<i class=" fas fa-calendar"></i>', 'ParentID' => 9900000, 'Route' => 'schedule.index'],
            ['ModuleID' => 9900200, 'Name' => 'Tickets', 'Icon' => '<i data-feather="check-square"></i>', 'ParentID' => 9900000, 'Route' => 'tickets.index'],
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
