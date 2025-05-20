<?php

namespace Database\Seeders;

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
        DB::table(config('permission.table_names.role_has_permissions'))->delete();
        DB::table(config('permission.table_names.permissions'))->delete();
        DB::table('t_Modules')->delete();
        $this->_seed($this->_inventory());
        $this->_seed($this->_procurement());
        $this->_seed($this->_documentManagement());
        $this->_seed($this->_fleetManagement());
        $this->_seed($this->_propertyManagement());
        $this->_seed($this->_insurance());
        $this->_seed($this->_legal());
        $this->_seed($this->_finance());
        $this->_seed($this->_hrm());
        $this->_seed($this->_crm());
        $this->_seed($this->_settingsManagement());
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

    protected function _inventory(): Collection
    {
        return collect([
            ['ModuleID' => 100000, 'Name' => 'Inventory', 'Icon' => '<i data-feather="archive"></i>', 'Description' => 'Inventory Management Module', 'Route' => null, 'ParentID' => null],
            ['ModuleID' => 101000, 'Name' => 'Item Master', 'Icon' => null, 'Description' => 'Item Master Management', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 101100, 'Name' => 'Item Master List', 'Icon' => null, 'Description' => 'Item Master List', 'Route' => 'itemmaster.index', 'ParentID' => 101000],
            ['ModuleID' => 101200, 'Name' => 'Stock Item', 'Icon' => null, 'Description' => 'Stock Item Management', 'Route' => 'sku.index', 'ParentID' => 101000],
            ['ModuleID' => 101300, 'Name' => 'Item Category', 'Icon' => null, 'Description' => 'Item Category Management', 'Route' => 'itemcategory.index', 'ParentID' => 101000],
            ['ModuleID' => 101400, 'Name' => 'Item Sub Category', 'Icon' => null, 'Description' => 'Item Sub Category Management', 'Route' => 'itemsubcategory.index', 'ParentID' => 101000],
            ['ModuleID' => 102000, 'Name' => 'InterBranch Requisition', 'Icon' => null, 'Description' => 'InterBranch Requisition Management', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 102100, 'Name' => 'New Requisition', 'Icon' => null, 'Description' => 'Create New Requisition', 'Route' => 'interbranchrequisition.index', 'ParentID' => 102000],
            ['ModuleID' => 102200, 'Name' => 'Requisition Approval', 'Icon' => null, 'Description' => 'Requisition Approval', 'Route' => 'interbranchrequisitionapproval.index', 'ParentID' => 102000],
            ['ModuleID' => 103000, 'Name' => 'Inventory Dashboard', 'Icon' => null, 'Description' => 'Inventory Dashboard', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 103100, 'Name' => 'By Branch or Store List', 'Icon' => null, 'Description' => 'Inventory Dashboard By Branch or Store', 'Route' => 'inventorydashboard.index', 'ParentID' => 103000],
            ['ModuleID' => 103200, 'Name' => 'Movement Dashboard', 'Icon' => null, 'Description' => 'Movement Dashboard', 'Route' => 'movementdashboard.index', 'ParentID' => 103000],
            ['ModuleID' => 104000, 'Name' => 'Transactions', 'Icon' => null, 'Description' => 'Inventory Transactions', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 104100, 'Name' => 'Receipts', 'Icon' => null, 'Description' => 'Transaction Receipts', 'Route' => 'transactionsreceipts.index', 'ParentID' => 104000],
            ['ModuleID' => 104200, 'Name' => 'Transfers', 'Icon' => null, 'Description' => 'Transaction Transfers', 'Route' => 'transactionstransfers.index', 'ParentID' => 104000],
            ['ModuleID' => 104300, 'Name' => 'Adjustments', 'Icon' => null, 'Description' => 'Transaction Adjustments', 'Route' => 'transactionsadjustment.index', 'ParentID' => 104000],
            ['ModuleID' => 105000, 'Name' => 'Stock Management', 'Icon' => null, 'Description' => 'Stock Management', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 105100, 'Name' => 'Stock Take', 'Icon' => null, 'Description' => 'Stock Take Management', 'Route' => 'stocktake.index', 'ParentID' => 105000],
            ['ModuleID' => 105200, 'Name' => 'Load Opening Stock', 'Icon' => null, 'Description' => 'Load Opening Stock', 'Route' => 'openingstock.index', 'ParentID' => 105000],
            ['ModuleID' => 105300, 'Name' => 'Location Tracking', 'Icon' => null, 'Description' => 'Location Tracking', 'Route' => 'bintracking.index', 'ParentID' => 105000],
            ['ModuleID' => 105400, 'Name' => 'Stock Valuation', 'Icon' => null, 'Description' => 'Stock Valuation History', 'Route' => 'stockvaluationhistory.index', 'ParentID' => 105000],
            ['ModuleID' => 105500, 'Name' => 'Expiry Batch Tracking', 'Icon' => null, 'Description' => 'Expiry Batch Tracking', 'Route' => 'expirytracking.index', 'ParentID' => 105000],
            ['ModuleID' => 106000, 'Name' => 'Conversion Mapping', 'Icon' => null, 'Description' => 'Conversion Mapping Management', 'Route' => null, 'ParentID' => 100000],
            ['ModuleID' => 106100, 'Name' => 'Conversion Mapping', 'Icon' => null, 'Description' => 'UOM Conversion Mapping', 'Route' => 'uomconversion.index', 'ParentID' => 106000],
            ['ModuleID' => 107000, 'Name' => 'Reports', 'Icon' => null, 'Description' => 'Inventory Reports', 'Route' => 'inventoryreports.index', 'ParentID' => 100000],
        ]);
    }

    protected function _procurement(): Collection
    {
        return collect([
            ['ModuleID' => 200000, 'Name' => 'Procurement', 'Icon' => '<i class="fas fa-layer-group"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 201000, 'Name' => 'Procurement Plan', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 201100, 'Name' => 'Department Needs', 'Icon' => null, 'ParentID' => 201000, 'Route' => null],
            ['ModuleID' => 201110, 'Name' => 'Raise Needs', 'Icon' => null, 'ParentID' => 201100, 'Route' => 'procurementdepartmentalplan.index'],
            ['ModuleID' => 201120, 'Name' => 'Approve Needs', 'Icon' => null, 'ParentID' => 201100, 'Route' => 'needsapproval.index'],
            ['ModuleID' => 201200, 'Name' => 'Plan Consolidation', 'Icon' => null, 'ParentID' => 201000, 'Route' => null],
            ['ModuleID' => 201210, 'Name' => 'Consolidated Needs', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'consolidated.index'],
            ['ModuleID' => 201220, 'Name' => 'New Plan', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'procurementplanmaintain.index'],
            ['ModuleID' => 201230, 'Name' => 'Amend Plan', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'editplan.index'],
            ['ModuleID' => 201240, 'Name' => 'Link Budget Lines', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'maptobudget.index'],
            ['ModuleID' => 201250, 'Name' => 'Set Method', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'procurementassignitem.index'],
            ['ModuleID' => 201260, 'Name' => 'Schedule Plan', 'Icon' => null, 'ParentID' => 201200, 'Route' => 'procurementplanquaterly.index'],
            ['ModuleID' => 201300, 'Name' => 'Dashboard', 'Icon' => null, 'ParentID' => 201000, 'Route' => null],
            ['ModuleID' => 201310, 'Name' => 'Plan View', 'Icon' => null, 'ParentID' => 201300, 'Route' => 'procurementplandetails.index'],
            ['ModuleID' => 201320, 'Name' => 'Timeline', 'Icon' => null, 'ParentID' => 201300, 'Route' => 'plantimeline.index'],
            ['ModuleID' => 201330, 'Name' => 'Calender Based', 'Icon' => null, 'ParentID' => 201300, 'Route' => 'calenderbased.index'],
            ['ModuleID' => 201340, 'Name' => 'Flagged Items', 'Icon' => null, 'ParentID' => 201300, 'Route' => 'delayeditems.index'],
            ['ModuleID' => 201350, 'Name' => 'Plan vs Actual', 'Icon' => null, 'ParentID' => 201300, 'Route' => 'planvsactual.index'],
            ['ModuleID' => 201400, 'Name' => 'Approval', 'Icon' => null, 'ParentID' => 201000, 'Route' => null],
            ['ModuleID' => 201410, 'Name' => 'Submit For Approval', 'Icon' => null, 'ParentID' => 201400, 'Route' => 'submitplan.index'],
            ['ModuleID' => 201420, 'Name' => 'Approval Inbox', 'Icon' => null, 'ParentID' => 201400, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 201430, 'Name' => 'Approve Plan', 'Icon' => null, 'ParentID' => 201400, 'Route' => 'procurementplanapproval.index'],
            ['ModuleID' => 201500, 'Name' => 'Plan Execution', 'Icon' => null, 'ParentID' => 201000, 'Route' => null],
            ['ModuleID' => 201510, 'Name' => 'Execution Dashboard', 'Icon' => null, 'ParentID' => 201500, 'Route' => 'submitplan.index'],
            ['ModuleID' => 201520, 'Name' => 'Pending Execution', 'Icon' => null, 'ParentID' => 201500, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 201530, 'Name' => 'Execution Calendar', 'Icon' => null, 'ParentID' => 201500, 'Route' => 'submitplan.index'],
            ['ModuleID' => 201540, 'Name' => 'Deviations', 'Icon' => null, 'ParentID' => 201500, 'Route' => 'approvalinbox.index'],
            ['ModuleID' => 202000, 'Name' => 'Purchase Requisition', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 202100, 'Name' => 'Requisition List', 'Icon' => null, 'ParentID' => 202000, 'Route' => 'requisition.create'],
            ['ModuleID' => 202200, 'Name' => 'Requisition Approval', 'Icon' => null, 'ParentID' => 202000, 'Route' => 'requisition.index'],
            ['ModuleID' => 202300, 'Name' => 'Priority List', 'Icon' => null, 'ParentID' => 202000, 'Route' => 'requisitionItem.index'],
            ['ModuleID' => 203000, 'Name' => 'Suppliers', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 204000, 'Name' => 'Procurement Modes', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 204100, 'Name' => 'List Modes', 'Icon' => null, 'ParentID' => 204000, 'Route' => 'procurement-modes.index'],
            ['ModuleID' => 204200, 'Name' => 'Add Mode', 'Icon' => null, 'ParentID' => 204000, 'Route' => 'procurement-modes.create'],
            ['ModuleID' => 205000, 'Name' => 'Tendering', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 205100, 'Name' => 'Tender Setup', 'Icon' => null, 'ParentID' => 205000, 'Route' => null],
            ['ModuleID' => 205110, 'Name' => 'Tender Initiation', 'Icon' => null, 'ParentID' => 205100, 'Route' => 'initiatetender.index'],
            ['ModuleID' => 205120, 'Name' => 'Initiation Approval', 'Icon' => null, 'ParentID' => 205100, 'Route' => 'initiateapprove.index'],

            ['ModuleID' => 206000, 'Name' => 'RFQS', 'Icon' => null, 'ParentID' => 200000, 'Route' => null],
            ['ModuleID' => 206100, 'Name' => 'View RFQs', 'Icon' => null, 'ParentID' => 206000, 'Route' => 'rfqs.index'],
            ['ModuleID' => 206200, 'Name' => 'RFQ Responses', 'Icon' => null, 'ParentID' => 206000, 'Route' => 'rfqresponses.index'],
            ['ModuleID' => 206300, 'Name' => 'RFQ Evaluation', 'Icon' => null, 'ParentID' => 206000, 'Route' => 'evaluations.index'],

        ]);
    }

    protected function _documentManagement(): Collection
    {
        return collect([
            ['ModuleID' => 300000, 'Name' => 'DMS', 'Icon' => '<i data-feather="file-text"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 301000, 'Name' => 'Repository Management', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'drepositorymanagement.index'],
            ['ModuleID' => 302000, 'Name' => 'Setup Management', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'dtypessetupmanagement.index'],
            ['ModuleID' => 303000, 'Name' => 'Categories Management', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'categoriesmanagement.index'],
            ['ModuleID' => 304000, 'Name' => 'Version Control', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'versioncontrolmanagement.index'],
            ['ModuleID' => 305000, 'Name' => 'Search Management', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'searchmanagement.index'],
            ['ModuleID' => 306000, 'Name' => 'Renewal Management', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'renewalmanagement.index'],
            ['ModuleID' => 307000, 'Name' => 'Access Control', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'accessmanagement.index'],
            ['ModuleID' => 308000, 'Name' => 'Audit Trail', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'trailmanagement.index'],
            ['ModuleID' => 309000, 'Name' => 'Bulk Upload', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'uploadmanagement.index'],
            ['ModuleID' => 310000, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 300000, 'Route' => 'reports.index'],
        ]);
    }

    protected function _fleetManagement(): Collection
    {
        return collect([
            ['ModuleID' => 400000, 'Name' => 'Fleet Management', 'Icon' => '<i data-feather="truck"></i>', 'ParentID' => null, 'Route' => null],
            ['ModuleID' => 401000, 'Name' => 'Driver Management', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'drivermanagement.index'],
            ['ModuleID' => 402000, 'Name' => 'Vehicle Registry', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'vehicle-registry.index'],
            ['ModuleID' => 403000, 'Name' => 'Trip Management', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'tripmanagement.index'],
            ['ModuleID' => 404000, 'Name' => 'Fuel Management', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'fuelmanagement.index'],
            ['ModuleID' => 405000, 'Name' => 'Compliance', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'complianceanddocumentation.index'],
            ['ModuleID' => 406000, 'Name' => 'Fleet Disposal', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'fleetprocurementanddisposal.index'],
            ['ModuleID' => 407000, 'Name' => 'Inventory Of Spare Parts', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'inventoryofspareparts.index'],
            ['ModuleID' => 408000, 'Name' => 'Utilization & Costing', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'utilization.index'],
            ['ModuleID' => 409000, 'Name' => 'Service Tracking', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'servicetracking.index'],
            ['ModuleID' => 410000, 'Name' => 'GPS Integration', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'gps.index'],
            ['ModuleID' => 411000, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 400000, 'Route' => 'reports.index'],
        ]);
    }

    protected function _propertyManagement(): Collection
    {
        return collect([
            // Main module
            ['ModuleID' => 500000, 'Name' => 'Property Management', 'Icon' => '<i data-feather="home"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Property Registry
            ['ModuleID' => 501000, 'Name' => 'Property Registry', 'Icon' => '<i class="fas fa-building"></i>', 'ParentID' => 500000, 'Route' => null],
            ['ModuleID' => 501100, 'Name' => 'Add Property', 'Icon' => null, 'ParentID' => 501000, 'Route' => 'addproperty.index'],
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
            ['ModuleID' => 505000, 'Name' => 'Reports', 'Icon' => '<i class="fas fa-chart-bar"></i>', 'ParentID' => 500000, 'Route' => 'propertyreports.index'],
            /* ['ModuleID' => 50510, 'Name' => 'Reports', 'Icon' => null, 'ParentID' => 50500, 'Route' =>],
             ['ModuleID' => 50520, 'Name' => 'Analytics', 'Icon' => null, 'ParentID' => 50500, 'Route' => 'propertyanalytics.index'],*/
        ]);
    }

    protected function _insurance(): Collection
    {
        return collect([
            // Main module - Insurance
            ['ModuleID' => 600000, 'Name' => 'Insurance', 'Icon' => '<i data-feather="shield"></i>', 'ParentID' => null, 'Route' => null],

            // First level children
            ['ModuleID' => 601000, 'Name' => 'Provider Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'providermanagement.index'],
            ['ModuleID' => 602000, 'Name' => 'Insurance Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'insurancetypemanagement.index'],
            ['ModuleID' => 603000, 'Name' => 'Policy Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'insurancepolicymanagement.index'],
            ['ModuleID' => 604000, 'Name' => 'Asset Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'coveredassetmanagement.index'],
            ['ModuleID' => 605000, 'Name' => 'Premium Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'premiumpaymentmanagement.index'],
            ['ModuleID' => 606000, 'Name' => 'Claims Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'claimsmanagement.index'],
            ['ModuleID' => 607000, 'Name' => 'Renewal Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'renewalmanagement.index'],
            ['ModuleID' => 608000, 'Name' => 'Report Management', 'Icon' => null, 'ParentID' => 600000, 'Route' => 'reportmanagement.index'],
        ]);
    }

    protected function _legal(): Collection
    {
        return collect([
            // Main module - Legal (700000)
            ['ModuleID' => 700000, 'Name' => 'Legal', 'Icon' => '<i data-feather="briefcase"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Case Management (701000)
            ['ModuleID' => 701000, 'Name' => 'Case Management', 'Icon' => '<i class="fas fa-gavel"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 701100, 'Name' => 'Case Register', 'Icon' => null, 'ParentID' => 701000, 'Route' => 'caseregister.index'],
            ['ModuleID' => 701200, 'Name' => 'Case details', 'Icon' => null, 'ParentID' => 701000, 'Route' => 'casedetails.index'],
            ['ModuleID' => 701300, 'Name' => 'Case Hearing', 'Icon' => null, 'ParentID' => 701000, 'Route' => 'hearing.index'],
            ['ModuleID' => 701400, 'Name' => 'Case Documents', 'Icon' => null, 'ParentID' => 701000, 'Route' => 'casedocuments.index'],
            ['ModuleID' => 701500, 'Name' => 'Case Notes', 'Icon' => null, 'ParentID' => 701000, 'Route' => 'casenotes.index'],

            // First level - Contract Management (702000)
            ['ModuleID' => 702000, 'Name' => 'Contract Management', 'Icon' => '<i class="fas fa-file-contract"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 702100, 'Name' => 'Contract Approval', 'Icon' => null, 'ParentID' => 702000, 'Route' => 'contractapproval.index'],
            ['ModuleID' => 702200, 'Name' => 'Contract Drafting', 'Icon' => null, 'ParentID' => 702000, 'Route' => 'contractdrafting.index'],
            ['ModuleID' => 702300, 'Name' => 'Contract Repository', 'Icon' => null, 'ParentID' => 702000, 'Route' => 'contractrepository.index'],
            ['ModuleID' => 702400, 'Name' => 'Obligation Tracker', 'Icon' => null, 'ParentID' => 702000, 'Route' => 'obligationtracker.index'],
            ['ModuleID' => 702500, 'Name' => 'Expiry Alerts & Renewals', 'Icon' => null, 'ParentID' => 702000, 'Route' => 'renewals.index'],

            // First level - Compliance Management (703000)
            ['ModuleID' => 703000, 'Name' => 'Compliance Management', 'Icon' => '<i class="fas fa-clipboard-check"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 703100, 'Name' => 'Regulatory Checklist', 'Icon' => null, 'ParentID' => 703000, 'Route' => 'regulatorychecklist.index'],
            ['ModuleID' => 703200, 'Name' => 'Compliance calendar', 'Icon' => null, 'ParentID' => 703000, 'Route' => 'compliancecalendar.index'],
            ['ModuleID' => 703300, 'Name' => 'Filing Tracker', 'Icon' => null, 'ParentID' => 703000, 'Route' => 'fillingtracker.index'],
            ['ModuleID' => 703400, 'Name' => 'Non-compliance Register', 'Icon' => null, 'ParentID' => 703000, 'Route' => 'noncomplianceregister.index'],

            // First level - Legal Dashboard & Reports (704000)
            ['ModuleID' => 704000, 'Name' => 'Legal Dashboard & Reports', 'Icon' => '<i class="fas fa-chart-line"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 704100, 'Name' => 'Open vs Closed Cases Summary', 'Icon' => null, 'ParentID' => 704000, 'Route' => 'casessummary.index'],
            ['ModuleID' => 704200, 'Name' => 'Legal Expenses by Case or Department', 'Icon' => null, 'ParentID' => 704000, 'Route' => 'legalexpenses.index'],
            ['ModuleID' => 704300, 'Name' => 'Upcoming Hearings Calendar', 'Icon' => null, 'ParentID' => 704000, 'Route' => 'hearings.index'],

            // First level - Intellectual Property (705000)
            ['ModuleID' => 705000, 'Name' => 'Intellectual Property', 'Icon' => '<i class="fas fa-lightbulb"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 705100, 'Name' => 'License Agreement', 'Icon' => null, 'ParentID' => 705000, 'Route' => 'copyrightlicenseagreement.index'],
            ['ModuleID' => 705200, 'Name' => 'Patent Tracking', 'Icon' => null, 'ParentID' => 705000, 'Route' => 'patenttracking.index'],
            ['ModuleID' => 705300, 'Name' => 'Trademark Register', 'Icon' => null, 'ParentID' => 705000, 'Route' => 'trademarkregister.index'],

            // First level - Legal Notices (706000)
            ['ModuleID' => 706000, 'Name' => 'Legal Notices', 'Icon' => '<i class="fas fa-exclamation-circle"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 706100, 'Name' => 'Correspondance', 'Icon' => null, 'ParentID' => 706000, 'Route' => 'archive.index'],
            ['ModuleID' => 706200, 'Name' => 'Notice Register', 'Icon' => null, 'ParentID' => 706000, 'Route' => 'register.index'],
            ['ModuleID' => 706300, 'Name' => 'Response Tracker', 'Icon' => null, 'ParentID' => 706000, 'Route' => 'responsetracker.index'],

            // First level - Lawyer Management (707000)
            ['ModuleID' => 707000, 'Name' => 'Lawyer Management', 'Icon' => '<i class="fas fa-user-tie"></i>', 'ParentID' => 700000, 'Route' => null],
            ['ModuleID' => 707100, 'Name' => 'External Directory', 'Icon' => null, 'ParentID' => 707000, 'Route' => 'externaldirectory.index'],
            ['ModuleID' => 707200, 'Name' => 'Fee Tracker', 'Icon' => null, 'ParentID' => 707000, 'Route' => 'feetracker.index'],
            ['ModuleID' => 707300, 'Name' => 'Performance Log', 'Icon' => null, 'ParentID' => 707000, 'Route' => 'performancelog.index'],

            // First level - Legal Reports (708000)
            ['ModuleID' => 708000, 'Name' => 'Legal Reports', 'Icon' => '<i class="fas fa-file-alt"></i>', 'ParentID' => 700000, 'Route' => 'compliancestatus.index'],
            /*  ['ModuleID' => 70810, 'Name' => 'Compliance Status', 'Icon' => null, 'ParentID' => 70800, 'Route' => ],
              ['ModuleID' => 70820, 'Name' => 'Pending Contracts', 'Icon' => null, 'ParentID' => 70800, 'Route' => 'pendingcontracts.index'],*/
        ]);
    }

    protected function _finance(): Collection
    {
        return collect([
            // Main module - Finance (800000)
            ['ModuleID' => 800000, 'Name' => 'Finance', 'Icon' => '<i data-feather="dollar-sign"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Accounts Payable (801000)
            ['ModuleID' => 801000, 'Name' => 'Accounts Payable', 'Icon' => '<i class="fas fa-file-invoice"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 801100, 'Name' => 'Vendor Master', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'vendormaster.index'],
            ['ModuleID' => 801200, 'Name' => 'Invoice Entry', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'invoiceentry.index'],
            ['ModuleID' => 801300, 'Name' => 'Credit/Debit Note', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'creditnote.index'],
            ['ModuleID' => 801400, 'Name' => 'Payment Processing', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'paymentprocessing.index'],
            ['ModuleID' => 801500, 'Name' => 'Payment Voucher', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'paymentvoucher.index'],
            ['ModuleID' => 801600, 'Name' => 'Aging Report', 'Icon' => null, 'ParentID' => 801000, 'Route' => 'agingreport.index'],

            // First level - Accounts Receivable (802000)
            ['ModuleID' => 802000, 'Name' => 'Accounts Receivable', 'Icon' => '<i class="fas fa-money-check-alt"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 802100, 'Name' => 'Customer Master', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'customermaster.index'],
            ['ModuleID' => 802200, 'Name' => 'Invoice Generation', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'invoicegeneration.index'],
            ['ModuleID' => 802300, 'Name' => 'Receipts Posting', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'receiptsposting.index'],
            ['ModuleID' => 802400, 'Name' => 'Credit Management', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'creditmanagement.index'],
            ['ModuleID' => 802500, 'Name' => 'Aging Report', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'agingreportar.index'],
            ['ModuleID' => 802600, 'Name' => 'Customer Statement', 'Icon' => null, 'ParentID' => 802000, 'Route' => 'customerstatement.index'],

            // First level - Direct Links (803000-807000)
            ['ModuleID' => 803000, 'Name' => 'Journal Batch', 'Icon' => '<i class="fas fa-book"></i>', 'ParentID' => 800000, 'Route' => 'journalbatch.index'],
            ['ModuleID' => 804000, 'Name' => 'Ledger Accounts', 'Icon' => '<i class="fas fa-list-alt"></i>', 'ParentID' => 800000, 'Route' => 'ledgeraccounts.index'],
            ['ModuleID' => 805000, 'Name' => 'Transaction Types', 'Icon' => '<i class="fas fa-exchange-alt"></i>', 'ParentID' => 800000, 'Route' => 'transactiontypes.index'],
            ['ModuleID' => 806000, 'Name' => 'Bank Reconciliation', 'Icon' => '<i class="fas fa-check-double"></i>', 'ParentID' => 800000, 'Route' => 'bankreconciliation.index'],
            ['ModuleID' => 807000, 'Name' => 'Period Management', 'Icon' => '<i class="fas fa-calendar-alt"></i>', 'ParentID' => 800000, 'Route' => 'periodmanagement.index'],

            // First level - Bank Management (808000)
            ['ModuleID' => 808000, 'Name' => 'Bank Management', 'Icon' => '<i class="fas fa-university"></i>', 'ParentID' => 800000, 'Route' => null],
            ['ModuleID' => 808100, 'Name' => 'Account Setup', 'Icon' => null, 'ParentID' => 808000, 'Route' => 'bankaccountsetup.index'],
            ['ModuleID' => 808200, 'Name' => 'Cash Book', 'Icon' => null, 'ParentID' => 808000, 'Route' => 'cashbook.index'],
            ['ModuleID' => 808300, 'Name' => 'Cash Management', 'Icon' => null, 'ParentID' => 808000, 'Route' => 'cashmanagement.index'],
            ['ModuleID' => 808400, 'Name' => 'Cheque Management', 'Icon' => null, 'ParentID' => 808000, 'Route' => 'chequemanagement.index'],
            ['ModuleID' => 808500, 'Name' => 'Vouchers', 'Icon' => null, 'ParentID' => 808000, 'Route' => 'paymentandreceiptvouchers.index'],

            // First level - Financial Reports (809000)
            ['ModuleID' => 809000, 'Name' => 'Financial Reports', 'Icon' => '<i class="fas fa-chart-pie"></i>', 'ParentID' => 800000, 'Route' => 'balancesheet.index'],
            /* ['ModuleID' => 80910, 'Name' => 'Balance Sheet', 'Icon' => null, 'ParentID' => 80900, 'Route' => 'balancesheet.index'],
             ['ModuleID' => 80920, 'Name' => 'Cash Flow Statement', 'Icon' => null, 'ParentID' => 80900, 'Route' => 'cashflowstatement.index'],
             ['ModuleID' => 80930, 'Name' => 'Consolidation Reports', 'Icon' => null, 'ParentID' => 80900, 'Route' => 'consolidationreports.index'],
             ['ModuleID' => 80940, 'Name' => 'Income Statement', 'Icon' => null, 'ParentID' => 80900, 'Route' => 'incomestatement.index'],*/
        ]);
    }

    protected function _hrm(): Collection
    {
        return collect([
            // Main module - HRM (900000)
            ['ModuleID' => 900000, 'Name' => 'HRM - Human Resource', 'Icon' => '<i data-feather="users"></i>', 'ParentID' => null, 'Route' => null],

            // First level - Employees (901000)
            ['ModuleID' => 901000, 'Name' => 'Employees', 'Icon' => '<i class="fas fa-user-tie"></i>', 'ParentID' => 900000, 'Route' => null],
            ['ModuleID' => 901100, 'Name' => 'New Employee', 'Icon' => null, 'ParentID' => 901000, 'Route' => 'employees.create'],
            ['ModuleID' => 901200, 'Name' => 'Employees List', 'Icon' => null, 'ParentID' => 901000, 'Route' => 'employees.index'],

            // First level - Departments (902000)
            ['ModuleID' => 902000, 'Name' => 'Departments', 'Icon' => '<i class="fas fa-building"></i>', 'ParentID' => 900000, 'Route' => 'departments.index'],
        ]);
    }

    protected function _crm(): Collection
    {
        return collect([
            ['ModuleID' => 1000000, 'Name' => 'CRM', 'Icon' => '<i class="fas fa-code-merge"></i>', 'ParentID' => null, 'Route' => null],
        ]);
    }
    protected function _settingsManagement(): Collection
    {
        return collect([
            // Main module - Users and Roles (1000000)
            ['ModuleID' => 9000000, 'Name' => 'Settings', 'Icon' => '<i class="fas fa-cogs"></i>', 'ParentID' => null, 'Route' => null],

            // First level children
            ['ModuleID' => 9001000, 'Name' => 'Users', 'Icon' => '<i class="fas fa-user"></i>', 'ParentID' => 9000000, 'Route' => 'users.index'],
            ['ModuleID' => 9002000, 'Name' => 'Roles', 'Icon' => '<i class="fas fa-user-tag"></i>', 'ParentID' => 9000000, 'Route' => 'roles.index'],
            ['ModuleID' => 9003000, 'Name' => 'Branches', 'Icon' => '<i class="fas fa-code-branch"></i>', 'ParentID' => 9000000, 'Route' => 'branches.index'],
            ['ModuleID' => 9004000, 'Name' => 'Code Details', 'Icon' => '<i data-feather="settings"', 'ParentID' => 9000000, 'Route' => 'settings.lists'],
        ]);
    }
}
