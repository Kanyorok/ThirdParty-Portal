# BRERP User Manual

Version: 1.0
Generated: 2025-09-19

---

## Table of Contents

1. Introduction
    - Purpose of this Manual
    - Audience and Prerequisites
    - System Overview
    - Roles and Permissions
    - Navigation Basics
    - Conventions Used in this Manual
2. Getting Started
    - Accessing the System (Login)
    - Password Reset and Verification
    - User Profile and My Account
    - Language, Timezone, Number/Currency Formats
3. Cross-Cutting Concepts
    - Search and Filters
    - Lists, Create/Edit Forms, Detail Views
    - Attachments and Document Uploads
    - Approval Workflows (Submit, Approve, Reject)
    - Notifications and Messaging
    - Reports and Exports
4. Modules
    - Third Party (External Parties & Supplier Portal)
    - CRM (Customer Relationship Management)
    - Procurement
    - Inventory
    - Property Management
    - Fleet Management
    - Document Management (DMS)
    - Legal
    - Insurance (Bancassurance)
    - HRM (Human Resource Management)
    - Finance
    - Budget Line & Analytics
    - Settings
    - My Account
5. Troubleshooting & FAQs
6. Glossary
7. Change Log

---

## 1. Introduction

### Purpose of this Manual

This manual explains how to use the BRERP system across all modules. It provides step-by-step workflows and guidance for
end-users and power users.

### Audience and Prerequisites

- End users performing day-to-day operations in modules
- Supervisors/approvers responsible for reviewing and approving tasks
- Administrators managing users, roles, and system setup

You should have an active user account and the correct roles assigned by your administrator.

### System Overview

BRERP is a modular ERP composed of functional domains (modules). Each module contains related features surfaced via the
left-side navigation. Permissions control access to specific pages and actions.

### Roles and Permissions

BRERP uses role-based access control. Your role determines what you can see and do. If you cannot access a page or
action described here, contact an administrator to review your role assignments under Settings > Roles/Users.

### Navigation Basics

- Use the left navigation to open a module.
- Within each module, use sub-menus to access lists, dashboards, and setup pages.
- Most lists support search, filter, sort, pagination, and export.

### Conventions Used in this Manual

- “Go to …” indicates the navigation path (Module > Menu > Page).
- Buttons and actions are written as: Save, Submit, Approve, Delete.
- Some features require supporting master data to be set up first.

---

## 2. Getting Started

### Accessing the System (Login)

- Open the BRERP URL provided by your IT team.
- Enter your email/username and password.
- Click Login. If multi-factor or email verification is required, follow the prompts.

### Password Reset and Verification

- Use “Forgot Password” on the login page to receive a reset link.
- For third-party (supplier) users, email verification can occur via links sent by the system.

### User Profile and My Account

Go to My Account:

- Schedule: view your upcoming activities and meetings.
- Tickets: review tickets assigned to you.
  Update your profile details as permitted by your role/organization policy.

### Language, Timezone, Number/Currency Formats

These follow system defaults unless overridden by your organization. Contact admins if adjustments are needed.

---

## 3. Cross-Cutting Concepts

### Search and Filters

- Most lists have a search box and column filters.
- Use filters to narrow results (e.g., status, date ranges).

### Lists, Create/Edit Forms, Detail Views

- Index/List: overview of records, with actions (View, Edit, Delete).
- Create/Edit: forms to input or update information.
- Detail/Show: read-only view with tabs for related items, activity, or attachments.

### Attachments and Document Uploads

- Many pages allow uploading documents (PDF, images, spreadsheets).
- Use the “Upload” button; add a description/tags where applicable.

### Approval Workflows (Submit, Approve, Reject)

- Some processes require approval (procurement plans, invoices, petty cash, policies, etc.).
- Typical flow: Draft → Submit → Approve/Reject → Posted/Active.
- Approvers see an inbox or specific “Approval” pages.

### Notifications and Messaging

- Bulk notifications (teams/users), email/sms logs exist in CRM and Settings.
- Some modules trigger system alerts (e.g., fleet service alerts).

### Reports and Exports

- Each module has a Reports menu. Select a report and export format (PDF/Excel) when available.

---

## 4. Modules

Below are step-by-step user guides for each module and its child features.

### 4.1 Third Party (External Parties & Supplier Portal)

#### Overview

Manage external parties (suppliers) and third-party users. External users can authenticate, maintain profiles, and
participate in procurement workflows.

#### Navigation

- Web: Third Party > Parties (list, create, edit, delete)
- API (for supplier portal processes): handled by your integration/frontend portal (login/register, bank details,
  categories)

#### Typical Tasks

1) View and manage third parties (internal users)

- Go to Third Party > Parties
- Search by name or category
- Create: click Create, fill in details, Save
- Edit: open a party, click Edit, update details, Save
- Bulk actions: select multiple and use Bulk Action

2) Supplier (third-party) user onboarding (external portal)

- Register (email verification via link)
- Login
- Complete company details
- Add bank details
- Access procurement invitations and submit responses where applicable

#### Tips & Dependencies

- Ensure categories and required codes are set up under Settings.
- External users require approval and active status.

---

### 4.2 CRM (Customer Relationship Management)

#### Overview

Covers calls, contacts, tickets, clients, leads, accounts, marketing (campaigns, lists, planner, socials), feedback (
surveys, reviews), email conversations, product development, debt collection, and reports.

#### Navigation

CRM > [feature], e.g.:

- Calls: Start Call; schedule/reschedule
- Contacts: manage contacts; attach to leads/clients
- Tickets: create, comment, watchers, resolve
- Clients: summary, activities, relations, discussions, notes, email, sms, tasks
- Leads: onboarding, status, activities, meetings, emails/sms, tasks
- Email: conversations, drafts, attachments
- Marketing: competitors, campaigns, lists, planner, socials
- Feedback: surveys and reviews
- Product Development: items, features, comments, workflows
- Debt Collection: loan lists, activities, scheduling, notifications
- Reports

#### Typical Workflows

1) Manage Leads → Clients

- Go to CRM > Leads
- Create new lead (basic info)
- Add activities (calls/meetings), notes, emails/sms
- Update status (qualified/disqualified)
- Use Onboarding to convert to Client

2) Manage Tickets

- Go to CRM > Tickets
- Create ticket, assign priority and assignees
- Add comments and documents
- Resolve ticket; restore if needed

3) Run a Marketing Campaign

- Create lists (CRM > Marketing > Marketing Lists)
- Create campaign (CRM > Marketing > Campaigns)
- Add contacts, submit for approval if applicable, track progress

4) Email Conversations

- CRM > Email Conversations
- Read, reply drafts, attach files, send

5) Debt Collection Activities

- CRM > Debt Collection
- View loan list, schedule calls/meetings, contact guarantors, send sms/email

#### Tips

- Use “select2/fetch” endpoints pages to search clients/leads quickly.
- Boards and committees pages support governance and planning meetings.

---

### 4.3 Procurement

#### Overview

