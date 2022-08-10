<?php

namespace App\Admin\Extensions\Nav;

use Illuminate\Contracts\Support\Renderable;

class Dropdown implements Renderable
{
  public function render()
  {

    $fees = admin_url('fees');
    $transactions = admin_url('transactions');
    $accounts = admin_url('accounts');
    $students = admin_url('students');
    $teachers = admin_url('employees');
    $classes = admin_url('students-classes');
    $marks = admin_url('marks');
    return <<<HTML
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-th"></i>
    </a>
    <ul class="dropdown-menu" style="padding: 0;box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);">
        <li>
           <div class="box box-solid" style="width: 300px;height: 300px;margin-bottom: 0;">
            <!-- /.box-header -->
            <div class="box-body">
              <a class="btn btn-app" href="$fees">
            <i class="fa fa-money"></i> School fees
              </a>
              <a class="btn btn-app" href="$transactions">
                <i class="fa fa-balance-scale"></i> Transactions
              </a>
              <a class="btn btn-app" href="$accounts">
                <i class="fa fa-calculator"></i>Accounts
              </a>
              <a class="btn btn-app" href="$students">
                <i class="fa fa-users"></i>Students
              </a>
              <a class="btn btn-app" href="$teachers">
                <i class="fa fa-graduation-cap"></i> Teachers
              </a>
              
              <a class="btn btn-app" href="$classes">
                <i class="fa fa-building-o"></i> Classes
              </a>
              <a class="btn btn-app" href="$marks">
                <i class="fa fa-check"></i> Marks
              </a>
 
              <a class="btn btn-app" href="javascript:;">
                <i class="fa fa-line-chart"></i> Charts
              </a>
            </div>
            <!-- /.box-body -->
          </div>
      </li>
    </ul>
</li>
HTML;
  }
}
