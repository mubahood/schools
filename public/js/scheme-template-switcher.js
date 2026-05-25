(function () {
    if (window.__schemeTplSwitcherBooted) return;
    window.__schemeTplSwitcherBooted = true;

    var TEMPLATES = [
        {
            key:   'lower',
            label: 'Lower Section',
            sub:   'Baby · Middle · Top · KG',
            icon:  'fa-child',
            color: '#1565C0',
            bg:    '#E3F2FD',
        },
        {
            key:   'upper',
            label: 'Upper Section',
            sub:   'Primary 1 – Primary 7',
            icon:  'fa-graduation-cap',
            color: '#2E7D32',
            bg:    '#E8F5E9',
        },
        {
            key:   'science',
            label: 'Science',
            sub:   'Biology · Chemistry · Physics',
            icon:  'fa-flask',
            color: '#BF360C',
            bg:    '#FBE9E7',
        },
        {
            key:   'mathematics',
            label: 'Mathematics',
            sub:   'Maths &amp; Numeracy',
            icon:  'fa-calculator',
            color: '#AD1457',
            bg:    '#FCE4EC',
        },
        {
            key:   'language',
            label: 'English / Language',
            sub:   'Reading · Writing · Speaking',
            icon:  'fa-book',
            color: '#00695C',
            bg:    '#E0F2F1',
        },
        {
            key:   'generic',
            label: 'General Purpose',
            sub:   'Any subject type',
            icon:  'fa-file-text-o',
            color: '#37474F',
            bg:    '#ECEFF1',
        },
        {
            key:   'auto',
            label: 'Auto by Subject',
            sub:   'Determined by subject name',
            icon:  'fa-magic',
            color: '#4527A0',
            bg:    '#EDE7F6',
        },
    ];

    function getCfg() { return window.SCHEME_POPUP_CONFIG || {}; }

    function getToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return (window.LA && window.LA.token) || (meta ? meta.getAttribute('content') : '') || '';
    }

    function tplMeta(key) {
        return TEMPLATES.find(function (t) { return t.key === key; }) || TEMPLATES[0];
    }

    /* ── modal HTML ─────────────────────────────────────────────── */
    function ensureModal() {
        if (document.getElementById('scheme-tpl-modal')) return;
        var html = ''
            + '<div class="modal fade" id="scheme-tpl-modal" tabindex="-1" role="dialog">'
            + '  <div class="modal-dialog scheme-tpl-dialog" role="document">'
            + '    <div class="modal-content scheme-tpl-mc">'
            + '      <div class="modal-header scheme-tpl-hd">'
            + '        <button type="button" class="close scheme-tpl-close" data-dismiss="modal">&times;</button>'
            + '        <h4 class="modal-title scheme-tpl-title"><i class="fa fa-sliders"></i>&nbsp; Change Scheme Template</h4>'
            + '        <div id="scheme-tpl-subname" class="scheme-tpl-subname"></div>'
            + '      </div>'
            + '      <div class="modal-body scheme-tpl-bd">'
            + '        <div id="scheme-tpl-alert" class="alert" style="display:none;margin-bottom:12px"></div>'
            + '        <div class="scheme-tpl-grid" id="scheme-tpl-grid"></div>'
            + '      </div>'
            + '      <div class="modal-footer scheme-tpl-ft">'
            + '        <button type="button" class="btn btn-default" data-dismiss="modal">'
            + '          <i class="fa fa-times"></i> Cancel'
            + '        </button>'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        document.body.insertAdjacentHTML('beforeend', html);
    }

    function buildCards(currentTpl) {
        var grid = document.getElementById('scheme-tpl-grid');
        if (!grid) return;
        var html = '';
        TEMPLATES.forEach(function (t) {
            var isActive = t.key === currentTpl;
            html += '<div class="scheme-tpl-card' + (isActive ? ' stc-active' : '') + '"'
                +        ' data-tpl="' + t.key + '"'
                +        ' style="--stc-color:' + t.color + ';--stc-bg:' + t.bg + '">'
                +   (isActive ? '<span class="stc-check"><i class="fa fa-check"></i></span>' : '')
                +   '<div class="stc-icon"><i class="fa ' + t.icon + '"></i></div>'
                +   '<div class="stc-info">'
                +     '<div class="stc-name">' + t.label + '</div>'
                +     '<div class="stc-desc">' + t.sub + '</div>'
                +   '</div>'
                + '</div>';
        });
        grid.innerHTML = html;
    }

    /* ── open ────────────────────────────────────────────────────── */
    function openSwitcher(triggerEl) {
        ensureModal();
        var subjectId   = triggerEl.getAttribute('data-subject-id');
        var subjectName = triggerEl.getAttribute('data-subject-name') || '';
        var currentTpl  = triggerEl.getAttribute('data-current-tpl') || 'lower';

        var modal = document.getElementById('scheme-tpl-modal');
        modal._subjectId = subjectId;
        modal._triggerEl = triggerEl;

        document.getElementById('scheme-tpl-subname').textContent = subjectName;
        buildCards(currentTpl);

        var alertEl = document.getElementById('scheme-tpl-alert');
        alertEl.style.display = 'none';
        alertEl.className = 'alert';

        window.jQuery('#scheme-tpl-modal').modal('show');
    }

    /* ── save ────────────────────────────────────────────────────── */
    function applyTemplate(cardEl) {
        var modal      = document.getElementById('scheme-tpl-modal');
        var tpl        = cardEl.getAttribute('data-tpl');
        var subjectId  = modal._subjectId;
        var triggerEl  = modal._triggerEl;
        var alertEl    = document.getElementById('scheme-tpl-alert');
        var url        = getCfg().updateTemplateUrl || '';

        if (!url) {
            alertEl.className = 'alert alert-danger';
            alertEl.textContent = 'Configuration error: update URL not set.';
            alertEl.style.display = 'block';
            return;
        }

        /* loading state */
        document.querySelectorAll('#scheme-tpl-grid .scheme-tpl-card').forEach(function (c) {
            c.classList.add('stc-dim');
        });
        cardEl.classList.remove('stc-dim');
        cardEl.classList.add('stc-loading');

        window.jQuery.ajax({
            url: url,
            method: 'POST',
            data: { _token: getToken(), subject_id: subjectId, template: tpl },
            dataType: 'json',
            success: function (res) {
                document.querySelectorAll('#scheme-tpl-grid .scheme-tpl-card').forEach(function (c) {
                    c.classList.remove('stc-dim', 'stc-loading');
                });
                if (!res || !res.status) {
                    alertEl.className = 'alert alert-danger';
                    alertEl.textContent = (res && res.message) ? res.message : 'Failed to update template.';
                    alertEl.style.display = 'block';
                    return;
                }

                var meta = tplMeta(tpl);

                /* update the badge chip in the grid row */
                var badge = document.querySelector('.js-tpl-badge[data-subject-id="' + subjectId + '"]');
                if (badge) {
                    badge.style.background = meta.color;
                    badge.setAttribute('data-current-tpl', tpl);
                    var iconEl = badge.querySelector('.js-tpl-icon');
                    var lblEl  = badge.querySelector('.js-tpl-label');
                    if (iconEl) iconEl.className = 'fa ' + meta.icon + ' js-tpl-icon';
                    if (lblEl)  lblEl.textContent = meta.label;
                }

                /* keep the trigger in sync (for re-opening) */
                if (triggerEl) triggerEl.setAttribute('data-current-tpl', tpl);

                /* keep the "Add item" popup in sync */
                var addBtn = document.querySelector('.js-open-scheme-popup[data-subject-id="' + subjectId + '"]');
                if (addBtn) addBtn.setAttribute('data-scheme-template', tpl);

                /* success flash then close */
                alertEl.className = 'alert alert-success';
                alertEl.innerHTML = '<i class="fa fa-check-circle"></i> Template set to <strong>' + meta.label + '</strong>.';
                alertEl.style.display = 'block';
                setTimeout(function () {
                    window.jQuery('#scheme-tpl-modal').modal('hide');
                }, 820);
            },
            error: function (xhr) {
                document.querySelectorAll('#scheme-tpl-grid .scheme-tpl-card').forEach(function (c) {
                    c.classList.remove('stc-dim', 'stc-loading');
                });
                var msg = 'Failed to update. Please try again.';
                if (xhr.status === 403) msg = 'You do not have permission to change this template.';
                else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alertEl.className = 'alert alert-danger';
                alertEl.textContent = msg;
                alertEl.style.display = 'block';
            }
        });
    }

    /* ── event delegation ────────────────────────────────────────── */
    document.addEventListener('click', function (e) {
        var switcher = e.target.closest('.js-tpl-switch-btn');
        if (switcher) {
            e.preventDefault();
            openSwitcher(switcher);
            return;
        }
        var card = e.target.closest('.scheme-tpl-card');
        if (card && card.closest('#scheme-tpl-modal')) {
            e.preventDefault();
            if (!card.classList.contains('stc-loading') && !card.classList.contains('stc-dim')) {
                applyTemplate(card);
            }
        }
    });
})();
