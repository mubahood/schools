# Assignment & Homework Module — Implementation Guide

> **Purpose**: Document the complete Assignments & Homework module across the **Laravel web portal** and **Flutter mobile app** to guide future implementation of similar features.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Database Schema](#database-schema)
3. [Web Portal (Laravel-Admin)](#web-portal-laravel-admin)
4. [API Layer](#api-layer)
5. [Mobile App (Flutter)](#mobile-app-flutter)
6. [Design Patterns & Conventions](#design-patterns--conventions)
7. [Checklist for Implementing Similar Modules](#checklist-for-implementing-similar-modules)

---

## Architecture Overview

```
┌─────────────────┐     REST API      ┌─────────────────┐
│  Flutter App     │ ◄──────────────► │  Laravel Backend │
│  (Mobile)        │   /api/...       │  (PHP)           │
└─────────────────┘                   └─────────────────┘
        │                                      │
        │  AssignmentApi.dart             ApiAssignmentController.php
        │  (Dio HTTP client)             (JSON responses)
        │                                      │
        ▼                                      ▼
  Local Display                     ┌─────────────────┐
  (Detail screens,                  │  Laravel-Admin   │
   Submission forms)                │  (Web CRUD)      │
                                    └─────────────────┘
                                    AssignmentController.php
                                    AssignmentSubmissionController.php
```

### Key Relationships
- **Assignment** → has many **AssignmentSubmission** records
- Each submission is auto-generated per student when the assignment is created
- Teachers create assignments → students submit → teachers grade

---

## Database Schema

### `assignments` table
| Column              | Type     | Notes                                          |
|---------------------|----------|-------------------------------------------------|
| id                  | bigint   | Primary key                                     |
| enterprise_id       | bigint   | Multi-tenant scoping                            |
| academic_year_id    | bigint   | Auto-filled from active term                    |
| term_id             | bigint   | Auto-filled from active term                    |
| subject_id          | bigint   | FK → subjects                                   |
| academic_class_id   | bigint   | FK → academic_classes (target class)             |
| stream_id           | bigint   | FK → academic_class_sctreams (optional)          |
| created_by_id       | bigint   | FK → users (teacher)                             |
| title               | string   | Required, min 3 chars                            |
| description         | text     | **HTML content** (Quill editor on web)            |
| instructions        | text     | Plain text or HTML                               |
| type                | enum     | Homework, Assignment, Project, Classwork, Quiz   |
| due_date            | date     | Nullable                                         |
| issue_date          | date     | Defaults to today                                |
| attachment          | string   | File path (stored in `storage/assignments/`)     |
| max_score           | decimal  | Nullable                                         |
| is_assessed         | enum     | Yes / No                                         |
| submission_type     | enum     | Both, File, Text, None                           |
| status              | enum     | Draft, Published, Closed, Archived               |
| marks_display       | enum     | Yes / No (show scores to students/parents)       |
| total_students      | int      | Auto-updated via model stats                     |
| submitted_count     | int      | Auto-updated via model stats                     |
| graded_count        | int      | Auto-updated via model stats                     |
| details             | text     | Additional notes                                 |

### `assignment_submissions` table
| Column              | Type      | Notes                                          |
|---------------------|-----------|------------------------------------------------|
| id                  | bigint    | Primary key                                    |
| enterprise_id       | bigint    | Multi-tenant scoping                           |
| assignment_id       | bigint    | FK → assignments                               |
| student_id          | bigint    | FK → users                                     |
| academic_class_id   | bigint    | Denormalized from assignment                   |
| stream_id           | bigint    | Denormalized from assignment                   |
| subject_id          | bigint    | Denormalized from assignment                   |
| status              | enum      | Pending, Submitted, Graded, Returned, Late, Not Submitted |
| submission_text     | text      | Student's text answer                          |
| attachment          | string    | File path (stored in `storage/assignments/student-submissions/`) |
| submitted_at        | datetime  | Auto-set when student submits                  |
| score               | decimal   | Teacher-assigned score                         |
| max_score           | decimal   | Copied from assignment at creation             |
| feedback            | text      | Teacher feedback (visible to student)          |
| teacher_comment     | text      | Internal comment (staff only)                  |
| graded_by_id        | bigint    | FK → users (teacher who graded)                |
| graded_at           | datetime  | Auto-set when status → Graded                 |

---

## Web Portal (Laravel-Admin)

### Files
| File | Purpose |
|------|---------|
| `app/Admin/Controllers/AssignmentController.php` | CRUD for assignments |
| `app/Admin/Controllers/AssignmentSubmissionController.php` | View/grade submissions |
| `app/Models/Assignment.php` | Eloquent model with boot events |
| `app/Models/AssignmentSubmission.php` | Eloquent model with auto-stats |

### Key Patterns Used

#### 1. Rich Text Editor (Quill)
```php
// Use $form->quill() for rich HTML content
$form->quill('description', 'Description');
```
- Saves HTML content to the database
- Used across 13+ forms in the codebase (documents, posts, report cards, etc.)
- The Flutter app must render this with `Html()` widget from `flutter_html`

#### 2. Active Academic Year Filtering
```php
// Always filter classes to the active academic year
$activeTerm = $u->ent->active_term();
if ($activeTerm) {
    $classes = AcademicClass::where('academic_year_id', $activeTerm->academic_year_id)
        ->pluck('name', 'id')->toArray();
}
```
- Use `$u->ent->active_term()` to get the current term
- Filter `AcademicClass` by `academic_year_id` from the active term
- Apply in both grid filters AND form selects

#### 3. Role-Based Access
```php
$isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
if (!$isPrivileged) {
    $grid->model()->where('created_by_id', $u->id); // Teachers see only theirs
}
```

#### 4. Cascading Select (Class → Stream)
```php
$form->select('academic_class_id', 'Target Class')
    ->options($classes)
    ->load('stream_id', url('/api/streams?enterprise_id=' . $u->enterprise_id));

$form->select('stream_id', 'Stream (Optional)')
    ->options(function ($id) {
        if (!$id) return [];
        $s = AcademicClassSctream::find($id);
        if ($s) return [$s->id => $s->name_text];
        return [];
    });
```

#### 5. Model Boot Events (Auto-Generation)
```php
// In Assignment model boot():
static::created(function ($m) {
    $m->generateSubmissions();  // Auto-create submission records for all students
});

// In AssignmentSubmission model boot():
static::updated(function ($m) {
    $m->assignment->updateStats(); // Keep parent stats in sync
});
```

#### 6. Grid Display with Computed Columns
```php
$grid->column('progress', 'Submissions')->display(function () {
    $total = $this->total_students ?: 0;
    $submitted = $this->submitted_count ?: 0;
    $pct = round(($submitted / $total) * 100);
    return "<small>{$submitted}/{$total} ({$pct}%)</small>";
});
```

#### 7. Submission Grid (Edit-Only, No Create)
```php
$grid->disableCreateButton(); // Submissions are auto-generated, not manually created
```

---

## API Layer

### File: `app/Http/Controllers/ApiAssignmentController.php`
### Routes: `routes/api.php`

| Method | Endpoint | Purpose | Access |
|--------|----------|---------|--------|
| GET    | `/api/assignments` | List assignments | All (filtered by role) |
| GET    | `/api/assignments/{id}` | Single assignment | All |
| POST   | `/api/assignments` | Create assignment | Staff only |
| POST   | `/api/assignments/{id}/status` | Update status | Staff only |
| POST   | `/api/assignments/{id}/regenerate-submissions` | Regenerate | Staff only |
| GET    | `/api/assignment-submissions` | List submissions | All (filtered by role) |
| POST   | `/api/assignment-submissions/{id}/submit` | Student submit | Students only |
| POST   | `/api/assignment-submissions/{id}/grade` | Grade submission | Staff only |

### Role-Based Query Filtering (Critical Pattern)
```php
// The API controller uses private methods to scope queries by user type:
private function assignmentsQueryForUser(User $user) {
    if ($this->canManageAssignments($user)) {
        return $this->assignmentQueryForManager($user);  // Staff: own or all
    }
    if ($user->user_type === 'student') {
        // Students: Published assignments for their class/stream
        $myClasses = StudentHasClass::where('administrator_id', $user->id)->get();
        // ... filter by class_id + stream_id
    }
    if ($user->user_type === 'parent') {
        // Parents: See assignments for their children's classes
        $studentIds = User::where('parent_id', $user->id)->pluck('id');
        // ... filter by children's classes
    }
}
```

### Submission Validation (in `submit()`)
```php
// Validate based on assignment's submission_type
$submissionType = $assignment->submission_type;
if ($submissionType === 'None') return error('Does not require submission');
if ($submissionType === 'Text' && $text === '') return error('Text required');
if ($submissionType === 'File' && !$hasFile) return error('File required');
if ($submissionType === 'Both' && !$hasFile && $text === '') return error('Provide text or file');

// Auto-detect late submissions
$isLate = strtotime(date('Y-m-d')) > strtotime($assignment->due_date);
$submission->status = $isLate ? 'Late' : 'Submitted';
```

### ApiResponser Trait
```php
use App\Traits\ApiResponser;
// Provides: $this->success($data, $message) and $this->error($message)
// Returns: { "code": 1, "data": [...], "message": "Success" }
// Error:   { "code": 0, "message": "Error text" }
```

---

## Mobile App (Flutter)

### Files
| File | Purpose |
|------|---------|
| `lib/screens/assignments/AssignmentsHomeScreen.dart` | Main screen + detail screens |
| `lib/models/AssignmentApi.dart` | HTTP API client |
| `lib/models/AssignmentModel.dart` | Data model |
| `lib/models/AssignmentSubmissionModel.dart` | Data model |

### Architecture Pattern

```
AssignmentsHomeScreen (StatefulWidget)
├── _AssignmentsHomeScreenState
│   ├── TabBar with 2 tabs: Assignments | Submissions
│   ├── assignmentCard() → taps to AssignmentDetailScreen
│   ├── submissionCard() → taps to SubmissionDetailScreen
│   ├── showCreateSheet() → bottom sheet for creating
│   ├── showSubmitSheet() → bottom sheet for submitting
│   └── showGradeSheet() → bottom sheet for grading
├── AssignmentDetailScreen (StatelessWidget, separate class)
└── SubmissionDetailScreen (StatelessWidget, separate class)
```

### Key Design Decisions

#### 1. Detail Screens as StatelessWidget with Callbacks
```dart
class AssignmentDetailScreen extends StatelessWidget {
  final AssignmentModel assignment;
  final Future<void> Function() onPublish;   // Callback to parent
  final Future<void> Function()? onSubmitUpdate;  // Nullable = disabled
  // ... all actions are callbacks, no API calls inside
}
```
**Why**: Keeps API logic centralized in the parent `_AssignmentsHomeScreenState`, which can refresh data after any action completes.

#### 2. HTML Content Rendering
```dart
import 'package:flutter_html/flutter_html.dart';

Widget _htmlContent(String htmlData) {
  return Html(
    data: htmlData,
    style: {
      '*': Style(
        color: AppColors.textPrimary,
        fontSize: FontSize(14),
        lineHeight: const LineHeight(1.6),
      ),
      'strong': Style(fontWeight: FontWeight.w700),
      'a': Style(
        color: AppColors.primary,
        textDecoration: TextDecoration.underline,
      ),
    },
  );
}
```
**When to use**: Any field stored with `$form->quill()` on the web portal contains HTML. Always render with `Html()` widget, never with plain `Text()`.

#### 3. PDF Viewing (In-App)
```dart
import 'package:schooldynamics/screens/students/PdfViewer.dart';

void _openAttachment(BuildContext context, String path) {
  final url = _attachmentUrl(path);  // Prepend base URL
  if (_isPdf(path)) {
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => PdfViewerScreen(url, 'Assignment Attachment'),
    ));
  } else {
    launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
  }
}

// URL construction for attachments
String _attachmentUrl(String path) {
  if (path.startsWith('http')) return path;
  return '${AppConfig.MAIN_SITE_URL}/storage/$path';
}
```
- PDFs: Open in built-in `PdfViewerScreen` (uses `SfPdfViewer.network()`)
- Images/docs: Open externally via `url_launcher`
- Attachment paths from API are relative (e.g., `assignments/teacher-attachments/abc.pdf`)
- Prepend `AppConfig.MAIN_SITE_URL + "/storage/"` to form the full URL

#### 4. File Picker for Submissions
```dart
import 'package:file_picker/file_picker.dart';

final result = await FilePicker.platform.pickFiles(
  type: FileType.custom,
  allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'xls', 'xlsx', 'ppt', 'pptx'],
);
if (result != null && result.files.isNotEmpty) {
  selectedFile = result.files.first;
  // Use selectedFile.path for API upload
}
```
**Why `file_picker` over `image_picker`**: Students need to submit PDFs, documents, and other file types — not just images.

#### 5. Submission Validation (Client-Side)
```dart
// Mirror the server-side submission_type validation
final submType = assignment?.submissionType ?? 'Both';

if (submType == 'None') {
  Utils.toast('This assignment does not require direct submission.', color: Colors.orange);
  return;
}
if (submType == 'Text' && text.isEmpty) {
  Utils.toast('Please enter your answer.', color: Colors.red);
  return;
}
if (submType == 'File' && filePath == null && submission.attachment.isEmpty) {
  Utils.toast('Please attach a file.', color: Colors.red);
  return;
}
if (submType == 'Both' && text.isEmpty && filePath == null && submission.attachment.isEmpty) {
  Utils.toast('Please provide text or attach a file.', color: Colors.red);
  return;
}
```

#### 6. API Client Pattern
```dart
class AssignmentApi {
  // GET requests use Utils.http_get (returns response.data)
  static Future<RespondModel> fetchAssignments({...}) async {
    final query = <String, dynamic>{'limit': limit};
    // Add optional filters...
    return RespondModel(await Utils.http_get('assignments', query));
  }

  // POST requests use Utils.http_post (supports FormData for files)
  static Future<RespondModel> submitAssignment({
    required int submissionId,
    String submissionText = '',
    String? attachmentPath,
  }) async {
    final body = <String, dynamic>{};
    if (submissionText.trim().isNotEmpty) body['submission_text'] = submissionText;
    if (attachmentPath != null) {
      body['attachment'] = await dio.MultipartFile.fromFile(attachmentPath, filename: ...);
    }
    return RespondModel(await Utils.http_post('assignment-submissions/$submissionId/submit', body));
  }
}
```

#### 7. Consistent Loading & Error Pattern
```dart
Utils.showLoader(true);
final resp = await AssignmentApi.someAction(...);
Utils.hideLoader();

if (resp.code == 1) {
  Utils.toast(resp.message.isEmpty ? 'Default success message.' : resp.message);
  Navigator.pop(context);
  await my_init();  // Refresh data
  return;
}
Utils.toast(resp.message.isEmpty ? 'Default error message.' : resp.message, color: Colors.red);
```

---

## Design Patterns & Conventions

### Design System Usage
```dart
import 'package:schooldynamics/design_system/design_system.dart';

// Colors (dynamic — primary changes per school)
AppColors.primary       // School's brand color (NOT const-safe)
AppColors.textPrimary   // Dark text
AppColors.textSecondary // Muted text
AppColors.surface       // Card backgrounds
AppColors.border        // Card borders
AppColors.background    // Page background
AppColors.success       // Green
AppColors.warning       // Orange
AppColors.error         // Red

// IMPORTANT: AppColors.primary is dynamic — do NOT use in const TextStyle
// ✗ const TextStyle(color: AppColors.primary)
// ✓ TextStyle(color: AppColors.primary)
```

### Detail Screen UI Pattern (Reusable Template)
```dart
// Section Header — colored left bar with uppercase label
Widget _sectionHeader(String title) {
  return Padding(
    padding: const EdgeInsets.fromLTRB(0, 20, 0, 8),
    child: Row(children: [
      Container(width: 3, height: 14, color: AppColors.primary,
          margin: const EdgeInsets.only(right: 8)),
      Text(title.toUpperCase(), style: TextStyle(
          fontSize: 12, fontWeight: FontWeight.w700,
          color: AppColors.primary, letterSpacing: 0.8)),
    ]),
  );
}

// Info Row — fixed-width label + expanding value + optional icon
Widget _infoRow(String label, String value, {IconData? icon}) {
  if (value.trim().isEmpty) return const SizedBox.shrink();
  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 6),
    child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      if (icon != null) ...[Icon(icon, size: 15, color: AppColors.textSecondary), const SizedBox(width: 6)],
      SizedBox(width: 110, child: Text(label, style: const TextStyle(fontSize: 13, color: AppColors.textSecondary))),
      Expanded(child: Text(value, style: const TextStyle(fontSize: 13, color: AppColors.textPrimary, fontWeight: FontWeight.w600))),
    ]),
  );
}
```

### Layout Structure
```
AppBar (primary background, white text)
├── Header Banner (primary bg, white title, white-background badges)
│   ├── Title
│   └── Wrap of badges (_typeBadge, _statusBadge, overdue indicator)
└── Body (ScrollView)
    ├── _sectionHeader("SECTION TITLE")
    └── Container with border (surface bg)
        ├── _infoRow("Label", "Value", icon: ...)
        └── _infoRow("Label", "Value", icon: ...)
```

### Badge Conventions
- **Header badges** (on primary background): `inHeader: true` → white background
- **Body badges** (on white/surface): `inHeader: false` → color.withAlpha(0.12) background
- Text color always matches the semantic status/type color
- Border radius: `3` (square-ish, matching app convention)

### Attachment Card Pattern
```dart
Widget _attachmentCard(BuildContext context, String path, String label) {
  // Shows: file type icon | label + filename | View/Open button
  // PDF → opens PdfViewerScreen in-app
  // Other → opens via url_launcher externally
}
```

---

## Checklist for Implementing Similar Modules

Use this checklist when building a new module that follows the same pattern (e.g., Exams, Notes, Projects):

### Backend (Laravel)

- [ ] **Model** with fillable fields, relationships, constants (TYPES, STATUSES)
- [ ] **Model boot events**: auto-fill `enterprise_id`, `academic_year_id`, `term_id` from active term
- [ ] **Admin Controller** with `grid()`, `detail()`, `form()`
- [ ] **Grid**: role-based filtering, computed display columns, quick search, custom actions
- [ ] **Form**: use `$form->quill()` for rich text, active-year class filtering, cascading selects
- [ ] **Submission/Child Controller** if applicable (edit-only, disable create button)
- [ ] **Admin routes** registered in `routes/admin.php` (resource + custom routes)

### API Layer

- [ ] **API Controller** extending `Controller`, using `ApiResponser` trait
- [ ] **Role-based query methods**: separate logic for staff, students, parents
- [ ] **Validation** using Laravel's `$request->validate()` with proper rules
- [ ] **File upload handling** with `$request->file()->store()` pattern
- [ ] **API routes** registered in `routes/api.php` under auth middleware
- [ ] **Response format**: always return `$this->success($data, $message)` or `$this->error($message)`

### Mobile App (Flutter)

- [ ] **Data Model** (`lib/models/`) with `fromJson()` factory, string getters
- [ ] **API Client** (`lib/models/`) with `http_get` for reads, `http_post` for writes
- [ ] **Main Screen** (StatefulWidget) with TabBar, list cards, action bottom sheets
- [ ] **Detail Screen** (StatelessWidget) with callback props — no direct API calls
- [ ] **HTML rendering** for any Quill/rich text fields using `flutter_html`
- [ ] **PDF viewing** via `PdfViewerScreen` for PDF attachments
- [ ] **File picker** (`file_picker` package) for file submissions — not just images
- [ ] **Client-side validation** mirroring server validation rules
- [ ] **Loading pattern**: `Utils.showLoader()` → API call → `Utils.hideLoader()` → toast
- [ ] **Data refresh**: call `my_init()` after any mutation, navigate back after action
- [ ] **Design system**: use `AppColors`, `_sectionHeader`, `_infoRow`, `_statusBadge` patterns
- [ ] **No `const` with dynamic colors**: `AppColors.primary` is runtime-dynamic
- [ ] **Imports**: `flutter_html`, `file_picker`, `url_launcher`, `PdfViewer.dart`, `AppConfig.dart`

### Testing Considerations

- [ ] Test with all user roles: admin, teacher, student, parent
- [ ] Test file upload with various file types (PDF, images, docs)
- [ ] Test submission validation for each `submission_type` (Both, Text, File, None)
- [ ] Test overdue detection and late submission auto-status
- [ ] Test with empty states (no assignments, no submissions, no score)
- [ ] Test HTML content rendering from Quill editor
- [ ] Test attachment URLs work in both dev and production environments
