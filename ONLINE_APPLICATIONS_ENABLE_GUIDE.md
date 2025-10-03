# Online Applications Enable Guide

## Overview
The system now supports enabling/disabling online student applications on a per-school basis. Schools will only show their application portal when they explicitly enable this feature in their settings.

## How It Works

### 1. Enabling Online Applications for a School

**Via Admin Panel:**
1. Log in to the admin panel
2. Navigate to **Enterprises** (Schools)
3. Click **Edit** on the school you want to enable applications for
4. Scroll down to the **"Online Application Portal Settings"** section (divider)
5. Set **"Accept Online Applications"** to **"Yes - Enable online application portal"**
6. Configure additional settings when enabled:
   - Application Deadline (optional)
   - Application Fee (default: 0 for free)
   - Application Instructions (optional)
   - Custom Welcome Message (optional)
7. Scroll to **"Required Documents Configuration"** section
8. Check the documents you want applicants to submit
9. Click **Submit** to save

**Via Database (Quick Enable):**
```sql
UPDATE enterprises 
SET accepts_online_applications = 'Yes' 
WHERE id = YOUR_SCHOOL_ID;
```

### 2. Application Portal Behavior

**When `accepts_online_applications = 'Yes'`:**
- ✅ Application form is fully accessible
- ✅ Students can submit applications
- ✅ All configured fields and documents are displayed
- ✅ "Start Application" button is active

**When `accepts_online_applications = 'No'`:**
- ⚠️ Application form shows "closed" message
- ⚠️ Students cannot submit applications
- ℹ️ Custom message can be configured (optional)
- ℹ️ Default message: "Online applications are currently closed. Please check back later."

### 3. Application Deadline (Optional)

If you set an `application_deadline` date:
- ✅ Applications are accepted if deadline is in the future
- ❌ Applications automatically close when deadline passes
- The system checks: `Carbon::parse($deadline)->isFuture()`

**Example:**
```php
// Set deadline to December 31, 2025
$enterprise->application_deadline = '2025-12-31';
$enterprise->save();
```

### 4. Current Status Check

**Check which schools have applications enabled:**
```bash
php artisan tinker --execute="
\$schools = App\Models\Enterprise::where('accepts_online_applications', 'Yes')->get(['id', 'name']);
foreach (\$schools as \$school) {
    echo \$school->id . ': ' . \$school->name . PHP_EOL;
}
"
```

**Check specific school status:**
```bash
php artisan tinker --execute="
\$school = App\Models\Enterprise::find(YOUR_SCHOOL_ID);
echo 'School: ' . \$school->name . PHP_EOL;
echo 'Accepts Applications: ' . (\$school->acceptsApplications() ? 'YES' : 'NO') . PHP_EOL;
"
```

## Recently Enabled Schools

✅ **Tusome** (ID: 1) - Applications Enabled
✅ **Tusome Primary School** (ID: 9) - Applications Enabled  
✅ **Newline Technologies Primary School** (ID: 10) - **JUST ENABLED**

## Configuration Fields Reference

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `accepts_online_applications` | Radio (Yes/No) | Yes | Master switch to enable/disable portal |
| `application_deadline` | Text | No | Display text for deadline (e.g., "December 31, 2025") |
| `application_fee` | Currency | No | Fee in UGX (0 = free) |
| `application_instructions` | Textarea | No | Instructions shown on landing page |
| `custom_application_message` | Rich Text | No | Custom welcome message for applicants |
| `required_application_documents` | JSON | No | Configured via checkboxes in admin panel |

## Document Configuration

Standard documents available (configured via checkboxes):
- Birth Certificate
- Previous School Report
- Passport Photo
- Parent/Guardian ID
- Immunization Records
- Recommendation Letter
- School Leaving Certificate
- Medical Report

**Custom Documents:**
Add school-specific documents in the "Custom Documents" textarea:
```
Transfer Certificate|required
Character Certificate|optional
Fee Clearance|required
```

## Troubleshooting

### Issue: "Applications are closed" showing but setting is enabled

**Solution:**
1. Clear application cache:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

2. Verify database value:
   ```sql
   SELECT id, name, accepts_online_applications, application_deadline 
   FROM enterprises 
   WHERE id = YOUR_SCHOOL_ID;
   ```

3. Check if deadline has passed:
   - If `application_deadline` is set and is in the past, applications will be closed
   - Either remove the deadline or set a future date

### Issue: Changes not reflecting on website

**Solution:**
- Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
- Clear Laravel caches (command above)
- Check if you're editing the correct school enterprise record

## Technical Details

### Enterprise Model Method
```php
public function acceptsApplications()
{
    // Must have applications enabled
    if ($this->accepts_online_applications !== 'Yes') {
        return false;
    }
    
    // If deadline is set, must be in the future
    if ($this->application_deadline) {
        return Carbon::parse($this->application_deadline)->isFuture();
    }
    
    // No deadline = always open (when enabled)
    return true;
}
```

### Landing Page Logic
```php
// Get first school with applications enabled
$enterprise = Enterprise::where('accepts_online_applications', 'Yes')->first();

// Check if accepts applications (includes deadline check)
$acceptsApplications = $enterprise->acceptsApplications();

// Pass to view
return view('student-application.landing', [
    'acceptsApplications' => $acceptsApplications,
    // ... other data
]);
```

## Best Practices

1. **Enable applications early** - Set up all configuration before enabling
2. **Test before going live** - Submit a test application to verify everything works
3. **Set clear deadlines** - If using deadlines, make them clear in instructions
4. **Configure documents** - Select only truly required documents to avoid overwhelming applicants
5. **Use custom messages** - Add welcoming, informative messages to guide applicants
6. **Monitor applications** - Check the Applications section regularly for new submissions

## Quick Enable Checklist

- [ ] Enable "Accept Online Applications" = Yes
- [ ] Set Application Fee (or leave at 0)
- [ ] Add Application Instructions
- [ ] Configure Required Documents (checkboxes)
- [ ] Test the application form
- [ ] Share the application URL with prospective students

## Support

For issues or questions:
1. Check this guide first
2. Review the console logs for errors
3. Verify database values match expected configuration
4. Clear all caches before assuming there's a bug

---

**Last Updated:** October 3, 2025  
**Status:** ✅ System Working - Schools can enable/disable applications as needed
