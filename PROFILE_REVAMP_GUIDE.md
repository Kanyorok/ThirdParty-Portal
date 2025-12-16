# Profile Management Revamp Guide

## 🎯 Overview

This guide provides a complete revamp of the profile management system to match your Laravel backend API structure with a sleek, modern design.

## 📋 Backend API Structure (What We're Matching)

### Endpoints
```
POST /api/v1/portal/profiles/supplier
POST /api/v1/portal/profiles/tenant
POST /api/v1/portal/profiles/customer
GET  /api/v1/portal/profiles
```

### Request/Response Format

**Supplier Profile Request:**
```json
{
  "third_party_name": "Acme Corporation",
  "trading_name": "Acme Co.",
  "registration_number": "RC12345",
  "tax_pin": "A001234567Z",
  "kra_pin": "A001234567P",
  "physical_address": "123 Main St",
  "country": "Kenya",
  "city": "Nairobi",
  "website": "https://acme.com",
  "business_type": "Corporation",
  "supplier_categories": [1, 2, 5]
}
```

**Response Format:**
```json
{
  "success": true,
  "message": "Supplier profile created successfully",
  "data": {
    "third_party_id": 1,
    "third_party_name": "Acme Corporation",
    "is_supplier": true,
    "is_tenant": false,
    "is_customer": false,
    "approval_status": "pending",
    ...
  }
}
```

## 🔄 Changes Required

### 1. Update Type Definitions

**File:** `types/profile-management.ts`

✅ Already updated to match backend structure:
- Changed from `id` to `third_party_id`
- Changed from `companyName` to `third_party_name`
- Added `is_supplier`, `is_tenant`, `is_customer` flags
- Updated all field names to snake_case to match backend

### 2. Update Validation Schemas

**File:** `lib/validations/profile-schemas.ts`

Update field names to match backend:

```typescript
// OLD (camelCase)
companyName: z.string()...
tradingName: z.string()...

// NEW (snake_case)
third_party_name: z.string()...
trading_name: z.string()...
```

###  3. Update API Routes

**Files to Update:**
- `app/api/portal/profiles/supplier/route.ts`
- `app/api/portal/profiles/tenant/route.ts`
- `app/api/portal/profiles/customer/route.ts`

**Changes Needed:**
1. Update request body transformation (camelCase → snake_case)
2. Update response transformation (snake_case → camelCase if needed)
3. Handle `data` wrapper in response

**Example Update:**

```typescript
// app/api/portal/profiles/supplier/route.ts
export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions)
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  }

  const accessToken = (session as any).accessToken as string

  try {
    const body = await request.json()

    // Transform camelCase to snake_case if form uses camelCase
    const transformedBody = {
      third_party_name: body.thirdPartyName || body.third_party_name,
      trading_name: body.tradingName || body.trading_name,
      registration_number: body.registrationNumber || body.registration_number,
      tax_pin: body.taxPin || body.tax_pin,
      kra_pin: body.kraPin || body.kra_pin,
      physical_address: body.physicalAddress || body.physical_address,
      country: body.country,
      city: body.city,
      website: body.website,
      business_type: body.businessType || body.business_type,
      supplier_categories: body.supplierCategories || body.supplier_categories,
    }

    const response = await fetch(
      `${EXTERNAL_API_BASE}/api/v1/portal/profiles/supplier`,
      {
        method: "POST",
        headers: {
          "Authorization": `Bearer ${accessToken}`,
          "Accept": "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(transformedBody),
      }
    )

    if (!response.ok) {
      const errorData = await response.json()
      return NextResponse.json(
        { error: errorData.message || "Failed to create supplier profile" },
        { status: response.status }
      )
    }

    const data = await response.json()
    // Backend returns { success, message, data }
    return NextResponse.json({
      success: data.success,
      message: data.message,
      profile: data.data, // Extract profile from data wrapper
    })
  } catch (error) {
    return NextResponse.json(
      { error: "Failed to connect to backend" },
      { status: 500 }
    )
  }
}
```