Covers department needs entry and consolidation; plan setup, scheduling, submission, and approvals; suppliers and
prequalification; tendering; RFQs and evaluation; awards; purchase orders; goods receipts; contracts lifecycle; reports.

#### Navigation (examples)

- Procurement > Department Needs (raise needs; approvals)
- Procurement > Plan: Plan Maintain, Set Method, Schedule, Submit for Approval, Approval Inbox
- Procurement > RFQ: create, responses, evaluation, criteria setup
- Procurement > Tendering: initiation, opening, decrypt, committee, evaluations, roles, member response
- Procurement > Suppliers and Prequalification
- Procurement > Purchase Orders
- Procurement > Goods Receipts
- Procurement > Contracts (create, approve, link LPO, amend, terminate, execute)
- Procurement > Reports

#### Typical Workflows

1) Department Needs → Consolidated Plan

- Department raises needs (items, quantities, categories)
- Approver reviews Department Needs (approve/reject)
- Consolidation dashboard aggregates needs into a plan

2) Plan Setup & Method

- Plan Maintain: add/edit plan items
- Set Method: assign procurement methods to items
- Schedule: define timelines
- Submit for Approval: send plan for approvals
- Approval Inbox: approvers act on submitted plans

3) RFQ Management

- Create RFQ and Lines (link to requisitions if needed)
- Gather supplier responses (RFQ Responses)
- Evaluate responses, consolidate scores, and award

4) Tendering

- Initiate tender, setup criteria and sections
- Open bids, decrypt (as applicable)
- Form committee and record member responses
- Evaluate and consolidate bids
- Approve awards

5) Post-Award

- Create Purchase Orders (POs)
- Record Goods Receipts (GRNs)
- Manage contracts lifecycle (approval, amendments, execution monitoring)

#### Tips

- Prequalification rounds and supplier applications streamline supplier pools.
- Use Procurement Reports for oversight and compliance evidence.

---

### 4.4 Inventory

#### Overview

Manage item masters, SKUs, stores; inter-branch requisitions; transactions (receipts, transfers, adjustments,
approvals); stock take; stock consumption; UOM/UOM conversion; price management; reports.

#### Navigation

- Inventory > Item Master List, SKU
- Inventory > Item Category/Subcategory, Stores
- Inventory > Inter-Branch Requisition (+ Approval)
- Inventory > Transactions: Receipts, Transfers, Adjustments, Approvals
- Inventory > Stock Take, Stock Consumption
- Inventory > Unit of Measure, UOM Conversion
- Inventory > Price Management
- Inventory > Reports

#### Typical Workflows

1) Master Data Setup

- Define Item Categories/Subcategories
- Create Item Master List entries
- Create SKUs, assign to Stores

2) Inter-Branch Requisition

- Create requisition (source/destination, items)
- Submit; approval manager reviews and approves

3) Stock Transactions

- Receipts: record inbound stock
- Transfers: move stock between stores/branches
- Adjustments: correct stock levels (with reason)
- Approvals: review/approve transactions if required

4) Stock Take & Consumption

- Stock Take: create cycle/count, enter counts, finalize
- Consumption: issue and record consumption for departments or projects

5) Price Management

- Upload or edit item prices; export sample template; re-import updates

#### Tips

- Always align UOM and conversions before transactions
- Use store/location filters to find stock quickly

---

### 4.5 Property Management

#### Overview

Manage property registry and structure (blocks, floors, units), tenants and leases, billing/invoicing, receipts, ledger
and dashboard, maintenance requests and work completion, settings, and reports.

#### Navigation

- Property > Registry (Properties, Types, Categories, Attachments)
- Property > Structural Mapping (Blocks, Floors, Units)
- Property > Tenant & Lease (Tenant, Clearance, New Lease, Renew/Terminate, Schedule)
- Property > Billing & Receipting (Invoices, Receipts, Ledger, Dashboard)
- Property > Maintenance (Requests, Assign, Dashboard, Work Completion)
- Property > Settings
- Property > Reports

#### Typical Workflows

1) Register a Property

- Add Category & Type (once per organization)
- Add Property (address, locality, metadata)
- Upload Attachments (ownership docs, drawings)

2) Structure Mapping

- Add Blocks → Floors → Units (with sizes and attributes)

3) Tenant Lifecycle

- Add Tenant (KYC, contact, documents)
- Create New Lease (unit, terms, start/end, rent schedule)
- Renew or Terminate as needed
- Maintain Lease Schedule (generate invoices per schedule)

4) Billing & Receipting

- Create/Review Rent Invoices
- Post Receipts; print receipt PDF
- Use Tenant Ledger and Rent Dashboard for tracking

5) Maintenance Management

- Log Maintenance Request
- Assign to staff/contractors
- Track Dashboard, confirm Work Completion

#### Tips

- Ensure payment frequency and schedule settings align with lease terms.
- Use tenant statement PDF for audits and tenant communication.

---

### 4.6 Fleet Management

#### Overview

Vehicle registry and documents, driver management (permanent/contracted), assignments, trip logs, route planning,
inspections, maintenance schedules, service alerts and rules, fuel logs, running costs, GPS/telematics,
compliance/inspection schedule, settings (make/model), and reports.

#### Navigation

- Fleet > Vehicles, Documents, Assignments
- Fleet > Drivers & Contracted Drivers (licenses, assignments)
- Fleet > Trip Logs; Route Planner
- Fleet > Maintenance Schedule; Repair Logs
- Fleet > Service Alerts; Alert Rules
- Fleet > Fuel Logs; Fuel Types; Running Costs
- Fleet > Compliance > Inspection Schedule
- Fleet > Settings: Fleet Make/Model
- Fleet > Reports

#### Typical Workflows

1) Vehicle & Driver Setup

- Add Vehicle, upload documents
- Add Drivers and Contracted Drivers; track license validity

2) Trip Management

- Create Trip Log (vehicle, driver, route, purpose)
- Optionally use Route Planner for waypoints and optimization

3) Maintenance & Compliance

- Create Maintenance Schedule; update status (done/cancel)
- Record Repair Logs (normal/emergency)
- Acknowledge Service Alerts; configure Alert Rules
- Manage Inspection Schedule (type/date/compliance)

4) Costs & Fuel

- Record Fuel Logs (date, quantity, price)
- Record running costs (tyres, insurance, oil, etc.)

#### Tips

- Use “Available Vehicles/Drivers” helpers when assigning trips.
- Ensure Make/Model master data is maintained for consistency.

---

### 4.7 Document Management (DMS)

#### Overview

Centralized repositories, files, tags and tagging rules, bulk upload, permissions at file and repository level, legal
hold, search, and reports.

#### Navigation

- DMS > Repository (create, manage visibility/permissions, move)
- DMS > Files (upload, preview, check-out, download, tags)
- DMS > Search
- DMS > Bulk Upload
- DMS > Legal Hold
- DMS > Reports

#### Typical Workflows

1) Organize a Repository

- Create repository; set visibility and permissions
- Upload files; organize folders (if enabled)

2) Work with Files

- Preview or embed preview
- Check-out (lock), edit offline, check-in
- Move files, manage tags and tagging rules
- Download individual files or bulk via selections

