# Scheme of Work — Fix Plan

## Overview of Issues vs Current State

After reading all relevant files, here is what is **already done** vs what **still needs fixing**.

### Already Done (No Change Needed)
| Feature | Where Implemented |
|---|---|
| Lower primary: single "Competences" column in print | `scheme-of-work-print.blade.php` line 164–165, 270 |
| Lower primary: "The learner; " auto-prefix on Content cell | `scheme-of-work-print.blade.php` line 268 |
| Lower primary: "Indicators of " auto-prefix on Life Skills cell | `scheme-of-work-print.blade.php` line 276 |
| Popup: language competence hidden for lower primary | `scheme-work-popup.js` lines 70–77 |
| Popup: competence label changes to "Competences" for lower primary | `scheme-work-popup.js` lines 63–65 |
| Competences not required for lower primary (validation) | `SchemeWorkController.php` lines 640–641 |

### Still Needs Fixing (3 issues)
1. **Life Skills column HEADER** is always "LIFE SKILLS & VALUES" — must say "INDICATORS OF LIFE SKILLS & VALUES" for lower primary
2. **All text fields are still marked required** in both the popup JS form and the server-side validator — client said make ALL optional
3. **Competences are still required for upper/standard templates** (science, mathematics, language) — client said competences should never be compulsory

---

## Fix 1 — Life Skills Column Header (Lower Primary Print)

**File:** `resources/views/print/scheme-of-work-print.blade.php`

**Problem:** Line 170–171 renders a fixed `<th>` header:
```html
<th class="col-life-skills" rowspan="2">LIFE SKILLS &amp;<br>VALUES</th>
```
This header is identical for both templates. The client wants the lower primary header to read **"INDICATORS OF LIFE SKILLS & VALUES"**.

**Fix:** Make the header conditional using the existing `$isLowerPrimary` PHP variable (already set at line 136).

**Before:**
```html
<th class="col-life-skills" rowspan="2">LIFE SKILLS &amp;<br>VALUES</th>
```

**After:**
```blade
<th class="col-life-skills" rowspan="2">
    @if($isLowerPrimary)
        INDICATORS OF<br>LIFE SKILLS &amp; VALUES
    @else
        LIFE SKILLS &amp;<br>VALUES
    @endif
</th>
```

**Impact:** Only the printed PDF changes. The popup form label remains "Life Skills & Values" (no change there needed — the "Indicators of" prefix is already auto-inserted on each cell value at print time).

---

## Fix 2 — All Form Fields Must Be Optional (Popup + Validator)

The client explicitly said: *"all these compulsory things make them optional"*.

### Part A — Popup JS (`public/js/scheme-work-popup.js`)

**Problem:** The `ensureModal()` function builds the form HTML. Inside it, `fieldRow(label, name, tag, required)` is called with `required=true` for most fields (lines 109–129). The 4th argument controls whether `required` is added to the HTML element.

**Fields currently marked required in the form:**
- theme, topic, sub_topic, content, competence_subject, competence_language, methods, life_skills_values, suggested_activity, instructional_material, references

**Fix:** Change every `fieldRow(...)` call to pass `false` as the 4th argument (required=false).

Lines to change (each `true` → `false`):
- Line 109: `fieldRow('Theme', 'theme', 'input', true)` → `false`
- Line 110: `fieldRow('Topic', 'topic', 'input', true)` → `false`
- Line 113: `fieldRow('Subtopic', 'sub_topic', 'input', true)` → `false`
- Line 116: `fieldRow('Content', 'content', 'textarea', true)` → `false`
- Line 117: `fieldRow('Competence - Subject', 'competence_subject', 'textarea', true)` → `false`
- Line 120: `fieldRow('Competence - Language', 'competence_language', 'textarea', true)` → `false`
- Line 121: `fieldRow('Methods &amp; Techniques', 'methods', 'textarea', true)` → `false`
- Line 124: `fieldRow('Life Skills &amp; Values', 'life_skills_values', 'textarea', true)` → `false`
- Line 125: `fieldRow('Suggested Activities', 'suggested_activity', 'textarea', true)` → `false`
- Line 128: `fieldRow('Instructional Materials', 'instructional_material', 'textarea', true)` → `false`
- Line 129: `fieldRow('References', 'references', 'textarea', true)` → `false`

**Note:** `week` and `period` are `<select>` dropdowns — they always have a value, no change needed. `subject_id` and `term_id` are hidden fields also left as-is.

### Part B — Also fix `configureSchemePopup()` in popup JS

**Problem:** Lines 66 and 71 in `configureSchemePopup()`:
```js
subjectField.required = !lower;   // still required for upper templates
languageField.required = !lower;  // still required for upper templates
```
This means for upper templates, JS re-enforces `required=true` on the competence fields even after we remove them from the initial HTML. We must always set them to `false`.

**Fix:**
```js
subjectField.required = false;   // never required
languageField.required = false;  // never required
```

### Part C — Server-Side Validator (`app/Admin/Controllers/SchemeWorkController.php`)

**Problem:** `storeItemAjax()` method, lines 632–647. Most fields are validated as `'required|string|min:X'`. Even if the browser skips validation (e.g., JS disabled, API call), the server still enforces them.

**Fields to change from `required` to `nullable`:**
```
'theme'                 => 'required|string|min:2|max:255'  →  'nullable|string|max:255'
'topic'                 => 'required|string|min:2|max:255'  →  'nullable|string|max:255'
'sub_topic'             => 'required|string|min:2|max:255'  →  'nullable|string|max:255'
'content'               => 'required|string|min:3'           →  'nullable|string'
'competence_subject'    => (conditional required)|string|min:3  →  'nullable|string'
'competence_language'   => (conditional required)|string|min:3  →  'nullable|string'
'methods'               => 'required|string|min:3'           →  'nullable|string'
'life_skills_values'    => 'required|string|min:3'           →  'nullable|string'
'suggested_activity'    => 'required|string|min:3'           →  'nullable|string'
'instructional_material'=> 'required|string|min:2'           →  'nullable|string'
'references'            => 'required|string|min:2'           →  'nullable|string'
```

**Fields that stay required (untouched):**
- `subject_id` — must know which subject
- `week` — always comes from a select, will never be empty
- `period` — always comes from a select, will never be empty

---

## Fix 3 — Competences Optional for All Templates (Already Covered by Fix 2)

Fix 2 Part B and Fix 2 Part C already make competences nullable for all templates (including science, mathematics, language). No extra step needed — these are subsets of the broader "all fields optional" fix.

---

## Files Modified Summary

| File | Changes |
|---|---|
| `resources/views/print/scheme-of-work-print.blade.php` | Make Life Skills `<th>` header conditional on `$isLowerPrimary` |
| `public/js/scheme-work-popup.js` | Change 11 `fieldRow()` calls from `required=true` to `false`; fix `configureSchemePopup()` to always set `required=false` |
| `app/Admin/Controllers/SchemeWorkController.php` | Change 11 validation rules from `required` to `nullable` in `storeItemAjax()` |

---

## Implementation Order

1. **Start with the print blade** (lowest risk — isolated to PDF output, no form logic).
2. **Then the controller validator** (server-side only, no UI impact until tested).
3. **Then the popup JS** (affects live form immediately on page load).
4. **Test** by opening the popup for a lower-primary subject and an upper-primary subject, saving with minimal data, then printing the scheme PDF for a lower primary subject to verify the header.
