<?php
require __DIR__.'/lib.php';
require_login();
$error='';$done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    try{
        $pdo=db();
        $cols=$pdo->query("SHOW COLUMNS FROM assets LIKE 'sector'")->fetchAll();
        if(!$cols)$pdo->exec("ALTER TABLE assets ADD COLUMN sector VARCHAR(80) NOT NULL DEFAULT 'Overig' AFTER exchange_name");
        $map=[
          'ABI.BR'=>'Consumentengoederen',
          'ASML.AS'=>'Technologie',
          'BAR.BR'=>'Technologie',
          'NVDA'=>'Technologie',
          'VUAA.DE'=>'Brede ETF'
        ];
        $st=$pdo->prepare("UPDATE assets SET sector=? WHERE ticker=? AND (sector='Overig' OR sector='')");
        foreach($map as $ticker=>$sector)$st->execute([$sector,$ticker]);
        $done=true;
    }catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Upgrade v3</title>
<style>body{font-family:system-ui;background:#071019;color:#fff;padding:30px}.box{max-width:600px;margin:auto;background:#0d1824;padding:24px;border-radius:16px;border:1px solid #203548}button{padding:12px 16px;background:#78a9ff;border:0;border-radius:9px;font-weight:800}.ok{color:#31d07f}.err{color:#ff626d}</style></head><body><div class="box"><h1>Upgrade naar v3</h1>
<?php if($done):?><p class="ok">Upgrade voltooid. Je kunt dit bestand nu verwijderen.</p><p><a style="color:#78a9ff" href="index.php">Terug naar dashboard</a></p>
<?php else:?><p>Voegt sectoren en ondersteuning voor directe positiecorrecties toe.</p><?php if($error):?><p class="err"><?=h($error)?></p><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button>Upgrade uitvoeren</button></form><?php endif;?></div></body></html>