3) Legal Hold

- Place legal hold on a set of files during litigation/compliance events
- Release hold when appropriate

#### Tips

- Use tags for quick retrieval and automated rule-based tagging.
- Activity views show file history for audit.

---

### 4.8 Legal

#### Overview

Manage legal documents, dispatches, execution logs; contracts; clauses; templates/drafts; disputes/cases with evidence,
counsels, outcomes; obligations and assignments; legal searches; loan securities; intellectual property and tracking;
comprehensive compliance suite (obligations, tasks, calendar, controls, incidents, filings, setup); and reports.

#### Navigation

- Legal > Documents (plus Dispatches & Execution Logs)
- Legal > Contracts (Maintenance)
- Legal > Clauses; Templates; Drafts
- Legal > Cases (with Evidence)
- Legal > Counsels; Outcomes
- Legal > Obligations (and Assignments)
- Legal > Search Requests (findings, approvals)
- Legal > Securities
- Legal > Intellectual Property; IP Tracking
- Legal > Compliance: Obligations, Tasks, Calendar, Controls, Incidents, Filings; Setup
- Legal > Reports

#### Typical Workflows

1) Contract Lifecycle

- Create Contract and related documents
- Add Clauses or Templates as building blocks
- Track Obligations from documents and assign to users

2) Disputes & Litigation

- Open Case; upload Evidence; assign Counsels; update Outcomes

3) Intellectual Property

- Maintain IP registry; raise disputes when conflicts arise

4) Compliance Management

- Create Obligations (regulatory) & assign tasks
- Maintain Calendar (filing dates, renewals) with alerts
- Record Controls and their Evidence
- Log Incidents and actions; view incident dashboards
- Filings: maintain templates and submit/record filings

#### Tips

- Use assignments and activity logs for accountability and audit trails.
- Compliance setup pages maintain master lists (bodies, areas, types).

---

### 4.9 Insurance (Bancassurance)

#### Overview

Referral intake and assignment; customer onboarding and communications; policy proposals, underwriting feedback,
issuance, renewals; premiums; claims assessment and closure with payments; commission rules/tiers and tracking;
insurers/products/riders; pricing rules; lifecycle controls; settings; and reports.

#### Navigation

- Insurance > Bancassurance > Referrals (create, assign, performance)
- Insurance > Customers (profile & KYC, listing, communications)
- Insurance > Policies (create, review, submit underwriting, feedback, issuance, register, renewals)
- Underwriting (review/submit)
- Premiums (CRUD, print receipt)
- Claims (index, assess, close; payments list/initiate)
- Commissions (rules, tiers, earned, payouts)
- Insurers & Mapped Products; Products & Riders; Pricing rules
- Lifecycle; Settings; Reports

#### Typical Workflows

1) From Referral to Policy

- Intake referral; assign to an officer
- Create or link Customer profile (KYC)
- Create Policy Proposal; submit for underwriting; capture feedback
- Issue policy upon approval; add endorsements as needed
- Manage renewals (upcoming renewals list)

2) Premiums & Receipts

- Record premium payments and print receipts

3) Claims

- List claims; open assessment; record findings
- Close claims when resolved; manage claim payments

4) Commissions

- Define Commission Rules and Tiers
- View earned commissions; post payouts and view history

#### Tips

- Pricing rules can be tied to products/providers for accurate premium calculation.
- Use portfolio view under Customer to see holdings.

---

### 4.10 HRM (Human Resource Management)

#### Overview

Manage departments, employees, and internal committees.

#### Navigation

- HRM > Departments
- HRM > Employees
- HRM > Employee Internal Committees

#### Typical Workflows

1) Maintain Department Structure

- Create/edit departments that other modules reference

2) Manage Employees

- Create employees; update data; manage status
- Add/remove employees to internal committees

---

### 4.11 Finance

#### Overview

General Ledger; Accounts Payable/Receivable; Bank Management (banks, accounts, cashbook, transfers, transactions,
cheques/chequebooks); tax configuration and reports; integrations (PO→Invoice; salary journal templates); petty cash;
approvals/posting; analytics reports.

#### Navigation

- Finance > GL: Journal Entry/Batch; Recurrent/Reversing Journals; Ledger Reports; Trial Balance
- Finance > AP: Vendor Master; Invoice Entry; Payment Vouchers; Payment Processing; Aging
- Finance > AR: Customer Master; Invoice Generation; Receipts Posting; Credit Management; Aging; Statements
- Finance > Bank Management: Banks, Branches, Bank Accounts, Cashbook, Transfers, Transactions, Cheques/Books
- Finance > Tax: Jurisdictions, Types, Rules, Summary; e-Filing
- Finance > Integrations: PO→Invoice sync; Salary Journal Templates
- Finance > Petty Cash: Floats; Pettycash Vouchers (disbursement, replenishment, refund)
- Finance > Reports

#### Typical Workflows

1) GL Journals

- Create manual Journal Entry; post upon approval
- Schedule Recurrent Journals; create Reversing Journals as needed

2) Payables (AP)

- Enter Supplier Invoices (link to PO/GRNs where applicable)
- Generate Payment Vouchers and process payments
- Manage Credit/Debit Notes and Approvals

3) Receivables (AR)

- Generate Customer Invoices
- Post Receipts; view Customer Statements

4) Bank Management

- Maintain banks/branches/accounts
- Cashbook: create payment/receipt, post/void
- Bank Transfers and Bank Transactions (post/void)
- Chequebooks: manage leaves; issue/receive; deposit/clear/bounce/cancel

5) Tax

- Setup jurisdictions/types/rules; view tax summary report

6) Petty Cash

- Setup floats; create vouchers; post/void; submit/approve/reject; wizard-assisted replenishment

#### Tips

- Use mapping previews for auto-GL postings
- Use drilldowns (e.g., AR aging) for analysis

---

### 4.12 Budget Line & Analytics

#### Overview

Budget setup and structure; GL mappings; activities; rates; budgeting workspace (periods, projections, entry by GL
line); approvals; consolidation; top-down allocation; re-allocation tools; analytics dashboards; CBS data sync and logs;
reports.

#### Navigation

- Budget > Budget Lines, GL Mapping, Activities Master, Rates
- Budget > Periods, Projections, Entry by GL Line
- Budget > Submit for Approval; Approvals
- Budget > Consolidation; Top-Down Allocation
- Budget & Analytics > Reallocation; Limits
- Budget > Analytics Dashboards & Data Exports
- Budget > Admin & Integration: CBS Sync, Data Sync Logs
- Budget > Reports

#### Typical Workflows

1) Setup

- Define Budget Lines & map to GL
- Create Activities Master; set Rates

2) Budgeting Workspace

- Create Budget Period; define scenarios
- Enter Projections (form-based or import where enabled)
- Post GL line entries for granular control

3) Approvals and Consolidation

- Submit for Approval; approvers review and decide
- Consolidate approved budgets; perform top-down allocation if required

4) Reallocation & Limits

- Use Reallocation tool to move budget between lines within rules
- Define Ledger Limits to control spending authority

5) Analytics

