<?php
$googleAnalyticsId = strtoupper(trim((string) ($googleAnalyticsId ?? '')));

if ($googleAnalyticsId === '') {
  return;
}

// If only a legacy account number is provided, default to property index 1.
if (preg_match('/^\d+$/', $googleAnalyticsId)) {
  $googleAnalyticsId = 'UA-' . $googleAnalyticsId . '-1';
}

if (!preg_match('/^(G-[A-Z0-9]+|UA-\d+-\d+)$/', $googleAnalyticsId)) {
  return;
}
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($googleAnalyticsId, 'attr') ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= esc($googleAnalyticsId, 'js') ?>');
</script>

