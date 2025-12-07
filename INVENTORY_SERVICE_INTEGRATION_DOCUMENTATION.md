# Inventory-Service Subscription Integration

## Overview
This feature connects service subscriptions with inventory management, allowing store keepers to track and fulfill service subscriptions that require physical inventory items (uniforms, books, equipment, etc.).

## Database Schema Changes

### Service Subscriptions Table
Added 6 new fields to `service_subscriptions`:

1. **to_be_managed_by_inventory** (string, nullable, default: 'No')
   - Indicates whether this subscription requires inventory tracking
   - Values: 'Yes' or 'No'

2. **is_service_offered** (string, nullable, default: 'No')
   - Tracks the fulfillment status of the inventory
   - Values: 'No', 'Pending', 'Yes', 'Cancelled'

3. **is_completed** (string, nullable, default: 'No')
   - Indicates whether the subscription process is complete
   - Automatically updated based on `is_service_offered` status
   - Values: 'Yes' or 'No'

4. **stock_record_id** (bigint, nullable)
   - Foreign key linking to the stock_records table
   - Created automatically when inventory is provided

5. **inventory_provided_date** (date, nullable)
   - Records when the inventory was given to the student
   - Set automatically when status changes to 'Yes'

6. **inventory_provided_by_id** (bigint, nullable)
   - Foreign key to admin_users (user who provided the inventory)
   - Set automatically when status changes to 'Yes'

### Stock Records Table
Added 1 new field to `stock_records`:

1. **service_subscription_id** (bigint, nullable)
   - Foreign key linking back to service_subscriptions
   - Prevents duplicate stock records for the same subscription

### Batch Service Subscriptions Table
Added 1 new field to `batch_service_subscriptions`:

1. **to_be_managed_by_inventory** (string, nullable, default: 'No')
   - Applied to all subscriptions created from this batch

## Automation Logic

### Model Hooks (ServiceSubscription Model)

**On Update:**
When a service subscription is updated, the `handleInventoryStatusChange()` method is triggered:

1. **Status: 'No' → 'Yes' (Offered)**
   - Creates a stock-out record automatically
   - Links the stock record to the subscription
   - Sets `is_completed = 'Yes'`
   - Records `inventory_provided_date = now()`
   - Records `inventory_provided_by_id = current_user_id`
   - Reduces stock batch quantity automatically

2. **Status: 'Cancelled'**
   - Sets `is_completed = 'Yes'`
   - Does NOT create a stock record

3. **Status: 'Pending' or 'No'**
   - Sets `is_completed = 'No'`
   - Does NOT create a stock record

### Stock Record Creation

When `is_service_offered` changes to 'Yes':

1. System finds available stock batch for the service
   - Searches by matching service name with stock category/batch names
   - Uses FIFO (First In, First Out) principle
   - Validates sufficient quantity available

2. Creates stock-out record with:
   - `type = 'OUT'` (automatically converted to negative quantity)
   - `received_by = student_id` (the student receiving the item)
   - `created_by = current_user_id` (store keeper)
   - `service_subscription_id = subscription_id`
   - Clear description linking to service and student

3. Links stock record back to subscription
   - Sets `subscription.stock_record_id`

4. Prevents duplicates
   - Checks if stock record already exists before creating
   - Idempotent: Multiple saves won't create multiple records

## User Interface

### Service Subscription Form

**For Creation:**
- Radio button: "Manage by Inventory?" (Yes/No)
- Help text explaining when to use inventory management

**For Editing:**
- Display: "Manage by Inventory?" status
- Radio button: "Inventory Status" (No/Pending/Yes/Cancelled)
- When status is 'Yes':
  - Display: Stock Record ID (with link)
  - Display: Date Provided
  - Display: Provided By (staff name)
- Display: Completion Status badge

### Service Subscription Grid

Added columns:
- **Inventory Mgmt**: Badge showing Yes/No
- **Inventory Status**: Badge showing current fulfillment status
  - Green (Success): Offered
  - Yellow (Warning): Pending
  - Red (Danger): Cancelled
  - Gray (Secondary): Not Offered or N/A
- **Completed**: Badge showing Yes/No

### Batch Service Subscription Form

- Radio button: "Manage by Inventory?" (Yes/No)
- Help text: Applied to all subscriptions in batch
- Value passed to individual subscriptions when processed

## Relationships

### ServiceSubscription Model

```php
// Belongs to stock record
$subscription->stockRecord; // StockRecord|null

// Belongs to user who provided inventory
$subscription->inventoryProvidedBy; // User|null

// Scopes
ServiceSubscription::managedByInventory()->get();
ServiceSubscription::pendingInventory()->get();
ServiceSubscription::inventoryCompleted()->get();
```

### StockRecord Model

```php
// Belongs to service subscription
$stockRecord->serviceSubscription; // ServiceSubscription|null

// Scope
StockRecord::fromServiceSubscription()->get();
```

## Workflow Examples

### Example 1: School Uniform

1. Admin creates service subscription:
   - Service: "School Uniform"
   - Student: John Doe
   - Quantity: 2
   - **Manage by Inventory: Yes**
   - Status: Pending

