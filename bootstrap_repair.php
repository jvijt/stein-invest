<?php
/**
 * Lightweight data consistency repair loaded before PHP requests.
 *
 * Historical Ahold Delhaize records may exist under the former Yahoo ticker
 * AH.AS while new purchases use AD.AS. The dashboard aggregates by asset_id,
 * so those records would otherwise appear as separate positions.
 *
 * This repair is deliberately narrow: it only consolidates Ahold Delhaize
 * aliases. Once consolidated, subsequent requests are a no-op.
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

    $sql = "SELECT a.id,a.ticker,a.name,
                   (SELECT COUNT(*) FROM transactions t WHERE t.asset_id=a.id) tx_count
            FROM assets a
            WHERE UPPER(a.ticker) IN ('AD.AS','AH.AS')
               OR UPPER(a.name) LIKE '%AHOLD%DELHAIZE%'
            ORDER BY (UPPER(a.ticker)='AD.AS') DESC, tx_count DESC, a.id ASC";
    $assets = $pdo->query($sql)->fetchAll();
    if (count($assets) < 2) return;

    $canonical = $assets[0];
    $canonicalId = (int)$canonical['id'];

    $pdo->beginTransaction();
    $move = $pdo->prepare('UPDATE transactions SET asset_id=? WHERE asset_id=?');
    $delete = $pdo->prepare('DELETE FROM assets WHERE id=?');

    foreach (array_slice($assets, 1) as $duplicate) {
        $duplicateId = (int)$duplicate['id'];
        if ($duplicateId === $canonicalId) continue;
        $move->execute([$canonicalId, $duplicateId]);
        $delete->execute([$duplicateId]);
    }

    // Keep the current Yahoo Finance ticker for Ahold Delhaize.
    $check = $pdo->prepare("SELECT id FROM assets WHERE UPPER(ticker)='AD.AS' AND id<>? LIMIT 1");
    $check->execute([$canonicalId]);
    if (!$check->fetchColumn()) {
        $upd = $pdo->prepare("UPDATE assets SET ticker='AD.AS' WHERE id=?");
        $upd->execute([$canonicalId]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    // Never interrupt the dashboard because of a maintenance repair.
}
