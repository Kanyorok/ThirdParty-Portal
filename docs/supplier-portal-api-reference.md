# Supplier Portal API Reference

> Frontend production contract for the supplier portal.
>
> Base URL: `/api/v1`
>
> Auth: `Authorization: Bearer {token}`

## Scope

This document is a production-oriented frontend reference for the supplier portal API.

It does four things:

1. Normalizes response and error conventions for frontend consumption.
2. Corrects or flags inconsistent areas in the raw backend contract.
3. Provides a tighter TypeScript model section for implementation.
4. Adds frontend integration notes so the portal can be built safely against an imperfect backend contract.

Where the backend is inconsistent, this document does not invent new server behavior. Instead, it defines the safest frontend-facing contract and explicitly notes backend caveats.

---

## Response Conventions

The backend currently returns multiple success envelope styles. For production frontend code, treat them as belonging to the following families.

### Canonical Success Shape

Use this as the normalized shape in frontend code after adapter/parsing:

```ts
interface ApiSuccess<T> {
  ok: true
  message?: string
  data: T
}
```

### Canonical Error Shape

Use this as the normalized shape in frontend code after adapter/parsing:

```ts
interface ApiFailure {
  ok: false
  message: string
  error?: string
  errors?: Record<string, string[]>
  status?: number
}
```

### Raw Backend Success Families

The backend currently returns one of these patterns:

1. `success: true`
2. `status: "success"`
3. `valid: true`
4. `status: true`
5. payloads with `message` and `data` but no explicit boolean

Frontend code should normalize all of these into `ApiSuccess<T>` before they reach feature code.

### Raw Backend Validation Errors

Validation errors use Laravel-style `422` responses:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Frontend Rule

Do not let page components consume raw backend envelopes directly. Parse them once in the API layer and expose normalized results.

---

## Authentication

All auth routes are prefixed with `/portal/auth`.

### POST `/portal/auth/login`

Authenticate a user and receive a bearer token.

#### Request

```json
{
  "email": "john@acme.com",
  "password": "SecureP@ss1",
  "profile_type": "Supplier"
}
```

#### Frontend Requirement

The frontend should send `profile_type` explicitly for production use.

Reason: the backend contract indicates profile-based authorization (`PROFILE_NOT_AUTHORIZED`), and there is not enough evidence in the source contract that the server will always infer a default profile safely.

#### Success `200`

