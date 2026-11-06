
<?php
session_start();
include './config/db_config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit;
}



$error_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
     if (mysqli_num_rows($result) > 0) {

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];

        header("Location: dashboard/index.php");


    } else {

        echo "Wrong password";

    }

} else {

    echo "Username not found";
}
}

?>


<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Login Page</title>

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
    <meta name="title" content="AdminLTE 4 | Login Page" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./dist/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./dist/css/adminlte.css" />
        <link rel="stylesheet" href="./dist/css/login-custom.css" />

    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  
  <body class="academy-login">

  <div class="login-wrapper">

    <!-- ================= LEFT SIDE ================= -->
    <div class="login-brand-side">

      <div class="brand-content">

        <div class="brand-logo">
          <img
            src="./dist/assets/img/tclogo2.png"
            alt="Trenova Codes"
          />
        </div>

        <div class="brand-line"></div>

        <p class="brand-tagline">
          Transforming Ideas into Digital Solutions
        </p>


        <!-- Features -->

        <div class="brand-features">

          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-shield-check"></i>
            </div>

            <div>
              <h5>Secure & Reliable</h5>
              <p>Your data is safe and protected</p>
            </div>
          </div>


          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-lightning-charge"></i>
            </div>

            <div>
              <h5>Fast & Efficient</h5>
              <p>Optimized for the best performance</p>
            </div>
          </div>


          <div class="feature-item">
            <div class="feature-icon">
              <i class="bi bi-people"></i>
            </div>

            <div>
              <h5>Smart Management</h5>
              <p>Manage everything in one place</p>
            </div>
          </div>

        </div>

      </div>

    </div>


    <!-- ================= RIGHT SIDE ================= -->

    <div class="login-form-side">

      <div class="login-container">

        <!-- Academy Logo -->

        <div class="academy-logo">

          <div class="academy-logo-icon">
            <i class="bi bi-mortarboard-fill"></i>
          </div>

          <h1>
            Academy<span>System</span>
          </h1>

          <div class="title-line"></div>

        </div>


        <!-- Login Card -->

        <div class="login-card">

          <div class="login-header">

            <h2>Welcome Back</h2>

            <p>
              Sign in to start your session
            </p>

          </div>


          <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">


            <!-- Email -->

            <div class="custom-input">

              <div class="input-icon">
                <i class="bi bi-person"></i>
              </div>

              <input
                id="username"
                type="text"
                name="username"
                placeholder="Username"
                
                required
              />

            </div>


            <!-- Password -->

            <div class="custom-input">

              <div class="input-icon">
                <i class="bi bi-lock"></i>
              </div>

              <input
                id="loginPassword"
                type="password"
                name="password"
                placeholder="Password"
                required
              />

              <button
                type="button"
                class="password-toggle"
                onclick="togglePassword()"
              >
                <i class="bi bi-eye"></i>
              </button>

            </div>


            <!-- Remember / Forgot -->

            <div class="login-options">

              <label class="remember">

                <input
                  type="checkbox"
                  value="1"
                />

                <span>Remember Me</span>

              </label>


              <a href="forgot-password.html">
                Forgot Password?
              </a>

            </div>


            <!-- Sign In -->

            <button
              type="submit"
              class="signin-btn"
            >

              <span>Log In</span>

              <i class="bi bi-arrow-right"></i>

            </button>


          

          

          </form>

        </div>


        <!-- Footer -->

        <div class="login-footer">

          © 2026 Academy System. All rights reserved.

        </div>

      </div>

    </div>

  </div>


  <!-- Password Toggle -->

  <script>

    function togglePassword() {

      const password =
        document.getElementById("loginPassword");

      const icon =
        document.querySelector(".password-toggle i");


      if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("bi-eye");

        icon.classList.add("bi-eye-slash");

      } else {

        password.type = "password";

        icon.classList.remove("bi-eye-slash");

        icon.classList.add("bi-eye");

      }

    }

  </script>

</body>
  <!--end::Body-->
</html>
