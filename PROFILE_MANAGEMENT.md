# Profile Management System

## Overview

A comprehensive profile management system built with Next.js 16, TypeScript, Zustand, ShadcnUI, and Framer Motion. This system allows users to create and manage multiple profile types: Supplier, Tenant, and Customer.

## Features

### 🎨 Modern UI/UX
- **Smooth Animations**: Framer Motion animations for all interactions
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **Accessible**: Built with Radix UI primitives for accessibility
- **Dark Mode Support**: Full theme support via next-themes

### 🚀 Performance
- **Optimistic Updates**: Instant UI feedback with background sync
- **State Persistence**: Profile preferences saved locally
- **Smart Caching**: Efficient data fetching and caching
- **Type Safety**: Full TypeScript coverage

### 📋 Profile Types

#### Supplier Profile
- Company information (name, trading name, registration)
- Tax details (Tax PIN, KRA PIN)
- Business type and categories
- Location and contact information
- Approval status tracking

#### Tenant Profile
- Company details
- Property type preferences
- Lease preferences
- Location information

#### Customer Profile
- Personal information
- Contact details
- Shipping and billing addresses
- Communication preferences

## Architecture

### Directory Structure

```
├── app/
│   └── api/
│       └── portal/
│           └── profiles/
│               ├── route.ts              # GET all profiles
│               ├── supplier/
│               │   └── route.ts          # POST supplier profile
│               ├── tenant/
│               │   └── route.ts          # POST tenant profile
│               └── customer/
│                   └── route.ts          # POST customer profile
├── components/
│   └── profiles/
│       ├── profile-management-page.tsx   # Main page component
│       ├── create-supplier-modal.tsx     # Supplier creation modal
│       ├── create-tenant-modal.tsx       # Tenant creation modal
│       └── create-customer-modal.tsx     # Customer creation modal
├── hooks/
│   └── use-profile-management.ts         # Custom hook for profile operations
├── lib/
│   └── validations/
│       └── profile-schemas.ts            # Zod validation schemas
├── store/
│   └── profile-management-store.ts       # Zustand state management
└── types/
    └── profile-management.ts             # TypeScript type definitions
```

### State Management

**Zustand Store** (`profile-management-store.ts`)
- Profiles list
- Active profile tracking
- Loading states
- Error handling
- Persistent storage for user preferences

**Features:**
- DevTools integration for debugging
- Persist middleware for local storage
- Optimized selectors for performance

### API Routes

All API routes follow Next.js 16 conventions with:
- Server-side session validation
- Bearer token authentication
- Proper error handling
- Type-safe request/response

**Endpoints:**
- `GET /api/portal/profiles` - Fetch all user profiles
- `POST /api/portal/profiles/supplier` - Create supplier profile
- `POST /api/portal/profiles/tenant` - Create tenant profile
- `POST /api/portal/profiles/customer` - Create customer profile

### Validation

**Zod Schemas** provide runtime validation:
- Required field validation
- Format validation (URLs, phone numbers)
- Custom error messages
- Type inference for forms

### Animations

**Framer Motion** animations:
- Page transitions
- Card entrance/exit animations
- Form step transitions
- Button interactions
- Loading states

**Animation Patterns:**
```typescript
// Entry animation
initial={{ opacity: 0, y: 20 }}
animate={{ opacity: 1, y: 0 }}

// Exit animation
exit={{ opacity: 0, scale: 0.9 }}

// Hover effects
whileHover={{ scale: 1.02 }}
whileTap={{ scale: 0.98 }}
```

## Usage

### Fetching Profiles

```typescript
import { useProfileManagement } from "@/hooks/use-profile-management"

function MyComponent() {
  const { profiles, fetchProfiles, isLoading } = useProfileManagement()

  useEffect(() => {
    fetchProfiles()
  }, [])

  return (
    <div>
      {profiles.map(profile => (
        <div key={profile.id}>{profile.type}</div>
      ))}
    </div>
  )
}
```

### Creating a Profile

