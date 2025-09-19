## Technical Overview

### Purpose
This document summarizes the implemented functionalities across the system, organized by parent modules as defined in `database/seeders/ModuleSeeder.php`. It also outlines the main web UIs and API endpoints that back each module.

### Stack & Architecture
- Backend: Laravel (PHP)
- Auth: Laravel Sanctum for APIs; standard session auth for web
- RBAC: Spatie permissions (roles/permissions tables referenced in `ModuleSeeder`)
- Routing: Modularized per domain under `routes/*.php` and grouped by module prefixes
- Controllers: Organized in `app/Http/Controllers/<Module>/...`

### Parent Modules (from ModuleSeeder)
Listed with key features and primary route groupings.

---

### Third Party
- Description: Management of external parties and supplier-facing auth/profile flows.
- Web UI:
  - Prefix `thirdparty` in `routes/thirdparty.php`: `thirdparty.parties.*`
- APIs (routes/api.php):
  - `third-party-auth/*`: login/register/email verification; logout (auth:sanctum)
  - `third-parties/*`: register details, CRUD (auth:sanctum)
  - `third-parties-bank-details`: resource (auth:sanctum)
  - `third-party-categories`: resource (auth:sanctum, thirdparty.approved)
  - Enums and public lists: `v1/currencies`, `v1/countries`, `enums/third-party-types`

---

### CRM
- Description: Calls, contacts, tickets, clients, leads, accounts, marketing, feedback, product development, debt collection, email, boards, reports.
- Web UI (routes/crm.php, prefix `crm`):
  - Calls, Contacts, Tickets, Clients, Leads, Accounts
  - Marketing: Competitors, Campaigns, Lists, Planner, Socials
  - Feedback: Surveys, Reviews
  - Product Development
  - Debt Collection: lists, activities, schedules, notifications
  - Email conversations, drafts, attachments
  - Boards and committees
  - Reports: `crm-reports.*`

---

### Procurement
- Description: Department needs; plan consolidation, scheduling, submission/approval; suppliers; tendering; RFQs; evaluations; awards; purchase orders; goods receipts; reports.
- Web UI (routes/procurement.php, prefix `procurement`):
  - Department Needs, Plan Maintain, Set Method, Schedule, Submit & Approvals
  - Requisitions, Suppliers, Procurement Periods
  - RFQs (lines, responses, evaluation, criteria setup)
  - Tendering: initiation, opening, decryption, committee, member responses, assign roles
  - Evaluations, Bid responsiveness, Awards (incl. unified view)
  - Purchase Orders, Delivery Notes, Inspections, Receipts
  - Contracts lifecycle (create, approve, link LPO, amendments, terminate, execute)
  - Reports: `procurement-reports.*`
- APIs (routes/api.php):
  - Auth-protected procurement API under `procurement/*` for supplier categories, suppliers
  - Prequalification API: rounds, show, applications
  - Supplier RFQ: invitations, responses, clarifications

---

### Inventory
- Description: Item master; SKU; stores; inter-branch requisitions; transactions (receipts/transfers/adjustments/approvals); stock take/consumption; price management; UOM/UOM conversions; reports.
- Web UI (routes/inventory.php, prefix `inventory`):
  - Item Master List, SKU
  - Item Categories/Subcategories, Stores
  - Interbranch Requisition + Approval
  - Transactions: receipts, transfers, adjustments, approvals
  - Stock Take, Stock Consumption
  - UOM, UOM Conversion
  - Price Management
  - Reports: `inventory-reports.*`
- APIs:
  - `v1/inventory/item-categories` (public list)

---

### Property Management
- Description: Property registry and structure; tenants & leases; billing and receipting; maintenance; settings; reports.
- Web UI (routes/property.php, prefix `property`):
  - Registry: categories, types, properties, attachments
  - Structural mapping: blocks, floors, units
  - Tenants & Leases: new tenant, clearance, new/renew/terminate lease, lease schedule
  - Billing & Receipting: rent invoices, rent receipts, tenant ledger, rent dashboard
  - Maintenance: request, assign, dashboard, work completion
  - Settings: property settings
  - Reports: `property-reports.*`

---

### Fleet Management
- Description: Vehicle registry; driver management; trip logs; route planner; inspections; maintenance; service alerts and rules; running costs; GPS/telematics; insurance tracker; compliance; settings; reports.
- Web UI (routes/fleet.php, prefix `fleet`):
  - Route Planner, Trip Management, Vehicle Requests
  - Driver Management (permanent/contracted), assignments, license tracking
  - Vehicle Registry, Documents, Assignments
  - Maintenance Schedule, Repair Logs, Service Alerts, Alert Rules
  - Fuel Logs, Fuel Types, Running Costs
  - GPS live dashboard, movement history; Telematics devices
  - Compliance/Inspection schedule; Fleet Settings: Make/Model
  - Reports: `fleet-reports.*`

---

### Document Management (DMS)
- Description: Repository, files, tagging, bulk upload, permissions, legal hold, search, reports.
- Web UI (routes/dms.php, prefix `dms`):
  - Repository CRUD and visibility, file CRUD and permissions/move/download
  - Document preview/checkout/tags/activity, recent
  - Global search
  - Legal Hold (cases and held files)
  - Reports: `dms-reports.*`

