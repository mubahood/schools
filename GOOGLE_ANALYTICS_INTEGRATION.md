# Google Analytics 4 Integration Documentation

## Overview
Google Analytics 4 (GA4) has been successfully integrated into the school management system using property ID **G-484716763**.

## Integration Points

### 1. Main Public Layout (`layouts/modern-public.blade.php`)
- **Location**: Added before `</head>` tag
- **Covers**: All public pages including landing page, knowledge base, and other public views
- **Features**:
  - Standard GA4 tracking code
  - Custom dimensions for school_name and user_type
  - Enhanced page view tracking with school metadata
  - App version tracking

### 2. Authentication Pages (`auth/login.blade.php`)
- **Location**: Added before `</head>` tag in login page
- **Features**:
  - Login page visit tracking
  - Login attempt event tracking
  - CAPTCHA refresh event tracking
  - Authentication-specific page categorization

### 3. Knowledge Base (`knowledge-base/layout.blade.php`)
- **Additional Tracking**:
  - Search query tracking
  - Category navigation click tracking
  - Support link click tracking
  - Knowledge base specific user interactions

## Tracked Events

### Standard Events
- **page_view**: Automatic page view tracking on all pages
- **login_attempt**: When users submit login form
- **search**: When users search in knowledge base
- **click**: For category navigation and support links

### Custom Dimensions
- **dimension1**: school_name - Tracks which school is being accessed
- **dimension2**: user_type - Tracks type of user (admin, teacher, student, etc.)

### Custom Parameters
- **school_name**: Current school/company name
- **app_version**: Application version from config
- **page_type**: Type of page (authentication, knowledge_base, etc.)

## Event Categories
- **authentication**: Login attempts, password resets, etc.
- **knowledge_base**: KB searches, category navigation
- **security**: CAPTCHA refreshes, security events
- **support**: Support link clicks, help requests

## Files Modified
1. `/resources/views/layouts/modern-public.blade.php`
2. `/resources/views/auth/login.blade.php`
3. `/resources/views/knowledge-base/layout.blade.php`

## Google Analytics Property Details
- **Property ID**: G-484716763
- **Measurement Protocol**: GA4
- **Tracking Method**: gtag.js (Global Site Tag)

## Benefits
1. **User Behavior Analysis**: Track how users navigate through the system
2. **Performance Monitoring**: Monitor page load times and user engagement
3. **School-Specific Analytics**: See usage patterns per school
4. **Authentication Insights**: Track login success rates and user patterns
5. **Knowledge Base Usage**: Understand which help topics are most accessed
6. **Support Optimization**: Track which support channels are most used

## Next Steps
1. Set up Google Analytics dashboard with custom reports
2. Configure conversion goals for key actions (successful logins, registrations)
3. Set up automated reports for school administrators
4. Consider adding more granular tracking for specific features

## Privacy Compliance
- Analytics respects user privacy
- No personally identifiable information (PII) is tracked
- School names are tracked for institutional analytics only
- Consider adding cookie consent if required by local regulations

## Testing
All views have been tested and compile successfully with the analytics integration. The tracking code is properly loaded and events are being sent to Google Analytics.