### 4. Update Profile Management Store

**File:** `store/profile-management-store.ts`

Update to handle new data structure:

```typescript
// Update type checks
const getProfileType = (profile: Profile): ProfileType => {
  if (profile.is_supplier) return "supplier"
  if (profile.is_tenant) return "tenant"
  if (profile.is_customer) return "customer"
  return "customer" // default
}

// Update selectors
getActiveProfile: () => {
  const { profiles, activeProfileId } = get()
  return profiles.find((p) => p.third_party_id === activeProfileId) || null
},
```

### 5. Update Form Components

#### Supplier Form Component
**File:** `components/profiles/create-supplier-modal.tsx`

**Changes:**
1. Update form field names to match backend
2. Add category selection (fetch from backend)
3. Update styling for sleeker look

**Key Updates:**
```tsx
// Update form registration
<Input id="third_party_name" {...register("third_party_name")} />
<Input id="trading_name" {...register("trading_name")} />
<Input id="registration_number" {...register("registration_number")} />

// Add category selector
<CategorySelector
  selected={selectedCategories}
  onChange={setSelectedCategories}
/>
```

#### Tenant Form Component
**File:** `components/profiles/create-tenant-modal.tsx`

**Changes:**
1. Update to use `tenant_type` (single number, not array)
2. Fetch tenant types from backend
3. Simplify form (remove property types & lease preferences arrays)

```tsx
<Select
  onValueChange={(value) => setValue("tenant_type", parseInt(value))}
>
  <SelectTrigger>
    <SelectValue placeholder="Select tenant type" />
  </SelectTrigger>
  <SelectContent>
    {tenantTypes.map((type) => (
      <SelectItem key={type.id} value={type.id.toString()}>
        {type.name}
      </SelectItem>
    ))}
  </SelectContent>
</Select>
```

#### Customer Form Component
**File:** `components/profiles/create-customer-modal.tsx`

**Changes:**
1. Simplify to match backend (just `third_party_name` and optional fields)
2. Remove first_name/last_name requirement

### 6. Update Profile Display Components

**File:** `components/profiles/profile-management-page.tsx`

Update profile label extraction:

```tsx
function getProfileLabel(profile: Profile): string {
  if (profile.is_supplier || profile.is_tenant) {
    return profile.trading_name || profile.third_party_name
  }
  return profile.third_party_name
}

function getProfileType(profile: Profile): ProfileType {
  if (profile.is_supplier) return "supplier"
  if (profile.is_tenant) return "tenant"
  return "customer"
}
```

### 7. Update Profile Switcher

**File:** `components/profiles/profile-switcher.tsx`

Update to use new field names:

```tsx
const getProfileLabel = (profile: Profile): string => {
  if (profile.is_supplier || profile.is_tenant) {
    return profile.trading_name || profile.third_party_name
  }
  return profile.third_party_name
}

const getProfileType = (profile: Profile): ProfileType => {
  if (profile.is_supplier) return "supplier"
  if (profile.is_tenant) return "tenant"
  return "customer"
}
```

## 🎨 Sleek Design Improvements

### 1. Enhanced Color Palette

```typescript
const profileColors = {
  supplier: {
    primary: "bg-gradient-to-br from-blue-500 to-blue-600",
    secondary: "bg-blue-50 dark:bg-blue-950",
    border: "border-blue-200 dark:border-blue-800",
    text: "text-blue-600 dark:text-blue-400",
  },
  tenant: {
    primary: "bg-gradient-to-br from-green-500 to-green-600",
    secondary: "bg-green-50 dark:bg-green-950",
    border: "border-green-200 dark:border-green-800",
    text: "text-green-600 dark:text-green-400",
  },
  customer: {
    primary: "bg-gradient-to-br from-purple-500 to-purple-600",
    secondary: "bg-purple-50 dark:bg-purple-950",
    border: "border-purple-200 dark:border-purple-800",
    text: "text-purple-600 dark:text-purple-400",
  },
}
```