- Use dashboards for trends, performance, risk indicators
- Export analytics data if available

#### Tips

- Ensure GL mapping is complete before entering projections
- Use department/branch filters in dashboards

---

### 4.13 Settings

#### Overview

User and access management, branches, code lists, integrations, teams, meeting rooms.

#### Navigation

- Settings > Users, Roles, Branches
- Settings > Lists (Code Lists & Orders), Localities, Currencies
- Settings > Integrations (system-level)
- Settings > Teams & Team Users
- Settings > Meeting Rooms

#### Typical Tasks

- Create Users; assign Roles; manage branch roles
- Maintain code lists and locality data for lookup fields
- Configure system integrations (where supported)
- Manage Teams and bulk notifications

---

### 4.14 My Account

#### Overview

Personal workspace including schedule and tickets.

#### Navigation

- My Account > Schedule (meetings/calls)
- My Account > Tickets
- Global: Help

#### Typical Tasks

- Review upcoming activities
- Check and update tickets assigned to you
- Access help pages

---

## Appendix A: Detailed Module Procedures

### A.1 Procurement

This chapter provides end-to-end, procedural guidance for all Procurement functions. Menu names are shown as navigation
hints; route names (in italics) are included where helpful.

#### A.1.1 Roles & Permissions

- Ensure your user has Procurement roles with permissions for: Department Needs, Plan Maintain, Set Method, Schedule,
  Submit/Approve, RFQs/Evaluations, Tendering, Suppliers, Purchase Orders, Goods Receipts, Contracts, Reports.
- Approvers must have appropriate approval permissions (Department Needs approvals; Plan approvals; Awards; PO
  approvals, etc.).

#### A.1.2 Department Needs (Raise Needs and Approvals)

Navigation: Procurement > Department Needs  (departmental plan)

Raise Needs (Department Users)

1) Go to Procurement > Department Needs (List)  (route: procurementdepartmentalplan.index)
2) Click Create to open the new Department Need page  (procurementdepartmentalplan.create)
3) Fill Header fields: Department, Financial Year/Period, Priority, Description.
4) Add Lines: select Category/Subcategory, Item, Quantity, UOM, Estimated Cost. Save each line.
5) Save draft (procurementdepartmentalplan.store). You can re-open and edit lines (updateLine) or delete the draft (
   destroy).
6) View lines: procurementdepartmentalplan.view; Data table: procurementdepartmentalplan.data.

Submit For Approval

7) When complete, click Submit for Approval (as configured by your process) or notify approver to review via Department
   Need Approval.

Approval (Approvers)

8) Go to Procurement > Approvals > Department Need Approval (department-need-approval.index)
9) Open a need (show) to review header and lines; check attachments and comments.
10) Approve or Reject (update/destroy) with remarks. The status updates and the need becomes available for
    consolidation.

Troubleshooting

- If items are missing, ensure Item Master/Category/Subcategory exist (Inventory module).
- If approval buttons are missing, verify your role has approval permissions.

#### A.1.3 Plan Maintain and Consolidation

Navigation: Procurement > Plan

Plan Maintain (Planners)

1) Go to Procurement > Plan Maintain (procurementplanmaintain.index)
2) Create Draft Plan (procurementplanmaintain.store)
3) Add/Update Items in the plan (edit). Import from Department Needs via dedicated actions where provided (
   planning.editDraft / planning.updateDraftItems).
4) Use “Planning > Edit Draft Items” to bulk edit draft line items.
5) Save frequently. Use “Show” (procurementplanmaintain.show) for a summary.

Consolidated Dashboard

6) Go to Procurement > Dashboard (dashboard.index)
7) Review consolidated needs; drill to specific items (dashboard.show)
8) Export consolidated data (dashboard.export) for management review.

Map to Budget/Timelines/Execution

9) Map to Budget (maptobudget.*): assign budget lines or cost centers as per finance guidelines.
10) Define Timelines (plantimeline.*) and Calendar-based plans (calenderbased.*) to sequence procurement events.
11) Monitor execution (executiondashboard.*) to track progression from plan to completion.

#### A.1.4 Set Method

Navigation: Procurement > Plan > Set Method

1) Open Set Method (procurement-set-method.index)
2) Select a Plan. Load plan items (getPlanItems) and assign procurement methods per line (e.g., Open Tender, RFQ, Direct
   Purchase).
3) Save. Ensure methods align with policy and thresholds.

#### A.1.5 Plan Schedule

Navigation: Procurement > Plan > Schedule

1) Open Schedule (Procurement-Plan-Schedule.index)
2) Create schedule for a Plan (create): set milestones, due dates, responsibilities.
3) Review/Edit schedule entries (edit/view). Save.

#### A.1.6 Submit Plan for Approval & Approval Inbox

Navigation: Procurement > Plan > Submit for Approval; Procurement > Plan > Approval Inbox

1) Submit: open Submission (Procurement-Plan-Submission.index), select plan, review summary, and Submit (update).
2) Approvers: open Approval Inbox (approvalinbox.*) or Planning > Approval (planning.approval.*) to fetch plan details (
   getPlanDetails) and Submit Decision (submitDecision).
3) Approved plans progress to execution; rejected plans return with comments.

#### A.1.7 RFQs: Creation, Supplier Responses, Evaluation, Award

Navigation: Procurement > RFQs

Setup Lines & Create RFQ

1) Create RFQ (rfqs.create) and fill header (title, reference, close date, evaluation method, currency).
2) Add Lines (RFQLinesController: rfqlines.create/store) or via line categories (linecategories.store) and requisition
   categories endpoints.
3) Optionally fetch requisition items to prefill RFQ lines.

Supplier Responses (Internal Recording/Portal Intake)

4) View RFQ Responses list (rfqresponses.index). Create/record responses received (store/edit/update/destroy) as
   applicable.
5) Fetch RFQ Suppliers (getSuppliers) to confirm invited vendors.
6) Get related requisition items (rfqs/{rfqId}/requisition-items) if needed.

Evaluation & Consolidation

7) Open Evaluation dashboard (evaluations.index/create). Capture scores/criteria per supplier.
8) Use Consolidated view (evaluations.consolidated) to compare and finalize recommendations.
9) Award supplier (evaluations.award) for selected RFQ.

Committee & Governance (RFQ)

10) Manage RFQ Committee (rfqcommittee.store and related views). Configure evaluation setup (
    rfqcriteriasetup.evaluations / save).

#### A.1.8 RFQ Criteria & Sections Setup (Evaluation Design)

Navigation: Procurement > RFQ Criteria Setup

1) Define Sections (rfqsettingsections.index/store/show/update/destroy).
2) Define Criteria (rfqsettingcriterias.*) and attach to sections.
3) Use Evaluation Setup page to align weightings and scoring rules (rfqcriteriasetup.evaluations).
4) Save evaluation configuration to apply across RFQs.

#### A.1.9 Tendering: Initiation to Award

Navigation: Procurement > Tendering

Tender Setup

1) Initiate Tender (initiatetender.*): create tender, define category (tendercategory.*), type (tendertype.*),
   evaluation criteria (tenderevaluations.*).