```json
{
  "success": true,
  "user": {
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "fullName": "John Doe",
    "email": "john@acme.com",
    "phone": "+254711111111",
    "gender": "Male",
    "imageId": null,
    "thirdPartyId": 42,
    "isActive": true,
    "emailVerified": true,
    "emailVerifiedOn": "2026-01-15 10:00:00",
    "isSupplier": true,
    "isTenant": false,
    "isCustomer": false,
    "createdOn": "2026-01-15 10:00:00",
    "modifiedOn": "2026-03-01 08:00:00",
    "thirdParty": {}
  },
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

#### Error Codes

| HTTP | `error` | Meaning |
|------|---------|---------|
| 401 | `INVALID_CREDENTIALS` | Wrong email or password |
| 403 | `EMAIL_NOT_VERIFIED` | Email not yet verified |
| 403 | `ACCOUNT_DISABLED` | Account deactivated |
| 403 | `ACCOUNT_NOT_APPROVED` | Account pending approval |
| 403 | `PROFILE_NOT_AUTHORIZED` | Requested `profile_type` is not allowed |

### GET `/portal/auth/me` 🔒

Get the current authenticated user.

### GET `/portal/auth/validate-token` 🔒

Validate that the current token is still active.

#### Backend Caveat

This endpoint returns `valid: true` instead of `success: true`. Normalize it in the API client.

### GET `/portal/auth/email/verify/{user}/{hash}`

Verify the email address.

#### Contract Caveat

The raw reference includes two suspicious fields:

1. `userIid` looks like a probable typo for `userId`
2. `email_verification_required: true` on a successful verification response appears semantically inverted

Frontend code should not depend on either field unless the backend team confirms them.

---

## Notifications

Prefix: `/portal`

### Supported Channels

The contract exposes these preference channels:

- `in_app`
- `email`
- `sms`

### Contract Caveat

The documented single-notification read endpoint only supports:

- `email`
- `sms`

```http
POST /portal/notifications/{type}/{id}/read
```

This means `in_app` is currently documented as a preference channel but not as a first-class readable notification resource.

### Frontend Rule

Until backend behavior is clarified:

1. Treat `in_app` as a preference capability.
2. Treat the notifications feed as readable if the endpoint returns it.
3. Do not assume `in_app` items can be individually marked read unless the backend exposes a documented route for that type.

### GET `/portal/notifications` 🔒

Returns the notifications feed, summary, and preferences.

### PUT `/portal/notifications/preferences` 🔒

Update channel and category preferences.

### POST `/portal/notifications/read-all` 🔒

Mark all currently supported notification resources as read.

---

## Naming Conventions

The backend mixes these conventions:

1. PascalCase fields such as `TradingName`, `TaxPIN`, `TenderNo`
2. snake_case fields such as `profile_type`, `created_at`
3. camelCase fields such as `firstName`, `fullName`, `createdOn`

### Frontend Rule

Keep raw transport types separate from UI/domain types.

Recommended approach:

1. Parse transport payloads exactly as received.
2. Map them into normalized frontend models in the API layer.
3. Do not spread raw API objects directly into components.

---

## TypeScript Types

The following types are intended for frontend implementation and fill gaps that were missing in the raw reference.

```ts
export interface ApiSuccess<T> {
  ok: true
  message?: string
  data: T
}

export interface ApiFailure {
  ok: false
  message: string
  error?: string
  errors?: Record<string, string[]>
  status?: number
}

export interface ThirdParty {
  id: number
  name: string
  tradingName?: string | null
  email?: string | null
  phone?: string | null
  physicalAddress?: string | null
  website?: string | null
  businessType?: CodeDetail | null
  country?: Country | null
  location?: Locality | null
  isSupplier?: boolean
  isTenant?: boolean
  isCustomer?: boolean
}

export interface AuthUser {
  id: number
  firstName: string
  lastName: string
  fullName: string
  email: string
  phone: string | null
  gender: string | null
  imageId: number | null
  thirdPartyId: number
  isActive: boolean
  emailVerified: boolean
  emailVerifiedOn: string | null
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  createdOn: string
  modifiedOn: string
  thirdParty: ThirdParty | null
}

export interface LoginResponse {
  success: boolean
  user: AuthUser
  token: string
  token_type: "Bearer"
}

export interface TicketStatus {
  code: "A" | "R" | "C" | "P"
  label: string
}

export interface TicketPriority {
  code: "N" | "L" | "U"
  label: string
}

export interface TicketCategory {
  id: number
  name: string
}

export interface TicketAssignee {
  type: "User" | "Team" | string
  name: string
}

export interface MentionUser {
  id: number
  name: string
  email: string
}

export interface TicketMessage {
  id: number
  body: string
  author: {
    type: "ThirdPartyUser" | "System"
    id: string | null
    name: string
    email: string | null
  }
  is_mine: boolean
  mentions: MentionUser[]
  created_at: string
}

export interface Ticket {
  id: string
  subject: string
  status: TicketStatus
  priority: TicketPriority
  category: TicketCategory | null
  assignee: TicketAssignee | null
  created_at: string
  updated_at: string
  messages?: TicketMessage[]
}

export interface RFQLine {
  id: number
  rfqLineNo: number
  itemName: string
  quantity: number
  uom: string
}

export interface RFQResponseItem {
  id: number
  rfqLineId: number | null
  description: string
  quantity: number
  unitPrice: number
  totalPrice: number
}

