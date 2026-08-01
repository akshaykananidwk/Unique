<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Print') ?></title>
<link rel="stylesheet" href="<?= e(asset_url('vendor/bootstrap/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(asset_url('css/print.css')) ?>">
</head>
<body class="print-body">
<div class="no-print p-2 text-center border-bottom mb-2">
  <button class="btn btn-primary btn-sm" onclick="window.print()">🖨️ Print</button>
  <a class="btn btn-outline-secondary btn-sm" href="javascript:history.back()">Back</a>
</div>
<?= $content ?>
</body>
</html>
