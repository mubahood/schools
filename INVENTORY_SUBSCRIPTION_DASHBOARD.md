# Service Subscription Inventory Dashboard

## Overview

This document describes the **Stock Dashboard** enhancements that display comprehensive inventory subscription metrics alongside existing stock statistics. The dashboard provides store keepers and administrators with real-time visibility into service subscription inventory status, pending requests, and completion rates.

## Location & Access

- **Route**: `/admin/stock-stats`
- **Controller Method**: `HomeController::stockStats()`
- **View File**: `resources/views/admin/stock/stats.blade.php`
- **Menu**: Admin → Stock Management → Stock Dashboard

## Features

### 1. Inventory Subscription Summary Cards

The dashboard displays **4 new summary cards** specifically for service subscription inventory:

#### Card 1: Subscription Overview
- **Total Managed**: Count of all subscriptions where `to_be_managed_by_inventory = 'Yes'`
- **Completed**: Subscriptions with `is_completed = 'Yes'`
- **Incomplete**: Subscriptions with `is_completed = 'No'`
- **Completion Rate**: Percentage of completed vs total subscriptions
- **Gradient**: Purple (667eea → 764ba2)

#### Card 2: Service Status
- **Offered**: Count of subscriptions with `is_service_offered = 'Yes'`
- **Pending**: Count of subscriptions with `is_service_offered = 'Pending'`
- **Cancelled**: Count of subscriptions with `is_service_offered = 'Cancelled'`
- **Icons**: Color-coded status indicators
- **Gradient**: Pink (f093fb → f5576c)

#### Card 3: Quantity Metrics
- **Total Allocated**: Sum of `provided_quantity` from all offered subscriptions
- **Services Pending**: Count of unique services with pending inventory
- **Items Needed**: Total quantity needed for pending subscriptions
- **Gradient**: Blue (4facfe → 00f2fe)

#### Card 4: Quick Stats
- **Avg. Items/Subscription**: Average quantity per subscription
- **Stock Utilization**: Percentage of total stock allocated to subscriptions
- **Active Requests**: Count of incomplete subscriptions requiring action
- **Gradient**: Orange-Yellow (fa709a → fee140)

### 2. Services Pending Inventory Panel

Shows all services that have subscriptions waiting for inventory provision.

**Columns:**
1. **#**: Row number
2. **Service Name**: Name of the service
3. **Pending Count**: Number of subscriptions waiting
4. **Quantity Needed**: Total quantity required for all pending subscriptions
5. **Available Stock**: Current stock matching the service name
6. **Status**: Visual indicator (Sufficient/Insufficient)

**Status Logic:**
- **Sufficient** (Green): `available_stock >= quantity_needed`
- **Insufficient** (Red): `available_stock < quantity_needed`

**Empty State**: "All services fulfilled" message when no pending services

### 3. Latest Incomplete Subscriptions Panel

Displays the **most recent 10 incomplete subscriptions** requiring attention.

**Columns:**
1. **Date**: Subscription creation date (formatted)
2. **Student**: Student name from linked account
3. **Service**: Service name
4. **Term**: Academic term
5. **Status**: Current `is_service_offered` status with color-coding
6. **Action**: Direct "Manage" button linking to InventorySubscriptionController edit page

**Status Badges:**
- **Pending** (Yellow): Waiting for inventory action
- **Not Offered** (Gray): Not yet initiated
- **Other statuses** (Blue): Custom status values

**Empty State**: No panel displayed when all subscriptions are completed

## Data Queries

### Controller Method Additions