2. Store keeper receives uniforms and updates:
   - **Inventory Status: Yes (Offered)**
   - System automatically:
     - Creates stock-out record for 2 uniforms
     - Links record to subscription
     - Marks as completed
     - Records date and store keeper's name
     - Reduces stock batch by 2 units

3. Student receives uniforms:
   - Stock record shows `received_by = student_id`
   - Full audit trail maintained

### Example 2: Cancelled Subscription

1. Admin creates subscription with inventory management
2. Before fulfillment, subscription is cancelled:
   - **Inventory Status: Cancelled**
   - System automatically:
     - Marks as completed
     - Does NOT create stock record
     - Does NOT reduce inventory

### Example 3: Regular Service (No Inventory)

1. Admin creates service subscription:
   - Service: "Exam Fees"
   - **Manage by Inventory: No**
   - System behavior:
     - No inventory tracking
     - All inventory fields ignored/hidden
     - Works like traditional subscription

## Validations & Error Handling

### Automatic Validations

1. **Insufficient Stock**
   - If requested quantity exceeds available stock
   - Throws exception with clear message
   - Transaction rolled back automatically

2. **Missing Stock Batch**
   - If no stock batch found for service
   - Throws exception with guidance
   - Suggests linking services to stock items

3. **Duplicate Prevention**
   - Checks existing stock records before creating
   - Prevents multiple stock-outs for same subscription
   - Idempotent operations

### Manual Checks

Store keepers should verify:
- Correct service-to-stock mapping
- Adequate stock levels before offering
- Correct student receiving items

## Testing

### Test Scenarios Included

Run: `php test_inventory_service_integration.php`

1. **Scenario 1**: Regular subscription without inventory
2. **Scenario 2**: Inventory-managed subscription with Pending status
3. **Scenario 3**: Status change from Pending to Offered (creates stock record)
4. **Scenario 4**: Cancelled subscription (no stock record)
5. **Scenario 5**: Multiple status changes and idempotency verification

### Expected Results

- All 5 scenarios should PASS
- Stock records created only when status = 'Yes'
- No duplicate stock records
- Correct stock batch quantity reduction
- Proper completion status updates

## Configuration Requirements

### Service-to-Stock Mapping

For automatic stock batch detection, either:

1. **Name Matching** (Current Implementation):
   - Stock category/batch name contains service name
   - Example: Service "Uniform" matches "School Uniforms" category
   - Case-insensitive partial matching

2. **Future Enhancement**:
   - Add `stock_batch_id` field to services table
   - Allow direct linking of services to specific batches
   - More explicit and reliable

### Permissions

Recommended user roles:
- **Admin**: Full access to all fields
- **Store Keeper**: Can update `is_service_offered` status
- **Regular Staff**: View-only on inventory fields

## Maintenance & Monitoring

### Key Queries

```php
// Pending inventory fulfillment
ServiceSubscription::pendingInventory()->count();

// Completed this week
ServiceSubscription::inventoryCompleted()
    ->where('inventory_provided_date', '>=', now()->startOfWeek())
    ->count();

// Stock records from subscriptions
StockRecord::fromServiceSubscription()
    ->whereDate('created_at', today())
    ->get();
```

### Common Issues

**Issue**: Stock record not created
- **Cause**: No matching stock batch found
- **Solution**: Ensure stock batches exist with matching names or add direct linking

**Issue**: Insufficient stock error
- **Cause**: Stock batch quantity < requested quantity
- **Solution**: Replenish stock before marking as offered

**Issue**: Duplicate records
- **Cause**: Manual stock record creation
- **Solution**: Always use the subscription status field, never create stock records manually for subscriptions

## Files Modified

### Models
- `app/Models/ServiceSubscription.php` (added inventory logic and hooks)
- `app/Models/StockRecord.php` (added relationship)

### Controllers
- `app/Admin/Controllers/ServiceSubscriptionController.php` (added form fields and grid columns)
- `app/Admin/Controllers/BatchServiceSubscriptionController.php` (added form field)

### Routes
- `routes/web.php` (updated batch processing to include inventory field)

### Migrations
- `database/migrations/2025_12_07_233942_add_inventory_fields_to_service_subscriptions_and_stock_records.php`

### Tests
- `test_inventory_service_integration.php` (comprehensive test scenarios)

## Future Enhancements

1. **Direct Service-Stock Linking**
   - Add `stock_batch_id` field to services table
   - Admin UI to map services to specific stock batches

2. **Bulk Inventory Fulfillment**
   - Mark multiple pending subscriptions as offered at once
   - Useful for batch distribution events

3. **Inventory Alerts**
   - Email/notification when stock runs low
   - Alert when subscriptions pending > available stock

4. **Return Handling**
   - Track returned/damaged items
   - Stock-in records linked to original subscription

5. **Reporting Dashboard**
   - Inventory fulfillment rate
   - Average time to fulfill
   - Most requested items

## Support

For issues or questions:
1. Run test scenarios to verify system state
2. Check stock batch availability and naming
3. Review error logs for detailed exception messages
4. Verify user permissions for inventory management
