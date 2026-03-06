<?php
use App\Models\Utils;
$ent = Utils::ent();
$_hex = $ent->color ?? '#343a40';
$_r = hexdec(substr($_hex, 1, 2));
$_g = hexdec(substr($_hex, 3, 2));
$_b = hexdec(substr($_hex, 5, 2));
?><style>
    :root {
        --primary: {{ $ent->color }};
        --primary-rgb: {{ $_r }}, {{ $_g }}, {{ $_b }};
    }

    .sidebar {
        background-color: #FFFFFF;
    }

    .content-header {
        background-color: #F9F9F9;
    }

    .sidebar-menu .active {
        border-left: solid 3px {{ $ent->color }} !important;
        color: {{ $ent->color }} !important;
    }

    .navbar,
    .logo,
    .sidebar-toggle,
    .user-header,
    .btn-dropbox,
    .btn-twitter,
    .btn-instagram,
    .btn-primary,
    .navbar-static-top {
        background-color: {{ $ent->color }} !important;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: {{ $ent->color }} !important;
        opacity: 0.88;
    }

    .dropdown-menu {
        border: 1px solid #e0e0e0 !important;
    }

    .box-success {
        border-top-color: {{ $ent->color }} !important;
    }
</style>
