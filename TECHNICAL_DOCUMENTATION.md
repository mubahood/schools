# School Dynamics - Laravel Backend API Documentation

## Project Overview

**School Dynamics** is a comprehensive School Management System (SMS) built as a SaaS platform using Laravel 8.x. It serves as the backend API for a multi-tenant school management solution that handles academic operations, financial management, student information systems, and administrative tasks for educational institutions.

## Architecture & Technology Stack

### Core Technologies
- **Framework**: Laravel 8.54+
- **PHP Version**: ^7.3|^8.0
- **Database**: MySQL/MariaDB with Eloquent ORM
- **Authentication**: JWT (JSON Web Tokens) via tymon/jwt-auth
- **Admin Panel**: Encore Laravel Admin 1.*
- **API Documentation**: Laravel LaRecipe (binarytorch/larecipe)

### Key Dependencies
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Operations**: maatwebsite/excel
- **Barcode Generation**: milon/barcode  
- **Image Processing**: spatie/laravel-image-optimizer, stefangabos/zebra_image
- **Cross-Origin Resource Sharing**: fruitcake/laravel-cors
- **UUID Support**: goldspecdigital/laravel-eloquent-uuid

## System Architecture

### Multi-Tenant SaaS Structure
The system is designed as a multi-tenant SaaS platform where:
- Each school is an **Enterprise** (tenant)
- Users belong to specific enterprises
- Data isolation is maintained through enterprise_id foreign keys
- Shared codebase serves multiple schools simultaneously

### Core Modules

#### 1. User Management & Authentication
- **Models**: User, Administrator, AdminRole, AdminRoleUser
- **Features**: 
  - Role-based access control (RBAC)
  - JWT-based API authentication  
  - Multiple user types: admin, teacher, student, parent, employee, bursar, dos
  - User profiles with comprehensive biographical data

#### 2. Academic Management
- **Models**: AcademicClass, AcademicYear, Term, Subject, StudentHasClass
- **Features**:
  - Class and stream management
  - Academic year and term cycles
  - Subject allocation and teacher assignments
  - Student class enrollment tracking
  - Curriculum management (secular and theology)

#### 3. Student Information System
- **Models**: User (students), StudentHasClass, StudentHasSemeter, StudentDataImport
- **Features**:
  - Student registration and profiling
  - Class enrollment and tracking
  - Academic progression management
  - Bulk student data import
  - Parent/guardian information management

#### 4. Financial Management
- **Models**: Account, Transaction, SchoolFeesDemand, AcademicClassFee
- **Features**:
  - Student financial accounts
  - School fees management
  - Payment processing and tracking
  - Fee demands and notices generation
  - Integration with SchoolPay payment gateway
  - Financial reporting and balance tracking

#### 5. Attendance & Session Management
- **Models**: Session, Participant, Visitor, VisitorRecord
- **Features**:
  - Digital attendance tracking
  - Session management for classes
  - Visitor registration and management
  - Attendance reporting and analytics

#### 6. Examination & Assessment
- **Models**: MarkRecord, StudentReportCard, TermlyReportCard, Exam, Mark
- **Features**:
  - Marks entry and management
  - Report card generation
  - Academic performance tracking
  - Examination scheduling
  - Grade calculations and analytics

#### 7. Transport Management
- **Models**: TransportRoute, TransportVehicle, TransportSubscription
- **Features**:
  - School transport route management
  - Vehicle tracking and management
  - Student transport subscriptions
  - Driver management

#### 8. Meal Card & Gate Pass System
- **Features**:
  - Digital meal card generation
  - Student gate pass creation
  - Integration with school fees system
  - Photo inclusion support

## API Architecture

### RESTful API Design
- **Base Controller**: ApiMainController
- **Authentication**: JWT middleware for protected routes
- **Response Format**: Standardized JSON responses using ApiResponser trait
- **Error Handling**: Comprehensive error handling with proper HTTP status codes

### Key API Endpoints

