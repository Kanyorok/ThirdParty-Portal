# Tender API Contracts

This document captures the current tender-related API surface and request/response contracts as implemented in the codebase. It is derived from routes and controllers, not assumptions.

## Auth And Routing Context
- `routes/api.php` is mounted under `/api` with the `api` middleware group.
- `routes/portal.php` is included under `/api/v1/portal` (see `routes/api.php` and `bootstrap/app.php`).
- Portal routes use `auth.thirdparty` middleware (token-based) and operate on `ThirdPartyUser` identities.
- Some tender endpoints are intentionally public or semi-public (no auth middleware), based on current routing.

## Tenders
### List Tenders
- Method: `GET`
- Path: `/api/tenders`
- Auth: none (semi-public; visibility filtering occurs inside the controller)
- Query params:
`status` optional, friendly or code
`tenderType` optional
`search` optional
`third_party_id` optional
- Response: `200`
```
{
  "success": true,
  "message": "Tenders retrieved successfully.",
  "data": [ /* Tender with relations */ ]
}
```
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:29`

### Show Tender
- Method: `GET`
- Path: `/api/tenders/{id}`
- Auth: none (visibility enforced in controller)
- Response: `200`
```
{
  "success": true,
  "message": "Tender details retrieved successfully.",
  "data": { /* Tender with relations */ },
  "invitation_status": "pending|accepted|declined|null"
}
```
- Response: `403` if restricted and not invited
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:137`

### Create Tender
- Method: `POST`
- Path: `/api/tenders`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Body:
`title` string required
`tender_type` string required (`op`, `rs`, `Open`, `Restricted`)
`tender_category_id` int required
`item_category_id` int required
`submission_deadline` date required
`opening_date` date required (after submission_deadline)
`currency_id` int required
`scope_of_work` string optional
`instructions` string optional
`procurement_mode_id` int optional
`start_date` date optional
`status` string optional
- Response: `201`
```
{
  "success": true,
  "message": "Tender created successfully.",
  "data": { /* Tender */ }
}
```
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:324`

### Update Tender
- Method: `PUT`
- Path: `/api/tenders/{id}`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Body: same fields as create, all optional
- Response: `200`
```
{
  "success": true,
  "message": "Tender updated successfully.",
  "data": { /* Tender */ }
}
```
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:398`

### Delete Tender
- Method: `DELETE`
- Path: `/api/tenders/{id}`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Response: `200` or `500`
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:398`

### Add Tender Item
- Method: `POST`
- Path: `/api/tenders/{tenderId}/items`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Body:
`qty_to_tender` number required
`item_id` int optional (`t_Items.Id`)
`manual_description` string optional
`plan_item_id` int optional
`item_category_id` int optional
`source_type` string optional (`PLAN` or `MANUAL`)
`remarks` string optional
- Response: `201`
```
{ "message": "Tender item added successfully.", "data": { /* TenderItem */ } }
```
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:466`

### Delete Tender Item
- Method: `DELETE`
- Path: `/api/tenders/{tenderId}/items/{itemId}`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Response: `200` or `404`
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:531`

### Add Supplier To Tender
- Method: `POST`
- Path: `/api/tenders/{tenderId}/suppliers`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Body:
`supplier_id` int required (`t_Suppliers.Id`)
- Response: `201`
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:324`

### Remove Supplier From Tender
- Method: `DELETE`
- Path: `/api/tenders/{tenderId}/suppliers/{supplierId}`
- Auth: `auth:sanctum` + `VerifiedUser` group
- Response: `200`
- Source: `app/Http/Controllers/Procurement/TenderApiController.php:370`

## Tender Invitations
### Supplier Invitations (API)
- Method: `GET`
- Path: `/api/procurement/tender-invitations`
- Auth: `auth:sanctum` + `VerifiedUser`
- Query params:
`page` optional
`limit` optional
`third_party_id` optional (debug/admin override)
- Response: `200`
```
{ "data": [ { "invitation": { /* fields */ }, "tender": { /* fields */ } } ], "total": 0, "page": 1, "limit": 10 }
```
- Source: `app/Http/Controllers/Procurement/TenderInvitationController.php:81`