```typescript
const { createProfile, isSubmitting } = useProfileManagement()

const handleCreate = async (data) => {
  const profile = await createProfile("supplier", data)
  if (profile) {
    console.log("Profile created:", profile)
  }
}
```

### Setting Active Profile

```typescript
const { setActiveProfile, activeProfile } = useProfileManagement()

// Set active profile by ID
setActiveProfile(profileId)

// Get current active profile
console.log(activeProfile)
```

## Form Validation Examples

### Supplier Profile

```typescript
import { supplierProfileSchema } from "@/lib/validations/profile-schemas"

const form = useForm({
  resolver: zodResolver(supplierProfileSchema),
  defaultValues: {
    companyName: "",
    tradingName: "",
    registrationNumber: "",
    taxPin: "",
    categories: [],
  }
})
```

### Customer Profile

```typescript
import { customerProfileSchema } from "@/lib/validations/profile-schemas"

const form = useForm({
  resolver: zodResolver(customerProfileSchema),
  defaultValues: {
    firstName: "",
    lastName: "",
    phoneNumber: "",
    country: "",
    preferences: {
      newsletter: false,
      promotions: false,
    }
  }
})
```

## Best Practices Implemented

### 1. Component Composition
- Small, reusable components
- Clear separation of concerns
- Prop typing with TypeScript

### 2. State Management
- Single source of truth (Zustand)
- Immutable state updates
- Optimistic UI updates

### 3. Form Handling
- React Hook Form for performance
- Zod for validation
- Clear error messages

### 4. API Communication
- Centralized API routes
- Consistent error handling
- Loading states

### 5. User Experience
- Immediate feedback
- Smooth animations
- Clear status indicators
- Accessible UI components

### 6. Type Safety
- Full TypeScript coverage
- Type inference from Zod schemas
- Strict null checks

## Performance Optimizations

1. **Lazy Loading**: Components loaded on demand
2. **Memoization**: useCallback and useMemo where appropriate
3. **Code Splitting**: Route-based splitting
4. **Optimistic Updates**: Instant UI feedback
5. **Persistent Storage**: Only active profile ID stored locally

## Error Handling

### API Errors
- Network errors caught and displayed
- Backend errors with user-friendly messages
- Toast notifications via Sonner

### Form Errors
- Field-level validation
- Real-time error display
- Clear error messages

### State Errors
- Error state in store
- Error clearing on success
- Retry mechanisms

## Testing Recommendations

### Unit Tests
- Zustand store actions
- Validation schemas
- Custom hooks

### Integration Tests
- API route handlers
- Form submissions
- Profile CRUD operations

### E2E Tests
- Complete profile creation flow
- Profile switching
- Error scenarios

## Future Enhancements

1. **Profile Editing**: Update existing profiles
2. **Profile Deletion**: Remove profiles with confirmation
3. **Profile Switching**: Quick profile switcher in navbar
4. **File Uploads**: Support for documents and images
5. **Profile Verification**: Backend verification workflow
6. **Bulk Operations**: Create multiple profiles at once
7. **Export/Import**: Profile data export/import
8. **Activity Log**: Track profile changes

## Dependencies

```json
{
  "zustand": "^5.0.9",
  "framer-motion": "^12.23.26",
  "react-hook-form": "^7.68.0",
  "@hookform/resolvers": "^5.2.2",
  "zod": "^3.25.76",
  "sonner": "^2.0.7",
  "next-auth": "^4.24.13"
}
```

## Environment Variables

```env
NEXT_PUBLIC_EXTERNAL_API_URL=http://your-backend-api.com
API_BASE_URL=http://your-backend-api.com
NEXTAUTH_SECRET=your-secret-key
```

## Contributing

When adding new profile types:

1. Add type definition to `types/profile-management.ts`
2. Create validation schema in `lib/validations/profile-schemas.ts`
3. Create API route in `app/api/portal/profiles/[type]/route.ts`
4. Create modal component in `components/profiles/create-[type]-modal.tsx`
5. Update profile config in `profile-management-page.tsx`

## Support

For issues or questions:
- Check the TypeScript errors for type issues
- Review console for API errors
- Check Zustand DevTools for state issues
- Verify environment variables are set correctly