2) Submit for Initiation Approval as required (initiateapprove.*).
3) Approve/Reject Tender (tender.approve/tender.reject).

Opening & Decryption

4) Conduct Opening (tenderopening.*) to log submission openings.
5) Decrypt (tenderdecrypt.*) when applicable to reveal technical/financial envelopes securely.

Committee & Roles

6) Manage Tender Committee (tendercommittee.*); add members (tendercommittee.save).
7) Record Member Responses (memberresponse.*). Assign evaluator roles (assignrole.*).

Evaluation & Scores

8) Define criteria (evaluationcriteria.*) and record bid evaluations (bidevaluation.*).
9) Evaluators Dashboard (evaluationdashboard.*) and Consolidated Scores (bidscores.*). Drilldown: bidscores.drilldown.
10) Bid Responsiveness (bidresponsiveness.*) including bulk updates.

Awards

11) View/Manage Awards (procawards.*); unified award views (awards.unified). Approve/Reject awards (
    awards.approve/awards.reject).

#### A.1.10 Prequalification & Supplier Management

Navigation: Procurement > Suppliers; Procurement > Prequalification

Suppliers

1) Manage Suppliers list (suppliers.*) including create/edit/show.

Prequalification

2) Manage Prequalification Criteria (preqcriteria.*) and Sections.
3) Applications/Evaluations (preqevaluation.*), with Evaluator-only submissions (middleware role:evaluator).
4) Evaluation Approval (preqevalapproval.*) and Prequalified Suppliers (preqsuppliers.*).
5) Public/API rounds (“prequalification.rounds.*”) available for portal/frontend integration.

#### A.1.11 Purchase Orders (PO)

Navigation: Procurement > Purchase Orders

1) List POs (purchaseOrder.index). Create/Link RFQ (purchaseOrder.linkRFQ; getRFQItems).
2) Approvals: submit approval (purchaseOrder.approval) and approve (purchaseOrder.approve).
3) Fetch Suppliers (purchaseOrder.getSuppliers). Save and print as needed.

#### A.1.12 Goods Receipts

Navigation: Procurement > Goods Receipts

1) View GRNs (procurementreceipts.index). Create GRN (create) and Save (store).
2) Fetch Lines by GRN/PO (fetchLinesByGRN). Update Line (updateLine) and Post (postReceipt).
3) Delete erroneous GRN (destroy). Ensure GRN aligns with delivered quantities.

#### A.1.13 Contracts & Lifecycle

Navigation: Procurement > Contracts

1) List Contracts (contracts.index). Create (contracts.create/store) and View (contracts.view/edit/update).
2) Approval Queue (contracts.approve_index) and Approval submission (contracts.approve/submit).
3) Link LPO (contracts.lpo.link) to tie award to purchasing.
4) Lifecycle: View/Amend/Terminate/Execute (contractcycle.*) including submit of amendments/termination.

#### A.1.14 Reports

Navigation: Procurement > Reports

1) Open Reports index (procurement-reports.index) and choose a report.
2) Export using procurement-reports.export with selected format.

#### A.1.15 Tips & Best Practices

- Maintain master data (items, categories, suppliers) before starting.
- Align procurement method thresholds with policy; ensure documentation at each stage.
- Use committee and evaluation configuration to standardize scoring.
- Keep audit trail via comments, attachments, and reports.

### A.2 Inventory

Covers Item Master, SKU, Stores, Inter-Branch Requisitions, Transactions (Receipts, Transfers, Adjustments, Approvals),
Stock Take, Stock Consumption, UOM/Conversions, Price Management, and Reports.

#### A.2.1 Master Data: Categories, Subcategories, Item Master, SKU, Stores

Navigation: Inventory > Item Category/Subcategory; Item Master List; SKU; Stores

1) Item Categories (itemcategory.*): create/edit/delete categories used for grouping.
2) Subcategories (itemsubcategory.*): create/edit/delete subcategories; link to categories.
3) Item Master List (itemmasterlist.*): create items with descriptions, default UOM, tracking options.
4) SKU (sku.*): create stock keeping units; assign item, store, current price, and min/max.
5) Stores (stores.*): create/edit stores and assign to branches.

#### A.2.2 Inter-Branch Requisition & Approval

Navigation: Inventory > InterBranch Requisition; InterBranch Requisition Approval

1) Create Requisition (interbranchrequisition.create/store): choose source/destination branch/store; add
   items/quantities.
2) Auxiliary data: get categories/subcategories/items by branch; get item codes and details via helper routes.
3) Edit/Show/Destroy as needed; submit for processing.
4) Approval (interbranchrequisitionapproval.index/submit): approver reviews and decides (approve/reject) in bulk or per
   item.

#### A.2.3 Inventory Transactions

Navigation: Inventory > Transactions

1) Receipts (transactionsreceipts.*): record inbound stock (transfer receipts, purchase receipts), add lines, update,
   post, and print if provided.
2) Transfers (transactionstransfers.*): create transfer document (source/destination), add lines; fetch requisitions by
   type; finalize.
3) Adjustments (transactionsadjustment.*): record positive/negative adjustments with reason; approval where enabled.
4) Approvals (transactionsapproval.*): approver processes pending transactions (approve/reject) and reviews detail
   pages.

#### A.2.4 Stock Take & Consumption

Navigation: Inventory > Stock Take; Stock Consumption

1) Stock Take (stocktake.*): start a count, add counted items/quantities, edit lines, submit finalization; branch/store
   filters help scope.
2) Stock Consumption (stockconsumption.*): record issues to departments/projects; supports fetching stores/UOM options.

#### A.2.5 UOM and Conversions

Navigation: Inventory > Unit of Measure; UOM Conversion

1) UOM (unitofmeasure.*): define base UOMs.
2) UOM Conversion (uomconversion.*): define conversion factors between UOMs.

#### A.2.6 Price Management

Navigation: Inventory > Price Management

1) Manage prices (pricemanagement.*): create/edit item prices; import via template (download sample), upload CSV/Excel.

#### A.2.7 Reports

Navigation: Inventory > Reports

1) Choose report and export (inventory-reports.*).

Tips

- Keep master data clean and consistent (UOMs, stores, items) to avoid transaction errors.
- Use branch/store filters to narrow working context.

### A.3 Property Management

Manages Property Registry/Structure, Tenants & Leases, Billing & Receipting, Maintenance, Settings, and Reports.

#### A.3.1 Registry and Structural Mapping

Navigation: Property > Registry; Structural Mapping

1) Categories/Types (propertycategory.* / propertytype.*): maintain master lists.
2) Property Registry (PropertyRegistry.*): create property with locality and metadata; upload attachments.
3) Structure: Blocks (addblock.*), Floors (addfloor.*), Units (addunit.*). Use helper routes to fetch blocks/floors by
   property.

#### A.3.2 Tenants & Leases

Navigation: Property > Tenant & Lease

1) Tenants (addtenant.*): create tenant profiles with KYC; update as needed.
2) Lease Maintenance (addlease.*): create lease for a unit, set terms and rent schedule; edit/renew/terminate via
   dedicated pages.
