<?php

namespace App\Admin\Extensions\Nav;

class Links
{
    public function __toString()
    {
        return <<<HTML
<!-- <li>
    <a href="#">
      <i class="fa fa-envelope-o"></i>
      <span class="label label-success">4</span>
    </a>
</li> -->

<!-- <li>
    <a href="#">
      <i class="fa fa-bell-o"></i>
      <span class="label label-warning">7</span>
    </a>
</li> -->

<!-- <li>
    <a href="#">
      <i class="fa fa-flag-o"></i>
      <span class="label label-danger">9</span>
    </a>
</li> -->

<li class="dropdown notifications-menu">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
<i class="fa fa-envelope-o"></i>
      <span class="label label-success">4</span>
</a>


<ul class="dropdown-menu">
  <li class="header">You have 4 new Messages</li>
  <li>
    <!-- inner menu: contains the actual data -->
    <ul class="menu">
      <li>
        <a href="#">
          <i class="fa fa-users text-aqua"></i> Hi
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-users text-aqua"></i> Hi
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-users text-aqua"></i> Hi
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-users text-aqua"></i> Hi
        </a>
      </li>
    </ul>
  </li>
  <li class="footer"><a href="#">View all</a></li>
</ul>
</li>


<li class="dropdown notifications-menu">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
  
<i class="fa fa-flag-o"></i>
      <span class="label label-danger">2</span>
</a>
<ul class="dropdown-menu">
  <li class="header">You have 2 flags</li>
  <li>
    <!-- inner menu: contains the actual data -->
    <ul class="menu">
      <li>
        <a href="#">
          <i class="fa fa-warning text-yellow"></i>Pending marks
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-warning text-yellow"></i>Missing action
        </a>
      </li>
    </ul>
  </li>
  <li class="footer"><a href="#">View all</a></li>
</ul>
</li>


<li class="dropdown notifications-menu">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
  <i class="fa fa-bell-o"></i>
  <span class="label label-warning">10</span>
</a>
<ul class="dropdown-menu">
  <li class="header">You have 10 notifications</li>
  <li>
    <!-- inner menu: contains the actual data -->
    <ul class="menu">
      <li>
        <a href="#">
          <i class="fa fa-users text-aqua"></i>5 new members joined today
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-warning text-yellow"></i>Very long description here that may not fit into the
          page and may cause design problems
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-users text-red"></i> 5 new members joined
        </a>
      </li>

      <li>
        <a href="#">
          <i class="fa fa-shopping-cart text-green"></i> 25 inspectors approved
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-user text-red"></i> You changed your username
        </a>
      </li>
    </ul>
  </li>
  <li class="footer"><a href="#">View all</a></li>
</ul>
</li>


HTML;
    }
}

