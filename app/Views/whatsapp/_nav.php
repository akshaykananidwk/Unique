<?php
use App\Core\Acl;

/** Sub-navigation shown on every WhatsApp page. $waActive = settings|templates|logs|inbound */
$tabs = [
    'settings' => ['whatsapp.settings', 'whatsapp/settings', 'gear', 'Settings'],
    'templates' => ['whatsapp.templates', 'whatsapp/templates', 'chat-square-text', 'Templates'],
    'logs' => ['whatsapp.logs', 'whatsapp/logs', 'list-ul', 'Logs'],
    'inbound' => ['whatsapp.logs', 'whatsapp/inbound', 'inbox', 'Inbound'],
];
$waActive = $waActive ?? '';
?>
<ul class="nav nav-pills mb-3 flex-wrap gap-1">
  <?php foreach ($tabs as $key => [$perm, $path, $icon, $label]): if (!Acl::can($perm)) continue; ?>
    <li class="nav-item">
      <a class="nav-link <?= $waActive === $key ? 'active' : '' ?>" href="<?= e(admin_url($path)) ?>">
        <i class="bi bi-<?= e($icon) ?>"></i> <?= e($label) ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
