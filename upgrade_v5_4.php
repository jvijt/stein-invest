<?php
require __DIR__.'/lib.php'; require_login(); $error='';$done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){require_admin();verify_csrf();try{
db()->exec("CREATE TABLE IF NOT EXISTS sectors (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL UNIQUE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$done=true;}catch(Throwable $e){$error=$e->getMessage();}}
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Upgrade v5.4</title>
<style>body{font-family:system-ui;background:#071019;color:#fff;padding:30px}.box{max-width:650px;margin:auto;background:#0d1824;padding:24px;border-radius:16px;border:1px solid #203548}button{padding:12px 16px;background:#78a9ff;border:0;border-radius:9px;font-weight:800}.ok{color:#31d07f}.err{color:#ff626d}.small{color:#91a7ba}</style></head><body><div class="box"><h1>Upgrade naar v5.4</h1>
<?php if($done):?><p class="ok">Upgrade voltooid.</p><p class="small">Eigen sectoren en sectoradvies zijn nu actief. Verwijder upgrade_v5_4.php via FTP.</p><p><a style="color:#78a9ff" href="index.php">Terug naar dashboard</a></p>
<?php else:?><p>V5.4 voegt eigen sectorcategorieën en automatische sectorvoorstellen toe.</p><?php if($error):?><p class="err"><?=h($error)?></p><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button>Upgrade uitvoeren</button></form><?php endif;?></div></body></html>