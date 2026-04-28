# Client Requirements Analysis (27-04-2026)

## Source Context
This document captures and organizes the client requirements shared in WhatsApp messages on 27-04-2026.

Primary requester: Mutale Muliro John

Core intention preserved: improve operational control in school workflows, strengthen accountability, and support follow-up actions with clear records.

---

## 1) Inventory Module Enhancement (Uniform Issuance Control)

### Client Feedback
- The inventory module has been helpful.
- A challenge exists when uniforms are issued to students who have not yet paid enough for the uniform service.

### Core Requirement
The system should provide a filter for students who are "not offered" uniforms, based on a payment threshold defined by the user.

### Detailed Requirement Interpretation (without changing core point)
1. Add a configurable amount-based eligibility filter in the uniform issuance flow.
2. The filter must isolate students who:
   - have not yet been offered uniforms, and
   - have paid at least a user-defined minimum amount (or exactly the business rule selected by user).
3. The bursar/store user should be able to choose the threshold amount at runtime.
4. Users should be able to proceed with issuing uniforms only to filtered eligible students and leave out others.

### Functional Expectations
- Input field for amount threshold.
- Option to apply filter to "Not Offered" set.
- Clear list output of students meeting selected criteria.
- Excluded students remain visible through reset/clear filter for transparency.

### Business Outcome
- Reduces accidental issuance to students who have not met required payment value.
- Supports controlled, policy-based distribution.

---

## 2) Monitoring Tool Request (Teacher Attendance/Performance Tracking)

### Client Feedback
Client described an existing paper-based monitoring process:
- DOS gives a secret monitoring sheet to class monitors.
- Monitors track teacher lesson activity.
- Sheet is returned to DOS at end of day.
- Sheet fields include: time in, subject, teacher name, time out, comment.

### Core Requirement
The client wants to know if this information can be captured in the system and analyzed comprehensively at weekly/monthly level, then printed as accountability evidence.

### Detailed Requirement Interpretation (without changing core point)
1. Build a digital monitoring data-entry tool for DOS workflow.
2. Capture records with at least the same columns as the paper form:
   - Time in
   - Subject
   - Teacher name
   - Time out
   - Comment
3. Support recurring analysis periods:
   - End of week
   - End of month
   - Multi-month as needed
4. Provide printable reports suitable for evidence/accountability discussions with teachers.

### Functional Expectations
- Secure role-based access (DOS and authorized staff only).
- Data entry by class/date/subject/teacher context.
- Aggregated analytics (lateness, missed periods, early exits, recurring comments, etc. if approved).
- Report export/print support.

### Business Outcome
- Moves a sensitive monitoring workflow from paper to auditable digital records.
- Strengthens teacher accountability with measurable evidence.

---

## 3) Commitment Section Request (Fees Payment Promise Tracking)

### Client-provided Narrative (directly reflected)
Scenario:
- A parent comes to school and requests to be excused from paying the current outstanding fees balance immediately.
- Parent commits to paying by a specific future date.

### Core Requirement
Create a "Commitment Section" where Bursar records and tracks these commitments until fulfilled or overdue.

### Mandatory Data Fields
1. Parent name
2. Parent contacts
3. Student name/class
4. Outstanding balance amount
5. Commitment date (exact future date by which payment will be made)

### Required System Behavior
1. Bursar can save the commitment record.
2. Once saved, account is flagged as: Commitment Pending.
3. If the commitment date passes without payment, system automatically notifies bursar.
4. Bursar can track status and update record when parent fulfills commitment.
5. If fulfilled, status changes from Commitment Pending to Fulfilled.

### Example Entry Flow (client flow retained)
1. Bursar selects Commitment Section.
2. Inputs parent/select and student details.
3. Enters/select outstanding balance.
4. Sets Commitment Date.
5. Saves record and system marks account as Commitment Pending.
6. If fulfilled, record is marked as Fulfilled.

### Business Outcome
- Adds formal accountability and follow-up mechanism for deferred fee promises.
- Improves bursar visibility on pending and overdue commitments.

---

## 4) Commercial Note from Client

Client indicates willingness to provide a small allowance for the additional work when completed.

Suggested handling in project scope:
- Treat this as paid enhancement scope.
- Confirm deliverables and acceptance criteria per module before implementation.

---

## 5) Consolidated Requirement Set (Implementation-ready)

1. Inventory: amount-threshold filter for not-offered uniforms to control issuance decisions.
2. Monitoring Tool: digital replacement of DOS paper monitoring sheet with weekly/monthly analysis and printable accountability evidence.
3. Commitment Section: structured bursar workflow for recording, tracking, notifying, and closing parent fee-payment commitments.

---

## 6) Clarifications to Confirm Before Build (to avoid changing intent)

1. Uniform filter rule:
   - Should eligibility be "paid greater than or equal to X" or "paid exactly X"?
2. Monitoring data entry ownership:
   - Entered by monitors directly, DOS, or both?
3. Monitoring confidentiality:
   - Who can view raw entries vs summary reports?
4. Commitment notification channel:
   - In-system alert only, or also SMS/email?
5. Commitment fulfillment trigger:
   - Manual bursar confirmation, automatic from ledger payment matching, or both?

