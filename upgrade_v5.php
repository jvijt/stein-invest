<?php
require __DIR__.'/lib.php';
require_login();
$error='';$done=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    // Bij upgrade vanaf v4 bestaat de rol nog niet in de sessie/database.
    // Daarom hier alleen login vereisen; na de migratie wordt de huidige gebruiker Admin.
    require_login();
    verify_csrf();
    try{
        $pdo=db();
        $cols=$pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
        if(!$cols){
            $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin','guest') NOT NULL DEFAULT 'guest' AFTER password_hash");
            $me=current_user();
            if($me){
                $st=$pdo->prepare("UPDATE users SET role='admin' WHERE id=?");
                $st->execute([$me['id']]);
            }
        } else {
            $me=current_user();
            if($me){
                $st=$pdo->prepare("UPDATE users SET role='admin' WHERE id=?");
                $st->execute([$me['id']]);
            }
        }
        start_session(); $_SESSION['role']='admin';
        $done=true;
    }catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Upgrade v5</title>
<style>body{font-family:system-ui;background:#071019;color:#fff;padding:30px}.box{max-width:650px;margin:auto;background:#0d1824;padding:24px;border-radius:16px;border:1px solid #203548}button{padding:12px 16px;background:#78a9ff;border:0;border-radius:9px;font-weight:800}.ok{color:#31d07f}.err{color:#ff626d}.small{color:#91a7ba}</style></head><body><div class="box"><h1>Upgrade naar v5</h1>
<?php if($done):?><p class="ok">Upgrade voltooid. Je huidige account is Admin.</p><p class="small">Verwijder upgrade_v5.php nu via FTP.</p><p><a style="color:#78a9ff" href="index.php">Terug naar dashboard</a></p>
<?php else:?><p>V5 voegt gebruikersrollen toe: Guest en Admin.</p><?php if($error):?><p class="err"><?=h($error)?></p><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button>Upgrade uitvoeren</button></form><?php endif;?></div></body></html>