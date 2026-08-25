<?php
require __DIR__.'/lib.php';
app_config(); start_session();
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $st=db()->prepare("SELECT * FROM users WHERE username=?");
    $st->execute([trim($_POST['username']??'')]); $u=$st->fetch();
    if ($u && password_verify((string)($_POST['password']??''),$u['password_hash'])) {
        session_regenerate_id(true); $_SESSION['user_id']=$u['id']; $_SESSION['username']=$u['username']; $_SESSION['role']=$u['role'] ?? 'guest';
        header('Location: index.php'); exit;
    }
    $error='Onjuiste gebruikersnaam of wachtwoord.';
}
?><!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title>
<style>body{font-family:system-ui;background:#071019;color:#fff;display:grid;place-items:center;min-height:100vh;margin:0}.box{width:min(420px,90vw);background:#0d1824;border:1px solid #203548;border-radius:16px;padding:24px}input{width:100%;box-sizing:border-box;padding:12px;margin:6px 0 14px;background:#08131e;border:1px solid #29425a;border-radius:9px;color:white}button{width:100%;padding:12px;background:#78a9ff;border:0;border-radius:9px;font-weight:800}.err{color:#ff7a83}</style></head>
<body><div class="box"><h1>Stein Invest Dashboard</h1><?php if(isset($_GET['installed'])):?><p style="color:#31d07f">Installatie voltooid.</p><?php endif;?><?php if($error):?><p class="err"><?=h($error)?></p><?php endif;?>
<form method="post"><label>Gebruikersnaam</label><input name="username" autocomplete="username" required><label>Wachtwoord</label><input name="password" type="password" autocomplete="current-password" required><button>Inloggen</button></form></div></body></html>
