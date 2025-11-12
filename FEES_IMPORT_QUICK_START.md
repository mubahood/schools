# FEES IMPORT SYSTEM - QUICK START GUIDE

## 🚀 How to Use the Optimized Import System

### Step 1: Prepare Your Excel File

Your Excel file should have this structure:

| Column | Content | Example |
|--------|---------|---------|
| A | Student Name | John Doe |
| B | School Pay Code OR Reg Number | SP001234 |
| C-D | Other Info | Class, etc |
| E | Previous Term Balance | -50000 |
| F | Current Balance | -120000 |
| G-Z | Service Fees | Tuition, Transport, Meals, etc |

**Important Notes:**
- First row must be headers
- Negative numbers represent amounts owed
- Service columns will be auto-detected from headers

### Step 2: Access the Import Section

1. Login to admin panel
2. Navigate to **Fees Data Import** section
3. Click **Create** button

### Step 3: Fill Import Form

```
Title: "Term 2 Income - October 2025"

Identify By:
  ○ School Pay Account ID  ← Select if using payment codes
  ○ Registration Number     ← Select if using reg numbers

School Pay Column: B  (if using payment codes)
OR
Reg Number Column: B  (if using reg numbers)

Services Columns: ☑ H  ☑ I  ☑ J  ☑ K
(Select all columns containing service fees)

Previous Term Balance Column: E

Current Balance Column: F

Does balance column cater for negative sign?:
  ● Yes  (if negative numbers are already negative)
  ○ No   (if positive numbers represent debt)

Upload File: [Choose your .xlsx file]
```

### Step 4: Validate the Import

After creating the import:
1. Click the **"Validate"** button
2. Review validation results:
   - ✓ File size OK
   - ✓ Total rows found
   - ✓ Columns mapped correctly  
   - ✓ Sample students found in database
3. Fix any errors reported
4. Estimated processing time will be shown

### Step 5: Process the Import

1. Click **"Import Data"** button
2. Wait for processing (don't close the window)
3. Monitor progress:
   - Total rows processed
   - Success count
   - Failed count
   - Skipped count

### Step 6: Review Results

After completion:
1. View summary statistics
2. Check failed records (if any)
3. Click **"View Records"** to see details
4. For failed records, click **"Retry Failed"**

## 📋 Column Mapping Guide

### Required Columns:

#### Option A: Using School Pay Codes
```
School Pay Column: The column with payment codes (e.g., SP001234)
```

#### Option B: Using Registration Numbers
```
Reg Number Column: The column with student reg numbers (e.g., 2024001)
```

### Optional Columns:

```
Previous Term Balance: Past term arrears/credits
Current Balance: Updated balance after this term
Service Columns: Individual fees (Tuition, Transport, etc.)
```

## 💡 Pro Tips

### Best Practices:

1. **Start Small**: Test with 5-10 rows first
2. **Check Data**: Ensure student codes exist in database
3. **Use Validation**: Always validate before importing
4. **Backup First**: Export current data before import
5. **Monitor Progress**: Keep browser tab open during import
6. **Check Logs**: Review failed records for errors

### Common Mistakes to Avoid:

❌ Wrong identifier type (using reg numbers when School Pay selected)
❌ Missing students in database  
❌ Wrong column letters
❌ Uploading same file twice
❌ Closing browser during processing

### File Checklist:

✅ File format is `.xlsx`
✅ First row contains headers
✅ No completely empty rows (except at end)
✅ Student identifiers match database
✅ Numeric values are properly formatted
✅ File size under 50MB

## 🔍 Understanding Results

### Import Statuses:

- **Pending**: Ready to process
- **Processing**: Currently importing
- **Completed**: Successfully finished
- **Failed**: Encountered errors
- **Cancelled**: Stopped by user

### Record Statuses:

- **Completed**: Row successfully processed
- **Failed**: Error occurred (see error message)
- **Skipped**: Empty row or missing identifier
- **Processing**: Currently being processed

## 🐛 Troubleshooting

### "Student not found"

**Problem**: Identifier doesn't match any student
**Solution**:
1. Check identifier column is correct
2. Verify students exist with those codes
3. Check identifier type matches (Pay Code vs Reg Number)

### "Duplicate file detected"

**Problem**: This exact file was already imported
**Solution**:
1. This is NORMAL - prevents duplicate imports
2. If you need to re-import, make a small change to the file
3. Or check existing import for results

### "Validation failed"

**Problem**: File structure has issues
**Solution**:
1. Read validation error messages carefully
2. Check column letters are correct
3. Ensure file has data rows (not just headers)
4. Verify headers are in row 1

### "Import is locked"

**Problem**: Another user is processing
**Solution**:
1. Wait for other user to finish
2. Locks auto-expire after 30 minutes
3. Contact admin if stuck

## 📞 Need Help?

1. **Check validation messages** - they're very specific
2. **Review failed records** - each has error details
3. **Check the logs** - detailed technical info
4. **Contact support** - provide import ID

## ✨ New Features You'll Love

1. **Duplicate Prevention**: Can't accidentally import same file twice
2. **Progress Tracking**: See real-time status updates
3. **Retry Failed**: Automatically retry failed records
4. **Smart Validation**: Catches errors before importing
5. **Detailed Errors**: Know exactly what went wrong
6. **Batch Processing**: Faster and more reliable
7. **Lock Protection**: No concurrent processing conflicts

## 📊 Sample Excel Template

Download this structure:

```
Row 1: Name | PayCode | Class | X | PrevBal | CurrBal | X | Tuition | Transport | Meals
Row 2: John  | SP001   | S1    | X | -50000  | -120000 | X | 80000   | 30000     | 40000
Row 3: Jane  | SP002   | S2    | X | -30000  | -95000  | X | 80000   | 0         | 35000
```

Column letters: A, B, C, D, E, F, G, H, I, J

Configuration for above:
- Identify By: School Pay Account ID
- School Pay Column: B
- Previous Balance: E
- Current Balance: F
- Services: H, I, J

---

**That's it! You're ready to import fees data safely and efficiently.** 🎉
