<?php
/**
 * Data consistency guard and narrow Ahold repair.
 *
 * IMPORTANT: purchases and sales are transaction history. The legacy
 * set_position endpoint deletes that history before inserting one synthetic
 * position. Until that editor is redesigned, block that destructive action.
 * New purchases/sales must be entered through + Transactie, which appends
 * transactions and therefore preserves weighted average cost correctly.
 */
if (defined('STEIN_BOOTSTRAP_REPAIR')) return;
define('STEIN_BOOTSTRAP_REPAIR', true);

// Prevent the legacy position editor from silently deleting purchase history.
if (($_GET['action'] ?? '') === 'set_position' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    http_response_code(409);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Positiecorrectie is geblokkeerd om de aankoopgeschiedenis te beschermen. Gebruik + Transactie voor een aankoop of verkoop.'
    ]);
    exit;
}

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

    // Narrow repair for the Ahold Delhaize position damaged by the old editor.
    $assets = $pdo->query("SELECT a.id,a.ticker,a.name,(SELECT COUNT(*) FROM transactions t WHERE t.asset_id=a.id) tx_count
        FROM assets a
        WHERE UPPER(a.ticker) IN ('AD.AS','AH.AS') OR UPPER(a.name) LIKE '%AHOLD%DELHAIZE%'
        ORDER BY (UPPER(a.ticker)='AD.AS') DESC, tx_count DESC, a.id ASC")->fetchAll();
    if (!$assets) return;

    $canonicalId = (int)$assets[0]['id'];
    $pdo->beginTransaction();
    $move = $pdo->prepare('UPDATE transactions SET asset_id=? WHERE asset_id=?');
    $deleteAsset = $pdo->prepare('DELETE FROM assets WHERE id=?');
    foreach (array_slice($assets, 1) as $duplicate) {
        $duplicateId = (int)$duplicate['id'];
        if ($duplicateId === $canonicalId) continue;
        $move->execute([$canonicalId, $duplicateId]);
        $deleteAsset->execute([$duplicateId]);
    }
    $pdo->prepare("UPDATE assets SET ticker='AD.AS',currency='EUR' WHERE id=?")->execute([$canonicalId]);

    $sum = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type='BUY' THEN quantity ELSE -quantity END),0) FROM transactions WHERE asset_id=?");
    $sum->execute([$canonicalId]);
    $qty = (float)$sum->fetchColumn();
    if (abs($qty - 80.0) > 0.000001) {
        $pdo->prepare('DELETE FROM transactions WHERE asset_id=?')->execute([$canonicalId]);
        $ins = $pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform,note) VALUES(?,?,'BUY',?,?,1.0,?,?)");
        $ins->execute([$canonicalId,'2026-09-06',60,30.510,'ING','Hersteld: oorspronkelijke aankoop Ahold Delhaize']);
        $ins->execute([$canonicalId,'2026-09-15',20,31.76,'ING','Hersteld: bijkoop Ahold Delhaize']);
    }
    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
}
