

<div class="row g-2">

  <!-- Manage Request -->
  <div class="col-lg-4 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;"  onclick="card_one()">
      <div class="card-body">
        <h5 class="card-title">Received <span class="text-muted">| records</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bx bx-file"></i>
          </div>
          <div>
            <h3 id="load_new_received_count" class="mb-0">0</h3>
            <small class="text-muted">document</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- OUTGOING -->
  <div class="col-lg-4 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="card_two()">
      <div class="card-body">
        <h5 class="card-title">Received <span class="text-muted">| Documents</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class='bx bx-archive-out'></i>
          </div>
          <div>
            <h3 id="load_received_count" class="mb-0">0</h3>
            <small class="text-muted">Need Actions</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Returned -->
<div class="col-lg-4 col-md-6">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#dc3545;--end-color:#ff4136;" onclick="card_three()">
    <div class="card-body">
      <h5 class="card-title">All Documents <span class="text-muted">Monitoring</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon">
          <i class="bi bi-alarm"></i>
        </div>
        <div>
          <h3 id="load_returned_count" class="mb-0">0</h3>
          <small class="text-muted">records</small>
        </div>
      </div>
    </div>
  </div>
</div>

   

</div>