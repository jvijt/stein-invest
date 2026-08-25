<?php
require __DIR__.'/lib.php';
require_login(true);
header('Content-Type: application/json; charset=utf-8');

$action=$_GET['action']??'dashboard';

try {

if($action==='asset_search'){
    $query=trim($_GET['q']??'');
    if(mb_strlen($query)<2){ echo json_encode(['ok'=>true,'results'=>[]]); exit; }
    $results=yahoo_search($query,12);
    echo json_encode(['ok'=>true,'results'=>$results]);exit;
}
if($action==='asset_validate'){
    $ticker=strtoupper(trim($_GET['ticker']??''));
    if(!$ticker) throw new Exception('Ticker ontbreekt.');
    $probe=parse_chart($ticker,'5d','1d');
    if(!$probe || empty($probe['price'])) throw new Exception('Dit effect kon niet worden gevalideerd.');
    echo json_encode(['ok'=>true,'asset'=>[
        'ticker'=>$ticker,
        'name'=>$probe['name']??$ticker,
        'currency'=>strtoupper($probe['currency']??'EUR'),
        'exchange'=>$probe['exchange']??'',
        'market_state'=>$probe['market_state']??null
    ]]);exit;
}
if($action==='dashboard'){
    $platform=$_GET['platform']??'ALL';
    $pos=current_positions();
    if($platform!=='ALL') $pos=array_values(array_filter($pos,fn($p)=>$p['platform']===$platform));
    $pos = $platform==='ALL' ? aggregate_positions($pos) : $pos;
    $quotes=[];
    foreach($pos as &$p){
        $q=quote_for_asset($p); $quotes[$p['ticker']]=$q;
        $p['quote']=$q;
        $p['avg_cost_eur']=$p['qty'] ? $p['cost_eur']/$p['qty'] : 0;
        // Gemiddelde aankoopprijs in de gekozen transactievaluta.
        $p['avg_cost_native']=$p['qty'] ? ($p['cost_native']/$p['qty']) : 0;
    }
    echo json_encode(['ok'=>true,'positions'=>$pos,'updated_at'=>date(DATE_ATOM),'csrf'=>csrf_token()]);
    exit;
}


if($action==='sectors'){
    $defaults=['Technologie','Healthcare','Financieel','Industrie','Consumentengoederen','Consumentendiensten','Energie','Materialen','Vastgoed','Telecom','Utilities','Brede ETF','Overig'];
    $custom=[];
    try{$custom=db()->query("SELECT name FROM sectors ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);}catch(Throwable $e){}
    $all=array_values(array_unique(array_merge($defaults,$custom)));
    natcasesort($all);
    echo json_encode(['ok'=>true,'sectors'=>array_values($all)]);exit;
}
if($action==='add_sector'){
    require_admin(true); verify_csrf();
    $name=trim($_POST['name']??'');
    if(mb_strlen($name)<2 || mb_strlen($name)>80) throw new Exception('Sectornaam moet tussen 2 en 80 tekens zijn.');
    db()->exec("CREATE TABLE IF NOT EXISTS sectors (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL UNIQUE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $st=db()->prepare("INSERT IGNORE INTO sectors(name) VALUES(?)");$st->execute([$name]);
    echo json_encode(['ok'=>true,'name'=>$name]);exit;
}
if($action==='sector_suggest'){
    $ticker=strtoupper(trim($_GET['ticker']??''));
    $name=trim($_GET['name']??'');
    if(!$ticker) throw new Exception('Ticker ontbreekt.');
    // Gratis, deterministische aanbeveling. Eerst bekende/specialistische effecten,
    // daarna naam/ticker-keywords. De gebruiker kan altijd overschrijven.
    $text=strtoupper($ticker.' '.$name);
    $sector='Overig'; $reason='Geen duidelijke automatische classificatie gevonden.';
    $semis=['NVDA','AMD','INTC','TSM','ASML','AVGO','QCOM','MU','ARM','AMAT','LRCX','KLAC','MRVL','NXPI','ON','STM','ADI','TXN'];
    $ai=['PLTR','AI','SOUN','BBAI'];
    $base=preg_replace('/\..*$/','',$ticker);
    if(in_array($base,$semis,true)){ $sector='Halfgeleiders'; $reason='Het bedrijf is primair actief in halfgeleiders/chiptechnologie of chipapparatuur.'; }
    elseif(in_array($base,$ai,true)){ $sector='AI'; $reason='Het bedrijf is sterk gespecialiseerd in artificiële intelligentie/software.'; }
    elseif(preg_match('/PHARMA|THERAPEUT|BIOTECH|MEDICAL|HEALTH|NOVO|LILLY|PFIZER|SANOFI/',$text)){ $sector='Healthcare'; $reason='Naam/activiteit wijst op healthcare of life sciences.'; }
    elseif(preg_match('/BANK|FINANC|INSUR|VISA|MASTERCARD/',$text)){ $sector='Financieel'; $reason='Naam/activiteit wijst op financiële dienstverlening.'; }
    elseif(preg_match('/ENERGY|OIL|PETROL|SHELL|EXXON|CHEVRON/',$text)){ $sector='Energie'; $reason='Naam/activiteit wijst op energie.'; }
    elseif(preg_match('/TECH|SOFTWARE|MICROSOFT|APPLE|META|GOOGLE|ALPHABET|ORACLE|SAP/',$text)){ $sector='Technologie'; $reason='Naam/activiteit wijst op technologie/software.'; }
    elseif(preg_match('/ETF|VANGUARD|ISHARES|SPDR|AMUNDI|XTRACKERS/',$text)){ $sector='Brede ETF'; $reason='Het effect lijkt een ETF; controleer of een specifiekere categorie gewenst is.'; }
    echo json_encode(['ok'=>true,'sector'=>$sector,'reason'=>$reason]);exit;
}

if($action==='assets'){
    $rows=db()->query("SELECT id,ticker,name,currency,exchange_name,sector FROM assets ORDER BY name")->fetchAll();
    echo json_encode(['ok'=>true,'assets'=>$rows,'csrf'=>csrf_token()]);exit;
}
if($action==='save_asset'){
    require_admin(true);
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $name=trim($_POST['name']??'');
    $sector=trim($_POST['sector']??'Overig');
    $currency=strtoupper(trim($_POST['currency']??'EUR'));
    if(!$id || !$name) throw new Exception('Naam ontbreekt.');
    if(!in_array($currency,supported_currencies(),true)) throw new Exception('Niet ondersteunde valuta.');
    $st=db()->prepare("UPDATE assets SET name=?,sector=?,currency=? WHERE id=?");
    $st->execute([$name,$sector?:'Overig',$currency,$id]);
    echo json_encode(['ok'=>true]);exit;
}
if($action==='set_position'){
    require_admin(true);
    verify_csrf();
    $assetId=(int)($_POST['asset_id']??0);
    $platform=$_POST['platform']??'ING';
    $sector=trim($_POST['sector']??'Overig');
    $qty=(float)($_POST['quantity']??0);
    $avgNative=(float)($_POST['avg_cost_native']??0);
    $currency=strtoupper(trim($_POST['currency']??'EUR'));
    $date=$_POST['trade_date']??date('Y-m-d');
    if(!$assetId || !in_array($platform,['ING','Saxo'],true) || $qty<0 || $avgNative<0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date))
        throw new Exception('Controleer positie, aantal, gemiddelde aankoopprijs en datum.');
    if(!in_array($currency,supported_currencies(),true)) throw new Exception('Niet ondersteunde valuta.');

    $a=db()->prepare("SELECT * FROM assets WHERE id=?");$a->execute([$assetId]);$asset=$a->fetch();
    if(!$asset) throw new Exception('Aandeel niet gevonden.');
    $fx=historical_fx_to_eur($currency,$date);
    $priceNative = $avgNative;

    $pdo=db();$pdo->beginTransaction();
    $up=$pdo->prepare("UPDATE assets SET currency=? WHERE id=?");$up->execute([$currency,$assetId]);
    $del=$pdo->prepare("DELETE FROM transactions WHERE asset_id=? AND platform=?");
    $del->execute([$assetId,$platform]);
    if($qty>0){
        $ins=$pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform,note) VALUES(?,?,'BUY',?,?,?,?,?)");
        $ins->execute([$assetId,$date,$qty,$priceNative,$fx,$platform,'Handmatige positiecorrectie via beheer']);
    }
    $pdo->commit();
    echo json_encode(['ok'=>true]);exit;
}


if($action==='positions_raw'){
    $pos=current_positions();
    foreach($pos as &$p){
        $p['avg_cost_eur']=$p['qty'] ? $p['cost_eur']/$p['qty'] : 0;
        $p['avg_cost_native']=$p['qty'] ? $p['cost_native']/$p['qty'] : 0;
    }
    echo json_encode(['ok'=>true,'positions'=>$pos,'csrf'=>csrf_token()]);exit;
}

if($action==='transactions'){
    $rows=db()->query("SELECT t.*,a.ticker,a.name,a.currency FROM transactions t JOIN assets a ON a.id=t.asset_id ORDER BY trade_date DESC,t.id DESC")->fetchAll();
    echo json_encode(['ok'=>true,'transactions'=>$rows,'csrf'=>csrf_token()]);exit;
}
if($action==='save_transaction'){
    require_admin(true);
    verify_csrf();
    $ticker=strtoupper(trim($_POST['ticker']??''));
    $name=trim($_POST['name']??$ticker);
    $date=$_POST['trade_date']??'';
    $type=$_POST['type']??'BUY';
    $qty=(float)($_POST['quantity']??0);
    $price=(float)($_POST['price']??0);
    $platform=$_POST['platform']??'ING';
    $sector=trim($_POST['sector']??'Overig');
    $currency=strtoupper(trim($_POST['currency']??'EUR'));
    if(!$ticker||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!in_array($type,['BUY','SELL'],true)||$qty<=0||$price<=0||!in_array($platform,['ING','Saxo'],true))
        throw new Exception('Controleer de ingevoerde gegevens.');
    if(!in_array($currency,supported_currencies(),true)) throw new Exception('Niet ondersteunde valuta.');

    // Server-side controle: alleen een bestaand, door de koersbron herkend effect kan worden opgeslagen.
    $probe=parse_chart($ticker,'5d','1d');
    $exchange=$probe['exchange']??null;
    if(!$probe || empty($probe['price'])) throw new Exception('Het gekozen effect bestaat niet of kon niet worden gevalideerd. Zoek en selecteer het effect opnieuw.');

    $pdo=db(); $pdo->beginTransaction();
    $st=$pdo->prepare("INSERT INTO assets(ticker,name,currency,exchange_name,sector) VALUES(?,?,?,?,?)
      ON DUPLICATE KEY UPDATE name=VALUES(name),currency=VALUES(currency),exchange_name=VALUES(exchange_name),
      sector=IF(sector='Overig' OR sector='',VALUES(sector),sector)");
    $st->execute([$ticker,$name?:($probe['name']??$ticker),$currency,$exchange,$sector?:'Overig']);
    $q=$pdo->prepare("SELECT id FROM assets WHERE ticker=?");$q->execute([$ticker]);$aid=(int)$q->fetchColumn();
    $fx=historical_fx_to_eur($currency,$date);
    $ins=$pdo->prepare("INSERT INTO transactions(asset_id,trade_date,type,quantity,price_native,fx_to_eur,platform) VALUES(?,?,?,?,?,?,?)");
    $ins->execute([$aid,$date,$type,$qty,$price,$fx,$platform]);
    $pdo->commit();
    echo json_encode(['ok'=>true,'fx_to_eur'=>$fx]);exit;
}

if($action==='delete_position'){
    require_admin(true);
    verify_csrf();
    $assetId=(int)($_POST['asset_id']??0);
    $platform=$_POST['platform']??'';
    if(!$assetId || !in_array($platform,['ING','Saxo'],true)) throw new Exception('Ongeldige positie.');
    $st=db()->prepare("DELETE FROM transactions WHERE asset_id=? AND platform=?");
    $st->execute([$assetId,$platform]);
    echo json_encode(['ok'=>true]);exit;
}

if($action==='delete_transaction'){
    require_admin(true);
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $st=db()->prepare("DELETE FROM transactions WHERE id=?");$st->execute([$id]);
    echo json_encode(['ok'=>true]);exit;
}

if($action==='users'){
    require_admin(true);
    $rows=db()->query("SELECT id,username,role,created_at FROM users ORDER BY username")->fetchAll();
    echo json_encode(['ok'=>true,'users'=>$rows,'csrf'=>csrf_token()]);exit;
}
if($action==='create_user'){
    require_admin(true); verify_csrf();
    $username=trim($_POST['username']??'');
    $password=(string)($_POST['password']??'');
    $role=$_POST['role']??'guest';
    if(!$username || strlen($password)<8 || !in_array($role,['admin','guest'],true))
        throw new Exception('Gebruikersnaam vereist, wachtwoord minstens 8 tekens en geldige rol.');
    $st=db()->prepare("INSERT INTO users(username,password_hash,role) VALUES(?,?,?)");
    $st->execute([$username,password_hash($password,PASSWORD_DEFAULT),$role]);
    echo json_encode(['ok'=>true]);exit;
}
if($action==='update_user'){
    require_admin(true); verify_csrf();
    $id=(int)($_POST['id']??0);
    $role=$_POST['role']??'guest';
    $password=(string)($_POST['password']??'');
    if(!$id || !in_array($role,['admin','guest'],true)) throw new Exception('Ongeldige gebruiker/rol.');
    $me=current_user();
    if($me && $me['id']===$id && $role!=='admin') throw new Exception('Je kunt je eigen adminrechten niet verwijderen.');
    if($password!==''){
        if(strlen($password)<8) throw new Exception('Nieuw wachtwoord moet minstens 8 tekens hebben.');
        $st=db()->prepare("UPDATE users SET role=?,password_hash=? WHERE id=?");
        $st->execute([$role,password_hash($password,PASSWORD_DEFAULT),$id]);
    }else{
        $st=db()->prepare("UPDATE users SET role=? WHERE id=?");
        $st->execute([$role,$id]);
    }
    echo json_encode(['ok'=>true]);exit;
}
if($action==='delete_user'){
    require_admin(true); verify_csrf();
    $id=(int)($_POST['id']??0);
    $me=current_user();
    if(!$id) throw new Exception('Ongeldige gebruiker.');
    if($me && $me['id']===$id) throw new Exception('Je kunt je eigen account niet verwijderen.');
    $st=db()->prepare("DELETE FROM users WHERE id=?");$st->execute([$id]);
    echo json_encode(['ok'=>true]);exit;
}

throw new Exception('Onbekende actie.');
} catch(Throwable $e){
    if(isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
