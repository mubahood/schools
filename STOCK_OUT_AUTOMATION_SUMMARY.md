# Stock-Out Record Auto-Creation Summary

## Implementation Overview

When a `ServiceItemToBeOffered` is marked as **"Offered"**, the system now automatically creates a corresponding **Stock Record (OUT)** to track the inventory movement.

## How It Works

### 1. Trigger Event
- **When**: `ServiceItemToBeOffered.is_service_offered` changes from 'No' to 'Yes'
- **Where**: `ServiceItemToBeOffered::boot()` method (updated event)

### 2. Auto-Created Stock Record
The system creates a `StockRecord` with:
- **Type**: `OUT` (negative quantity)
- **Quantity**: Same as `ServiceItemToBeOffered.quantity`
- **Stock Batch**: Links to selected `stock_batch_id`
- **Category**: Links to `stock_item_category_id`
- **Received By**: Student (from subscription's `administrator_id`)
- **Created By**: User who marked as offered (`offered_by_id`)
- **Date**: `offered_at` timestamp
- **Description**: Detailed description including:
  - Service Item ID
  - Item name
  - Student name
  - Service name
  - Quantity

### 3. Stock Batch Update
- `StockBatch.current_quantity` automatically decreases
- Handled by `StockRecord::boot()` method
- Updates happen through `StockBatch::update_balance()`

### 4. Duplicate Prevention
- Checks if stock record already exists for same:
  - Service subscription
  - Stock batch
  - Item category
  - Type (OUT)
- Updates existing record if found (instead of creating duplicate)

## Validation

### Before Creating Stock Record
1. ✅ Stock batch must be selected
2. ✅ Quantity must be > 0
3. ✅ Batch must have sufficient quantity
4. ✅ Batch must match item category
5. ✅ Service subscription must exist
6. ✅ Student must exist

### Error Messages
- "Stock batch must be selected before marking item as offered"
- "Quantity must be greater than zero"
- "Insufficient stock in batch. Available: X, Required: Y"
- "Stock batch #X not found"
- "Service subscription not found"
- "Student not found for subscription"

## Database Flow

```
ServiceItemToBeOffered (is_service_offered = 'Yes')
    ↓
createStockOutRecord()
    ↓
StockRecord created (type = 'OUT', quanity = negative)
    ↓
StockRecord::created() event fires
    ↓
StockBatch::update_balance() called
    ↓
StockBatch.current_quantity reduced
    ↓
StockItemCategory::update_quantity() called
```

## Code Locations

- **Model**: `/app/Models/ServiceItemToBeOffered.php`
  - `boot()` method (lines 30-50)
  - `createStockOutRecord()` method (lines 58-125)
  - `generateStockRecordDescription()` method (lines 129-145)
  - `stockRecord()` relationship (lines 149-156)

- **Controller**: `/app/Admin/Controllers/ServiceItemToBeOfferedController.php`
  - Validation in `saving()` callback ensures stock batch selected when marking as offered

## Testing

To test the implementation:

1. Navigate to Service Items Tracking
2. Edit an item with status "Not Offered"
3. Select stock batch
4. Enter quantity
5. Change status to "Offered"
6. Save

**Expected Results**:
- Stock Record (OUT) created automatically
- Stock batch quantity decreased
- Item shows as "Offered" with timestamp
- Stock record visible in Stock Records list

## Notes

- Stock-out records are linked via `service_subscription_id`
- Each item can have its own stock batch and quantity
- Multiple items from same subscription can use different batches
- System prevents duplicate stock records for same item