These clarifications are not requirement changes; they are implementation decisions needed to preserve the client's core points accurately.

## 7) Module-by-Module Implementation Plan: Start with EMT

Implementation approach confirmed:
- Build module by module.
- Start with teacher monitoring and report generation.

Module name and placement:
- Name: Employee Monitoring Tool (EMT)
- Location: Human Resource Management module

### 7.1 Phase 1 Scope (EMT)
1. Monitoring record capture
2. Monitoring dashboard and analysis
3. Report parameter management and report generation
4. Export and HR integration

### 7.2 EMT Core Data Model
Primary model: EmployeeMonitoringRecord

Required fields (as specified):
- Enterprise ID: links records to a specific school
- Due Term: academic term, default should be current active term
- Due Date: date by which monitoring record should be completed
- Time In: when teacher starts lesson
- Time Out: when teacher ends lesson
- Hours: calculated from Time In and Time Out
- Subject: subject being taught
- Class: class being taught
- Employee ID: teacher being monitored
- Comment: additional notes/comments

Additional model requirement retained:
- Include any other important fields needed for effective monitoring/reporting.
- Keep model flexible for future requirement changes.

### 7.3 EMT Data Entry Form Requirements
Form users:
- Class monitors or DOS

Form expectations:
- User-friendly and fast data entry
- Accurate capture of all required fields
- Validation for required fields and logical time flow (Time Out must not be before Time In)
- Default Due Term to current active term
- Auto-calculate Hours from Time In and Time Out

### 7.4 EMT Dashboard Requirements (DOS View)
Purpose:
- Allow DOS to view and analyze monitoring records

Dashboard capabilities:
- Filtering by date/date range, subject, teacher, class, term, and status
- Sorting by key columns (date, teacher, subject, class, time in/out, hours)
- Visualizations (charts/graphs) to identify trends and patterns quickly
- Summary widgets/KPIs for fast decision support

### 7.5 EMT Reporting Model
Independent reporting model required:
- Suggested model name: EmployeeMonitoringReportRecord

Required intent:
- User can define report parameters and generate reports from monitoring records

Suggested fields:
- Report Name
- Report Parameters (date range, term, subject, teacher, class, and related filters)
- Report Type
- Generated By
- Generated At
- Any additional relevant metadata needed for reliable report regeneration/audit

Design expectation retained:
- Reporting must remain flexible by parameter combination and still produce accurate, meaningful insights.

### 7.6 Required Report Types
System must support reports for:
1. Individual teacher performance over a specified period
2. Subject-wise performance across multiple teachers
3. Class-wise performance across multiple teachers
4. Academic term performance trends for teachers, subjects, or classes

Required parameter combinations:
- Term or date range
- Subject
- Teacher
- Class
- Other compatible combinations needed for comprehensive analysis

### 7.7 Export Requirements
Generated reports must be exportable in:
- PDF
- Excel
- Additional export formats if needed by stakeholders

### 7.8 Integration Requirements (HR Module)
EMT must integrate with Human Resource Management so that:
- Monitoring records link to relevant employee profiles
- Data can support HR actions where needed
- Example downstream usage includes performance reviews and disciplinary workflows

Expected outcome retained:
- Improve teacher performance and accountability through actionable monitoring evidence.

### 7.9 Acceptance Criteria for EMT Phase 1
1. User can create monitoring records with all required fields.
2. Hours are correctly computed from Time In and Time Out.
3. DOS dashboard supports filter/sort and shows trend visualizations.
4. Users can define report parameters and generate each required report type.
5. Reports export successfully to PDF and Excel.
6. Monitoring records are visible in linked employee HR context.

### 7.10 Notes
This section preserves the client's original core points and order while structuring them into implementation-ready requirements.

.

just like how we have imlmented the EMT module, I need us to creatively think and plan to implment the Parents Commitment Module.
this will result to 2 more menu items that will be placed under the fees mode.
-  it will have commitment records
- and a commitment dashboard for the bursar to track pending and fulfilled commitments.
.
basicallty, the commitment records will have one core model that captures the commitment details and a dashboard that allows the bursar to track the status of commitments (pending, fulfilled, overdue) and take necessary follow-up actions.
- the model should basically capture 
    - enterprise id (to link to specific school)
    - student id
    - parent id (automatically linked to student)
    - parent name (auto-filled if left empty, but editable for corrections)
    - parent contact (auto-filled if left empty, but editable for corrections)
    - outstanding balance amount (linked to student ledger, but editable for adjustments)
    - commitment date (date by which parent commits to pay)
    - promise status (Pending, Fulfilled, Overdue)
    - created by (user who created the record)
    - comments (optional field for any additional notes)
- the dashboard should allow the bursar to filter commitments by status, view details of each commitment, and receive notifications for overdue commitments.
- the system should automatically update the promise status to "Overdue" if the commitment date passes without payment being recorded in the student ledger.
- ensure that the commitment records are linked to the student profiles for easy reference and follow-up during parent meetings or communications.
- do things with knowledge of the existing fees module and how it integrates with student ledgers to ensure seamless data flow and accurate tracking of outstanding balances and payments against commitments.