---

### Legal
- Description: Legal documents; dispatches; execution logs; contracts; clauses; templates/drafts; cases (evidence/counsels/outcomes); obligations (and assignments); searches; loan securities; intellectual property (and disputes); compliance (obligations, tasks, calendar, controls, incidents, filings, setup); reports.
- Web UI (routes/legal.php, prefix `legal`):
  - Documents, Dispatches, Execution Logs
  - Contracts (maintenance), Clauses, Templates, Drafts
  - Cases with evidence; Counsels; Outcomes
  - Obligations (document-level and main list) with assignments
  - Search Requests (with findings), Loan Securities
  - Intellectual Property and IP Tracking (raise disputes)
  - Compliance: obligations, tasks, calendar, controls, incidents, filings
  - Compliance Setup: regulatory bodies, areas, control types, severity levels, filing types, file formats, policy categories, training types
  - Reports: `legal-reports.*`

---

### Insurance (Bancassurance)
- Description: Referrals; customers & KYC; policies (proposal, underwriting, feedback, issuance, renewals); premiums; claims (assessment, closure, payments); commissions (rules/tiers/earned/payouts); insurers & products & riders; pricing; lifecycle; settings; reports.
- Web UI (routes/insurance.php, prefix `insurance`):
  - Referrals & assignment/performance
  - Customers, beneficiaries, communications
  - Policies: create, review, submit for underwriting, feedback, issue, register, renewals
  - Underwriting: review/submit
  - Premiums: CRUD and receipt printing
  - Claims: list, assess, close; payments list/initiate
  - Commissions: rules, tiers, earned, payouts
  - Insurers: providers, mapped products, detach
  - Products & Riders; Pricing rules; Product lifecycle
  - Settings
  - Reports: `insurance-reports.*`

---

### HRM
- Description: Departments, employees, internal committees.
- Web UI (routes/hrms.php, prefix `hrm`):
  - Departments, Employees, Employee Committees (and remove)

---

### Finance
- Description: Chart of accounts and segments; general ledger (journals, reversing/recurrent); AR/AP (invoices, receipts, credit/debit notes, credit management); bank management (banks, branches, accounts, cashbook, transfers, transactions, cheques/chequebooks); tax (jurisdiction, types, rules, reporting); integrations; petty cash; posting actions; reports.
- Web UI (routes/finance.php, prefix `finance`):
  - GL: journal entry/batch, ledger reports, trial balance
  - AP: invoice entry, payment vouchers, payment processing
  - AR: invoice generation, receipts posting, customer master/statement
  - Banks: registry, bank branches, bank accounts, cashbook, transfers, transactions, cheques/chequebooks
  - Tax: jurisdictions, types, rules, summary
  - Integrations: PO→Invoice sync; salary journal templates
  - Petty Cash floats and vouchers (submit/approve/reject/post/void)
  - Reports: `finance-reports.*`

---

### Budget Line
- Description: Budget setup & mapping; budgeting workspace (periods, activities, projections, GL line entry); consolidation; top-down allocation; re-allocation; analytics & dashboards; admin & integration; reports.
- Web UI (routes/budget.php, prefixes `budget`, `budgetandanalytics/*`):
  - Setup & Structure: product master/type, budget lines & GL mapping, activities master, rates
  - Budgeting Workspace: periods, activities, projections, entry by GL line
  - Consolidation; Budget approvals; Top-down allocation
  - Re-allocation tools under `budgetandanalytics/reallocation`
  - Limits management under `budgetandanalytics/limits`
  - Analytics & BI dashboards and exports
  - Admin & Integration: CBS Sync and Data Sync Logs
  - Reports: `budgetline-reports.*`

---

### Settings
- Description: Users, roles, branches, codes/lists, integrations, teams, meeting rooms.
- Web UI (routes/web.php under `settings`):
  - `settings.lists`, currencies, code lists/order, localities
  - Users, roles (with AJAX show), branches, teams & team-users, meeting rooms
  - Integrations (view + POST)

---

### My Account
- Description: Personal workspace features exposed via existing controllers.
- Web UI:
  - `schedule.index` (CRM User schedule), `tickets.index`
  - Global `help` route

---

### Cross-cutting Endpoints & Middleware
- Auth-required web groups under `Route::middleware(['auth'])`.
- API auth via `auth:sanctum` and custom middleware: `VerifiedUser`, `WebsiteAuthMiddleware`, `ChannelAuthMiddleware`, `PBXAuthMiddleware`.
- Health check: `GET /api/health`.

### Data & Seeding
- Modules inserted into `t_Modules` by `ModuleSeeder` across the above domains. When seeding a fresh DB, existing roles/permissions are reset to align with seeded modules.

### Conventions
- Route names reflect module and feature scopes (e.g., `property-*`, `inventory-*`, `finance-*`).
- Controllers live under `app/Http/Controllers/<Module>/...` matching route namespaces.

### Where to Start
- Menus are driven by parent modules from `ModuleSeeder.php`.
- Browse per-module routes in `routes/*.php` for feature surfaces, and find implementation in the corresponding controller namespaces.

