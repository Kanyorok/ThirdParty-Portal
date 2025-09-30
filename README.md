### Licensing (module-based, Ed25519)

This build includes a signed licensing system bound to DB instance.

Setup env:

```
LICENSING_PUBLIC_KEY_BASE64=BASE64_VENDOR_PUBLIC_KEY
LICENSING_PUBLIC_KEY_ID=vendor-key-1
```

Key commands:

```
php artisan license:info
php artisan license:verify --refresh
php artisan license:import payload.json signature.b64 --id=LIC-001 --kid=vendor-key-1
```

Admin UI:
- GET /admin/license (upload payload+signature)

Route guards:
- Global `license` middleware added to authenticated groups
- Per-module middleware `module:{ModuleID}` wraps module routes (CRM=200000, Procurement=300000, Inventory=400000, Property=500000, Fleet=600000, DMS=700000, Legal=800000, Insurance=900000, HRM=1000000, Finance=1100000, Budget=1200000)

# Br ERP

**Br ERP** is a powerful and scalable Enterprise Resource Planning (ERP) system developed by **Craft Silicon**. It is designed to optimize and streamline business processes by integrating various organizational resources and operational modules into a single, unified system.

Built on top of the modern **Laravel Framework** (v11.37.0), Br ERP offers a robust backend architecture combined with a user-friendly and intuitive frontend experience. The system is tailored for enterprises of all sizes, addressing diverse needs with configurable modules.

---

## Features

Br ERP provides a comprehensive suite of features covering all critical business functions:

### Core ERP Modules:

1. **Human Resources (HR) Module**:
    - Employee management (profiles, attendance, leave tracking)
    - Payroll management
    - Recruitment and applicant tracking
    - Performance evaluations

2. **Finance and Accounting**:
    - General ledger, accounts payable (AP), and accounts receivable (AR)
    - Invoicing and billing
    - Budgeting and expense tracking
    - Comprehensive financial reporting and analytics

3. **Inventory and Warehouse Management**:
    - Stock control and tracking
    - Warehouse organization and management
    - Supplier and purchase order management
    - Barcode and RFID inventory options

4. **Procurement and Vendor Management**:
    - Vendor records and relationship management
    - Purchase order (PO) automation
    - Product sourcing and supplier catalog integrations
    - Approval workflows for procurement

5. **Sales and Customer Relationship Management (CRM)**:
    - Lead tracking and opportunity management
    - Sales pipeline and quotation management
    - Order management and invoicing
    - Customer communication (email, phone, chat logs)

6. **Manufacturing and Production Planning**:
    - Bill of materials (BOM)
    - Production order and job scheduling
    - Demand forecasting and planning
    - Quality control and compliance management

7. **Project Management**:
    - Task assignments, progress tracking
    - Time tracking for employees
    - Cost estimation and budget adherence
    - Gantt charts and milestone setting

8. **Supply Chain Management (SCM)**:
    - Real-time tracking of supply chain activities
    - Logistics and transportation management
    - Vendor collaboration portals
    - Optimization for timely delivery

9. **Reports and Analytics**:
    - Comprehensive dashboard for KPI monitoring
    - Customizable reports for operational, financial, and resource analysis
    - Predictive analytics and actionable insights
    - Export reports via Excel, CSV, or PDF

10. **Compliance and Audit Management**:
    - Document and policy management
    - Audit trail tracking
    - Industry-specific compliance support

### 11. **Third-party Portal Endpoints**

| Method  | Endpoint                                                  |
|---------|-----------------------------------------------------------|
| POST    | `/api/third-parties`                                      |
| POST    | `/api/third-parties-bank-details`                         |
| DELETE  | `/api/third-parties-bank-details/{third_parties_bank_detail}` |
| PUT     | `/api/third-parties/{third_party}`                        |
| DELETE  | `/api/third-parties/{third_party}`                        |
| POST    | `/api/third-parties/{third_party}/approve`               |
| POST    | `/api/third-parties/{third_party}/reject`                |
| PATCH   | `/api/third-parties/{third_party}/status`                |
| POST    | `/api/third-party-auth/login`                            |
| POST    | `/api/third-party-auth/logout`                           |
| POST    | `/api/third-party-auth/register`                         |
| POST    | `/api/third-party-categories`                            |
| DELETE  | `/api/third-party-categories/{third_party_category}`     |
| GET     | `/api/third-party-profile`                                |
| PUT     | `/api/third-party-profile`                                |
| DELETE  | `/api/third-party-profile`                                |
---

### Additional Features:
- Modular and Scalable Architecture: Easily customizable to suit specific business needs.
- Role-Based Access Control (RBAC): Enhanced security through granular user permissions.
- Multi-Currency and Multi-Language Support: For businesses operating globally.
- Automated Workflow: Task automation through cron-job scheduling.
- Real-Time Notifications: Alerts for operational events and issues.
- Seamless API Support: Integrations with external services.

---

