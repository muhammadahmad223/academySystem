<?php
include '../config/db_config.php';
$tec_id = $_GET['id'];
$sql = "SELECT * FROM teachers WHERE id = {$tec_id}";
$result = mysqli_query($conn, $sql) or die("Query Unsuccessful");
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
        <?php
        session_start();
            if (!isset($_SESSION['user_id'])) {
                header("Location: ../login.php");
                exit;
            }
        ?>
        <!doctype html>
        <html lang="en">      
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
        .teacher-card { border-radius: 8px; }
        .teacher-profile-section { display: flex; align-items: center; gap: 20px; }
        .teacher-photo-wrapper { width: 85px; height: 85px; position: relative; flex-shrink: 0; }
        .teacher-photo { width: 85px; height: 85px; object-fit: cover; border-radius: 50%; border: 1px solid #dee2e6; }
        .teacher-photo-edit { position: absolute; right: -3px; bottom: -3px; width: 27px; height: 27px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #ddd; border-radius: 50%; font-size: 12px; cursor: pointer; box-shadow: 0 2px 4px rgba(0, 0, 0, .12); }
        .teacher-id-box {    width: 260px;  }
    </style>
        </head>
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
                                        <h1>Teacher Management</h1>
                                        <p>Add a new teacher record</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb float-sm-end">
                                                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                                <li class="breadcrumb-item"><a href="../teachers/index.php">Forms</a></li>
                                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="app-content">
                            <div class="container-fluid">
                                <form action="update.php" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card teacher-card">
                                                <div class="card-body">
                                                    <div class="teacher-form-header">
                                                        <div>
                                                            <h2 class="h5 mb-1">Teacher Information</h2>
                                                            <p class="text-muted mb-0">
                                                                Enter the teacher's basic information and employment details.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="teacher_id" value="<?php echo $row['id']; ?>">
                                                    <hr class="my-4">
                                                    <!-- Profile Section -->
                                                    <div class="teacher-profile-section mb-4">
                                                        <div class="teacher-photo-wrapper">
                                                            <img src="<?php
                                                                        echo !empty($row['teacher_img'])
                                                                            ? '../uploads/teachers/' . $row['teacher_img']
                                                                            : '../dist/assets/img/defaultimage.jpg';
                                                                        ?>"
                                                                id="teacher_photo_preview" class="teacher-photo" alt="Teacher Photo">
                                                            <label for="teacher_photo" class="teacher-photo-edit">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </label>
                                                            <input type="file" name="teacher_photo" id="teacher_photo" hidden>
                                                            <div class="form-text mt-1 text-muted">
                                                                Teacher Photo
                                                            </div>
                                                        </div>
                                                        <div class="teacher-id-box">
                                                            <label class="form-label">
                                                                Teacher ID <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">TEC-</span>
                                                                <input type="text" name="teacher_form_no" class="form-control"
                                                                    value="<?php echo str_replace('TEC-', '', $row['tform_no']); ?>"  readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Teacher Name <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" name="teacher_name" class="form-control " placeholder="Enter Teacher Name"
                                                                value="<?php echo $row['teacher_name']; ?>"  required>
                                                        </div>                               
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Father Name <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text" name="father_name" class="form-control" placeholder="Enter Father Name"
                                                                value="<?php echo $row['father_name']; ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Date of Birth <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="date" class="form-control" name="date_of_birth" id="date_of_birth"
                                                                value="<?php echo $row['teacher_dob']; ?>"
                                                                required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                CNIC
                                                            </label>
                                                            <input id="teacher_cnic"  value="<?php echo $row['teacher_cnic']; ?>" type="text" class="form-control " data-inputmask="'mask': '99999-9999999-9'" placeholder="XXXXX-XXXXXXX-X" name="teacher_cnic"
                                                                maxlength="15">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Gender <span class="text-danger">*</span>
                                                            </label>
                                                            <select name="gender" class="form-select" required>
                                                                <option value="">Select Gender</option>
                                                                <option value="Male" <?php if ($row['teacher_gender'] == 'Male') echo 'selected'; ?>> Male </option>
                                                                <option value="Female" <?php if ($row['teacher_gender'] == 'Female') echo 'selected'; ?>> Female </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label" for="fcontact">
                                                                Contact No<span class="text-danger">*</span>
                                                            </label>
                                                            <input id="contact_no" type="text"class="form-control" data-inputmask="'mask': '0399-99999999'" placeholder="03XX-XXXXXXX" name="contact_no" maxlength="12"
                                                                value="<?php echo $row['teacher_contact']; ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Qualification <span class="text-danger">*</span>
                                                            </label>

                                                            <input type="text" name="qualification"  class="form-control"  placeholder="e.g. BS Computer Science"
                                                                value="<?php echo $row['qualification']; ?>"  required>
                                                        </div>                                
                                                        <div class="col-md-4">
                                                            <label class="form-label">   Experience   </label>
                                                            <input  type="text"  name="experience"  class="form-control"  placeholder="e.g. 2 Years" 
                                                             value="<?php echo $row['experience']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Joining Date <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="date" name="joining_date"class="form-control"
                                                                value="<?php echo date('Y-m-d'); ?>"
                                                                value="<?php echo $row['joining_date']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">
                                                                Basic Salary <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="number" name="basic_salary"   class="form-control"   placeholder="Enter Basic Salary"
                                                                value="<?php echo $row['basic_salary']; ?>"  required>
                                                        </div>              
                                                        <div class="col-md-8">
                                                            <label class="form-label">
                                                                Address <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea  name="address"  class="form-control"  rows="2" placeholder="Enter Address"
                                                                required><?php echo $row['teacher_address']; ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="text-center justify-content-end gap-2 mt-4 pt-3 border-top">
                                                        <a href="index.php" class="btn btn-outline-secondary"> Cancel  </a>

                                                        <button type="submit" class="btn btn-success px-4">
                                                            <i class="bi bi-check-lg me-1"></i>
                                                            Update Teacher
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                </form>
                        <?php
                    }
                } else {
                    echo "Teacher not found.";
                }
                mysqli_close($conn);
                        ?>
                            </div>
                        </div>
                </div>
            </div>
            </main>
            <?php include '../layout/footer.php'; ?>
            </div>
            </div>

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
            <!-- Core jQuery -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
            <script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/3/jquery.inputmask.bundle.js"></script>
            <script>
                $("#teacher_cnic").inputmask();
                $("#contact_no").inputmask();
            </script>
            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
                document.getElementById('teacher_photo').addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                          document.getElementById('teacher_photo_preview').src =
                            URL.createObjectURL(file);
                    }
                });
            </script>
        </body>

        </html>