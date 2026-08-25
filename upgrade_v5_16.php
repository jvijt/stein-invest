<?php
require __DIR__.'/lib.php'; require_login(); $done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){require_admin();verify_csrf();$done=true;}
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Stein Invest Dashboard — Upgrade v5.16</title>
<style>body{font-family:system-ui;background:#071019;color:#fff;padding:30px}.box{max-width:650px;margin:auto;background:#0d1824;padding:24px;border-radius:16px;border:1px solid #203548}button{padding:12px 16px;background:#78a9ff;border:0;border-radius:9px;font-weight:800}.ok{color:#31d07f}.small{color:#91a7ba}a{color:#78a9ff}</style></head><body><div class="box"><h1>Stein Invest Dashboard</h1><h2>Upgrade v5.16</h2>
<?php if($done):?><p class="ok">Upgrade voltooid.</p><p class="small">De naamgeving is aangepast naar Stein Invest Dashboard. Verwijder upgrade_v5_16.php via FTP.</p><p><a href="index.php">Terug naar dashboard</a></p>
<?php else:?><p>Deze update past de project- en dashboardnaam aan.</p><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button>Upgrade uitvoeren</button></form><?php endif;?></div></body></html>