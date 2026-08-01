<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>419 — Session Expired</title>
<style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f6f7f9;color:#333}.box{text-align:center;padding:2rem}h1{font-size:4rem;margin:0;color:#fd7e14}a{color:#0d6efd}</style></head>
<body><div class="box"><h1>419</h1><p><?= e($error_message ?? 'Your session expired. Please go back, refresh and try again.') ?></p><a href="javascript:history.back()">← Go back</a></div></body></html>