```php
// Service Subscription Inventory Stats
$inventorySubscriptionsQ = \App\Models\ServiceSubscription::where('enterprise_id', $eid)
    ->where('to_be_managed_by_inventory', 'Yes');

$totalInventorySubscriptions = (clone $inventorySubscriptionsQ)->count();
$inventoryOffered = (clone $inventorySubscriptionsQ)->where('is_service_offered', 'Yes')->count();
$inventoryPending = (clone $inventorySubscriptionsQ)->where('is_service_offered', 'Pending')->count();
$inventoryCancelled = (clone $inventorySubscriptionsQ)->where('is_service_offered', 'Cancelled')->count();
$inventoryCompleted = (clone $inventorySubscriptionsQ)->where('is_completed', 'Yes')->count();
$inventoryIncomplete = (clone $inventorySubscriptionsQ)->where('is_completed', 'No')->count();
$totalAllocatedQuantity = (clone $inventorySubscriptionsQ)
    ->where('is_service_offered', 'Yes')
    ->sum('provided_quantity');

// Services with pending inventory
$pendingServices = DB::table('service_subscriptions')
    ->join('services', 'service_subscriptions.service_id', '=', 'services.id')
    ->where('service_subscriptions.enterprise_id', $eid)
    ->where('service_subscriptions.to_be_managed_by_inventory', 'Yes')
    ->whereIn('service_subscriptions.is_service_offered', ['No', 'Pending'])
    ->select(
        'services.name as service_name',
        'services.id as service_id',
        DB::raw('COUNT(service_subscriptions.id) as pending_count'),
        DB::raw('SUM(service_subscriptions.quantity) as total_quantity_needed')
    )
    ->groupBy('services.id', 'services.name')
    ->orderByDesc('pending_count')
    ->limit(10)
    ->get()
    ->map(function ($item) use ($eid) {
        // Match available stock by service name
        $availableStock = DB::table('stock_batches')
            ->join('stock_item_categories', 'stock_batches.stock_item_category_id', '=', 'stock_item_categories.id')
            ->where('stock_batches.enterprise_id', $eid)
            ->where('stock_batches.is_archived', 'No')
            ->where(function($query) use ($item) {
                $query->where('stock_item_categories.name', 'LIKE', '%' . $item->service_name . '%')
                      ->orWhere('stock_batches.description', 'LIKE', '%' . $item->service_name . '%');
            })
            ->sum('stock_batches.current_quantity');
        
        return [
            'service_name' => $item->service_name,
            'pending_count' => $item->pending_count,
            'quantity_needed' => $item->total_quantity_needed,
            'available_stock' => $availableStock,
            'status' => $availableStock >= $item->total_quantity_needed ? 'sufficient' : 'insufficient'
        ];
    });

// Latest 10 incomplete subscriptions
$latestInventorySubscriptions = \App\Models\ServiceSubscription::where('enterprise_id', $eid)
    ->where('to_be_managed_by_inventory', 'Yes')
    ->where('is_completed', 'No')
    ->with(['sub', 'service', 'due_term'])
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();
```

### Performance Considerations

1. **Query Cloning**: Uses `(clone $query)` to avoid modifying base query
2. **Eager Loading**: Uses `->with(['sub', 'service', 'due_term'])` to prevent N+1 queries
3. **Limits**: Restricts results to top 10 to maintain performance
4. **Indexing**: Recommend indexes on:
   - `service_subscriptions.to_be_managed_by_inventory`
   - `service_subscriptions.is_service_offered`
   - `service_subscriptions.is_completed`
   - `service_subscriptions.enterprise_id`

## Design & Styling

### Visual Design Principles

