<?php
declare(strict_types=1);

$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    echo "<h2>Installatie is al voltooid.</h2><p><a href='login.php'>Ga naar login</a></p>";
    exit;
}
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $host=trim($_POST['db_host']??'localhost');
    $name=trim($_POST['db_name']??'');
    $user=trim($_POST['db_user']??'');
    $pass=(string)($_POST['db_pass']??'');
    $admin=trim($_POST['admin_user']??'admin');
    $adminPass=(string)($_POST['admin_pass']??'');
    try {
        if (!$name || !$user || strlen($adminPass)<8) throw new Exception('Vul alle velden in; beheerderswachtwoord minstens 8 tekens.');
        $pdo=new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4",$user,$pass,[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
          id INT AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(100) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,
          role ENUM('admin','guest') NOT NULL DEFAULT 'guest',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS assets (
          id INT AUTO_INCREMENT PRIMARY KEY,
          ticker VARCHAR(50) NOT NULL UNIQUE,
          name VARCHAR(200) NOT NULL,
          currency VARCHAR(10) DEFAULT NULL,
          exchange_name VARCHAR(100) DEFAULT NULL,
          sector VARCHAR(80) NOT NULL DEFAULT 'Overig',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        CREATE TABLE IF NOT EXISTS transactions (
          id INT AUTO_INCREMENT PRIMARY KEY,
          asset_id INT NOT NULL,
          trade_date DATE NOT NULL,
          type ENUM('BUY','SELL') NOT NULL DEFAULT 'BUY',
          quantity DECIMAL(18,6) NOT NULL,
          price_native DECIMAL(18,6) NOT NULL,
          fx_to_eur DECIMAL(18,10) NOT NULL DEFAULT 1,
          platform ENUM('ING','Saxo') NOT NULL,
          note VARCHAR(255) DEFAULT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT fk_tx_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $st=$pdo->prepare("INSERT INTO users(username,password_hash,role) VALUES(?,?, 'admin')");
        $st->execute([$admin,password_hash($adminPass,PASSWORD_DEFAULT)]);

        $seedAssets = [
          ['ABI.BR','Anheuser-Busch InBev','EUR','Brussels','Consumentengoederen'],
          ['ASML.AS','ASML Holding','EUR','Amsterdam','Technologie'],
          ['BAR.BR','Barco','EUR','Brussels','Technologie'],
          ['NVDA','NVIDIA','USD','NASDAQ','Technologie'],
          ['VUAA.DE','Vanguard S&P 500 UCITS ETF Acc','EUR','XETRA','Brede ETF'],
        ];
        $a=$pdo->prepare("INSERT IGNORE INTO assets(ticker,name,currency,exchange_name,sector) VALUES(?,?,?,?,?)");
        foreach($seedAssets as $x)$a->execute($x);

        // Seed op basis van de reeds bekende ING screenshot; datum is placeholder.
        $seedTx=[
          ['ABI.BR','2026-08-24',1,77.88,1.0,'ING'],
          ['ASML.AS','2026-08-24',1,1417.92,1.0,'ING'],
          ['BAR.BR','2026-08-24',160,8.392625,1.0,'ING'],
          ['NVDA','2026-08-24',17,177.34,0.86,'ING'],
          ['VUAA.DE','2026-08-24',9,131.4978,1.0,'ING'],
        ];
        $q=$pdo->prepare("SELECT id FROM assets WHERE ticker=?");
        $t=$pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform,note) VALUES(?,?,'BUY',?,?,?,?,?)");
        foreach($seedTx as $x){
            $q->execute([$x[0]]);$id=$q->fetchColumn();
            $t->execute([$id,$x[1],$x[2],$x[3],$x[4],$x[5],'Vooraf ingevuld; controleer aankoopdatum en prijs.']);
        }

        $cfg="<?php\nreturn ".var_export([
            'db_host'=>$host,'db_name'=>$name,'db_user'=>$user,'db_pass'=>$pass,
            'app_name'=>'Stein Invest Dashboard','timezone'=>'Europe/Brussels'
        ],true).";\n";
        if (@file_put_contents($configPath,$cfg)===false) throw new Exception('Kan config.php niet schrijven. Geef de map tijdelijk schrijfrechten of maak config.php handmatig.');
        @chmod($configPath,0640);
        header('Location: login.php?installed=1');exit;
    } catch(Throwable $e){$error=$e->getMessage();}
}
?>
<!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installatie Stein Invest Dashboard</title>
<style>body{font-family:system-ui;background:#071019;color:#f3f7fb;margin:0;padding:28px}.box{max-width:620px;margin:auto;background:#0d1824;border:1px solid #203548;padding:24px;border-radius:16px}label{display:block;margin-top:14px;color:#9fb0c2;font-size:13px}input{width:100%;padding:11px;border-radius:9px;border:1px solid #284058;background:#08131e;color:white;box-sizing:border-box}button{margin-top:20px;padding:12px 16px;border:0;border-radius:9px;background:#78a9ff;font-weight:700}.err{background:#4b171c;padding:12px;border-radius:9px}</style></head>
<body><div class="box"><h1>Stein Invest Dashboard installeren</h1><p>De gegevens blijven op je eigen hosting. Gebruik een lege MySQL/MariaDB database.</p>
<?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?>
<form method="post">
<label>Database host</label><input name="db_host" value="<?=htmlspecialchars($_POST['db_host']??'localhost')?>" required>
<label>Database naam</label><input name="db_name" required>
<label>Database gebruiker</label><input name="db_user" required>
<label>Database wachtwoord</label><input name="db_pass" type="password">
<label>Beheerder gebruikersnaam</label><input name="admin_user" value="admin" required>
<label>Beheerder wachtwoord (min. 8 tekens)</label><input name="admin_pass" type="password" required minlength="8">
<button>Installeren</button>
</form></div></body></html>
