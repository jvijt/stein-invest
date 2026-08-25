<?php
declare(strict_types=1);

function app_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $path = __DIR__ . '/config.php';
    if (!file_exists($path)) {
        if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
            header('Location: install.php');
            exit;
        }
        return [];
    }
    $cfg = require $path;
    date_default_timezone_set($cfg['timezone'] ?? 'Europe/Brussels');
    return $cfg;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = app_config();
    $dsn = "mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'httponly' => true, 'secure' => $secure, 'samesite' => 'Lax'
        ]);
        session_start();
    }
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    start_session();
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Ongeldige sessie/CSRF-token.');
    }
}


function current_user(): ?array {
    start_session();
    if (empty($_SESSION['user_id'])) return null;
    return [
        'id'=>(int)$_SESSION['user_id'],
        'username'=>(string)($_SESSION['username'] ?? ''),
        'role'=>(string)($_SESSION['role'] ?? 'guest'),
    ];
}

function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function require_admin(bool $json=false): void {
    require_login($json);
    if (!is_admin()) {
        if ($json) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Geen beheerdersrechten']);
        } else {
            http_response_code(403);
            echo 'Geen beheerdersrechten.';
        }
        exit;
    }
}

function require_login(bool $json=false): void {
    start_session();
    if (empty($_SESSION['user_id'])) {
        if ($json) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'Niet ingelogd']);
        } else {
            header('Location: login.php');
        }
        exit;
    }
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function yahoo_url(string $symbol, string $range='5d', string $interval='1d', ?int $p1=null, ?int $p2=null): string {
    $base = 'https://query1.finance.yahoo.com/v8/finance/chart/' . rawurlencode($symbol);
    $params = ['interval'=>$interval, 'includePrePost'=>'false', 'events'=>'div,splits'];
    if ($p1 && $p2) {
        $params['period1'] = $p1;
        $params['period2'] = $p2;
    } else {
        $params['range'] = $range;
    }
    return $base . '?' . http_build_query($params);
}

function http_json(string $url, int $timeout=8): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_USERAGENT => 'Mozilla/5.0 SharesDashboard/2.0',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($raw === false || $code < 200 || $code >= 300) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function cache_get(string $key, int $maxAge): ?array {
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $file = $dir . '/' . sha1($key) . '.json';
    if (is_file($file) && (time() - filemtime($file) <= $maxAge)) {
        $x = json_decode((string)file_get_contents($file), true);
        return is_array($x) ? $x : null;
    }
    return null;
}

function cache_put(string $key, array $data): void {
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . '/' . sha1($key) . '.json', json_encode($data));
}

function fetch_chart(string $symbol, string $range='1mo', string $interval='1d', int $cacheSeconds=300): ?array {
    $key = "$symbol|$range|$interval";
    $cached = cache_get($key, $cacheSeconds);
    if ($cached) return $cached;
    $data = http_json(yahoo_url($symbol,$range,$interval));
    if (!$data) return null;
    cache_put($key,$data);
    return $data;
}

function parse_chart(string $symbol, string $range='1mo', string $interval='1d'): ?array {
    $data = fetch_chart($symbol,$range,$interval);
    $r = $data['chart']['result'][0] ?? null;
    if (!$r) return null;
    $meta = $r['meta'] ?? [];
    $timestamps = $r['timestamp'] ?? [];
    $closes = $r['indicators']['quote'][0]['close'] ?? [];
    $points = [];
    foreach ($timestamps as $i=>$ts) {
        $v = $closes[$i] ?? null;
        if ($v !== null) $points[] = ['t'=>(int)$ts,'v'=>(float)$v];
    }
    $last = $meta['regularMarketPrice'] ?? ($points ? end($points)['v'] : null);
    $prev = $meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null;
    return [
        'symbol'=>$symbol,
        'price'=>$last !== null ? (float)$last : null,
        'previous_close'=>$prev !== null ? (float)$prev : null,
        'currency'=>$meta['currency'] ?? null,
        'exchange'=>$meta['exchangeName'] ?? ($meta['fullExchangeName'] ?? null),
        'market_state'=>$meta['marketState'] ?? null,
        'timezone'=>$meta['exchangeTimezoneName'] ?? null,
        'regular_start'=>$meta['currentTradingPeriod']['regular']['start'] ?? null,
        'regular_end'=>$meta['currentTradingPeriod']['regular']['end'] ?? null,
        'name'=>$meta['longName'] ?? ($meta['shortName'] ?? $symbol),
        'points'=>$points,
    ];
}

function fx_to_eur(string $currency): float {
    $currency = strtoupper($currency);
    if ($currency === 'EUR' || $currency === '') return 1.0;
    $pair = 'EUR' . $currency . '=X'; // e.g. EURUSD=X = USD per EUR
    $d = parse_chart($pair, '5d', '1d');
    $p = $d['price'] ?? null;
    return $p ? 1.0 / (float)$p : 1.0;
}

