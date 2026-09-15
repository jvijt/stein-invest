<?php
/**
 * Narrow one-time repair for the Ahold Delhaize position.
 *
 * The position editor previously replaced all transactions on a platform.
 * Restore the two confirmed purchases while preserving their individual prices:
 * - 2026-09-06: 60 @ EUR 30.510
 * - 2026-09-15: 20 @ EUR 31.76
 */
if (defined('STEIN_BOOTSTRAP_REPAIR')) return;
define('STEIN_BOOTSTRAP_REPAIR', true);

try {
    $configFile = __DIR__ . '/config.php';
    if (!is_file($configFile)) return;
    $cfg = require $configFile;
    if (!is_array($cfg) || empty($cfg['db_host']) || empty($cfg['db_name']) || empty($cfg['db_user'])) return;

    $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $assets = $pdo->query("SELECT a.id,a.ticker,a.name,(SELECT COUNT(*) FROM transactions t WHERE t.asset_id=a.id) tx_count
        FROM assets a
        WHERE UPPER(a.ticker) IN ('AD.AS','AH.AS') OR UPPER(a.name) LIKE '%AHOLD%DELHAIZE%'
        ORDER BY (UPPER(a.ticker)='AD.AS') DESC, tx_count DESC, a.id ASC")->fetchAll();
    if (!$assets) return;

    $canonicalId = (int)$assets[0]['id'];
    $pdo->beginTransaction();

    // Consolidate any duplicate Ahold asset records first.
    $move = $pdo->prepare('UPDATE transactions SET asset_id=? WHERE asset_id=?');
    $deleteAsset = $pdo->prepare('DELETE FROM assets WHERE id=?');
    foreach (array_slice($assets, 1) as $duplicate) {
        $duplicateId = (int)$duplicate['id'];
        if ($duplicateId === $canonicalId) continue;
        $move->execute([$canonicalId, $duplicateId]);
        $deleteAsset->execute([$duplicateId]);
    }
    $pdo->prepare("UPDATE assets SET ticker='AD.AS',currency='EUR' WHERE id=?")->execute([$canonicalId]);

    // Only run the purchase-history restoration until the confirmed 80-share total exists.
    $sum = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type='BUY' THEN quantity ELSE -quantity END),0) FROM transactions WHERE asset_id=?");
    $sum->execute([$canonicalId]);
    $qty = (float)$sum->fetchColumn();

    if (abs($qty - 80.0) > 0.000001) {
        // The damaged position currently contains the replacement transaction(s).
        // Rebuild Ahold only; no other security is touched.
        $pdo->prepare('DELETE FROM transactions WHERE asset_id=?')->execute([$canonicalId]);
        $ins = $pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform,note) VALUES(?,?,'BUY',?, ?,1.0,?,?)");
        $ins->execute([$canonicalId,'2026-09-06',60,30.510,'ING','Hersteld: oorspronkelijke aankoop Ahold Delhaize']);
        $ins->execute([$canonicalId,'2026-09-15',20,31.76,'ING','Hersteld: bijkoop Ahold Delhaize']);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
}
