# Assignment / Homework Module Finalization

Date: 2026-03-01

## Scope Completed

This finalization covers the full assignment/homework lifecycle in the `schools` backend:

- Core assignment logic hardening (`Assignment`, `AssignmentSubmission` models)
- Stable API endpoints for staff/student/parent scenarios
- Data consistency guarantees (deduplication + indexes)
- Automated feature tests for critical scenarios
- Unified behavior and validation rules

## Stability Improvements Implemented

## 1) Model Logic Hardening

### `Assignment` model improvements

- Added canonical constants for:
  - `TYPES`: Homework, Assignment, Project, Classwork, Quiz
  - `STATUSES`: Draft, Published, Closed, Archived
  - `SUBMISSION_TYPES`: Both, File, Text, None
- Added normalization + validation before create/update:
  - required enterprise, title, target class
  - valid type/status/submission type fallback defaults
  - `is_assessed` and `marks_display` normalized to `Yes/No`
  - `max_score` must be non-negative
  - `due_date >= issue_date` enforced
- Improved post-create failure handling:
  - submission generation errors are logged (no silent failure)
- Refactored submission generation:
  - transactional generation
  - idempotent insert behavior (skips existing student submissions)
  - bulk insert for performance
  - stats refresh after generation

### `AssignmentSubmission` model improvements

- Added canonical status constants and submitted-status set:
  - Pending, Submitted, Graded, Returned, Late, Not Submitted
- Added normalization + validation before create/update:
  - invalid status is normalized to `Pending`
  - score and max score cannot be negative
  - score cannot exceed max score
- Auto timestamps standardized:
  - `submitted_at` set for Submitted/Late
  - `graded_at` set for Graded
- Parent assignment stats now refresh on:
  - create
  - update
  - delete

## 2) Database Consistency

Migration added:

- `database/migrations/2026_03_01_000003_add_assignment_indexes_and_uniques.php`

What it does:

- Cleans duplicate `assignment_submissions` by `(assignment_id, student_id)` while keeping oldest row
- Adds unique constraint:
  - `(assignment_id, student_id)`
- Adds indexes:
  - `assignment_submissions(assignment_id, status)`
  - `assignment_submissions(student_id, status)`
  - `assignments(enterprise_id, status)`
  - `assignments(academic_class_id, stream_id)`
  - `assignments(due_date)`

## 3) API Finalization

New controller:

- `app/Http/Controllers/ApiAssignmentController.php`

Routes added (JWT middleware group in `routes/api.php`):

- `GET /api/assignments` — list assignments by role scope
- `GET /api/assignments/{id}` — assignment details with scope checks
- `POST /api/assignments` — staff create assignment
- `POST /api/assignments/{id}/status` — staff status update
- `POST /api/assignments/{id}/regenerate-submissions` — staff regenerate
- `GET /api/assignment-submissions` — list submissions by role scope
- `POST /api/assignment-submissions/{id}/submit` — student submit
- `POST /api/assignment-submissions/{id}/grade` — staff grade

### Role behavior

- Staff (`employee`): can manage assignments/submissions; non-privileged staff limited to own assignments
- Student: sees class/stream relevant published items and own submissions; can submit own assignment record
- Parent: read-only scoped assignment/submission visibility for child students

### Submission rules enforced

- assignment must be `Published` to accept student submission
- `submission_type` behavior:
  - `None`: rejects direct submission
  - `Text`: requires text
  - `File`: requires file
  - `Both`: requires either text or file
- due-date lateness auto-sets `Late` status

## 4) Automated Test Coverage

New test file:

- `tests/Feature/AssignmentModuleApiTest.php`

Scenarios covered:

1. Staff creates assignment and submissions are auto-generated
2. Student sees only published assignments for their class
3. Student submission updates status and assignment submission stats
4. Grading rejects score above max score

## 5) Consistency Standards Applied

- Single-source status/type constants in models
- Uniform API response shape via `ApiResponser` (`code`, `message`, `data`)
- Consistent enterprise scoping across all endpoints
- Defensive validation before persistence
- Deterministic idempotent submission-generation behavior

## Operational Notes

- Run migrations to apply new unique/index constraints before production usage.
- If historical duplicates exist, migration will auto-clean duplicates safely before adding uniqueness.
- API file uploads use the `public` disk; ensure storage symlink and write permissions are configured.

## Readiness Summary

The assignment/homework module now has:

- stable write paths,
- constrained and consistent data behavior,
- role-safe API access patterns,
- duplicate prevention at DB level,
- and baseline automated regression coverage for critical flows.
