<?php
include '../config/db_config.php';
$fee_structure = [];
$fee_sql = "SELECT class_name, monthly_fee FROM fee_structure";
$fee_result = mysqli_query($conn, $fee_sql);
if ($fee_result) {
    while ($fee_row = mysqli_fetch_assoc($fee_result)) {
        $fee_structure[$fee_row['class_name']] = $fee_row['monthly_fee'];
    }
}
$sql_form = "SELECT sformNo FROM students ORDER BY id DESC LIMIT 1";
$result_form = mysqli_query($conn, $sql_form);
if (mysqli_num_rows($result_form) > 0) {
    $row_form = mysqli_fetch_assoc($result_form);
    $last_number = (int) str_replace('STU-', '', $row_form['sformNo']);
    $next_number = $last_number + 1;
} else {
    $next_number = 1001;
}
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE v4 | Dashboard</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
        (() => {
            'use strict';
            const root = document.documentElement;

            // Applications with their own theming opt out of AdminLTE's color mode
            // entirely, here as well as in the bundle.
            if (root.getAttribute('data-lte-color-mode') === 'off') {
                return;
            }

            const STORAGE_KEY = 'lte-theme';
            let stored = null;
            try {
                stored = localStorage.getItem(STORAGE_KEY);
            } catch {
                // localStorage may be unavailable (private mode, sandboxed iframe).
            }
            // Mirror the precedence in color-mode.ts: the visitor's stored choice
            // wins, then a theme this page declared itself, then the OS preference.
            const authored = root.getAttribute('data-bs-theme');
            let resolved = 'light';
            if (stored === 'dark' || stored === 'light') {
                resolved = stored;
            } else if (authored === 'dark' || authored === 'light') {
                resolved = authored;
            } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
                resolved = 'dark';
            }
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
            // Flag values computed here, so the bundle does not mistake them for a
            // theme the page declared and stop following the OS preference.
            if (resolved !== authored) {
                root.setAttribute('data-lte-theme-resolved', '');
            }
        })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
        name="description"
        content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
    <meta
        name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../dist/css/adminlte.css" as="style" />
    <link rel="stylesheet" href="../dist/css/custom.css">
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
        crossorigin="anonymous"
        media="print"
        onload="this.media = 'all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
    <link rel="stylesheet" href="../dist/css/student-create.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
        crossorigin="anonymous" />
    <!-- jsvectormap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
        crossorigin="anonymous" />

    <style>
        .wizard-steps {
            counter-reset: step;
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 1rem;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--bs-border-color);
            z-index: 0;
        }

        .wizard-steps li {
            position: relative;
            z-index: 1;
            background: var(--bs-body-bg);
            padding: 0 0.75rem;
            text-align: center;
            color: var(--bs-secondary-color);
            font-size: 0.875rem;
        }

        .wizard-steps li::before {
            counter-increment: step;
            content: counter(step);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            margin: 0 auto 0.5rem;
            border-radius: 50%;
            background: var(--bs-body-tertiary-bg);
            border: 2px solid var(--bs-border-color);
            color: var(--bs-secondary-color);
            font-weight: 600;
        }

        .wizard-steps li.active {
            color: var(--bs-primary);
            font-weight: 600;
        }

        .wizard-steps li.active::before {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
        }

        .wizard-steps li.completed::before {
            background: var(--bs-success);
            border-color: var(--bs-success);
            color: #fff;
            content: '\f633';
            font-family: 'bootstrap-icons';
        }

        /* profile pic css */
        .student-photo-wrapper {
            width: 120px;
            height: 120px;
            position: relative;
            margin: 0 auto;
        }

        .student-photo {
            width: 100% !important;
            height: 100% !important;
            max-width: 120px;
            max-height: 120px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            border: 2px solid #e0e0e0;
            /* Optional: Clean border for photo */
        }

        .photo-edit-btn {
            position: absolute;
            bottom: 0px;
            right: 0px;
            width: 32px;
            height: 32px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease-in-out;
        }

        .photo-edit-btn:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
    </style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <?php include '../layout/sidebar.php'; ?>
        <div class="layout-page">
            <?php include '../layout/header.php'; ?>



            <main class="app-main">
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <h1>Student Admission</h1>
                                <p>Create New Student Admission Record</p>
                            </div>
                            <div class="col-sm-6">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb float-sm-end">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                        <li class="breadcrumb-item"><a href="index.php">Lists</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Students</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="app-content">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 col-xl-8">
                                <div class="card">
                                    <div class="card-body p-4">
                                        <!-- Step indicators -->
                                        <ol class="wizard-steps mb-4" id="wizard-steps">
                                            <li class="active" data-step="0">Admission</li>
                                            <li data-step="1">Student & Family</li>
                                            <li data-step="2">Academic & Fees</li>

                                        </ol>

                                        <!-- Form -->

                                        <form id="wizard-form" novalidate action="insertcreate.php" method="post" enctype="multipart/form-data">


                                            <fieldset class="wizard-step" data-step="0">

                                                <h2 class="h5 mb-3">Admission Information</h2>

                                                <!-- student photo -->
                                                <div class="text-center mb-4">

                                                    <div class="student-photo-wrapper">

                                                        <img
                                                            src="../dist/assets/img/defaultimage.jpg"
                                                            alt="Student Photo"
                                                            id="student_photo_preview"
                                                            class="student-photo">

                                                        <label for="student_photo" class="photo-edit-btn">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </label>

                                                    </div>

                                                    <input
                                                        type="file"
                                                        name="student_photo"
                                                        id="student_photo"
                                                        hidden>

                                                    <div class="form-text mt-2">
                                                        Student Photo
                                                    </div>

                                                </div>


                                                <!-- Admission Information -->
                                                <div class="row g-3">

                                                    <!-- Student Form No -->
                                                    <div class="col-md-6">

                                                        <label class="form-label" for="student_form_no">
                                                            Student Form No <span class="text-danger">*</span>
                                                        </label>

                                                        <div class="input-group">
                                                            <span class="input-group-text">STU-</span>

                                                            <input
                                                                type="text"
                                                                class="form-control required-field"
                                                                name="student_form_no"
                                                                id="student_form_no"
                                                                value="<?php echo $next_number; ?>"
                                                                placeholder="11001"
                                                                required>
                                                        </div>

                                                        <div class="invalid-feedback">
                                                            Please enter Student Form No.
                                                        </div>

                                                    </div>


                                                    <!-- Admission Date -->
                                                    <div class="col-md-6">

                                                        <label class="form-label" for="admission_date">
                                                            Admission Date <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="date"
                                                            class="form-control"
                                                            name="admission_date"
                                                            id="admission_date"
                                                            value="<?php echo date('Y-m-d'); ?>">

                                                    </div>


                                                    <!-- Academic Year -->
                                                    <div class="col-md-6">

                                                        <label class="form-label" for="academic_year">
                                                            Academic Year <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control required-field"
                                                            name="academic_year"
                                                            id="academic_year"
                                                            placeholder="e.g. 2026"
                                                            required>

                                                        <div class="invalid-feedback">
                                                            Please select Admission Year.
                                                        </div>

                                                    </div>

                                                </div>

                                            </fieldset>


                                            <fieldset class="wizard-step d-none" data-step="1">

                                                <h2 class="h5 mb-3">Student & Family Information</h2>

                                                <div class="row g-3">


                                                    <div class="col-md-6">
                                                        <label class="form-label" for="student_name">
                                                            Student Name <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control required-field"
                                                            name="student_name"
                                                            id="student_name"
                                                            placeholder="Enter Student Name">
                                                        <div class="invalid-feedback">
                                                            Please enter Student Name.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-6">
                                                        <label class="form-label" for="date_of_birth">
                                                            Date of Birth <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="date"
                                                            class="form-control required-field"
                                                            name="date_of_birth"
                                                            id="date_of_birth">
                                                        <div class="invalid-feedback">
                                                            Please enter Student DOB.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            Gender <span class="text-danger">*</span>
                                                        </label>

                                                        <select class="form-select required-field" id="gender" name="gender">
                                                            <option value="">Choose...</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>

                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Please Select Gender.
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label" for="sbform">
                                                            B-Form/CNIC
                                                        </label>

                                                        <input id="sbform" type="text" class="form-control " data-inputmask="'mask': '99999-9999999-9'" placeholder="XXXXX-XXXXXXX-X" name="sbform" maxlength="15">
                                                        <div class="invalid-feedback">
                                                            Please enter Student B-form.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-6">
                                                        <label class="form-label" for="religion">
                                                            Religion
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="religion"
                                                            id="religion"
                                                            placeholder="Enter Religion">
                                                    </div>



                                                    <div class="col-md-6">
                                                        <label class="form-label" for="father_name">
                                                            Father Name <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control required-field"
                                                            name="father_name"
                                                            id="father_name"
                                                            placeholder="Enter Father Name">
                                                        <div class="invalid-feedback">
                                                            Please enter Father Name.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-6">
                                                        <label class="form-label" for="fcontact">
                                                            Father Contact <span class="text-danger">*</span>
                                                        </label>

                                                        <input id="fcontact" type="text" class="form-control required-field" data-inputmask="'mask': '0399-99999999'" placeholder="03XX-XXXXXXX" type="number" name="fcontact" maxlength="12">
                                                        <div class="invalid-feedback">
                                                            Please enter Father Contact.
                                                        </div>
                                                    </div>




                                                    <div class="col-md-6">
                                                        <label class="form-label" for="city">
                                                            City <span class="text-danger">*</span>
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="form-control required-field"
                                                            name="city"
                                                            id="city"
                                                            placeholder="Enter City">
                                                        <div class="invalid-feedback">
                                                            Please enter City.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-12">
                                                        <label class="form-label" for="address">
                                                            Address <span class="text-danger">*</span>
                                                        </label>

                                                        <textarea
                                                            class="form-control required-field"

                                                            name="address"
                                                            id="address"
                                                            rows="2"
                                                            placeholder="Enter Address"></textarea>
                                                        <div class="invalid-feedback">
                                                            Please enter Address.
                                                        </div>
                                                    </div>

                                                </div>

                                            </fieldset>




                                            <fieldset class="wizard-step d-none" data-step="2">
                                                <div id="academic-fees-content">
                                                    <h2 class="h5 mb-3">Academic & Fees</h2>

                                                    <div class="row g-3">

                                                        <!-- Class -->
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="student_class">
                                                                Class <span class="text-danger">*</span>
                                                            </label>

                                                            <select class="form-select required-field" id="student_class" name="student_class" required>

                                                                <option value="">Choose Class...</option>
                                                                <option>Play Group</option>
                                                                <option>Nursery</option>
                                                                <option>Prep</option>
                                                                <option>One</option>
                                                                <option>Two</option>
                                                                <option>Three</option>
                                                                <option>Four</option>
                                                                <option>Five</option>
                                                                <option>Six</option>
                                                                <option>Seven</option>
                                                                <option>Eight</option>
                                                                <option>Nine</option>
                                                                <option>Ten</option>
                                                                <option>Eleven</option>
                                                                <option>Twelve</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please Select Class.
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">

                                                            <label class="form-label" for="subjects">
                                                                Subjects <span class="text-danger">*</span>
                                                            </label>

                                                            <select class=" select2 required-field " name="subjects[]" id="subjects" multiple>


                                                                <?php

                                                                $sql = "SELECT * FROM subjects";
                                                                $result = mysqli_query($conn, $sql);

                                                                while ($row = mysqli_fetch_assoc($result)) {
                                                                ?>

                                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['subject_name']; ?></option>
                                                                <?php
                                                                }
                                                                ?>


                                                            </select>




                                                            <div class="invalid-feedback">
                                                                Please select Subjects.
                                                            </div>

                                                        </div>


                                                        <!-- Section -->
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="section">
                                                                Section <span class="text-danger">*</span>
                                                            </label>

                                                            <select class="form-select" id="section" name="section" required>

                                                                <option value="">Choose Section...</option>

                                                                <option value="Red">Red</option>
                                                                <option value="Green">Green</option>
                                                                <option value="Blue">Blue</option>

                                                            </select>
                                                            <div class="invalid-feedback">
                                                                Please Select Section.
                                                            </div>
                                                        </div>


                                                        <!-- Monthly Fee -->
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="monthly_fee">
                                                                Monthly Fee <span class="text-danger">*</span>
                                                            </label>

                                                            <input
                                                                type="number"
                                                                class="form-control required-field"
                                                                name="monthly_fee"
                                                                id="monthly_fee"
                                                                placeholder="Enter Monthly Fee">
                                                            <div class="invalid-feedback">
                                                                Please enter Monthly Fee.
                                                            </div>
                                                        </div>


                                                        <!-- Admission Fee -->
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="admission_fee">
                                                                Admission Fee <span class="text-danger">*</span>
                                                            </label>

                                                            <input
                                                                type="number"
                                                                class="form-control required-field"
                                                                name="admission_fee"
                                                                id="admission_fee"
                                                                placeholder="Enter Admission Fee" required>
                                                            <div class="invalid-feedback">
                                                                Please enter Admission Fee.
                                                            </div>
                                                        </div>


                                                    </div>

                                                </div>
                                                <div id="save-success" class="d-none text-center py-5">

                                                    <i class="bi bi-check-circle-fill text-success fs-1"></i>

                                                    <h4 class="mt-3">
                                                        Student admission saved successfully!
                                                    </h4>

                                                    <button type="button" class="btn btn-primary mt-3" id="add-new-student">
                                                        <i class="bi bi-plus-lg me-1"></i>
                                                        Add New Student
                                                    </button>

                                                </div>




                                            </fieldset>



                                            <div class="d-flex justify-content-between mt-4">

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary"
                                                    id="wz-prev"
                                                    disabled>
                                                    <i class="bi bi-arrow-left me-1"></i>
                                                    Previous
                                                </button>


                                                <button
                                                    type="button"
                                                    class="btn btn-primary"
                                                    id="wz-next">
                                                    Next
                                                    <i class="bi bi-arrow-right ms-1"></i>
                                                </button>


                                                <button
                                                    type="submit"
                                                    class="btn btn-success d-none"
                                                    id="wz-submit">
                                                    <i class="bi bi-check-lg me-1"></i>
                                                    Save Student
                                                </button>

                                            </div>



                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>












            <?php include '../layout/footer.php'; ?>

        </div>
    </div>


    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

            // Disable OverlayScrollbars on mobile devices to prevent touch interference
            const isMobile = window.innerWidth <= 992;

            if (
                sidebarWrapper &&
                OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
                !isMobile
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Color Mode Toggle-->
    <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
    <!--end::Color Mode Toggle-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script
        src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
        crossorigin="anonymous"></script>
    <!-- sortablejs -->
    <script>
        new Sortable(document.querySelector('.connectedSortable'), {
            group: 'shared',
            handle: '.card-header',
        });

        const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
        cardHeaders.forEach((cardHeader) => {
            cardHeader.style.cursor = 'move';
        });
    </script>
    <!-- apexcharts -->
    <script
        src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
        crossorigin="anonymous"></script>


    <!-- jsvectormap -->
    <script
        src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
        integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
        crossorigin="anonymous"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
        integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
        crossorigin="anonymous"></script>






    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const form = document.getElementById('wizard-form');
            const steps = form.querySelectorAll('.wizard-step');
            const indicators = document.querySelectorAll('#wizard-steps li');

            const prevBtn = document.getElementById('wz-prev');
            const nextBtn = document.getElementById('wz-next');
            const submitBtn = document.getElementById('wz-submit');
            const academicFeesContent = document.getElementById('academic-fees-content');
            const successMessage = document.getElementById('save-success');
            const addNewStudentBtn = document.getElementById('add-new-student');

            const params = new URLSearchParams(window.location.search);

            const validateStep = (step) => {

                const fields = step.querySelectorAll('.required-field');

                let valid = true;

                fields.forEach((field) => {

                    if (field.value.trim() === '') {

                        field.classList.add('is-invalid');

                        valid = false;

                    } else {

                        field.classList.remove('is-invalid');

                    }

                });

                return valid;
            };

            let current = parseInt(params.get('step')) || 0;

            const showStep = (index) => {

                steps.forEach((step, i) => {

                    step.classList.toggle('d-none', i !== index);

                });


                indicators.forEach((indicator, i) => {

                    indicator.classList.toggle('active', i === index);
                    indicator.classList.toggle('completed', i < index);

                });


                // Previous
                prevBtn.disabled = index === 0;

                // Next / Save
                const lastStep = index === steps.length - 1;
                nextBtn.classList.toggle('d-none', lastStep);

                submitBtn.classList.toggle('d-none', !lastStep);

            };



            nextBtn.addEventListener('click', () => {

                const currentStep = steps[current];

                if (!validateStep(currentStep)) {
                    return;
                }

                if (current < steps.length - 1) {
                    current++;
                    showStep(current);

                    const url = new URL(window.location);
                    url.searchParams.set('step', current);
                    window.history.pushState({}, '', url);
                }

            });

            prevBtn.addEventListener('click', () => {

                if (current > 0) {
                    current--;
                    showStep(current);

                    // Update URL
                    const url = new URL(window.location);
                    url.searchParams.set('step', current);
                    window.history.pushState({}, '', url);

                }

            });

            form.addEventListener('keydown', (e) => {

                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }

            });



            form.addEventListener('submit', (e) => {

                const currentStep = steps[current];

                if (!validateStep(currentStep)) {
                    e.preventDefault();
                    return;
                }

            });

            addNewStudentBtn.addEventListener('click', () => {

                window.location.href = 'create.php';

            });


            if (params.get('save') === 'success') {

                // Step 2 show karo
                current = 2;
                showStep(current);

                // All 3 steps completed
                indicators.forEach((indicator) => {
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                });

                // Academic & Fees fields hide
                academicFeesContent.classList.add('d-none');

                // Success message show
                successMessage.classList.remove('d-none');

                // All buttons hide
                prevBtn.classList.add('d-none');
                submitBtn.classList.add('d-none');
                nextBtn.classList.add('d-none');

            } else {

                // Normal wizard
                showStep(current);

            }

        });
    </script>

    <script>

    const studentClass = document.getElementById('student_class');
    const monthlyFee = document.getElementById('monthly_fee');

    const feeStructure = <?php echo json_encode($fee_structure); ?>;


    studentClass.addEventListener('change', function () {

        const selectedClass = this.value;

        if (selectedClass && feeStructure[selectedClass]) {

            monthlyFee.value = feeStructure[selectedClass];

        } else {

            monthlyFee.value = '';

        }

    });

</script>
    <!--end::Script-->

    <!-- Core jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/3/jquery.inputmask.bundle.js"></script>
    <script>
        $("#sbform").inputmask();
        $("#fcontact").inputmask();
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#subjects').select2({
                placeholder: "Search and select subjects",
                width: '100%'
            });
        });
    </script>
    <script>
        document.getElementById('student_photo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                document.getElementById('student_photo_preview').src =
                    URL.createObjectURL(file);
            }
        });
    </script>

</body>

<!--end::Body-->

</html>