export interface RFQInvitation {
  id: number
  rfqNumber: string
  title: string
  status: string
  closingDate: string
  createdDate: string
  responseStatus: string | null
  currency: { id: number; code: string }
  rfqLines: RFQLine[]
  response?: {
    id: number
    status: string
    totalAmount: number
    items: RFQResponseItem[]
  }
}

export interface TenderItem {
  id: number
  itemDescription: string
  quantity: number | null
  uom: string | null
  estimatedPrice: number | null
}

export interface TenderDocument {
  Id: number
  DocumentId: string
  Name: string
  downloadUrl: string
}

export interface Tender {
  Id: number
  TenderNo: string
  Title: string
  TenderType: string
  TenderCategory: number | string
  ScopeOfWork: string | null
  Instructions: string | null
  SubmissionDeadline: string
  OpeningDate: string
  Status: string
  ProcurementModeId: number | null
  StartDate: string | null
  CurrencyId: number
  currency_code: string
  ApprovalRemarks: string | null
  ApprovalStatus: string | null
  ItemCategoryId: number | null
  CreatedOn: string
  ModifiedOn: string
  procurementMode: { Id: number; Description: string } | null
  tenderCategoryRelation: { ID: number; Description: string } | null
  itemCategoryRelation: { ID: number; Description: string } | null
  documents: TenderDocument[]
  items: TenderItem[]
}

export interface NotificationPreferences {
  channels: Array<"in_app" | "email" | "sms">
  muteAll: boolean
  categories: {
    prequalification: boolean
    tenders: boolean
    general: boolean
  }
}

export interface NotificationItem {
  id: string
  type: "email" | "sms" | "in_app"
  subject: string
  body: string
  read_at: string | null
  created_at: string
  meta: Record<string, unknown>
}

export interface PortalDocument {
  id: string
  name: string
  mimeType: string
  size: number | null
  source: "profile" | "rfq" | "rfq_response" | "tender" | "prequalification"
  sourceLabel: string
  createdOn: string
}

export interface DocumentPermissions {
  prequalification: { view: boolean; upload: boolean; download: boolean; delete: boolean }
  tender: { view: boolean; upload: boolean; download: boolean; delete: boolean }
  rfq: { view: boolean; upload: boolean; download: boolean; delete: boolean }
}

export interface AvailableProfile {
  type: "base" | "supplier" | "tenant" | "customer"
  label: string
  hasProfile: boolean
}

export interface Country {
  id: number
  name: string
  code: string
  flag?: string
}

export interface Locality {
  id: number
  name: string
}

export interface CodeDetail {
  id: number
  name: string
  value: string
}
```

---

## Frontend Integration Notes

### 1. Always Normalize at the API Layer

Create adapter functions for:

- auth payloads
- profile payloads
- notifications
- tenders
- RFQs

This is required because transport naming is inconsistent.

### 2. Treat Login as Profile-Aware

The portal UI should send `profile_type` explicitly when calling login.

Recommended values:

- `Supplier`
- `Tenant`
- `Customer`

Use exact values accepted by the backend.

### 3. Centralize Error Parsing

Handle these categories centrally:

1. validation errors (`422` with `errors`)
2. machine-code application errors (`error`)
3. transport/network errors
4. auth/session expiry

### 4. Do Not Depend on Undocumented Fields

Avoid shipping frontend logic that depends on:

- `userIid`
- `email_verification_required` on successful verification
- per-item read semantics for `in_app` notifications

Use them only after backend confirmation.

### 5. Keep Portal Permissions Explicit

For documents and procurement actions, the frontend should rely on the explicit permissions endpoints and route-level server responses, not UI assumptions.

### 6. Prefer Narrow Feature Types

Do not pass the large transport types directly into UI components. Create feature-specific mapped types such as:

- `CurrentUserViewModel`
- `TenderListItemViewModel`
- `RFQDetailViewModel`
- `NotificationListItemViewModel`

---

## Recommended Next Step

If the backend team is available, confirm these three items and update the contract immediately:

1. Whether `profile_type` is always required on login
2. Whether `userIid` is a typo
3. Whether `in_app` notifications support individual read operations