### 2. Modern Card Design

```tsx
<Card className="group hover:shadow-xl transition-all duration-300 border-2">
  <CardHeader className="pb-3">
    <div className="flex items-start justify-between">
      <div className="flex items-center gap-3">
        <div className={cn(
          "p-3 rounded-xl",
          profileColors[type].primary,
          "shadow-lg group-hover:scale-110 transition-transform"
        )}>
          <Icon className="h-6 w-6 text-white" />
        </div>
        <div>
          <CardTitle className="text-lg font-bold">{label}</CardTitle>
          <CardDescription className="text-sm">{type}</CardDescription>
        </div>
      </div>
      {isActive && (
        <Badge className="animate-pulse">Active</Badge>
      )}
    </div>
  </CardHeader>
</Card>
```

### 3. Smooth Form Transitions

```tsx
<motion.div
  initial={{ opacity: 0, y: 20 }}
  animate={{ opacity: 1, y: 0 }}
  exit={{ opacity: 0, y: -20 }}
  transition={{ duration: 0.3, ease: "easeOut" }}
>
  {/* Form content */}
</motion.div>
```

### 4. Enhanced Button States

```tsx
<Button
  className={cn(
    "relative overflow-hidden",
    "before:absolute before:inset-0",
    "before:bg-gradient-to-r before:from-transparent before:via-white/20 before:to-transparent",
    "before:translate-x-[-200%] hover:before:translate-x-[200%]",
    "before:transition-transform before:duration-700"
  )}
>
  Create Profile
</Button>
```

## 📝 Implementation Checklist

- [x] Update type definitions
- [ ] Update validation schemas
- [ ] Update API route transformations
- [ ] Update store selectors
- [ ] Update supplier form component
- [ ] Update tenant form component
- [ ] Update customer form component
- [ ] Update profile display components
- [ ] Update profile switcher
- [ ] Add loading skeletons
- [ ] Add error boundaries
- [ ] Test all profile creation flows
- [ ] Test profile switching
- [ ] Verify backend integration

## 🚀 Quick Implementation Steps

1. **Start with Types** (✅ Done)
   - Already updated in `types/profile-management.ts`

2. **Update Validation Schemas**
   ```bash
   # Update field names in lib/validations/profile-schemas.ts
   ```

3. **Fix API Routes**
   ```bash
   # Add transformation logic in app/api/portal/profiles/*/route.ts
   ```

4. **Update Components**
   ```bash
   # Update all form components to use new field names
   ```

5. **Test Integration**
   ```bash
   npm run dev
   # Test create supplier profile
   # Test create tenant profile
   # Test create customer profile
   ```

## 💡 Pro Tips

1. **Use Transform Functions**: Create helper functions to transform between camelCase (frontend) and snake_case (backend)

```typescript
// lib/utils/transform.ts
export function toSnakeCase(obj: any): any {
  // Transform object keys to snake_case
}

export function toCamelCase(obj: any): any {
  // Transform object keys to camelCase
}
```

2. **Handle Both Formats**: Support both formats in forms for smooth transition

3. **Add Loading States**: Show skeletons while fetching categories/tenant types

4. **Error Handling**: Display user-friendly error messages from backend

5. **Validation**: Match frontend validation with backend rules exactly

## 📚 Additional Resources

- Backend API Docs: `PORTAL_API_PROFILES.md`
- Original Implementation: `PROFILE_MANAGEMENT.md`
- Profile Switcher: `PROFILE_SWITCHER_FEATURES.md`

## 🆘 Need Help?

If you encounter issues:
1. Check browser console for errors
2. Check backend logs
3. Verify token is being sent correctly
4. Ensure field names match exactly

---

**Status**: Types Updated ✅ | Validation Pending ⏳ | Components Pending ⏳
