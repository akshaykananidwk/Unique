<?php
$branches = ins_data('branches') ?: [
    ['name' => 'Shop 1', 'code' => 'S1', 'type' => 'shop', 'address' => '', 'phone' => ''],
    ['name' => 'Shop 2', 'code' => 'S2', 'type' => 'shop', 'address' => '', 'phone' => ''],
    ['name' => 'Godown / Production', 'code' => 'GD', 'type' => 'godown', 'address' => '', 'phone' => ''],
];
?>
<h5>Step 5 — Branches</h5>
<p class="text-muted small">The branch <strong>code</strong> appears in job numbers (e.g. JOB-<strong>S1</strong>-2608-0042) and cannot be changed later.</p>
<form method="post">
  <input type="hidden" name="_csrf" value="<?= ins_csrf() ?>">
  <input type="hidden" name="step" value="5">
  <div id="branchRows">
    <?php foreach ($branches as $b): ?>
    <div class="row g-2 mb-2 branch-row">
      <div class="col-md-3"><input name="branch_name[]" class="form-control" placeholder="Branch name" value="<?= ins_e($b['name']) ?>"></div>
      <div class="col-md-1"><input name="branch_code[]" class="form-control" placeholder="Code" maxlength="10" value="<?= ins_e($b['code']) ?>"></div>
      <div class="col-md-2"><select name="branch_type[]" class="form-select">
        <?php foreach (['shop' => 'Shop', 'godown' => 'Godown', 'office' => 'Office'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= $b['type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?></select></div>
      <div class="col-md-3"><input name="branch_address[]" class="form-control" placeholder="Address" value="<?= ins_e($b['address']) ?>"></div>
      <div class="col-md-2"><input name="branch_phone[]" class="form-control" placeholder="Phone" value="<?= ins_e($b['phone']) ?>"></div>
      <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 rm-row">×</button></div>
    </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-outline-secondary btn-sm" id="addRow">+ Add branch</button>
  <div class="d-flex justify-content-between mt-3">
    <a class="btn btn-outline-secondary" href="?step=4">← Back</a>
    <button class="btn btn-primary">Continue →</button>
  </div>
</form>
<script>
document.getElementById('addRow').addEventListener('click', function () {
  const first = document.querySelector('.branch-row');
  const row = first.cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  document.getElementById('branchRows').appendChild(row);
});
document.getElementById('branchRows').addEventListener('click', function (e) {
  if (e.target.classList.contains('rm-row') && document.querySelectorAll('.branch-row').length > 1) {
    e.target.closest('.branch-row').remove();
  }
});
</script>
