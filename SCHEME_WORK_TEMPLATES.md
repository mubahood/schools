# Scheme of Work Templates

This document outlines the two distinct templates for printing Scheme of Work documents, derived from client requirements and implemented in the system.

## 1. Lower Primary Scheme of Work Template

**Trigger**: Activated when the subject's `scheme_template` is set to `'auto'` or `'generic'`.

### Characteristics:
- **Competences**: Single undivided column combining both subject and language competences
- **Content**: Automatically prefixed with "The learner; "
- **Life Skills & Values**: Automatically prefixed with "Indicators of "
- **Subject Competences**: Optional (not required) when adding scheme work items
- **Layout**: Simplified structure suitable for lower primary education levels

### Use Case:
- Nursery and lower primary classes
- Subjects requiring simpler competence tracking
- Educational contexts where competences are not strictly divided by subject/language

## 2. Upper / Standard Scheme of Work Template

**Trigger**: Activated when the subject's `scheme_template` is set to `'science'`, `'mathematics'`, or `'language'`.

### Characteristics:
- **Competences**: Divided into two separate columns:
  - Subject Competences
  - Language Competences
- **Content**: Standard formatting without automatic prefixes
- **Life Skills & Values**: Standard formatting without automatic prefixes
- **Subject Competences**: Can be required or optional based on configuration
- **Layout**: Detailed structure for upper primary and secondary education

### Use Case:
- Upper primary and secondary classes
- Specialized subjects (Science, Mathematics, Languages)
- Educational contexts requiring detailed competence separation

## Template Selection

Templates are selected per subject through the Subject edit form in the admin panel:

- **Auto by Subject**: Uses Lower Primary template
- **Science**: Uses Upper/Standard template
- **Mathematics**: Uses Upper/Standard template
- **English / Language**: Uses Upper/Standard template
- **General Purpose**: Uses Lower Primary template

## Implementation Details

- **File**: `resources/views/print/scheme-of-work-print.blade.php`
- **Logic**: Conditional rendering based on `$sub->scheme_template`
- **Form Validation**: Subject competences made optional in `SchemeWorkController@storeItemAjax`
- **Database**: `scheme_template` field in `subjects` table

## Notes

- The Lower Primary template emphasizes simplicity and automatic formatting aids
- The Upper/Standard template provides detailed separation for advanced educational tracking
- Templates can be extended or modified by updating the view file and template options