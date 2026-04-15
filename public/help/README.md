# Help Documentation

This directory contains comprehensive documentation for the Third Party Portal.

## Documentation Structure

The documentation is organized into the following guides:

### 📚 Main Documentation Files

1. **[index.md](./index.md)** - Help Center homepage with overview and quick links
2. **[general-guide.md](./general-guide.md)** - Common features for all users
3. **[supplier-guide.md](./supplier-guide.md)** - Complete supplier documentation
4. **[tenant-guide.md](./tenant-guide.md)** - Complete tenant documentation
5. **[customer-guide.md](./customer-guide.md)** - Complete customer documentation

## User Roles

### Supplier
Suppliers can access documentation for:
- Prequalification process
- Tender bidding
- Request for Quotations (RFQs)
- Document management
- Application tracking

### Tenant
Tenants can access documentation for:
- Property browsing
- Submitting property interests
- Lease management
- Maintenance requests
- Invoice and payment management

### Customer
Customers can access documentation for:
- Insurance policy management
- Claims filing and tracking
- Payment and billing
- Policy renewals
- Coverage understanding

## Accessing Documentation

### In the Portal
Navigate to **Dashboard > Help Center** to access:
- Quick FAQs
- Role-specific comprehensive guides
- Support tickets
- Quick actions

### Direct Links
Documentation is also available at:
- `/help/index.md` - Help Center home
- `/help/general-guide.md` - General guide
- `/help/supplier-guide.md` - Supplier guide
- `/help/tenant-guide.md` - Tenant guide
- `/help/customer-guide.md` - Customer guide

## Documentation Updates

The documentation is maintained in two locations:
1. **`/help/`** - Source documentation (edit here)
2. **`/public/help/`** - Published documentation (copied from source)

When updating documentation:
1. Edit files in `/help/` directory
2. Copy updated files to `/public/help/` for web access
3. Use: `Copy-Item -Path "help\*.md" -Destination "public\help\" -Force`

## Features Covered

### All Users
- Dashboard navigation
- Profile management
- Settings and preferences
- Notifications
- Support tickets
- Document uploads
- Search functionality
- Keyboard shortcuts

### Suppliers
- Prequalification categories
- Tender discovery and bidding
- RFQ response submission
- Application tracking
- Document compliance
- Evaluation process
- Best practices

### Tenants
- Property search and filtering
- Interest submission
- Lease lifecycle
- Maintenance request process
- Payment methods and history
- Move-in/move-out procedures
- Tenant rights and responsibilities

### Customers
- Policy viewing and management
- Claims filing and tracking
- Premium payments
- Policy renewals
- Beneficiary management
- Coverage understanding
- Insurance best practices

## Support

For questions or issues not covered in the documentation:
- Create a support ticket at [My Tickets](/dashboard/help/tickets)
- Email: support@thirdpartyportal.com
- Check the FAQ sections in each guide

## Maintenance

**Last Updated**: April 2026  
**Version**: 1.0  
**Maintained by**: Portal Development Team

---

For technical implementation details, see the help page component at `/app/dashboard/help/page.tsx`.
