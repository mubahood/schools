<?php
use App\Models\Utils;
$ent = Utils::ent();
?><style>
    .sidebar {
        background-color: #FFFFFF;
    }

    .content-header {
        background-color: #F9F9F9;
    }

    .sidebar-menu .active {
        border-left: solid 5px {{ $ent->color }} !important;
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

    .dropdown-menu {
        border: none !important;
    }

    .box-success {
        border-top: {{ $ent->color }} .5rem solid !important;
    }

    :root {
        --primary: {{ $ent->color }};
    }
</style>
