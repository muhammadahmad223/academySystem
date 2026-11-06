<?php
$current_module = basename(dirname($_SERVER['PHP_SELF']));

$is_dashboard = ($current_module == 'dashboard');
$is_students = ($current_module == 'student');
$is_teachers = ($current_module == 'teachers');
$is_fees = ($current_module == 'fees');
$is_attendance = ($current_module == 'attendance');
$is_accounts = ($current_module == 'accounts');
?>

<aside class="app-sidebar academy-sidebar shadow" data-bs-theme="dark">

    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="../dashboard/index.php" class="brand-link">
            <img
                src="../dist/assets/img/uniquelogo2.png"
                alt="Unique School"
                class="brand-image academy-brand-image"
            >
            <span class="brand-text">Academy System</span>
        </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">

        <div class="academy-sidebar-label">MAIN MENU</div>

        <nav class="mt-1">
            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="../dashboard/index.php"
                       class="nav-link <?= $is_dashboard ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-grid-1x2-fill"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Students -->
                <li class="nav-item">
                    <a href="../student/index.php"
                       class="nav-link <?= $is_students ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-mortarboard-fill"></i>
                        <p>Manage Students</p>
                    </a>
                </li>

                <!-- Teachers -->
                <li class="nav-item">
                    <a href="../teachers/index.php"
                       class="nav-link <?= $is_teachers ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-person-video3"></i>
                        <p>Manage Teachers</p>
                    </a>
                </li>

                <!-- Fees -->
                <li class="nav-item <?= $is_fees ? 'menu-open' : '' ?>">
                    <a href="#"
                       class="nav-link <?= $is_fees ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>
                            Fees
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="../fees/fee_structure.php"
                               class="nav-link <?= ($is_fees && basename($_SERVER['PHP_SELF']) == 'fee_structure.php') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Fee Structure</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../fees/fee_collection.php"
                               class="nav-link <?= ($is_fees && basename($_SERVER['PHP_SELF']) == 'fee_collection.php') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Fee Collection</p>
                            </a>
                        </li>

                    </ul>
                </li>

                <!-- Attendance -->
                <li class="nav-item <?= $is_attendance ? 'menu-open' : '' ?>">
                    <a href="#"
                       class="nav-link <?= $is_attendance ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-calendar-check-fill"></i>
                        <p>
                            Attendance
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="../attendance/index.php"
                               class="nav-link <?= ($is_attendance && basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Daily Attendance</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="../attendance/attendance_history.php"
                               class="nav-link <?= ($is_attendance && basename($_SERVER['PHP_SELF']) == 'attendance_history.php') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Attendance History</p>
                            </a>
                        </li>

                    </ul>
                </li>

                <!-- Accounts -->
                <li class="nav-item">
                    <a href="../accounts/index.php"
                       class="nav-link <?= $is_accounts ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>Accounts</p>
                    </a>
                </li>

            </ul>
        </nav>

        <div class="academy-sidebar-footer">
            <i class="bi bi-shield-check"></i>
            <span>Academy Management System</span>
        </div>

    </div>
</aside>