## Important Notes

### Synonyms to be Created

To ensure proper interaction with the **Banking Realm Core** system, the following SQL synonyms must be created in your
database. Synonyms map tables from different databases/schemas for easier access and maintenance. Below is an example of
some of the synonyms that need to be established:

``` sql
CREATE SYNONYM [dbo].[syn_t_AdvancesReport] FOR [BRNET_IMARISHA_REPORTS].[dbo].[t_AdvancesReport];

CREATE SYNONYM [dbo].[syn_t_ImageAccount] FOR [ImarishaImagesDB_UAT].[dbo].[t_ImageAccount];

CREATE SYNONYM [dbo].[syn_t_AccountGuarantor] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_AccountGuarantor];

CREATE SYNONYM [dbo].[syn_t_AccountCollateral] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_AccountCollateral];

CREATE SYNONYM [dbo].[syn_t_Collateral] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_Collateral];

CREATE SYNONYM [dbo].[syn_t_AccountCustomer] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_AccountCustomer];

CREATE SYNONYM [dbo].[syn_t_AccountTrx] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_AccountTrx];

CREATE SYNONYM [dbo].[syn_t_SystemBranchSetting] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_SystemBranchSetting];

CREATE SYNONYM [dbo].[syn_t_SystemBranchStatus] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_SystemBranchStatus];

CREATE SYNONYM [dbo].[syn_t_Client] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_Client];

CREATE SYNONYM [dbo].[syn_t_ClientCorporate] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_ClientCorporate];

CREATE SYNONYM [dbo].[syn_t_ClientIndividual] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_ClientIndividual];

CREATE SYNONYM [dbo].[syn_t_CollateralType] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_CollateralType];

CREATE SYNONYM [dbo].[syn_t_Product] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_Product];

CREATE SYNONYM [dbo].[syn_t_SystemCodeDetail] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_SystemCodeDetail];

CREATE SYNONYM [dbo].[syn_csb_t_User] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_User];

CREATE SYNONYM [dbo].[syn_t_ClientIntroducer] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_ClientIntroducer];

CREATE SYNONYM [dbo].[syn_t_ClientRelation] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_ClientRelation];

CREATE SYNONYM [dbo].[syn_t_UserCodeDetail] FOR [BRNET_IMARISHA_LIVE_255].[dbo].[t_UserCodeDetail];

```

## Technology Stack

### Backend:
- **Framework**: Laravel (v11.37.0)
- **Database**: SQL Server (sqlsrv)

### Other Key Dependencies:
- **Mockery**: For testing
- **Guzzle**: For handling HTTP requests and API integrations
- **Faker**: For generating dummy data during development
- **Monolog**: For advanced logging
- **Symfony Mailer**: For emails
- **Laravel Sanctum**: For secure API authentication
- **Barryvdh/DomPDF**: For PDF generation
- **Dragonmantank/Cron-Expression**: For managing complex task schedules

---

## Installation Guide

### Requirements:
- PHP 8.3 or higher
- Composer (PHP dependency manager)
- SQL Server instance for the database

---

## Contributors

- **[Mureithi Maina](https://github.com/mureithimaina/)**:
- **Robert**
- **Eric**
- 
---

## License

**Br ERP** is a proprietary software product developed and owned by **Craft Silicon**. It is subject to the following licensing terms:

### Key Points:
1. **Proprietary License**:
    - The application is licensed, not sold, and remains the exclusive intellectual property of **Craft Silicon**.
    - Users are granted non-transferable rights to use the software under specific terms defined in the licensing agreement.

2. **Restrictions**:
    - Modifying, decompiling, reverse-engineering, redistributing, or reproducing parts of this software in any form without explicit written permission from **Craft Silicon** is prohibited.
    - The software is not open source.

3. **Legal Compliance**:
    - This software complies with **Kenyan Laws** and adheres to international copyright and intellectual property laws.
    - Customers are required to abide by both their local regulations as well as provisions outlined under **Kenyan law**.

4. **Acceptance**:
    - Use of this software constitutes acceptance of the End-User License Agreement (EULA).

For further details about the licensing terms, or to purchase a license, please contact **Craft Silicon** at the provided contact information.

---

## Copyright

**©2025-Present Craft Silicon. All Rights Reserved.**

This software and its documentation are copyrighted materials, protected under:
1. **Kenya's Copyright Act (Cap 130)**.
2. Relevant international copyright laws, including but not limited to:
    - The **Berne Convention for the Protection of Literary and Artistic Works**.
    - The **Universal Copyright Convention (UCC)**.
    - The **WIPO Copyright Treaty**.

Any unauthorized use, copying, or distribution is punishable as per applicable Kenyan and international laws.

---

## Contact

For licensing, support, and inquiries, reach out to **Craft Silicon**:

- **Website**: [https://craftsilicon.com](https://craftsilicon.com)
- **Email**: support@craftsilicon.com
- **Location**: Nairobi, Kenya