function historical_fx_to_eur(string $currency, string $date): float {
    $currency = strtoupper($currency);
    if ($currency === 'EUR' || $currency === '') return 1.0;
    $start = strtotime($date . ' 00:00:00 UTC') - 86400*3;
    $end = strtotime($date . ' 23:59:59 UTC') + 86400*4;
    $symbol = 'EUR' . $currency . '=X';
    $data = http_json(yahoo_url($symbol,'1mo','1d',$start,$end));
    $r = $data['chart']['result'][0] ?? null;
    if (!$r) return fx_to_eur($currency);
    $ts = $r['timestamp'] ?? [];
    $cl = $r['indicators']['quote'][0]['close'] ?? [];
    $target = strtotime($date . ' 12:00:00 UTC');
    $best = null; $bestDiff = PHP_INT_MAX;
    foreach ($ts as $i=>$t) {
        $v = $cl[$i] ?? null;
        if ($v === null) continue;
        $diff = abs($t-$target);
        if ($diff < $bestDiff) { $bestDiff=$diff; $best=(float)$v; }
    }
    return $best ? 1.0/$best : fx_to_eur($currency);
}


function yahoo_search(string $query, int $count=10): array {
    $query = trim($query);
    if ($query === '') return [];
    $url = 'https://query2.finance.yahoo.com/v1/finance/search?' . http_build_query([
        'q'=>$query,
        'quotesCount'=>max(1,min(15,$count)),
        'newsCount'=>0,
        'enableFuzzyQuery'=>'true',
    ]);
    $data = http_json($url);
    if (!$data) return [];
    $out = [];
    foreach (($data['quotes'] ?? []) as $q) {
        $type = strtoupper((string)($q['quoteType'] ?? ''));
        if (!in_array($type, ['EQUITY','ETF','MUTUALFUND'], true)) continue;
        $symbol = trim((string)($q['symbol'] ?? ''));
        if ($symbol === '') continue;
        $out[] = [
            'symbol'=>$symbol,
            'name'=>$q['longname'] ?? ($q['shortname'] ?? $symbol),
            'exchange'=>$q['exchDisp'] ?? ($q['exchange'] ?? ''),
            'type'=>$type,
        ];
    }
    return $out;
}

function supported_currencies(): array {
    return ['EUR','USD','GBP','CHF','SEK','NOK','DKK','JPY','CAD','AUD','PLN','CZK','HUF'];
}


function exchange_is_open(array $d, array $asset): bool {
    $now = time();

    // 1) Beste bron: concrete start/eindtijd van de huidige handelsdag van Yahoo.
    $start = isset($d['regular_start']) ? (int)$d['regular_start'] : 0;
    $end   = isset($d['regular_end']) ? (int)$d['regular_end'] : 0;
    if ($start > 0 && $end > $start) {
        return $now >= $start && $now < $end;
    }

    // 2) Fallback op beurs/ticker wanneer marketState ontbreekt of onbetrouwbaar is.
    $ticker = strtoupper((string)($asset['ticker'] ?? ''));
    $exchange = strtoupper((string)($d['exchange'] ?? ($asset['exchange_name'] ?? '')));
    $tzName = $d['timezone'] ?? null;

    try {
        if ($tzName) {
            $tz = new DateTimeZone($tzName);
        } elseif (str_ends_with($ticker,'.BR') || str_contains($exchange,'BRUSSEL')) {
            $tz = new DateTimeZone('Europe/Brussels');
        } elseif (str_ends_with($ticker,'.AS') || str_contains($exchange,'AMSTERDAM')) {
            $tz = new DateTimeZone('Europe/Amsterdam');
        } elseif (str_ends_with($ticker,'.DE') || str_contains($exchange,'XETRA')) {
            $tz = new DateTimeZone('Europe/Berlin');
        } elseif (in_array($exchange,['NMS','NGM','NCM','NASDAQ'],true) || !str_contains($ticker,'.')) {
            $tz = new DateTimeZone('America/New_York');
        } else {
            return strtoupper((string)($d['market_state'] ?? '')) === 'REGULAR';
        }

        $dt = new DateTime('now',$tz);
        $dow = (int)$dt->format('N');
        if ($dow >= 6) return false;

        $hm = ((int)$dt->format('H'))*60 + (int)$dt->format('i');

        // Euronext Brussels/Amsterdam en Xetra normale sessie.
        if (str_ends_with($ticker,'.BR') || str_ends_with($ticker,'.AS') || str_ends_with($ticker,'.DE')
            || str_contains($exchange,'BRUSSEL') || str_contains($exchange,'AMSTERDAM') || str_contains($exchange,'XETRA')) {
            return $hm >= (9*60) && $hm < (17*60+30);
        }

        // Nasdaq/NYSE normale sessie: 09:30-16:00 lokale tijd.
        if (in_array($exchange,['NMS','NGM','NCM','NASDAQ','NYQ','NYSE'],true) || !str_contains($ticker,'.')) {
            return $hm >= (9*60+30) && $hm < (16*60);
        }
    } catch(Throwable $e) {}

    return strtoupper((string)($d['market_state'] ?? '')) === 'REGULAR';
}

