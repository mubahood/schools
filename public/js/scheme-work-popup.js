(function () {
    if (window.__schemePopupBootedExternal) return;
    window.__schemePopupBootedExternal = true;

    // Config is read dynamically so pjax navigation (which re-injects the config
    // inline script inside #pjax-container) always provides the current values.
    function getCfg() { return window.SCHEME_POPUP_CONFIG || {}; }

    var suggestionMap = {
        methods: ['Guided discovery', 'Question and answer', 'Discussion', 'Demonstration', 'Group work', 'Explanation'],
        life_skills_values: ['Critical thinking', 'Effective communication', 'Collaboration', 'Self-awareness', 'Creativity', 'Problem solving'],
        suggested_activity: ['Group discussion', 'Class presentation', 'Role play', 'Pair work', 'Practical activity', 'Write summary notes'],
        instructional_material: ['Textbook', 'Charts', 'Flash cards', 'Manila papers', 'Chalkboard', 'Model'],
        references: ['P7 curriculum pg.64', 'Teacher guide', 'Learner book', 'Oxford dictionary'],
        competence_subject: ['Learner identifies key concepts.', 'Learner explains the topic accurately.', 'Learner applies concepts in exercises.'],
        competence_language: ['Learner uses correct vocabulary.', 'Learner writes complete sentences.', 'Learner gives clear oral responses.']
    };

    function getToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return (window.LA && window.LA.token) || (meta ? meta.getAttribute('content') : '') || '';
    }

    function currentTermId() {
        var qs = new URLSearchParams(window.location.search);
        var t = qs.get('term_filter');
        return t || getCfg().defaultTermId || '';
    }

    function chipsHtml(field) {
        var items = suggestionMap[field];
        if (!items || !items.length) return '';
        var html = '<div class="inline-sugg-row">';
        items.forEach(function (txt) {
            var safe = String(txt).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            html += '<button type="button" class="sugg-chip js-sugg-chip" data-target="' + field + '" data-text="' + safe + '">' + safe + '</button>';
        });
        html += '</div>';
        return html;
    }

    function fieldRow(label, name, tag, required) {
        var req = required ? ' required' : '';
        var ctrl = tag === 'textarea'
            ? '<textarea class="form-control" name="' + name + '" rows="3"' + req + '></textarea>'
            : '<input class="form-control" name="' + name + '"' + req + '>';
        return '<label>' + label + '</label>' + ctrl + chipsHtml(name);
    }

    function ensureModal() {
        if (document.getElementById('scheme-item-popup')) return;

        var weekOpts = '', periodOpts = '';
        for (var i = 1; i <= 18; i++) weekOpts += '<option value="' + i + '">Week ' + i + '</option>';
        for (var p = 1; p <= 10; p++) periodOpts += '<option value="' + p + '">' + p + ' Period' + (p > 1 ? 's' : '') + '</option>';

        var html = ''
            + '<div class="modal fade scheme-popup" id="scheme-item-popup" tabindex="-1">'
            + '  <div class="modal-dialog modal-lg">'
            + '    <div class="modal-content">'
            + '      <div class="modal-header">'
            + '        <button type="button" class="close" data-dismiss="modal">&times;</button>'
            + '        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Scheme Work Item <small id="popup-subject-name" style="font-weight:600;color:#4f6c84"></small></h4>'
            + '      </div>'
            + '      <div class="modal-body">'
            + '        <div class="alert alert-danger popup-feedback" id="scheme-popup-error"></div>'
            + '        <div class="alert alert-success popup-feedback" id="scheme-popup-ok"></div>'
            + '        <form id="scheme-popup-form">'
            + '          <input type="hidden" name="_token" value="' + getToken() + '">'
            + '          <input type="hidden" name="subject_id" id="popup-subject-id">'
            + '          <input type="hidden" name="term_id" id="popup-term-id">'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-3 col-sm-4"><label>Week</label><select class="form-control" name="week" id="popup-week">' + weekOpts + '</select></div>'
            + '            <div class="col-md-3 col-sm-4"><label>Periods</label><select class="form-control" name="period" id="popup-period">' + periodOpts + '</select></div>'
            + '            <div class="col-md-6 col-sm-4"><label>Status</label><select class="form-control" name="teacher_status"><option>Pending</option><option>Conducted</option><option>Skipped</option></select></div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-6 col-sm-6">' + fieldRow('Theme', 'theme', 'input', true) + '</div>'
            + '            <div class="col-md-6 col-sm-6">' + fieldRow('Topic', 'topic', 'input', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-12">' + fieldRow('Subtopic', 'sub_topic', 'input', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Content', 'content', 'textarea', true) + '</div>'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Competence - Subject', 'competence_subject', 'textarea', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Competence - Language', 'competence_language', 'textarea', true) + '</div>'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Methods &amp; Techniques', 'methods', 'textarea', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Life Skills &amp; Values', 'life_skills_values', 'textarea', true) + '</div>'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Suggested Activities', 'suggested_activity', 'textarea', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('Instructional Materials', 'instructional_material', 'textarea', true) + '</div>'
            + '            <div class="col-md-6 col-sm-12">' + fieldRow('References', 'references', 'textarea', true) + '</div>'
            + '          </div>'
            + '          <div class="row form-grid">'
            + '            <div class="col-md-12">' + fieldRow('Remarks (optional)', 'teacher_comment', 'textarea', false) + '</div>'
            + '          </div>'
            + '        </form>'
            + '      </div>'
            + '      <div class="modal-footer">'
            + '        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>'
            + '        <button type="button" class="btn btn-save-more" id="popup-save-more-btn">Save &amp; Add Another</button>'
            + '        <button type="button" class="btn btn-primary" id="popup-save-btn">Save Item</button>'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';

        document.body.insertAdjacentHTML('beforeend', html);
    }

    function setVal(name, val) {
        var el = document.querySelector('#scheme-popup-form [name="' + name + '"]');
        if (el) el.value = val || '';
    }

    function appendFieldValue(name, text) {
        var el = document.querySelector('#scheme-popup-form [name="' + name + '"]');
        if (!el || !text) return;
        var current = (el.value || '').trim();
        var next = String(text).trim();
        if (el.tagName.toLowerCase() === 'textarea') {
            var bullet = '- ' + next;
            el.value = current ? current + '\n' + bullet : bullet;
        } else {
            el.value = current ? current + ' ' + next : next;
        }
        el.classList.add('field-append-flash');
        setTimeout(function () { el.classList.remove('field-append-flash'); }, 300);
        el.focus();
    }

    function showError(text) {
        var err = document.getElementById('scheme-popup-error');
        var ok = document.getElementById('scheme-popup-ok');
        ok.style.display = 'none';
        err.innerText = text;
        err.style.display = 'block';
    }

    function showOk(text) {
        var err = document.getElementById('scheme-popup-error');
        var ok = document.getElementById('scheme-popup-ok');
        err.style.display = 'none';
        ok.innerText = text;
        ok.style.display = 'block';
    }

    function setSaveButtonsBusy(isBusy) {
        var saveBtn = document.getElementById('popup-save-btn');
        var moreBtn = document.getElementById('popup-save-more-btn');
        if (saveBtn) {
            saveBtn.disabled = isBusy;
            saveBtn.innerHTML = isBusy ? 'Saving...' : 'Save Item';
        }
        if (moreBtn) {
            moreBtn.disabled = isBusy;
            moreBtn.innerHTML = isBusy ? 'Saving...' : 'Save & Add Another';
        }
    }

    function updateRowStats(subjectId, stats) {
        var block = document.getElementById('scheme-stat-' + subjectId);
        if (!block || !stats) return;
        var map = {
            '.js-stat-total': stats.total,
            '.js-stat-done': stats.conducted,
            '.js-stat-pending': stats.pending,
            '.js-stat-skipped': stats.skipped,
            '.js-stat-percent': stats.percent
        };
        Object.keys(map).forEach(function (sel) {
            var el = block.querySelector(sel);
            if (el) el.textContent = map[sel];
        });
    }

    function clearForNextEntry() {
        var form = document.getElementById('scheme-popup-form');
        if (!form) return;
        ['theme', 'topic', 'sub_topic', 'content', 'competence_subject', 'competence_language', 'methods', 'life_skills_values', 'suggested_activity', 'instructional_material', 'references', 'teacher_comment']
            .forEach(function (name) { setVal(name, ''); });

        var statusEl = form.querySelector('[name="teacher_status"]');
        if (statusEl) statusEl.value = 'Pending';

        var periodEl = document.getElementById('popup-period');
        if (periodEl) {
            var current = parseInt(periodEl.value || '1', 10);
            if (!isNaN(current) && current < 10) {
                periodEl.value = String(current + 1);
            }
        }
    }

    function openModal(btn) {
        ensureModal();
        var subjectId = btn.getAttribute('data-subject-id');
        var subjectName = btn.getAttribute('data-subject-name') || 'Subject';
        var form = document.getElementById('scheme-popup-form');
        document.getElementById('popup-subject-id').value = subjectId;
        document.getElementById('popup-subject-name').innerText = '- ' + subjectName;
        document.getElementById('popup-term-id').value = currentTermId();
        form.reset();
        // Refresh CSRF token on each open in case session was renewed
        var tokenEl = form.querySelector('[name="_token"]');
        if (tokenEl) tokenEl.value = getToken();
        document.getElementById('popup-week').value = '1';
        document.getElementById('popup-period').value = '1';
        document.querySelector('#scheme-popup-form [name="teacher_status"]').value = 'Pending';
        document.getElementById('scheme-popup-error').style.display = 'none';
        document.getElementById('scheme-popup-ok').style.display = 'none';
        window.jQuery('#scheme-item-popup').modal('show');
    }

    function saveItem(mode) {
        var form = document.getElementById('scheme-popup-form');
        setSaveButtonsBusy(true);
        window.jQuery.ajax({
            url: getCfg().storeUrl || '',
            method: 'POST',
            data: window.jQuery(form).serialize(),
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': getToken(), 'Accept': 'application/json' },
            success: function (res) {
                if (!res || !res.status) {
                    showError((res && res.message) ? res.message : 'Unable to save item.');
                    return;
                }
                showOk(res.message || 'Item saved successfully.');
                updateRowStats(document.getElementById('popup-subject-id').value, res.stats || null);
                if (mode === 'more') {
                    clearForNextEntry();
                } else {
                    setTimeout(function () {
                        window.jQuery('#scheme-item-popup').modal('hide');
                    }, 450);
                }
            },
            error: function (xhr) {
                var msg = 'Failed to save. Please check required fields.';
                if (xhr.status === 419) {
                    msg = 'Session expired. Please refresh the page and try again.';
                } else if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                        msg = firstKey ? xhr.responseJSON.errors[firstKey][0] : (xhr.responseJSON.message || msg);
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.status) {
                    var snippet = xhr.responseText ? xhr.responseText.substring(0, 120).replace(/<[^>]+>/g, '') : '';
                    msg = 'Server error (' + xhr.status + ').' + (snippet ? ' Detail: ' + snippet : ' Please try again.');
                }
                showError(msg);
            },
            complete: function () {
                setSaveButtonsBusy(false);
            }
        });
    }

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('.js-open-scheme-popup');
        if (openBtn) {
            e.preventDefault();
            openModal(openBtn);
            return;
        }

        var chip = e.target.closest('.js-sugg-chip');
        if (chip) {
            e.preventDefault();
            appendFieldValue(chip.getAttribute('data-target'), chip.getAttribute('data-text'));
            return;
        }

        if (e.target && e.target.id === 'popup-save-btn') {
            e.preventDefault();
            saveItem('close');
            return;
        }

        if (e.target && e.target.id === 'popup-save-more-btn') {
            e.preventDefault();
            saveItem('more');
        }
    });
})();
