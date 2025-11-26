<style>
  header.header {
    height: 60px;
    backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(0,0,0,0.05);
  }

  header .btn-outline-danger:hover {
    background: #dc3545;
    color: #fff;
  }  
</style>
<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center justify-content-between px-3 shadow-sm" style="background: #fff;">

  <!-- Left Side: Logo + System Name -->
  <div class="d-flex align-items-center">
    <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
      <img src="../assets/img/logo.png" alt="Logo" style="height:40px;">
      <span class="ms-2 fw-semibold text-dark"><?php echo $rowconfig['systemname']; ?></span>
    </a>
  </div>

  <!-- Right Side: Profile + Logout -->
  <div class="d-flex align-items-center">
    <div class="d-flex align-items-center me-3">
      <img src="../assets/img/profile.png" alt="Profile" class="rounded-circle me-2" style="width:38px; height:38px;">
      <span class="fw-medium text-dark"><?php echo ucfirst($_SESSION['username']); ?></span>
    </div>

    <a href="../logout.php" class="btn btn-sm btn-outline-danger px-3">
      <i class="bi bi-box-arrow-right me-1"></i> Logout
    </a>
  </div>

</header>
