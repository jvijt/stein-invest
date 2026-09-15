<?php
require __DIR__.'/lib.php';
require_login(true);
require_admin(true);

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = db();
    $assets = $pdo->query("SELECT a.id,a.ticker,a.name FROM assets a WHERE UPPER(a.ticker) IN ('AD.AS','AH.AS') OR UPPER(a.name) LIKE '%AHOLD%DELHAIZE%' ORDER BY (UPPER(a.ticker)='AD.AS') DESC,a.id ASC")->fetchAll();
    if (!$assets) throw new Exception('Ahold Delhaize werd niet gevonden.');

    $canonicalId = (int)$assets[0]['id'];
    $pdo->beginTransaction();
    $move = $pdo->prepare('UPDATE transactions SET asset_id=? WHERE asset_id=?');
    $deleteAsset = $pdo->prepare('DELETE FROM assets WHERE id=?');
    foreach (array_slice($assets,1) as $a) {
        $id=(int)$a['id'];
        if ($id===$canonicalId) continue;
        $move->execute([$canonicalId,$id]);
        $deleteAsset->execute([$id]);
    }

    $pdo->prepare("UPDATE assets SET ticker='AD.AS',currency='EUR' WHERE id=?")->execute([$canonicalId]);
    $pdo->prepare("DELETE FROM transactions WHERE asset_id=?")->execute([$canonicalId]);
    $ins=$pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform,note) VALUES(?,?,'BUY',?,?,1.0,'ING',?)");
    $ins->execute([$canonicalId,'2026-09-06',60,30.510,'Hersteld: aankoop 6 september 2026']);
    $ins->execute([$canonicalId,'2026-09-15',20,31.76,'Hersteld: bijkoop 15 september 2026']);
    $pdo->commit();

    echo '<!doctype html><meta charset="utf-8"><title>Ahold hersteld</title><style>body{font:16px system-ui;background:#071521;color:#fff;padding:40px}a{color:#72a7ff}</style><h1>Ahold Delhaize hersteld</h1><p>De positie bevat nu 80 aandelen: 60 × €30,510 en 20 × €31,76.</p><p><a href="./">Terug naar dashboard</a></p>';
} catch(Throwable $e) {
    if(isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo 'Herstel mislukt: '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');
}
