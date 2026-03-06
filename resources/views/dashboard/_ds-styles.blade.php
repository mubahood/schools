@once
<style>
/* Dashboard Design System — compact, square corners, enterprise primary */
.ds-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 0;
    overflow: hidden;
    margin-bottom: 14px;
}
.ds-card-header {
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e8e8e8;
    background: #fafbfc;
}
.ds-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ds-card-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
    border-radius: 0;
    flex-shrink: 0;
}
.ds-card-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #343a40;
    line-height: 1.3;
}
.ds-card-subtitle {
    font-size: 11px;
    color: #868e96;
    margin-top: 1px;
    line-height: 1.3;
}
.ds-badge {
    color: #fff;
    font-weight: 700;
    font-size: 11px;
    padding: 3px 10px;
    min-width: 32px;
    text-align: center;
    border-radius: 0;
}
.ds-table-scroll {
    max-height: 340px;
    overflow-y: auto;
}
.ds-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.ds-table thead th {
    background: #f5f6f8;
    border-bottom: 1px solid #dee2e6;
    padding: 6px 10px;
    font-weight: 700;
    color: #495057;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.4px;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
}
.ds-table tbody td {
    padding: 5px 10px;
    border-bottom: 1px solid #f2f2f2;
    color: #495057;
    vertical-align: middle;
}
.ds-table tbody tr:hover {
    background: #f8f9fa;
}
.ds-table tfoot td {
    background: #f5f6f8;
    font-weight: 700;
    padding: 6px 10px;
    border-top: 1px solid #dee2e6;
    font-size: 12px;
    position: sticky;
    bottom: 0;
    z-index: 1;
}
.ds-link {
    color: var(--ds-accent, #343a40);
    font-weight: 600;
    text-decoration: none;
    font-size: 12px;
}
.ds-link:hover {
    text-decoration: underline;
    color: var(--ds-accent, #343a40);
}
.ds-tag {
    display: inline-block;
    border: 1px solid #dee2e6;
    padding: 1px 6px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 0;
}
.ds-btn-sm {
    border: 1px solid #ced4da;
    color: #6c757d;
    background: none;
    padding: 2px 6px;
    font-size: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    border-radius: 0;
    cursor: pointer;
}
.ds-btn-sm:hover {
    border-color: var(--ds-accent, #343a40);
    color: var(--ds-accent, #343a40);
    text-decoration: none;
}
.ds-card-footer {
    padding: 8px 14px;
    background: #fafbfc;
    border-top: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: #868e96;
}
.ds-card-footer a {
    color: var(--ds-accent, #343a40);
    font-weight: 600;
    text-decoration: none;
    font-size: 11px;
}
.ds-card-footer a:hover {
    text-decoration: underline;
}
.ds-muted {
    color: #adb5bd;
}
.ds-chart-container {
    position: relative;
    width: 100%;
    height: 300px;
}
.ds-legend {
    display: flex;
    gap: 14px;
    margin-top: 8px;
    justify-content: center;
    flex-wrap: wrap;
}
.ds-legend-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 600;
    color: #6c757d;
}
.ds-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 0;
    display: inline-block;
}
.ds-boys { color: #2980b9; font-weight: 600; }
.ds-girls { color: #e74c8b; font-weight: 600; }
.ds-total { color: #343a40; font-weight: 700; }
.ds-class-name { font-weight: 600; color: #343a40; }
.ds-streams { text-align: center; }

@media (max-width: 767px) {
    .ds-chart-container { height: 220px; }
    .ds-table-scroll { max-height: 260px; }
}
</style>
@endonce
