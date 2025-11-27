<!-- Improved Dashboard Cards -->
<div class="row g-2">

  <!-- Manage Document Types -->
  <div class="col-lg-3 col-md-6">
    <a href="index.php" class="text-decoration-none">
      <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;">
        <div class="card-body">
          <h5 class="card-title">All Documents <span class="text-muted">| List</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon">
              <i class="bx bx-book"></i>
            </div>
            <div>
              <h3 id="all_doc_counts" class="mb-0">0</h3>
              <small class="text-muted">available document types</small>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Manage Request -->
  <div class="col-lg-3 col-md-6">
    <a href="manage_user_account.php" class="text-decoration-none">
      <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;">
        <div class="card-body">
          <h5 class="card-title">Manage Account <span class="text-muted">| All</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon">
              <i class="bx bx-file"></i>
            </div>
            <div>
              <h3 id="get_count" class="mb-0">0</h3>
              <small class="text-muted">pending / processed</small>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Released Summary -->
  <div class="col-lg-3 col-md-6">
    <a href="#" class="text-decoration-none" onclick="released_summary()">
      <div class="card info-card border-0 shadow-sm" style="--start-color:#ffc107;--end-color:#ffb347;">
        <div class="card-body">
          <h5 class="card-title">Summary <span class="text-muted">| Released</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon">
              <i class="bx bxs-objects-vertical-top"></i>
            </div>
            <div>
              <h3 id="load_summary" class="mb-0">0</h3>
              <small class="text-muted">transactions this period</small>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <!-- Statistics -->
  <div class="col-lg-3 col-md-6">
    <a href="#" class="text-decoration-none" onclick="statistics_summary()">
      <div class="card info-card border-0 shadow-sm" style="--start-color:#dc3545;--end-color:#fd7e14;">
        <div class="card-body">
          <h5 class="card-title">Statistics <span class="text-muted">| Documents</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon">
              <i class="bx bx-bar-chart-alt-2"></i>
            </div>
            <div>
              <h3 id="load_statistics" class="mb-0">0</h3>
              <small class="text-muted">total transactions</small>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

</div>