3) Lease Schedule (schedulelease.*): generate invoices per schedule; fetch tenant property/lease via helpers; print
   schedule.
4) Renewals (renewlease.*): manage lease renewals; fetch property/lease by tenant.
5) Termination (terminatelease.*): create termination request with end date/remarks.
6) Tenant Clearance (tenantclearance.*): finalize tenant exit workflow.

#### A.3.3 Billing & Receipting

Navigation: Property > Billing & Receipting

1) Rent Invoices (rentinvoice.*): create/update/delete invoices; review list.
2) Rent Receipts (rentreceipt.*): record tenant payments; view amount paid so far (amountPaid); print receipt (pdf
   route).
3) Tenant Ledger (tenantledger.*): view ledger; export PDF (tenantledger.pdf).
4) Rent Dashboard (rentdashboard.*): KPIs and trends.

#### A.3.4 Maintenance & Work Completion

Navigation: Property > Maintenance

1) Maintenance Requests (maintenancerequest.*): log new request; attach details; edit/update.
2) Assign (assignrequest.*): assign requests to staff/contractor; track progress.
3) Dashboard (maintenancedashboard.*): monitor status.
4) Work Completion (workcompletion.*): record completion details and close.

#### A.3.5 Settings & Reports

1) Settings (propertysettings.*): manage property/unit related settings.
2) Reports (property-reports.*): export via reports pages.

Tips

- Ensure schedules match lease payment frequency to avoid invoice gaps.
- Use attachments for proof of ownership, inspections, and handover forms.

### A.4 Fleet Management

Covers Vehicles, Documents, Assignments, Drivers (permanent/contracted), Licenses, Trip Logs, Route Planner, Maintenance
Schedules, Repair Logs, Service Alerts, Alert Rules, Fuel Logs, Running Costs, Compliance/Inspection, Settings, Reports.

#### A.4.1 Vehicle & Document Management

Navigation: Fleet > Vehicles; Documents

1) Vehicles (fleet.vehicles.*): create/edit vehicles; deactivate when retired; models-by-make helper.
2) Vehicle Documents (fleet.documents.*): upload insurance, logbook, service docs.
3) Assignments (fleet.assignments.*): assign vehicle to drivers/departments.

#### A.4.2 Driver Management & Licenses

Navigation: Fleet > Drivers; Contracted Drivers; License Tracking

1) Drivers (fleet.drivers.*) and Contracted Drivers (fleet.contracted_drivers.*): CRUD, deactivate contracted drivers.
2) Driver Assignments (fleet.driver_assignments.*): assign drivers; (un)assign inspections.
3) License Tracking (fleet.licenses.* / contracted_driver_licenses.*): monitor expiry; update renewals.

#### A.4.3 Trip Logs & Route Planner

Navigation: Fleet > Trip Logs; Route Planner

1) Trip Logs (fleet.trip_logs.*): create trips, fetch available vehicles/drivers.
2) Route Planner (route_planner.*): plan routes and waypoints for optimization.

#### A.4.4 Maintenance, Repairs, Service Alerts

Navigation: Fleet > Maintenance Schedule; Repair Logs; Service Alerts

1) Maintenance Schedule (fleet.maintenance_schedule.*): create/edit/cancel; track.
2) Repair Logs (fleet.repair_logs.*): record repairs; show/edit/destroy.
3) Service Alerts (fleet.alerts.*): view and acknowledge; Alert Rules (fleet.alert_rules.*): define thresholds (
   mileage/time).

#### A.4.5 Fuel & Running Costs; GPS & Telematics

Navigation: Fleet > Fuel Logs; Fuel Types; Running Costs; GPS/Telematics

1) Fuel Logs (fleet.fuel_logs.*): record fueling events.
2) Fuel Types (fueltypes.*): manage list.
3) Running Costs (fleet.running_costs.*): log recurring costs.
4) GPS (fleet.gps.*): live dashboard and movement history.
5) Telematics (fleet.telematics.*): device registry.

#### A.4.6 Compliance/Inspection

Navigation: Fleet > Compliance > Inspection Schedule

1) Inspection Schedule (fleet.inspection_schedule.*): plan and track inspections; edit/update; destroy old entries.

#### A.4.7 Reports

Navigation: Fleet > Reports (fleet-reports.*)

Tips

- Keep driver license and insurance trackers updated to avoid compliance breaches.
- Use available drivers/vehicles helpers to avoid clashes.

### A.5 Document Management (DMS)

Central repository with file-level operations, tagging, permissions, legal hold, search, and reports.

#### A.5.1 Repository & Files

Navigation: DMS > Repo; Files

1) Repository (repo.*): create, manage visibility and permissions (repo.visibility, repo-permissions), move files across
   repositories (repo-move).
2) Files (files.*): upload, preview (file.preview/embed-preview), check-outs, moves, downloads; manage document tags (
   document-tags.*).

#### A.5.2 Tagging & Search

Navigation: DMS > File Tags; Search

1) Tags (file-tags.*): manage tags; Tagging Rules (tagging-rules.*) to automate.
2) Search (dms.search): global search across repos/files.

#### A.5.3 Legal Hold

Navigation: DMS > Legal Hold

1) Create legal hold; add/remove files; release (legal-hold.release).

#### A.5.4 Reports

Navigation: DMS > Reports (dms-reports.*)

Tips

- Use check-out for version-safe edits and audit.
- Employ tagging rules for classification consistency.

### A.6 Legal

Manages legal documents, contracts, clauses, templates/drafts, cases, evidence, counsels, outcomes,
obligations/assignments, search requests, securities, IP & tracking, and extensive compliance (obligations, tasks,
calendar, controls, incidents, filings, setup), plus reports.

#### A.6.1 Documents & Contracts

Navigation: Legal > Documents; Contracts

1) Documents (documents.*): CRUD; nested Dispatches and Execution Logs.
2) Contracts (maintenance.*): manage contract records.
3) Clauses (clauses.*), Templates (templates.*), Drafts (drafts.*): maintain content libraries.

#### A.6.2 Cases & Disputes

Navigation: Legal > Cases

1) Cases (cases.*): create and manage; add Evidence (cases.evidence.*); Counsels (disputes.counsels.*); Outcomes (
   disputes.outcomes.*).

#### A.6.3 Obligations & Assignments

Navigation: Legal > Obligations

1) Obligations (obligations.*): main list; getObligations for details; assign users via obligations.assignments.*
2) Document-level obligations (documents.obligations.*) tie obligations to source documents.

#### A.6.4 Search Requests & Securities

Navigation: Legal > Search Requests; Securities

1) Search Requests (search_requests.*): submit and store findings/approval status (store_findings.*).
2) Loan Securities (securities.*): maintain collateral/legal security records.

#### A.6.5 Intellectual Property & IP Tracking

Navigation: Legal > Intellectual; IP Tracking

1) IP (intellectual.*): manage IP assets; raise disputes (intellectual.raiseDispute).
2) IP Tracking (ip-tracking.*): monitor status/actions of IP cases.

