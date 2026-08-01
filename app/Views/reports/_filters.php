<?php /** Shared report filter bar. Expects: $branches, $selectedBranch; optional $extraFilters html. */ ?>
<form method="get" class="row g-2 mb-3 no-print">
  <div class="col-6 col-md-2">
    <select name="range" class="form-select form-select-sm" onchange="document.getElementById('customRange').style.display = this.value==='custom' ? '' : 'none'">
      <?php $ranges = ['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month',
                       'last_month' => 'Last Month', 'this_year' => 'This Year', 'custom' => 'Custom'];
      foreach ($ranges as $v => $l): ?>
        <option value="<?= $v ?>" <?= ($_GET['range'] ?? 'this_month') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-4" id="customRange" style="<?= ($_GET['range'] ?? '') === 'custom' ? '' : 'display:none' ?>">
    <div class="input-group input-group-sm">
      <input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>" class="form-control">
      <span class="input-group-text">to</span>
      <input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>" class="form-control">
    </div>
  </div>
  <?php if (isset($branches) && count($branches) > 1): ?>
  <div class="col-6 col-md-2">
    <select name="branch_id" class="form-select form-select-sm">
      <option value="">All branches</option>
      <?php foreach ($branches as $b): ?>
        <option value="<?= (int)$b['id'] ?>" <?= ($selectedBranch ?? null) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?= $extraFilters ?? '' ?>
  <div class="col-6 col-md-3 d-flex gap-1">
    <button class="btn btn-outline-primary btn-sm">Apply</button>
    <button class="btn btn-outline-secondary btn-sm" name="export" value="csv"><i class="bi bi-filetype-csv"></i> CSV</button>
    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Print / PDF</button>
  </div>
</form>
