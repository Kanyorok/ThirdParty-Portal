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
This manual explains how to use the BRERP system across all modules. It provides step-by-step workflows and guidance for end-users and power users.

### Audience and Prerequisites
- End users performing day-to-day operations in modules
- Supervisors/approvers responsible for reviewing and approving tasks
- Administrators managing users, roles, and system setup

You should have an active user account and the correct roles assigned by your administrator.

### System Overview
BRERP is a modular ERP composed of functional domains (modules). Each module contains related features surfaced via the left-side navigation. Permissions control access to specific pages and actions.

### Roles and Permissions
BRERP uses role-based access control. Your role determines what you can see and do. If you cannot access a page or action described here, contact an administrator to review your role assignments under Settings > Roles/Users.

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
Manage external parties (suppliers) and third-party users. External users can authenticate, maintain profiles, and participate in procurement workflows.

#### Navigation
- Web: Third Party > Parties (list, create, edit, delete)
- API (for supplier portal processes): handled by your integration/frontend portal (login/register, bank details, categories)

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
Covers calls, contacts, tickets, clients, leads, accounts, marketing (campaigns, lists, planner, socials), feedback (surveys, reviews), email conversations, product development, debt collection, and reports.

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
Covers department needs entry and consolidation; plan setup, scheduling, submission, and approvals; suppliers and prequalification; tendering; RFQs and evaluation; awards; purchase orders; goods receipts; contracts lifecycle; reports.

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
Manage item masters, SKUs, stores; inter-branch requisitions; transactions (receipts, transfers, adjustments, approvals); stock take; stock consumption; UOM/UOM conversion; price management; reports.

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
Manage property registry and structure (blocks, floors, units), tenants and leases, billing/invoicing, receipts, ledger and dashboard, maintenance requests and work completion, settings, and reports.

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
Vehicle registry and documents, driver management (permanent/contracted), assignments, trip logs, route planning, inspections, maintenance schedules, service alerts and rules, fuel logs, running costs, GPS/telematics, compliance/inspection schedule, settings (make/model), and reports.

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
Centralized repositories, files, tags and tagging rules, bulk upload, permissions at file and repository level, legal hold, search, and reports.

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
Manage legal documents, dispatches, execution logs; contracts; clauses; templates/drafts; disputes/cases with evidence, counsels, outcomes; obligations and assignments; legal searches; loan securities; intellectual property and tracking; comprehensive compliance suite (obligations, tasks, calendar, controls, incidents, filings, setup); and reports.

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
Referral intake and assignment; customer onboarding and communications; policy proposals, underwriting feedback, issuance, renewals; premiums; claims assessment and closure with payments; commission rules/tiers and tracking; insurers/products/riders; pricing rules; lifecycle controls; settings; and reports.

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
General Ledger; Accounts Payable/Receivable; Bank Management (banks, accounts, cashbook, transfers, transactions, cheques/chequebooks); tax configuration and reports; integrations (PO→Invoice; salary journal templates); petty cash; approvals/posting; analytics reports.

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
Budget setup and structure; GL mappings; activities; rates; budgeting workspace (periods, projections, entry by GL line); approvals; consolidation; top-down allocation; re-allocation tools; analytics dashboards; CBS data sync and logs; reports.

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