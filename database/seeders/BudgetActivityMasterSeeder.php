<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetActivityMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $faker = \Faker\Factory::create();

        // Fetch user IDs
        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            throw new \Exception('❌ No users found in t_Users. Please seed users first.');
        }

        // Fetch budget lines keyed by LineName
        $budgetLines = DB::table('t_BudgetLines')
            ->select('Id', 'LineName')
            ->get()
            ->keyBy('LineName');

        if ($budgetLines->isEmpty()) {
            throw new \Exception('❌ No budget lines found in t_BudgetLines. Please seed budget lines first.');
        }

        // Activity definition [Name, Description, IsActive, BudgetLineName]
        $activities = [
            ['Micro Loan Origination', 'Processing and disbursing micro loan applications.', true, 'Micro Loan Interest Income'],
            ['Micro Loan Portfolio Analysis', 'Weekly review of micro loan performance and defaults.', true, 'Micro Loan Interest Income'],
            ['Micro Loan Credit Assessment', 'Evaluating creditworthiness for micro loan applicants.', true, 'Micro Loan Interest Income'],
            ['Salary Loan Processing', 'Handling applications for salary-based loans.', true, 'Salary Based Loan Interest Income'],
            ['Salary Loan Repayment Tracking', 'Monitoring repayment schedules for salary-based loans.', true, 'Salary Based Loan Interest Income'],
            ['SME Loan Underwriting', 'Assessing and approving SME loan applications.', true, 'SME Loan Interest Income'],
            ['SME Loan Portfolio Management', 'Managing and reviewing SME loan portfolios.', true, 'SME Loan Interest Income'],
            ['Transaction Fee System Audit', 'Reviewing systems for accurate fee collection.', true, 'Transaction Fee Income'],
            ['Transaction Customer Support', 'Assisting customers with transaction-related queries.', true, 'Transaction Fee Income'],
            ['Wealth Client Advisory', 'Conducting advisory sessions with wealth management clients.', true, 'Wealth Management Fees'],
            ['Investment Portfolio Review', 'Weekly review of client investment portfolios.', true, 'Wealth Management Fees'],
            ['Forex Market Analysis', 'Analyzing forex market trends for trading decisions.', true, 'Foreign Exchange Gains'],
            ['Forex Trade Execution', 'Executing forex trades for clients.', true, 'Foreign Exchange Gains'],
            ['Investment Strategy Evaluation', 'Evaluating investment strategies for optimal returns.', true, 'Investment Income'],
            ['Dividend Collection Monitoring', 'Monitoring dividend payments from investments.', true, 'Investment Income'],
            ['Service Fee Policy Update', 'Revising service fee policies and rates.', true, 'Service Charges'],
            ['Fee-Related Staff Training', 'Training staff on fee-related customer interactions.', true, 'Service Charges'],
            ['Payroll Processing', 'Preparing and disbursing employee salaries.', true, 'Salaries and Wages'],
            ['Staff Performance Evaluation', 'Conducting weekly staff performance reviews.', true, 'Salaries and Wages'],
            ['HR Compensation Policy Update', 'Revising HR policies related to compensation.', false, 'Salaries and Wages'],
            ['Server Maintenance', 'Weekly maintenance of IT servers and networks.', true, 'IT Infrastructure Costs'],
            ['Cybersecurity Audit', 'Conducting security checks on IT systems.', true, 'IT Infrastructure Costs'],
            ['Software License Renewal', 'Managing renewals of IT software licenses.', true, 'IT Infrastructure Costs'],
            ['Digital Ad Campaigns', 'Running online ads and social media campaigns.', true, 'Marketing Expenses'],
            ['Market Trend Analysis', 'Analyzing market trends for marketing strategies.', true, 'Marketing Expenses'],
            ['Utility Bill Verification', 'Verifying and paying utility bills.', true, 'Utilities'],
            ['Energy Efficiency Assessment', 'Assessing energy usage in facilities.', true, 'Utilities'],
            ['Compliance Staff Training', 'Organizing staff training on regulations.', true, 'Regulatory Compliance Costs'],
            ['Regulatory Report Preparation', 'Preparing reports for regulatory bodies.', true, 'Regulatory Compliance Costs'],
            ['Travel Expense Processing', 'Processing employee travel expense claims.', true, 'Travel Expenses'],
            ['Travel Policy Compliance', 'Ensuring compliance with travel policies.', true, 'Travel Expenses'],
            ['Asset Depreciation Calculation', 'Calculating weekly depreciation for fixed assets.', true, 'Depreciation Expense'],
            ['Professional Development Workshops', 'Organizing professional development workshops.', true, 'Training and Development'],
            ['Training Program Assessment', 'Assessing the effectiveness of training programs.', true, 'Training and Development'],
            ['Contract Legal Review', 'Reviewing contracts for legal compliance.', true, 'Legal Fees'],
            ['Litigation Support', 'Supporting ongoing legal cases.', true, 'Legal Fees'],
            ['Consultant Coordination', 'Coordinating with external consultants.', true, 'Consultancy Fees'],
            ['Office Supply Inventory', 'Monitoring and restocking office supplies.', true, 'Office Supplies'],
            ['Security Patrol Scheduling', 'Organizing security patrols for branches.', true, 'Security Services'],
            ['Equipment Procurement', 'Purchasing new office equipment.', true, 'Office Equipment Purchases'],
            ['Branch Site Inspection', 'Inspecting sites for new branch locations.', true, 'Branch Expansion Costs'],
            ['Loan Fund Disbursement', 'Disbursing approved loan funds.', true, 'Loan Disbursements'],
            ['Loan Approval Management', 'Managing the loan approval process.', true, 'Loan Disbursements'],
            ['Software Rollout Planning', 'Planning the rollout of new software.', true, 'Computer Software Acquisition'],
            ['Furniture Installation', 'Coordinating delivery and setup of furniture.', true, 'Furniture and Fittings'],
            ['Cash Vault Reconciliation', 'Reconciling cash in vaults and accounts.', true, 'Cash and Cash Equivalents'],
            ['Loan Loss Provision Review', 'Assessing provisions for loan defaults.', true, 'Expected Loan Losses'],
            ['Funding Agreement Negotiation', 'Negotiating terms for borrowed funds.', true, 'Funding Costs'],
            ['Tax Compliance Review', 'Ensuring compliance with tax obligations.', true, 'Tax Liabilities'],
            ['Lease Payment Management', 'Managing payments for leased assets.', true, 'Lease Obligations'],
            ['Deposit Account Reconciliation', 'Reconciling customer deposit accounts.', true, 'Deposit Liabilities'],
        ];

        // Insert activity records
        foreach ($activities as [$name, $desc, $isActive, $lineName]) {
            $line = $budgetLines[$lineName] ?? $budgetLines->first();

            DB::table('t_BudgetActivityMaster')->insert([
                'ActivityName' => $name,
                'Description' => $desc,
                'IsActive' => $isActive,
                'BudgetLineID' => $line->Id,
                'CreatedBy' => $userIds[array_rand($userIds)],
                'CreatedOn' => $now->copy()->subDays(rand(10, 30)),
                'ModifiedBy' => $userIds[array_rand($userIds)],
                'ModifiedOn' => $now->copy()->subDays(rand(1, 5)),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }
}