#### Authentication
```php
POST /api/users/register - User registration
POST /api/users/login - User authentication
POST /api/forget-password-request - Password reset request
POST /api/forget-password-reset - Password reset
```

#### Dynamic Resource Management
```php
GET/POST /api/dynamic-listing/{model} - Dynamic model operations
GET /api/ajax-users - User search/lookup
GET /api/streams - Academic streams lookup
```

#### Student Management
```php
GET /api/my-students - Get teacher's students
GET /api/student-has-class - Student class relationships
POST /api/students - Create/update students
```

### Database Architecture

#### Multi-Tenant Design
- All major tables include `enterprise_id` for tenant isolation
- Foreign key constraints maintain data integrity
- Soft deletes implemented where appropriate

#### Key Database Tables

**Users & Authentication**
- `administrators` - Main users table
- `admin_roles` - System roles
- `admin_role_users` - Role assignments

**Academic Structure**
- `enterprises` - School/tenant information  
- `academic_years` - Academic year cycles
- `terms` - Academic terms
- `academic_classes` - Class definitions
- `subjects` - Subject management
- `student_has_classes` - Student-class relationships

**Financial Management**
- `accounts` - Financial accounts
- `transactions` - Payment records
- `school_fees_demands` - Fee demands
- `academic_class_fees` - Class fee structures

**Assessment & Reporting**
- `mark_records` - Student marks
- `student_report_cards` - Individual reports
- `termly_report_cards` - Term reports

## Security Features

### Authentication & Authorization
- JWT token-based authentication
- Role-based access control (RBAC)
- Enterprise-level data isolation
- API rate limiting and throttling

### Data Protection
- Password hashing using Laravel's Hash facade
- SQL injection prevention through Eloquent ORM
- Cross-site scripting (XSS) protection
- CSRF protection for web routes

## Integration Capabilities

### Payment Gateways
- **SchoolPay Integration**: Automated payment processing
- **PegPay Support**: Alternative payment method
- Webhook handling for payment notifications

### External Services
- **OneSignal**: Push notification service
- **School Pay**: Payment gateway integration
- **SMS Services**: For notifications and alerts

## Deployment & Configuration

### Environment Configuration
```php
APP_NAME=School Dynamics
APP_ENV=production
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
TIMEZONE=Africa/Nairobi
```

### Multi-Language Support
Localization support for:
- English (default)
- Arabic, Azerbaijani, Bengali, German
- Spanish, Persian, French, Hebrew
- Indonesian, Japanese, Korean, Malay
- And many more regional languages

## File & Media Management

### Storage Structure
- Student photos and documents
- Signature storage for officials
- Report card PDF generation
- Academic documents and certificates

### PDF Generation
- Dynamic meal cards
- School gate passes  
- Student report cards
- Financial statements
- Academic transcripts

## Performance Optimizations

### Database Optimizations
- Proper indexing on frequently queried columns
- Query optimization using Eloquent relationships
- Database connection pooling

### Caching Strategy
- Model relationship caching
- Configuration caching
- Route caching for production

## API Documentation Features

### Built-in Documentation
- LaRecipe integration for API documentation
- Interactive API explorer
- Code examples and response formats
- Authentication guides

## Development & Maintenance

### Code Organization
- **Controllers**: Organized by module (Admin, API)
- **Models**: Feature-rich Eloquent models with relationships
- **Middleware**: Custom authentication and authorization
- **Traits**: Reusable functionality (ApiResponser, UUID)

### Testing Support
- PHPUnit testing framework
- Feature and unit tests
- Database factories for test data
- Mockery for mocking dependencies

## Extensibility

### Plugin Architecture
- Modular design allows easy feature additions
- Laravel package integration support
- Custom service provider support

### API Versioning
- RESTful API design allows for version management
- Backward compatibility considerations
- Gradual feature rollout capabilities

This Laravel backend serves as a robust, scalable foundation for the School Dynamics SaaS platform, providing comprehensive school management capabilities through a well-structured API that supports the Flutter mobile application and web interfaces.