function quote_for_asset(array $asset): array {
    $symbol = $asset['ticker'];
    $d = parse_chart($symbol, '1mo', '1d') ?: [
        'symbol'=>$symbol,'price'=>null,'previous_close'=>null,'currency'=>$asset['currency'] ?: 'EUR',
        'exchange'=>$asset['exchange_name'] ?: null,'market_state'=>null,'timezone'=>null,
        'name'=>$asset['name'],'points'=>[]
    ];
    $currency = strtoupper($d['currency'] ?: ($asset['currency'] ?: 'EUR'));
    $fx = fx_to_eur($currency);
    $priceEur = $d['price'] !== null ? $d['price']*$fx : null;
    $prevEur = $d['previous_close'] !== null ? $d['previous_close']*$fx : null;
    $dayPct = ($priceEur && $prevEur) ? (($priceEur/$prevEur)-1)*100 : 0.0;

    return array_merge($d, [
        'currency'=>$currency,
        'fx_to_eur'=>$fx,
        'price_eur'=>$priceEur,
        'previous_close_eur'=>$prevEur,
        'day_pct'=>$dayPct,
        'is_open'=>exchange_is_open($d,$asset),
    ]);
}

function current_positions(): array {
    $sql = "SELECT a.id asset_id,a.ticker,a.name,a.currency,a.exchange_name,a.sector,
                   t.platform,t.type,t.quantity,t.price_native,t.fx_to_eur,t.id tx_id,t.trade_date
            FROM transactions t JOIN assets a ON a.id=t.asset_id
            ORDER BY t.trade_date,t.id";
    $rows = db()->query($sql)->fetchAll();
    $p = [];
    foreach ($rows as $r) {
        $key = $r['platform'].'|'.$r['asset_id'];
        if (!isset($p[$key])) $p[$key] = [
            'asset_id'=>(int)$r['asset_id'],'ticker'=>$r['ticker'],'name'=>$r['name'],
            'currency'=>$r['currency'],'exchange_name'=>$r['exchange_name'],'sector'=>$r['sector'] ?: 'Overig',
            'platform'=>$r['platform'],'qty'=>0.0,'cost_eur'=>0.0,'cost_native'=>0.0,'purchases'=>[]
        ];
        $q = (float)$r['quantity'];
        $cost = (float)$r['price_native'] * (float)$r['fx_to_eur'] * $q;
        $costNative = (float)$r['price_native'] * $q;
        if ($r['type']==='BUY') {
            $p[$key]['qty'] += $q;
            $p[$key]['cost_eur'] += $cost;
            $p[$key]['cost_native'] += $costNative;
            $p[$key]['purchases'][] = [
                'date'=>$r['trade_date'],
                'qty'=>$q,
                'price_native'=>(float)$r['price_native'],
                'price_eur'=>(float)$r['price_native']*(float)$r['fx_to_eur'],
                'platform'=>$r['platform']
            ];
        } else {
            $avg = $p[$key]['qty'] > 0 ? $p[$key]['cost_eur']/$p[$key]['qty'] : 0;
            $avgNative = $p[$key]['qty'] > 0 ? $p[$key]['cost_native']/$p[$key]['qty'] : 0;
            $p[$key]['qty'] -= $q;
            $p[$key]['cost_eur'] -= $avg*$q;
            $p[$key]['cost_native'] -= $avgNative*$q;
        }
    }
    return array_values(array_filter($p, fn($x)=>$x['qty']>0.0000001));
}

function aggregate_positions(array $positions): array {
    $m=[];
    foreach ($positions as $p) {
        $k=(string)$p['asset_id'];
        if (!isset($m[$k])) $m[$k]=[
            'asset_id'=>$p['asset_id'],'ticker'=>$p['ticker'],'name'=>$p['name'],
            'currency'=>$p['currency'],'exchange_name'=>$p['exchange_name'],'sector'=>$p['sector'] ?: 'Overig',
            'platforms'=>[],'platform_qty'=>[],'qty'=>0.0,'cost_eur'=>0.0,'cost_native'=>0.0,'purchases'=>[]
        ];
        $m[$k]['qty'] += $p['qty'];
        $m[$k]['cost_eur'] += $p['cost_eur'];
        $m[$k]['cost_native'] += $p['cost_native'];
        $m[$k]['purchases'] = array_merge($m[$k]['purchases'],$p['purchases'] ?? []);
        $m[$k]['platforms'][$p['platform']] = true;
        $m[$k]['platform_qty'][$p['platform']] = ($m[$k]['platform_qty'][$p['platform']] ?? 0) + $p['qty'];
    }
    foreach ($m as &$x) $x['platforms']=array_keys($x['platforms']);
    return array_values($m);
}