#### A.6.6 Compliance Suite

Navigation: Legal > Compliance (Obligations, Tasks, Calendar, Controls, Incidents, Filings); Setup

1) Compliance Obligations (legal.compliance.obligations.*) & tasks (legal.compliance.tasks.*)
2) Compliance Calendar (legal.compliance.calendar.*)
3) Compliance Controls (legal.compliance.controls.*) and upload evidence
4) Incidents (legal.compliance.incidents.*) + dashboard
5) Filings (legal.compliance.filings.*) + Templates
6) Setup (legal.setup.*): regulatory bodies, areas, control types, severity levels, filing types, formats, policy
   categories, training types

#### A.6.7 Reports

Navigation: Legal > Reports (legal-reports.*)

Tips

- Use assignments and calendar to ensure obligations are tracked and executed.
- Evidence uploads are key for audits/compliance.

### A.7 Insurance (Bancassurance)

Manages referrals, customers & KYC, policies (proposal → underwriting → issuance → renewals), premiums, claims (
assessment, closure, payments), commissions (rules/tiers/earned/payouts), providers/products/riders, pricing, lifecycle,
settings, reports.

#### A.7.1 Referrals & Assignment

Navigation: Insurance > Bancassurance > Referrals

1) Create referral (create/store); assign (assign/list) to officers; view performance.

#### A.7.2 Customers & Communications

Navigation: Insurance > Customers

1) Customers (bancassurance.customers.*): check for existing, create, edit; portfolio view; referral defaults.
2) Beneficiaries (bancassurance.customers.beneficiaries.*): add beneficiaries.
3) Communications (bancassurance.customers.communication.*): log calls, emails, visits, sms.

#### A.7.3 Policy Lifecycle

Navigation: Insurance > Policies; Underwriting

1) Policies (bancassurance.policies.*): create proposals; review list; submit for underwriting (submitUnderwriting);
   capture feedback; issue (storeIssuance); endorsements; renewals.
2) Underwriting (bancassurance.underwriting.*): review/submit decisions.

#### A.7.4 Premiums & Receipts

Navigation: Insurance > Premiums

1) Premiums (bancassurance.premiums.*): CRUD; print receipts.

#### A.7.5 Claims & Payments

Navigation: Insurance > Claims; Claims Payments

1) Claims (bancassurance.claims.*): index, assess, update; close via closure flows; initiate payments in Claim Payments.
2) Claim Payments (bancassurance.claims.payments.*): list unpaid approved claims; store payments.

#### A.7.6 Commissions

Navigation: Insurance > Commissions

1) Rules (commissions.rules.*) and Tiers (bancassurance.commissions.tiers.*): configure structure.
2) Earned (bancassurance.commissions.earned.*) and Payouts (bancassurance.commissions.payouts.*): track and process
   payments.

#### A.7.7 Providers, Products, Riders, Pricing, Lifecycle, Settings

1) Providers (bancassurance.insurers.*): CRUD, manage mapped products, detach.
2) Products (bancassurance.products.*) & Riders (bancassurance.riders.*): manage offerings.
3) Pricing (bancassurance.pricing.*): pricing rules per product/provider.
4) Lifecycle (bancassurance.lifecycle.*): toggle status.
5) Settings (bancassurance.settings.*): system settings for insurance.

#### A.7.8 Reports

Navigation: Insurance > Reports (insurance-reports.*)

Tips

- Keep underwriting feedback loops tight to speed issuance.
- Use portfolio view for cross-sell/upsell insights.

### A.8 HRM

Manages departments, employees, and internal committees.

#### A.8.1 Departments & Employees

Navigation: HRM > Departments; Employees

1) Departments (departments.*): maintain org structure.
2) Employees (employees.*): CRUD employee records; manage statuses.

#### A.8.2 Internal Committees

Navigation: HRM > Employee Internal Committees

1) Manage committee membership (employeescommittee.*); bulk remove endpoint provided (employeescommittee.remove).

Tips

- Keep departments current to power filters and reporting in other modules.

### A.9 Finance

Comprehensive finance operations: GL, AP, AR, Banks (accounts, cashbook, transfers, transactions, cheques/books), Tax,
Integrations, Petty Cash, and Reports.

#### A.9.1 Chart of Accounts & Segments

Navigation: Finance > COA/Segments

1) Segments (segments.* / coasegment.*): define segment order, length, and values; helpers to save orders and GL-type
   mappings.
2) Chart of Accounts (chartofaccounts.*): create GL accounts; view dynamic/ledger reports.
3) GL Mapping (glpostingmap.*): map operational transaction types to GL; fetch transaction types and list GL accounts.

#### A.9.2 General Ledger

Navigation: Finance > GL

1) Journal Entry (journalentry.*): create/post journals; reversing/recurrent journals (reversingjournal.*,
   recurrentjournal.*).
2) Ledger reports (ledgerreporting.*, ledgerreport.*), Trial Balance (trialbalance.*).

#### A.9.3 Accounts Payable (AP)

Navigation: Finance > AP

1) Invoice Entry (invoiceentry.*): create invoices; fetch POs/GRNs; save; approve/reject.
2) Credit/Debit Notes (creditnote.*, debitnote.*)
3) Payment Vouchers (paymentvoucher.*): approve/reject; post in Payment Processing.
4) Payment Processing (paymentprocessing.*): index/create/voucher view; post vouchers (voucher.post).

#### A.9.4 Accounts Receivable (AR)

Navigation: Finance > AR

1) Invoice Generation (invoicegeneration.*): create/post; approve/reject.
2) Receipts Posting (receiptsposting.*): index/create/store; find customers API; AR drilldowns.
3) Customer Master/Statement (customermaster.*, customerstatement.*).

#### A.9.5 Bank Management

Navigation: Finance > Bank, Bank Branch, Bank Account Setup, Cashbook, Transfers, Transactions, Cheques/Books

1) Banks (finance.bank.*) & Branches (finance.bankbranch.*) including by-bank lists and CRUD.
2) Bank Accounts (finance.bankaccountsetup.*)
3) Cashbook (cashbook.*): create payment/receipt; post/void; mapping preview; specialized create flows.
4) Bank Transfers (banktransfers.*): full CRUD; post/void.
5) Bank Transactions (banktransactions.*): full CRUD; post/void.
6) Chequebooks (chequebooks.*) and Cheques (cheques.*): manage leaves and lifecycle (deposit, clear, bounce, cancel);
   spoil leaves as needed.

#### A.9.6 Tax Management

Navigation: Finance > Tax

1) Jurisdictions (taxjurisdiction.*), Types (taxtypes.*), Rule Config (taxruleconfig.*), Summary (taxsummaryreport.*),
   eFiling (efiling.*), Return Generator (taxreturngenerator.*), GL Mapping (taxglmapping.*).

#### A.9.7 Petty Cash

Navigation: Finance > Petty Cash

1) Petty Cash Floats (pettyfloats.*): setup floats.
2) Petty Cash (pettycash.*): disbursement/replenishment/refund; post/void; submit/approve/reject; replenishment wizard.

#### A.9.8 Integrations