1. **Gradient Backgrounds**: Each card uses unique gradient for visual distinction
2. **Enterprise Branding**: Uses `Admin::user()->ent->color` for headers and buttons
3. **Responsive Layout**: Flexbox with responsive breakpoints at 1100px
4. **Color Coding**:
   - **Green (#10b981)**: Success, Sufficient, Completed
   - **Yellow (#f59e0b)**: Warning, Pending, Incomplete
   - **Red (#ef4444)**: Error, Insufficient, Cancelled
   - **Blue (#1976d2)**: Info, Neutral status
   - **Gray (#6b7280)**: Default, Not Offered

### Typography & Spacing

- **Section Header**: 3px solid border, enterprise color, 30px top margin
- **Card Padding**: 24px internal padding for comfortable spacing
- **Table Padding**: 14px cell padding for readability
- **Gap**: 24px between cards and panels

### Hover Effects

- **Cards**: Scale transform (1.02) with shadow increase on hover
- **Tables**: Light gray background on row hover (#f3f6fa)
- **Buttons**: Enterprise color with smooth transitions

### Icon Usage

- **fa-cubes**: Section header icon
- **fa-clipboard**: Subscription overview
- **fa-tasks**: Service status
- **fa-box**: Quantity metrics
- **fa-bolt**: Quick stats
- **fa-bell**: Services pending alert
- **fa-hourglass-half**: Latest incomplete subscriptions
- **fa-check-circle, fa-clock-o, fa-times-circle**: Status indicators

## Integration Points

### Links to Other Pages

1. **Manage Button**: Links to `admin/inventory-subscriptions/{id}/edit`
   - Opens InventorySubscriptionController edit form
   - Allows store keepers to update inventory status
   - Pre-loads subscription with all relationships

### Real-Time Data

- Dashboard displays **live data** from database on each page load
- No caching implemented (can be added if performance issues arise)
- All queries scoped to current user's enterprise

### Conditional Display

- **Services Pending Panel**: Only shown if `$pendingServices->count() > 0`
- **Latest Incomplete Panel**: Only shown if `$latestInventorySubscriptions->count() > 0`
- **Empty States**: Friendly messages when no data available

## User Experience Flow

### For Store Keepers

1. **Navigate** to Stock Dashboard from main menu
2. **Review** summary cards for overall status at a glance
3. **Identify** services with pending inventory from dedicated panel
4. **Check** if sufficient stock is available
5. **Click "Manage"** on incomplete subscriptions to process inventory
6. **Update** subscription status in InventorySubscriptionController
7. **Return** to dashboard to see updated metrics

### For Administrators

1. **Monitor** completion rates and stock utilization
2. **Identify** bottlenecks (insufficient stock, pending services)
3. **Analyze** quantity allocation trends
4. **Plan** inventory restocking based on pending needs
5. **Track** active requests requiring attention

## Metrics Explained

### Completion Rate
```
(inventoryCompleted / totalInventorySubscriptions) * 100
```
Indicates percentage of inventory-managed subscriptions that have been fulfilled.

### Avg. Items/Subscription
```
totalAllocatedQuantity / inventoryOffered
```
Shows average quantity provided per subscription (divides by offered count to avoid division by zero).

### Stock Utilization
```
(totalAllocatedQuantity / currentQuantity) * 100
```
Percentage of total stock that has been allocated to service subscriptions.

### Status Calculation
For each pending service:
- If `available_stock >= quantity_needed` → **Sufficient**
- If `available_stock < quantity_needed` → **Insufficient**

## Testing

### Manual Testing Checklist

1. **Navigate to Dashboard**:
   ```
   URL: /admin/stock-stats
   ```

2. **Verify Summary Cards Display**:
   - [ ] 4 original stock cards visible
   - [ ] 4 new inventory subscription cards visible
   - [ ] All counts display correctly
   - [ ] Completion rate calculates properly
   - [ ] Gradient backgrounds render correctly

3. **Verify Services Pending Panel**:
   - [ ] Panel shows when subscriptions are pending
   - [ ] All 6 columns display correctly
   - [ ] Status badges show correct colors
   - [ ] Available stock matches actual stock quantities
   - [ ] Panel hidden when no pending services

4. **Verify Latest Incomplete Panel**:
   - [ ] Shows up to 10 most recent incomplete subscriptions
   - [ ] Student names display correctly
   - [ ] Service and term information correct
   - [ ] Status badges color-coded properly
   - [ ] "Manage" button links to correct edit page
   - [ ] Panel hidden when all completed

5. **Test Responsive Design**:
   - [ ] Desktop view (>1100px): Cards in rows
   - [ ] Mobile view (<1100px): Cards stack vertically
   - [ ] All content readable on small screens

6. **Test with Different Data**:
   - [ ] No inventory subscriptions (empty state)
   - [ ] All completed (no incomplete panel)
   - [ ] All pending (full panels)
   - [ ] Mixed statuses

7. **Test Enterprise Scoping**:
   - [ ] Only shows data for current user's enterprise
   - [ ] Enterprise color theming applied correctly

### Sample Test Data

```php
// Create test inventory subscriptions
$testService = Service::where('to_be_managed_by_inventory', 'Yes')->first();
$testStudent = Administrator::where('user_type', 'student')->first();
$activeTerm = Term::getActive();

// Pending subscription
$pending = new ServiceSubscription();
$pending->enterprise_id = Admin::user()->enterprise_id;
$pending->service_id = $testService->id;
$pending->administrator_id = $testStudent->id;
$pending->quantity = 5;
$pending->to_be_managed_by_inventory = 'Yes';
$pending->is_service_offered = 'Pending';
$pending->is_completed = 'No';
$pending->due_term_id = $activeTerm->id;
$pending->save();

// Offered subscription
$offered = new ServiceSubscription();
$offered->enterprise_id = Admin::user()->enterprise_id;
$offered->service_id = $testService->id;
$offered->administrator_id = $testStudent->id;
$offered->quantity = 3;
$offered->provided_quantity = 3;
$offered->to_be_managed_by_inventory = 'Yes';
$offered->is_service_offered = 'Yes';
$offered->is_completed = 'Yes';
$offered->due_term_id = $activeTerm->id;
$offered->stock_batch_id = StockBatch::where('is_archived', 'No')->first()->id;
$offered->save();
```

## Troubleshooting

### Issue: No Data Showing

**Possible Causes:**
1. No subscriptions have `to_be_managed_by_inventory = 'Yes'`
2. Enterprise scoping filtering out all records
3. Database connection issue

**Solution:**
- Check service configuration: Ensure services are marked for inventory management
- Verify user enterprise_id matches subscriptions
- Check database connectivity

### Issue: Available Stock Shows Zero

**Possible Causes:**
1. No stock batches match service name
2. All matching batches are archived
3. Stock batch quantities are zero

**Solution:**
- Check stock_item_categories.name matches service name
- Ensure stock batches are not archived (`is_archived = 'No'`)
- Verify stock batch `current_quantity` values

### Issue: Manage Button Not Working

**Possible Causes:**
1. InventorySubscriptionController not registered
2. Incorrect route configuration
3. Permissions issue

**Solution:**
- Verify route exists: `php artisan route:list | grep inventory-subscriptions`
- Check `app/Admin/routes.php` includes InventorySubscriptionController
- Verify user has permission to access inventory subscriptions

### Issue: Slow Dashboard Loading

**Possible Causes:**
1. Large number of subscriptions
2. Missing database indexes
3. N+1 query problems

**Solution:**
- Add indexes on frequently queried columns
- Implement caching (5-10 minute cache)
- Use database query logging to identify slow queries:
  ```php
  DB::enableQueryLog();
  // ... run dashboard
  dd(DB::getQueryLog());
  ```

## Future Enhancements

### Potential Improvements

1. **Charts & Graphs**:
   - Pie chart for status distribution
   - Bar chart for pending qty vs available stock
   - Line chart for completion trend over time

2. **Filters**:
   - Filter by academic term
   - Filter by service category
   - Date range filters

3. **Caching**:
   ```php
   $stats = Cache::remember('inventory_stats_' . $eid, 600, function() {
       // Build all metrics
       return compact(...);
   });
   ```

4. **Export Functionality**:
   - Export pending services to Excel
   - Generate printable inventory report
   - Email alerts for low stock

5. **Real-Time Updates**:
   - Auto-refresh every 5 minutes
   - WebSocket notifications for new subscriptions
   - Live stock quantity updates

6. **Advanced Analytics**:
   - Trend analysis (completion rate over time)
   - Service demand forecasting
   - Stock turnover rates
   - Store keeper performance metrics

## Related Documentation

- [Service Subscription Inventory Integration](INVENTORY_IMPROVEMENTS_COMPLETE.md)
- [Inventory Subscription Controller](INVENTORY_SUBSCRIPTION_CONTROLLER.md)
- [Inventory Service Integration](INVENTORY_SERVICE_INTEGRATION_DOCUMENTATION.md)
- [Inventory Integration Implementation Summary](INVENTORY_INTEGRATION_IMPLEMENTATION_SUMMARY.md)

## Summary

The Stock Dashboard now provides comprehensive visibility into service subscription inventory management with:

✅ **4 summary cards** for at-a-glance metrics
✅ **Services pending panel** showing inventory needs
✅ **Latest incomplete subscriptions** for quick action
✅ **Real-time data** with enterprise scoping
✅ **Responsive design** for all devices
✅ **Visual consistency** with existing stock stats
✅ **Direct action links** to manage inventory
✅ **Color-coded indicators** for quick status identification
✅ **Empty state handling** for better UX
✅ **Performance optimized** queries with eager loading

The dashboard empowers store keepers and administrators to monitor, analyze, and act on inventory subscription data efficiently.
