# BR CRM - Banking CRM System

## Introduction

Welcome to **BR CRM**, a powerful Customer Relationship Management (CRM) system specifically designed to integrate
seamlessly with the **Banking Realm Core** banking system. This application enhances client engagement by offering tools
to manage various business processes, including tickets, campaigns, surveys, leads, and feedback across multiple banking
channels such as **mobile** and **internet**.
BR CRM streamlines customer interactions, providing a unified platform to engage with clients and analyze their needs,
enabling banks to deliver better services and improve overall customer satisfaction.

## Key Features

- **Client Management**: Handle all customer information and their related interactions with ease.
- **Ticket Management**: Track and resolve client issues using a robust ticketing system.
- **Campaigns**: Plan, execute, and monitor campaigns aimed at enhancing client relationships and engagement.
- **Surveys**: Collect valuable insights through custom surveys to assess client satisfaction and preferences.
- **Leads Management**: Streamline operations by efficiently managing leads and turning them into opportunities.
- **Feedback from Channels**: Gather and manage feedback from various channels, including mobile and internet banking
  platforms.
- **Data Integration**: Designed to integrate directly with the **Banking Realm Core** banking system for a cohesive
  customer database and operational flow.

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

Make sure to adjust the database and schema names to reflect your environment configuration.

1. Install PHP dependencies:

```bash
composer install
```

2. Install Node.js dependencies:

```bash
npm install
```

3. If you need Puppeteer for tasks like browser automation, make sure to install it as well:

```bash
npm i puppeteer --save
```

4. Configure the environment file (`.env`) with the necessary database and queue settings.
5. Run migrations (if required):

```bash
php artisan migrate
```

6. Create the required synonyms in your SQL Server using the provided syntax.
7. Start the development server:

```bash
php artisan serve
```

## Contributing

We welcome contributions to enhance **BR CRM**. If you'd like to contribute:

1. Fork the repository.
2. Create your feature branch:

``` bash
   git checkout -b feature/YourFeatureName
```

3. Commit your changes:

``` bash
   git commit -m "Add your message here"
```

4. Push to your branch:

``` bash
   git push origin feature/YourFeatureName
```

5. Open a pull request.

