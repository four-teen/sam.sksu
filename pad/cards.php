<div class="row g-2">

  <!-- Incoming/Outgoing -->
  <div class="col-lg-3 col-md-6 d-flex">
    <div class="card info-card border-0 shadow-sm flex-fill" style="--start-color:#198754;--end-color:#20c997;" onclick="card_one()">
      <div class="card-body">
        <h5 class="card-title">Incoming/Outgoing <span class="text-muted">| Documents</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class='bx bx-archive-out'></i>
          </div>
          <div>
            <h3 id="load_outgoing_count" class="mb-0">0</h3>
            <small class="text-muted">Need Actions</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RECEIVED -->
  <div class="col-lg-3 col-md-6 d-flex">
    <div class="card info-card border-0 shadow-sm flex-fill" style="--start-color:#dc3545;--end-color:#fd7e14;" onclick="card_two()">
      <div class="card-body">
        <h5 class="card-title">RECEIVED <span class="text-muted">| Documents</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bi bi-bootstrap-reboot"></i>
          </div>
          <div>
            <h3 id="load_received_count" class="mb-0">0</h3>
            <small class="text-muted">Need Actions</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Records Section -->
  <div class="col-lg-3 col-md-6 d-flex">
    <div class="card info-card border-0 shadow-sm flex-fill" style="--start-color:#007bff;--end-color:#17a2b8;" onclick="card_three()">
      <div class="card-body">
        <h5 class="card-title">Records Section <span class="text-muted">| List</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bx bx-file"></i>
          </div>
          <div>
            <h3 id="load_doc_count" class="mb-0">0</h3>
            <small class="text-muted">Processed</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Released Summary -->
  <div class="col-lg-3 col-md-6 d-flex">
    <div class="card info-card border-0 shadow-sm flex-fill" style="--start-color:#ffc107;--end-color:#ffb347;" onclick="documents_summary()">
      <div class="card-body">
        <h5 class="card-title">Summary <span class="text-muted">| Released</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bx bxs-objects-vertical-top"></i>
          </div>
          <div>
            <h3 id="load_summary" class="mb-0">0</h3>
            <small class="text-muted">Transactions this period</small>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>