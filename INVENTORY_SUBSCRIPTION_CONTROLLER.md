# Store Keeper Inventory Subscription Controller - Implementation Complete

## Location
**File:** `/Applications/MAMP/htdocs/schools/app/Admin/Controllers/InventorySubscriptionController.php`  
**Route:** `/admin/inventory-subscriptions`  
**Route Name:** `inventory-subscriptions`

## Purpose
This controller provides a focused interface for store keepers to manage the inventory aspects of service subscriptions without the ability to modify core subscription data or delete subscriptions.

## Key Features

### 1. Restricted Visibility
- Shows ONLY subscriptions where `to_be_managed_by_inventory = 'Yes'`
- Filters to active term by default
- Shows only active students
- Enterprise-scoped (only shows subscriptions from logged-in user's enterprise)

### 2. Grid Display (List View)
**Columns:**
- Date (subscription created date)
- Term
- Student (clickable link to student profile)
- Service name
- Subscription Quantity
- **Inventory Status** (color-coded badges):
  - 🟢 Green "Offered" - Inventory provided
  - 🟡 Yellow "Pending" - Awaiting fulfillment
  - 🔴 Red "Cancelled" - Subscription cancelled
  - ⚪ Gray "Not Offered" - Initial state
- **Status** (Completed/Incomplete)
- **Stock Batch** (shows category name and batch ID)
- **Provided Qty** (actual quantity provided)
- **Stock Record** (clickable button to view stock record)
- **Date Provided** (when inventory was given to student)
- **Provided By** (staff member who provided inventory)

**Filters Available:**
- Filter by term
- Filter by student (AJAX search)
- Filter by service
- Filter by inventory status (Not Offered/Pending/Offered/Cancelled)
- Filter by completion status (Completed/Not Completed)
- Quick search by student name

**Disabled Actions:**
- ❌ Create button disabled - store keepers cannot create new subscriptions
- ❌ Delete button disabled - store keepers cannot delete subscriptions
- ❌ Batch actions disabled
- ❌ View detail disabled

### 3. Form (Edit View)

**Read-Only Fields (Display Only):**
- Subscription ID
- Student name
- Service name
- Subscription Quantity
- Term

**Editable Fields:**

1. **Inventory Status** (Radio buttons)
   - Not Offered
   - Pending
   - Offered ← When selected, shows additional fields
   - Cancelled

2. **When "Offered" is Selected:**
   - **Select Stock Batch** (dropdown)
     - Shows available batches with current quantities
     - Format: "Category Name - 100 items available - Batch #123"
     - Only shows non-archived batches with quantity > 0
   
   - **Quantity to Provide** (decimal input)
     - Defaults to subscription quantity
     - Can be adjusted based on actual provision
   
   - **Stock Record ID** (display)
     - Shows link to view created stock record
     - Shows "Will be created automatically" if not yet created
   
   - **Date Provided** (display)
     - Auto-populated when status set to "Offered"
   
   - **Provided By** (display)
     - Shows logged-in user who marked as offered

3. **Completion Status** (Display only)
   - Auto-updated based on inventory status

**Contextual Help:**
- Shows info message when "Pending" selected
- Shows warning message when "Cancelled" selected

### 4. Protection Mechanisms

**In Form Saving Callback:**
```php
// These fields are locked and cannot be changed by store keepers:
- enterprise_id
- service_id
- administrator_id (student)
- quantity (subscription quantity)
- total (fee amount)
- due_academic_year_id
- due_term_id
- to_be_managed_by_inventory (always stays 'Yes')
```

## Workflow for Store Keepers

### Typical Use Case:

1. **View Pending Subscriptions:**
   - Navigate to `/admin/inventory-subscriptions`
   - Use filters to find pending inventories
   - Can filter by service (e.g., "Uniform", "Books")

2. **Mark as Pending:**
   - Click edit on a subscription
   - Select "Pending" status
   - Save - system marks as incomplete

3. **Provide Inventory to Student:**
   - Click edit on pending subscription
   - Select "Offered" status
   - Select appropriate stock batch from dropdown
   - Enter quantity being provided (default is subscription qty)
   - Save
   - System automatically:
     - Creates stock-out record
     - Records batch ID and quantity
     - Sets completion status to "Yes"
     - Records date and provider ID
     - Reduces stock batch quantity

4. **View Stock Record:**
   - After marking as offered, click "View #XXX" button in grid
   - Opens stock record in new tab
   - Can see full details of inventory transaction

5. **Cancel Subscription:**
   - Select "Cancelled" status
   - Save - marks as completed without creating stock record

## Integration with Main System

### Model Events
All changes trigger ServiceSubscription model events:
- `updating` event runs validation and creates stock records
- `updated` event updates related models (Service fees, etc.)

### Automation
When store keeper marks as "Offered":
1. Validates stock_batch_id and provided_quantity exist
2. Validates batch has sufficient quantity
3. Creates StockRecord (type='OUT')
4. Links record to subscription (bidirectional)
5. Records student as receiver
6. Updates is_completed = 'Yes'
7. Records date and provider
8. Stock batch quantity reduced automatically (via StockRecord boot)

## Security & Permissions

**What Store Keepers CAN Do:**
✅ View list of inventory-managed subscriptions
✅ Filter and search subscriptions
✅ Edit inventory status
✅ Select stock batch and quantity
✅ View linked stock records
✅ Export list to Excel

**What Store Keepers CANNOT Do:**
❌ Create new subscriptions
❌ Delete subscriptions
❌ Modify student information
❌ Change service type
❌ Modify subscription quantity
❌ Change fees or totals
❌ Change term or academic year
❌ View subscription details page

## Differences from ServiceSubscriptionController

| Feature | ServiceSubscriptionController | InventorySubscriptionController |
|---------|------------------------------|--------------------------------|
| **Purpose** | Full subscription management | Inventory management only |
| **Can Create** | ✅ Yes | ❌ No |
| **Can Delete** | ✅ Yes | ❌ No |
| **Can Edit Core Fields** | ✅ Yes | ❌ No (locked) |
| **Can Edit Inventory Fields** | ✅ Yes | ✅ Yes |
| **Shows All Subscriptions** | ✅ Yes | ❌ Only inventory-managed |
| **Target Users** | Admins, Accountants | Store Keepers |
| **Help Text** | Yes | No (cleaner interface) |

## Testing

To test the controller:

1. Navigate to `/admin/inventory-subscriptions`
2. Should see list of only inventory-managed subscriptions
3. Try to create - button should be missing
4. Edit a subscription
5. Try changing student/service - fields should be read-only displays
6. Change inventory status to "Offered"
7. Select stock batch and quantity
8. Save and verify:
   - Stock record created
   - is_completed = 'Yes'
   - Date and provider recorded
   - Stock batch quantity reduced

## Route Registration

Added to `/Applications/MAMP/htdocs/schools/app/Admin/routes.php`:
```php
$router->resource('inventory-subscriptions', InventorySubscriptionController::class);
```

## Menu Integration

To add to admin menu, add this to menu configuration:
```php
[
    'title' => 'Inventory Subscriptions',
    'icon' => 'fa-cubes',
    'uri' => 'inventory-subscriptions',
],
```

## Summary

✅ Controller created at `app/Admin/Controllers/InventorySubscriptionController.php`  
✅ Route registered at `/admin/inventory-subscriptions`  
✅ Store keepers can view and manage inventory status  
✅ Cannot create or delete subscriptions  
✅ Cannot modify core subscription data  
✅ Cascading form fields for better UX  
✅ Color-coded status badges  
✅ Export functionality included  
✅ Comprehensive filters and search  
✅ Integration with existing automation logic  
✅ Security protections in place  

**Status:** ✅ READY FOR USE