### Update Invitation (API)
- Method: `PUT`
- Path: `/api/procurement/tender-invitations/{id}`
- Auth: `auth:sanctum` + `VerifiedUser`
- Body:
`responseStatus` string required (`accepted|declined|pending`)
`declineReason` string optional
- Response: `200`
- Source: `app/Http/Controllers/Procurement/TenderInvitationController.php:263`

### Supplier Invitation Response (Portal)
- Method: `POST`
- Path: `/api/v1/portal/supplier/tenders/respond`
- Auth: `auth.thirdparty`
- Body:
`tender_id` int required
`response_status` string required (`accepted|declined|pending`)
`decline_reason` string optional
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/TenderInvitationResponseApiController.php`

## Tender Clarifications
### Submit Clarification
- Method: `POST`
- Path: `/api/tender-clarifications`
- Auth: none (current routing)
- Body:
`tender_id` int required
`question` string required
`is_public` boolean optional
`third_party_id` int optional
- Response: `201`
- Source: `app/Http/Controllers/API/Procurement/TenderClarificationApiController.php:16`

### List Clarifications
- Method: `GET`
- Path: `/api/tender-clarifications`
- Auth: none (current routing)
- Query params:
`tender_id` required
`third_party_id` optional
`supplier_id` optional
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/TenderClarificationApiController.php:144`

### List Pending Clarifications
- Method: `GET`
- Path: `/api/tender-clarifications/pending`
- Auth: none (current routing)
- Query params:
`tender_id` optional
`third_party_id` optional
`supplier_id` optional
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/TenderClarificationApiController.php:262`

### Respond To Clarification
- Method: `PUT`
- Path: `/api/tender-clarifications/{id}/respond`
- Auth: none (current routing)
- Body:
`answer` string required
`is_published_to_all` boolean optional
`responded_by` string required
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/TenderClarificationApiController.php:264`

### Portal Clarifications
- Method: `GET`
- Path: `/api/v1/portal/supplier/tender-clarifications`
- Auth: `auth.thirdparty`
- Method: `POST`
- Path: `/api/v1/portal/supplier/tender-clarifications`
- Auth: `auth.thirdparty`
- Source: `routes/portal.php:110`

## Bid Submissions
### Create Bid Submission
- Method: `POST`
- Path: `/api/bid-submissions`
- Auth: none (current routing)
- Body:
`tender_id` int required
`bid_amount` number required
`currency` string required
`validity_period` int required
`delivery_period` int required
`status` string required (`draft|submitted`)
`payment_terms` string optional
`bid_documents[]` files
`supplier_id` int optional
`third_party_id` int optional
- Response: `201` or `200` (if draft update)
- Source: `app/Http/Controllers/API/Procurement/BidSubmissionApiController.php:26`

### List Supplier Bids
- Method: `GET`
- Path: `/api/bid-submissions`
- Auth: none (current routing)
- Query params:
`third_party_id` optional
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/BidSubmissionApiController.php:621`

### Get Existing Bid For Tender
- Method: `GET`
- Path: `/api/bid-submissions/existing`
- Auth: none (current routing)
- Query params:
`tender_id` required
`third_party_id` optional
- Response: `200`
- Source: `app/Http/Controllers/API/Procurement/BidSubmissionApiController.php:518`

### Legacy Bid Submission
- Method: `POST`
- Path: `/api/bid-submissions/legacy`
- Auth: none (current routing)
- Body:
`tender_id` int required
`third_party_id` int required
`bid_documents[]` files required
`submission_notes` string optional
- Response: `201`
- Source: `app/Http/Controllers/API/Procurement/BidSubmissionApiController.php:357`

### Portal Bid Submission
- Method: `GET`
- Path: `/api/v1/portal/supplier/bid-submissions`
- Auth: `auth.thirdparty`
- Method: `POST`
- Path: `/api/v1/portal/supplier/bid-submissions`
- Auth: `auth.thirdparty`
- Source: `app/Http/Controllers/API/Procurement/TenderSubmissionApiController.php`

## RFQ Supplier API (Related)
- RFQ invitation listing: `GET /api/procurement/rfq-suppliers`
- RFQ invitation detail: `GET /api/procurement/rfq-suppliers/{rfq}`
- RFQ response: `POST /api/procurement/rfq-responses`
- RFQ clarifications: `GET /api/procurement/rfq-clarifications/{rfq}` and `POST /api/procurement/rfq-clarifications`
- Source: `app/Http/Controllers/API/Procurement/SupplierRFQController.php`
