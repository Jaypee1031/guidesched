<?php
// Shared Head component
$baseUrl = isset($base_url_path) ? $base_url_path : '../';
if (!isset($page_title)) {
    $page_title = 'GuideSched — Guidance Counseling System';
}
?>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($page_title); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="<?php echo $baseUrl; ?>assets/js/chart.umd.min.js"></script>
<script>
if (typeof Chart === 'undefined') {
  document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"><\/script>');
}
</script>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css">