Navigation: Finance > Integrations

1) PO→Invoice Sync (integration.po_invoice_sync.*)
2) Salary Journal Templates (salary-journal-templates.*)

#### A.9.9 Reports

Navigation: Finance > Reports (finance-reports.*)

Tips

- Use posting/approval flows consistently for audit readiness.
- Segment/GL mapping consistency is critical for accurate reporting.

### A.10 Budget Line & Analytics

End-to-end budgeting: setup & mapping, workspace (periods, projections, entry by GL line), approvals, consolidation,
top-down allocation, reallocation, limits, analytics, admin & integration, reports.

#### A.10.1 Setup & Structure

Navigation: Budget > Setup & Structure

1) Budget Lines (budgetlinemapping.*) and GL Mapping (budgetglmapping.*)
2) Activities Master (activitymaster.*)
3) Rates (rates.*)

#### A.10.2 Budgeting Workspace

Navigation: Budget > Workspace

1) Periods (budgetperiod.*): create period; attach GLs; delete attachments/budget as needed.
2) Activities (budgetactivities.*)
3) Projections (budgetprojections.*): store/delete projections; get product types per budget line.
4) Entry by GL Line (entrybyglline.*) with GL View.

#### A.10.3 Approvals, Consolidation, Top-Down

Navigation: Budget > Submit for Approval; Approvals; Consolidation; Top-Down Allocation

1) Submit (submitapproval.*); Approvals (budgetapproval.*) with approve/reject.
2) Consolidation (budgetconsolidation.*); Top-Down Allocation (topdownallocation.*) including display helper.

#### A.10.4 Reallocation & Limits

Navigation: Budget & Analytics > Reallocation; Limits

1) Reallocation (budgetandanalytics.reallocation.*): index/create/store/allocate; AJAX endpoints for budget-lines and
   budget-line.details.
2) Limits (budgetandanalytics.limits.*): index/create/store; show/update limits; optional edit/update/destroy routes.

#### A.10.5 Analytics & Admin

Navigation: Budget > Analytics & Admin

1) Dashboards: analyticsdashboard, kpidashboards, trendsdashboards; BI reports (e.g., branchperformance,
   productprofitability, etc.).
2) Admin/Integration: cbssync, datasynclogs.

#### A.10.6 Reports

Navigation: Budget > Reports (budgetline-reports.*)

Tips

- Establish mapping and activities first to avoid rework later.
- Use reallocation and limits to control budget governance post-approval.

### A.11 Settings

Manages users, roles, branches, code lists, currencies, localities, integrations, teams, team members, meeting rooms.

#### A.11.1 Users & Roles

Navigation: Settings > Users; Roles

1) Users (users.*): CRUD; user-level actions (activities, password reset/sync, SMS).
2) Roles (roles.*): CRUD; AJAX show; map permissions as needed.
3) User Roles by Branch (user_roles.*): assign/remove via dedicated routes.

#### A.11.2 Lists & Localities

Navigation: Settings > Lists

1) Currencies (currencies.index)
2) Code Lists (code-lists.*) with order endpoint (code-lists.order)
3) Localities (localities.*) and select2 endpoint (locality.select2)

#### A.11.3 Teams & Meetings

Navigation: Settings > Teams; Meeting Rooms

1) Teams (teams.*) and Team Users (team-users.*)
2) Team/Users Bulk Notification endpoints provided
3) Meeting Rooms (meeting-room.*)

#### A.11.4 Branches & Integrations

Navigation: Settings > Branches; Integrations

1) Branches (branches.*)
2) Integrations (settings.integrations) and POST IntegrationController

Tips

- Keep roles minimal and permission-scoped; use team messaging for internal comms.

### A.12 Third Party (External Parties & Supplier Portal)

#### A.12.1 Parties (Internal Web)

Navigation: Third Party > Parties

1) Parties (thirdparty.parties.*): list/create/edit/delete; bulk actions.

#### A.12.2 Supplier Portal (API-driven)

1) Auth: third-party-auth login/register, verify, resend verification, logout.
2) Third Party CRUD (third-parties.*), Bank Details, Categories (with middleware for approved users).
3) Procurement Supplier APIs: supplier categories, suppliers, RFQ invitations/responses/clarifications.

Tips

- Ensure third-party user Active/Approved flags are set for access.

### A.13 CRM (Customer Relationship Management)

Detailed front-office operations: calls, contacts, tickets, clients, leads, email, marketing, feedback, product
development, debt collection, boards, reports.

#### A.13.1 Calls & Contacts

Navigation: CRM > Call; Contact

1) Calls: start incoming calls; mark unreachable or reschedule.
2) Contacts: view/add calls and emails; attach to leads/clients; manage unattached contacts.

#### A.13.2 Tickets

Navigation: CRM > Tickets

1) Tickets: create/update; watchers; comments; upload docs; workflows/activities; resolve/restore.

#### A.13.3 Clients & Leads

Navigation: CRM > Clients; Leads

1) Clients: summary, activities, relations, discussions; client contacts; feedback; tickets/tasks; schedule (
   meetings/calls); notes/email/sms.
2) Leads: onboarding; status; activities; watch/assign; schedule; contacts; mail/sms; tasks/tickets; products;
   analytics.

#### A.13.4 Email & Boards

Navigation: CRM > Email; Board

1) Emails: conversations summary; reply drafts; edit drafts; attachments; send drafts.
2) Boards/Committees: board meetings, notifications, SMS; manage committees.

#### A.13.5 Marketing & Feedback

Navigation: CRM > Marketing; Feedback

1) Competitors: products/descriptions, LLM fetch
2) Campaigns: approvals, workflow, submit, contacts, progress
3) Lists: upload, filters, leads/clients membership
4) Planner: global/branch/manager/CEO planners; activities calendar; workflows; documents
5) Socials: manage social posts and comments
6) Feedback: surveys, questions/answers, submissions; reviews

#### A.13.6 Product Development & Debt Collection

Navigation: CRM > Product Development; Debt Collection

1) Product Dev: comments, features, submit, (dis/en)able comments, workflows, activities, document uploads
2) Debt Collection: loan assignments/tasks; schedule; collaterals/guarantors (sms/email); messaging

#### A.13.7 Base Utilities

Navigation: CRM > Base

1) Products select2; documents; tasks; notes; discussions; SMS summary

#### A.13.8 Reports

Navigation: CRM > Reports (crm-reports.*)

Tips

- Use select2 endpoints for fast entity lookup.
- Leverage planners to coordinate field activities.

## 5. Troubleshooting & FAQs

- I cannot see a module or menu: Check with admin if your role grants access.
- I cannot edit or submit: The record might be in a locked/approved state; check status or permissions.
- File upload fails: Check file size/type; try again or contact support.
- Approval not visible: Ensure the item was submitted and you are an approver.

## 6. Glossary

- RFQ: Request for Quotation
- GRN: Goods Receipt Note
- COA: Chart of Accounts
- UOM: Unit of Measure
- DMS: Document Management System

## 7. Change Log

- 1.0: Initial comprehensive user manual covering all modules.
