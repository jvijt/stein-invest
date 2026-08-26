<?php require __DIR__.'/lib.php'; require_login(); $csrf=csrf_token(); $user=current_user(); $isAdmin=is_admin(); ?>
<!doctype html><html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Stein Invest Dashboard</title>
<style>
:root{--bg:#06101a;--panel:#0d1824;--panel2:#101f2e;--line:#203548;--text:#f5f8fb;--muted:#91a7ba;--green:#31d07f;--red:#ff626d;--accent:#78a9ff}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at top,#102238 0,#07131e 35%,#050b11 100%);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Arial;min-height:100vh}
header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--line);background:rgba(6,16,26,.93);position:sticky;top:0;z-index:10;backdrop-filter:blur(12px)}
.brand{font-weight:850;font-size:22px}.sub{color:var(--muted);font-size:12px;margin-top:3px}.buttons{display:flex;gap:8px;flex-wrap:wrap}
button,.btn,select,input{font:inherit}.btn,button{border:1px solid var(--line);background:#112234;color:var(--text);border-radius:10px;padding:10px 13px;cursor:pointer;font-weight:700;text-decoration:none}.primary{background:var(--accent);color:#071019;border-color:transparent}
main{padding:20px 24px 40px}.kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:15px}.kpi{background:linear-gradient(145deg,var(--panel),var(--panel2));border:1px solid var(--line);border-radius:15px;padding:17px}.label{font-size:11px;color:var(--muted);letter-spacing:.08em;text-transform:uppercase}.big{font-size:28px;font-weight:850;margin-top:6px}.small{font-size:12px;color:var(--muted)}.positive{color:var(--green)!important}.negative{color:var(--red)!important}
.toolbar{display:flex;gap:9px;align-items:center;margin:14px 0}.toolbar select{background:#081521;color:#fff;border:1px solid var(--line);padding:10px;border-radius:9px}.overview-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:12px;margin-bottom:15px}
.sector-panel{background:linear-gradient(145deg,var(--panel),var(--panel2));border:1px solid var(--line);border-radius:15px;padding:17px;display:grid;grid-template-columns:minmax(180px,280px) 1fr;gap:18px;align-items:center}
.sector-title{font-size:15px;font-weight:800;margin-bottom:4px}.pie-wrap{display:grid;place-items:center;min-height:220px}.pie-wrap svg{width:min(250px,100%);height:auto}
.legend{display:grid;gap:8px}.legend-row{display:grid;grid-template-columns:12px 1fr auto;gap:8px;align-items:center;font-size:12px}.swatch{width:10px;height:10px;border-radius:3px}
.grid{display:grid;grid-template-columns:repeat(var(--cols,3),minmax(0,1fr));gap:12px}.desktop-only{display:inline-block}
.card{background:linear-gradient(145deg,#0e1a27,#09141e);border:1px solid var(--line);border-radius:15px;padding:15px;min-height:300px}.top{display:flex;justify-content:space-between;gap:10px}.ticker{font-size:12px;font-weight:850;color:var(--accent)}.name{font-size:17px;font-weight:800;margin-top:3px}.badge{font-size:11px;background:#14273a;border-radius:99px;padding:5px 8px;height:max-content;color:#bed0e0}.price{font-size:25px;font-weight:850;margin-top:13px}
.market{display:inline-flex;align-items:center;gap:6px;font-size:11px;color:var(--muted);margin-left:7px}.dot{width:9px;height:9px;border-radius:50%;display:inline-block;background:var(--red);box-shadow:0 0 8px rgba(255,98,109,.25)}.dot.open{background:var(--green);box-shadow:0 0 9px rgba(49,208,127,.5)}
.stats{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;margin-top:12px}.stat span{display:block;font-size:11px;color:var(--muted)}.stat b{font-size:14px}.spark{height:100px;margin-top:14px;width:100%}.spark svg{width:100%;height:100%;overflow:visible}
dialog{width:min(720px,95vw);background:#09131e;color:#fff;border:1px solid var(--line);border-radius:16px;padding:0}dialog::backdrop{background:rgba(0,0,0,.72)}.mh,.mf{padding:16px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)}.mf{border-top:1px solid var(--line);border-bottom:0;justify-content:flex-end;gap:8px}.mb{padding:17px}.fg{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field label{display:block;color:var(--muted);font-size:12px;margin-bottom:5px}.field input,.field select{width:100%;background:#07131e;color:#fff;border:1px solid var(--line);padding:10px;border-radius:9px}
.searchbox{position:relative}.search-results{position:absolute;left:0;right:0;top:100%;z-index:30;background:#0d1b29;border:1px solid var(--line);border-radius:10px;max-height:270px;overflow:auto;margin-top:4px;box-shadow:0 12px 30px rgba(0,0,0,.35)}.search-results:empty{display:none}.search-item{width:100%;display:block;text-align:left;background:transparent;border:0;border-bottom:1px solid #1c3043;border-radius:0;padding:10px 11px}.search-item:last-child{border-bottom:0}.search-item b{display:block}.search-meta{font-size:11px;color:var(--muted);margin-top:2px}.verified{font-size:11px;color:var(--green);margin-top:5px;display:none}.verified.show{display:block}
table{width:100%;border-collapse:collapse;font-size:12px}th,td{text-align:left;padding:9px 7px;border-bottom:1px solid var(--line)}.scroll{overflow:auto;max-height:60vh}
body.tv{overflow:hidden;height:100vh}body.tv header{display:none}body.tv main{padding:10px;height:100vh;overflow:hidden;display:flex;flex-direction:column}body.tv .toolbar{display:none}body.tv .overview-grid{margin-bottom:8px}body.tv .sector-panel{padding:10px;grid-template-columns:160px 1fr;min-height:0}body.tv .pie-wrap{min-height:120px}body.tv .pie-wrap svg{width:140px}body.tv .legend{grid-template-columns:repeat(2,minmax(0,1fr));gap:4px 10px}body.tv .legend-row{font-size:10px}body.tv .kpis{gap:8px;margin-bottom:8px}body.tv .kpi{padding:10px}body.tv .label{font-size:9px}body.tv .big{font-size:20px;margin-top:3px}body.tv .small{font-size:10px}body.tv .grid{flex:1;min-height:0;display:grid;grid-template-columns:repeat(var(--tv-cols,4),minmax(0,1fr))!important;grid-template-rows:repeat(var(--tv-rows,2),minmax(0,1fr));gap:7px;overflow:hidden}body.tv .card{min-height:0;height:100%;padding:9px;overflow:hidden;display:flex;flex-direction:column}body.tv .ticker{font-size:9px}body.tv .name{font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}body.tv .badge{font-size:9px;padding:3px 6px}body.tv .price{font-size:17px;margin-top:6px;white-space:nowrap}body.tv .market{font-size:9px;margin-left:3px}body.tv .dot{width:7px;height:7px}body.tv .stats{gap:4px 8px;margin-top:6px}body.tv .stat span{font-size:8px}body.tv .stat b{font-size:10px}body.tv .spark{flex:1;min-height:34px;height:auto;margin-top:6px}body.tv .card>.small{font-size:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
body.tv.tv-dense .overview-grid{display:none}body.tv.tv-dense .kpis{grid-template-columns:repeat(4,1fr);margin-bottom:6px}body.tv.tv-dense .card{padding:7px}body.tv.tv-dense .spark{min-height:24px}body.tv.tv-dense .name{font-size:11px}body.tv.tv-dense .price{font-size:15px}body.tv.tv-dense .stat b{font-size:9px}
@media(max-width:1050px){.kpis{grid-template-columns:repeat(2,1fr)}.grid{grid-template-columns:repeat(2,1fr)!important}.overview-grid{grid-template-columns:1fr}.sector-panel{grid-template-columns:240px 1fr}.desktop-only{display:none!important}}
@media(max-width:680px){header{padding:14px}.brand{font-size:19px}.sub{display:none}.buttons .hide-mobile{display:none}main{padding:12px}.kpis{grid-template-columns:1fr 1fr;gap:8px}.kpi{padding:13px}.big{font-size:21px}.grid{grid-template-columns:1fr}.fg{grid-template-columns:1fr}.card{min-height:285px}.toolbar{position:sticky;top:66px;background:#07131e;padding:8px 0;z-index:5}.sector-panel{grid-template-columns:1fr}.pie-wrap{min-height:190px}.desktop-only{display:none!important}}

@media(max-width:680px){
  html,body{max-width:100%;overflow-x:hidden}
  main{max-width:100%;overflow-x:hidden}
  .toolbar{
    position:sticky;
    top:66px;
    width:100%;
    max-width:100%;
    box-sizing:border-box;
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    gap:7px;
    align-items:stretch;
    overflow:visible;
  }
  .toolbar select,.toolbar button,.toolbar .btn{
    min-width:0;
    width:100%;
    max-width:100%;
    box-sizing:border-box;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }
  .toolbar #platform{grid-column:1}
  .toolbar #sortOrder{grid-column:2}
  .toolbar #updated{
    grid-column:1/-1;
    width:100%;
    min-width:0;
    white-space:normal;
  }
  .toolbar .desktop-only{display:none!important}
  .grid,.kpis,.overview-grid,.sector-panel{min-width:0;max-width:100%}
  .card{min-width:0}
}
@media(max-width:430px){
  .toolbar{grid-template-columns:1fr}
  .toolbar #platform,.toolbar #sortOrder,.toolbar #updated{grid-column:1}
}


@media(max-width:680px){
  html,body{max-width:100%;overflow-x:hidden}
  main{max-width:100%;overflow-x:hidden}
  .toolbar{
    position:sticky;
    top:66px;
    width:100%;
    max-width:100%;
    box-sizing:border-box;
    display:block;
    padding:8px 0;
    overflow:visible;
  }
  .toolbar > *{display:none!important}
  .toolbar #sortOrder{
    display:block!important;
    width:100%;
    max-width:100%;
    min-width:0;
    box-sizing:border-box;
    font-size:16px;
  }
  .toolbar #updated{
    display:block!important;
    width:100%;
    max-width:100%;
    margin-top:6px;
    white-space:normal;
  }
  .grid{
    display:grid!important;
    grid-template-columns:1fr!important;
    width:100%;
    max-width:100%;
    gap:10px;
  }
  .card{
    width:100%;
    max-width:100%;
    min-width:0;
  }
  .kpis,.overview-grid,.sector-panel{grid-template-columns:1fr!important;min-width:0;max-width:100%}
  .desktop-only{display:none!important}
}

/* Mobiele header: titel boven compacte beheersknoppen. TV fullscreen blijft alleen op grotere schermen zichtbaar. */
@media(max-width:680px){
  header{
    flex-direction:column;
    align-items:flex-start;
    justify-content:flex-start;
    gap:9px;
    padding:11px 14px;
  }
  header>div:first-child{width:100%}
  .brand{font-size:19px;line-height:1.15}
  .buttons{
    width:100%;
    display:flex;
    justify-content:flex-start;
    align-items:center;
    gap:5px;
    flex-wrap:wrap;
  }
  .buttons button,.buttons .btn{
    width:auto;
    min-width:0;
    padding:6px 8px;
    border-radius:7px;
    font-size:11px;
    line-height:1.1;
  }
  .buttons .hide-mobile{display:none!important}
  .toolbar{position:static!important;top:auto!important}
}

</style></head><body>
<header><div><div class="brand">Stein Invest Dashboard</div><div class="sub">ING + Saxo · portfolio in EUR</div></div><div class="buttons">
<button class="hide-mobile" onclick="toggleTV()">TV fullscreen</button>
<?php if($isAdmin):?><button onclick="openPositions()">Posities</button><button onclick="openSectors()">Sectoren</button><button onclick="openUsers()">Gebruikers</button><button class="primary" onclick="openAdd()">+ Transactie</button><?php endif;?>
<a class="btn hide-mobile" href="logout.php">Uitloggen</a></div></header>
<main>
<div class="kpis">
<div class="kpi"><div class="label">Totale waarde</div><div class="big" id="total">—</div><div class="small" id="invested">Geïnvesteerd: —</div><div class="small" id="mix" style="margin-top:3px">—</div></div>
<div class="kpi"><div class="label">Winst / verlies <span style="opacity:.7">ALL TIME</span></div><div class="big" id="pnl">—</div><div class="small" id="pnlpct">—</div></div>
<div class="kpi"><div class="label">Vandaag</div><div class="big" id="day">—</div><div class="small" id="daypct">— sinds vorige slotkoers</div></div>
<div class="kpi"><div class="label">Grootste positie</div><div class="big" id="largest">—</div><div class="small" id="largestpct">—</div></div>
</div>
<div class="overview-grid">
  <section class="sector-panel">
    <div class="pie-wrap" id="sectorPie"><div class="small">Sectorverdeling laden…</div></div>
    <div><div class="sector-title">Spreiding per sector</div><div class="small" style="margin-bottom:12px">Op basis van de actuele marktwaarde.</div><div class="legend" id="sectorLegend"></div></div>
  </section>
</div>
<div class="toolbar">
<select id="platform" onchange="loadDashboard()"><option value="ALL">Alle platformen</option><option>ING</option><option>Saxo</option></select>
<select id="sortOrder" onchange="renderFromCache()">
  <option value="pnl_eur_desc">Winst € ↓</option>
  <option value="pnl_eur_asc">Winst € ↑</option>
  <option value="weight_desc">Sorteren: % portefeuille ↓</option>
  <option value="weight_asc">Sorteren: % portefeuille ↑</option>
  <option value="pnl_pct_desc">Winst % ↓</option>
  <option value="pnl_pct_asc">Winst % ↑</option>
  <option value="value_desc">Waarde ↓</option>
  <option value="value_asc">Waarde ↑</option>
  <option value="day_desc">Vandaag % ↓</option>
  <option value="day_asc">Vandaag % ↑</option>
  <option value="name_asc">Naam A-Z</option>
  <option value="name_desc">Naam Z-A</option>
</select>
<select id="columnCount" class="desktop-only" onchange="applyColumnCount()">
  <option value="3">3 kolommen</option>
  <option value="4">4 kolommen</option>
  <option value="5">5 kolommen</option>
  <option value="6">6 kolommen</option>
</select>
<button onclick="loadDashboard()">Vernieuwen</button><?php if($isAdmin):?><button onclick="openTransactions()">Beheer transacties</button><?php endif;?><span class="small" id="updated"></span></div>
<div class="grid" id="cards"></div>
</main>

<dialog id="addDialog"><div class="mh"><b>Transactie toevoegen</b><button onclick="addDialog.close()">✕</button></div><div class="mb"><div class="fg">
<div class="field searchbox"><label>Aandeel zoeken op naam, ISIN of ticker</label><input id="assetSearch" placeholder="bv. NVIDIA, US67066G1040 of NVDA" autocomplete="off"><div class="search-results" id="assetSearchResults"></div><div class="verified" id="assetVerified">✓ Geverifieerd: <span id="assetVerifiedText"></span></div><input id="ticker" type="hidden"></div>
<div class="field"><label>Naam</label><input id="assetName" readonly></div>
<div class="field"><label>Sector</label><select id="sector"><option>Technologie</option><option>Healthcare</option><option>Financieel</option><option>Industrie</option><option>Consumentengoederen</option><option>Consumentendiensten</option><option>Energie</option><option>Materialen</option><option>Vastgoed</option><option>Telecom</option><option>Utilities</option><option>Brede ETF</option><option selected>Overig</option></select><div class="small" id="sectorSuggestion" style="margin-top:6px"></div></div>
<div class="field"><label>Datum gekocht/verkocht</label><input id="tradeDate" type="date"></div>
<div class="field"><label>Koers gekocht/verkocht</label><input id="price" type="number" step="0.000001"></div>
<div class="field"><label>Valuta van de ingevoerde koers</label><select id="currency"><option>EUR</option><option>USD</option><option>GBP</option><option>CHF</option><option>SEK</option><option>NOK</option><option>DKK</option><option>JPY</option><option>CAD</option><option>AUD</option><option>PLN</option><option>CZK</option><option>HUF</option></select></div>
<div class="field"><label>Aantal aandelen</label><input id="quantity" type="number" step="0.000001"></div>
<div class="field"><label>Platform</label><select id="txPlatform"><option>ING</option><option>Saxo</option></select></div>
<div class="field"><label>Type</label><select id="type"><option value="BUY">Koop</option><option value="SELL">Verkoop</option></select></div>
</div><p class="small">Zoek eerst het effect en selecteer een geverifieerd resultaat. Je kunt de aankoop/verkoopkoers invoeren in EUR, USD, GBP, CHF, SEK, NOK, DKK, JPY, CAD, AUD, PLN, CZK of HUF. De app zoekt automatisch de historische wisselkoers voor de gekozen datum.</p></div>
<div class="mf"><button onclick="addDialog.close()">Annuleren</button><button class="primary" onclick="saveTx()">Opslaan</button></div></dialog>

<dialog id="posDialog"><div class="mh"><b>Posities aanpassen</b><button onclick="posDialog.close()">✕</button></div><div class="mb">
<p class="small">Posities worden hier altijd apart per platform getoond. Op het dashboard worden dezelfde aandelen van ING en Saxo automatisch samengevoegd.</p>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px"><button class="primary" onclick="openNewPosition()">+ Positie toevoegen</button></div>
<div class="scroll" id="posList"></div></div></dialog>

<dialog id="editPosDialog"><div class="mh"><b>Positie corrigeren</b><button onclick="editPosDialog.close()">✕</button></div><div class="mb"><div class="fg">
<input type="hidden" id="epAssetId"><input type="hidden" id="epMode" value="edit">
<div class="field" id="epExistingField"><label>Aandeel</label><input id="epName" readonly></div>
<div class="field" id="epNewField" style="display:none"><label>Aandeel</label><select id="epAssetSelect"></select></div>
<div class="field"><label>Platform</label><select id="epPlatform"><option>ING</option><option>Saxo</option></select></div>
<div class="field"><label>Aantal aandelen</label><input id="epQty" type="number" step="0.000001"></div>
<div class="field"><label>Gemiddelde aankoopprijs</label><input id="epAvg" type="number" step="0.000001"></div>
<div class="field"><label>Valuta aankoopprijs</label><select id="epCurrency"><option>EUR</option><option>USD</option><option>GBP</option><option>CHF</option><option>SEK</option><option>NOK</option><option>DKK</option><option>JPY</option><option>CAD</option><option>AUD</option><option>PLN</option><option>CZK</option><option>HUF</option></select></div>
<div class="field"><label>Referentiedatum</label><input id="epDate" type="date"></div>
<div class="field"><label>Sector</label><select id="epSector"><option>Technologie</option><option>Healthcare</option><option>Financieel</option><option>Industrie</option><option>Consumentengoederen</option><option>Consumentendiensten</option><option>Energie</option><option>Materialen</option><option>Vastgoed</option><option>Telecom</option><option>Utilities</option><option>Brede ETF</option><option>Overig</option></select></div>
</div></div><div class="mf"><button id="deletePositionBtn" class="negative" onclick="deletePosition()" style="margin-right:auto;background:#34151a;border-color:#6b252d">Positie verwijderen</button><button onclick="editPosDialog.close()">Annuleren</button><button class="primary" onclick="savePosition()">Positie opslaan</button></div></dialog>

<?php if($isAdmin):?>
<dialog id="sectorDialog"><div class="mh"><b>Sectoren beheren</b><button onclick="sectorDialog.close()">✕</button></div>
<div class="mb"><p class="small">Voeg eigen categorieën toe, bijvoorbeeld AI of Halfgeleiders. Ze verschijnen daarna automatisch in alle sector-keuzelijsten.</p>
<div style="display:flex;gap:8px"><input id="newSectorName" placeholder="Nieuwe sector" style="flex:1;background:#07131e;color:#fff;border:1px solid var(--line);padding:10px;border-radius:9px"><button class="primary" onclick="addSector()">Toevoegen</button></div>
<div id="sectorManageList" class="small" style="margin-top:15px"></div></div></dialog>

<dialog id="userDialog"><div class="mh"><b>Gebruikersbeheer</b><button onclick="userDialog.close()">✕</button></div>
<div class="mb">
  <div class="fg" style="margin-bottom:16px">
    <div class="field"><label>Nieuwe gebruikersnaam</label><input id="newUsername"></div>
    <div class="field"><label>Rol</label><select id="newRole"><option value="guest">Guest</option><option value="admin">Admin</option></select></div>
    <div class="field"><label>Wachtwoord</label><input id="newPassword" type="password" minlength="8"></div>
  </div>
  <button class="primary" onclick="createUser()">Gebruiker aanmaken</button>
  <div class="scroll" id="userList" style="margin-top:16px"></div>
</div></dialog>
<?php endif;?>

<dialog id="txDialog"><div class="mh"><b>Transacties beheren</b><button onclick="txDialog.close()">✕</button></div><div class="mb scroll" id="txList"></div></dialog>

<script>
let csrf=<?=json_encode($csrf)?>;
const IS_ADMIN=<?= $isAdmin ? 'true' : 'false' ?>;
let lastPositions=[];let lastDashboardData=null;
const euro=v=>new Intl.NumberFormat('nl-BE',{style:'currency',currency:'EUR',maximumFractionDigits:2}).format(v||0);
const pct=v=>(v>=0?'+':'')+(v||0).toFixed(2)+'%';
const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
function cls(v){return v>=0?'positive':'negative'}
function spark(points,purchases=[],avgCost=null){
 if(!points||points.length<2)return '';
 const clean=points.filter(p=>Number.isFinite(Number(p.v))&&Number.isFinite(Number(p.t)));
 if(clean.length<2)return '';
 const vals=clean.map(p=>Number(p.v));
 let min=Math.min(...vals),max=Math.max(...vals);
 if(Number.isFinite(Number(avgCost))&&Number(avgCost)>0){min=Math.min(min,Number(avgCost));max=Math.max(max,Number(avgCost))}
 const span=(max-min)||1,w=320,h=88;
 const xy=clean.map((p,i)=>({x:i/(clean.length-1)*w,y:h-(Number(p.v)-min)/span*h}));
 const ps=xy.map(p=>`${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
 let extra='';
 if(Number.isFinite(Number(avgCost))&&Number(avgCost)>0){const y=h-(Number(avgCost)-min)/span*h;extra+=`<line x1="0" y1="${y.toFixed(1)}" x2="${w}" y2="${y.toFixed(1)}" stroke="#f5b942" stroke-width="1" stroke-dasharray="4 4" opacity=".72" vector-effect="non-scaling-stroke"><title>Gemiddelde aankoopprijs ${euro(Number(avgCost))}</title></line>`;}
 return `<svg viewBox="0 0 ${w} ${h}" preserveAspectRatio="none" aria-label="Koersgrafiek met gemiddelde aankoopprijs"><polyline points="${ps}" fill="none" stroke="#78a9ff" stroke-width="2.2" vector-effect="non-scaling-stroke"/>${extra}</svg>`;
}
const sectorColors=['#78a9ff','#31d07f','#f5b942','#c084fc','#fb7185','#22d3ee','#f97316','#a3e635','#e879f9','#94a3b8','#facc15','#60a5fa','#64748b'];
function renderSectorPie(positions){const m={};let total=0;positions.forEach(x=>{const v=Number(x.quote?.price_eur||0)*Number(x.qty||0);const sec=x.sector||'Overig';m[sec]=(m[sec]||0)+v;total+=v;});const rows=Object.entries(m).filter(([,v])=>v>0).sort((a,b)=>b[1]-a[1]);const pie=document.getElementById('sectorPie'),legend=document.getElementById('sectorLegend');if(!rows.length||!total){pie.innerHTML='<div class="small">Nog geen sectorgegevens.</div>';legend.innerHTML='';return;}let a=-Math.PI/2,paths='';rows.forEach(([name,val],i)=>{const frac=val/total,b=a+frac*Math.PI*2;const x1=100+82*Math.cos(a),y1=100+82*Math.sin(a),x2=100+82*Math.cos(b),y2=100+82*Math.sin(b);const large=frac>0.5?1:0;paths+=`<path d="M100 100 L${x1.toFixed(2)} ${y1.toFixed(2)} A82 82 0 ${large} 1 ${x2.toFixed(2)} ${y2.toFixed(2)} Z" fill="${sectorColors[i%sectorColors.length]}"><title>${esc(name)} ${(frac*100).toFixed(1)}%</title></path>`;a=b;});paths+=`<circle cx="100" cy="100" r="47" fill="#0d1824"/><text x="100" y="96" text-anchor="middle" fill="#91a7ba" font-size="10">Sectoren</text><text x="100" y="114" text-anchor="middle" fill="#f5f8fb" font-size="16" font-weight="800">${rows.length}</text>`;pie.innerHTML=`<svg viewBox="0 0 200 200" role="img" aria-label="Taartgrafiek sectorverdeling">${paths}</svg>`;legend.innerHTML=rows.map(([name,val],i)=>`<div class="legend-row"><i class="swatch" style="background:${sectorColors[i%sectorColors.length]}"></i><span>${esc(name)}</span><b>${(val/total*100).toFixed(1)}%</b></div>`).join('');}
async function loadDashboard(){const p=document.getElementById('platform').value;const r=await fetch('api.php?action=dashboard&platform='+encodeURIComponent(p),{credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Fout');return}csrf=d.csrf||csrf;lastDashboardData=d;lastPositions=d.positions||[];renderFromCache();document.getElementById('updated').textContent='Update '+new Date(d.updated_at).toLocaleTimeString('nl-BE',{hour:'2-digit',minute:'2-digit'});}
function renderFromCache(){const d=lastDashboardData;if(!d)return;let ps=(d.positions||[]).map(x=>({...x}));let provisionalTotal=0;ps.forEach(x=>{x._price=Number(x.quote?.price_eur??0);x._value=x._price*Number(x.qty);provisionalTotal+=x._value});ps.forEach(x=>{x._cost=Number(x.cost_eur);x._pnl=x._value-x._cost;x._pnlPct=x._cost?x._pnl/x._cost*100:0;x._weight=provisionalTotal?x._value/provisionalTotal*100:0;x._dayPct=Number(x.quote?.day_pct||0);});const mode=document.getElementById('sortOrder')?.value||'pnl_eur_desc';const cmp={weight_desc:(a,b)=>b._weight-a._weight,weight_asc:(a,b)=>a._weight-b._weight,pnl_pct_desc:(a,b)=>b._pnlPct-a._pnlPct,pnl_pct_asc:(a,b)=>a._pnlPct-b._pnlPct,pnl_eur_desc:(a,b)=>b._pnl-a._pnl,pnl_eur_asc:(a,b)=>a._pnl-b._pnl,value_desc:(a,b)=>b._value-a._value,value_asc:(a,b)=>a._value-b._value,day_desc:(a,b)=>b._dayPct-a._dayPct,day_asc:(a,b)=>a._dayPct-b._dayPct,name_asc:(a,b)=>(a.name||'').localeCompare(b.name||''),name_desc:(a,b)=>(b.name||'').localeCompare(a.name||'')}[mode];if(cmp)ps.sort(cmp);lastPositions=ps;renderSectorPie(ps);if(document.body.classList.contains('tv'))applyTVLayout();let total=0,cost=0,day=0,prevTotal=0,largest=null;const mix={};const cards=document.getElementById('cards');cards.innerHTML='';ps.forEach(x=>{const q=x.quote||{},price=x._price,value=x._value,c=x._cost,pl=x._pnl,plp=x._pnlPct;const prevPrice=Number(q.previous_close_eur||0);const qty=Number(x.qty||0);const todayLocal=new Date();const yyyy=todayLocal.getFullYear(),mm=String(todayLocal.getMonth()+1).padStart(2,'0'),dd=String(todayLocal.getDate()).padStart(2,'0');const todayStr=`${yyyy}-${mm}-${dd}`;const purchases=Array.isArray(x.purchases)?x.purchases:[];const olderPurchases=purchases.filter(b=>b.date&&b.date<todayStr);const hasOlderPosition=olderPurchases.length>0;let eligibleQty=0;if(hasOlderPosition){eligibleQty=olderPurchases.reduce((sum,b)=>sum+Number(b.qty||0),0);eligibleQty=Math.min(qty,Math.max(0,eligibleQty));}const dayPos=(hasOlderPosition&&price>0&&prevPrice>0)?(price-prevPrice)*eligibleQty:0;const prevValue=(hasOlderPosition&&prevPrice>0)?prevPrice*eligibleQty:0;total+=value;cost+=c;day+=dayPos;prevTotal+=prevValue;if(!largest||value>largest.value)largest={name:x.name,value};const plats=x.platforms?x.platforms.join(' + '):x.platform;if(x.platform_qty&&Object.keys(x.platform_qty).length){Object.entries(x.platform_qty).forEach(([z,qy])=>{mix[z]=(mix[z]||0)+price*Number(qy||0);});}else{const z=x.platform||((x.platforms||[])[0]||'');if(z)mix[z]=(mix[z]||0)+value;}const open=!!q.is_open,marketText=open?'Beurs open':'Beurs gesloten';cards.insertAdjacentHTML('beforeend',`<article class="card"><div class="top"><div><div class="ticker">${esc(x.ticker)}</div><div class="name">${esc(x.name)}</div></div><div class="badge">${esc(plats)}</div></div><div class="price">${price?euro(price):'Geen koers'} <span class="${cls(Number(q.day_pct||0))}" style="font-size:12px">${pct(Number(q.day_pct||0))}</span><span class="market"><i class="dot ${open?'open':''}"></i>${marketText}</span></div><div class="stats"><div class="stat"><span>Aantal</span><b>${Number(x.qty).toLocaleString('nl-BE',{maximumFractionDigits:6})}</b></div><div class="stat"><span>Gem. aankoop (EUR)</span><b>${euro(Number(x.avg_cost_eur))}</b></div><div class="stat"><span>Waarde</span><b>${euro(value)}</b></div><div class="stat"><span>Winst/verlies</span><b class="${cls(pl)}">${euro(pl)} · ${pct(plp)}</b></div><div class="stat"><span>Gewicht portefeuille</span><b>${x._weight.toFixed(1)}%</b></div></div><div class="spark">${spark(q.points,x.purchases||[],Number(x.avg_cost_eur||0))}</div><div class="small">${esc(q.exchange||x.exchange_name||'')} · ${esc(q.currency||x.currency||'')}</div></article>`);});const pl=total-cost,plp=cost?pl/cost*100:0,dp=prevTotal?day/prevTotal*100:0;document.getElementById('total').textContent=euro(total);document.getElementById('invested').textContent='Geïnvesteerd: '+euro(cost);document.getElementById('pnl').textContent=euro(pl);document.getElementById('pnl').className='big '+cls(pl);document.getElementById('pnlpct').textContent=pct(plp);document.getElementById('day').textContent=euro(day);document.getElementById('day').className='big '+cls(day);document.getElementById('daypct').textContent=pct(dp)+' sinds vorige slotkoers';document.getElementById('largest').textContent=largest?largest.name:'—';document.getElementById('largestpct').textContent=largest&&total?(largest.value/total*100).toFixed(1)+'% van portefeuille':'—';document.getElementById('mix').textContent=Object.entries(mix).map(([k,v])=>k+' '+euro(v)).join(' · ')||'—';}
function applyColumnCount(){const n=parseInt(document.getElementById('columnCount')?.value||'3',10);document.documentElement.style.setProperty('--cols',String(Math.max(3,Math.min(6,n))));}
async function loadSectors(){try{const r=await fetch('api.php?action=sectors',{credentials:'same-origin'}),d=await r.json();if(!d.ok)return;['sector','epSector'].forEach(id=>{const el=document.getElementById(id);if(!el)return;const current=el.value;el.innerHTML=d.sectors.map(x=>`<option value="${esc(x)}">${esc(x)}</option>`).join('');if(d.sectors.includes(current))el.value=current;else if(d.sectors.includes('Overig'))el.value='Overig';});return d.sectors;}catch(e){}}
async function openSectors(){const sectors=await loadSectors()||[];sectorManageList.innerHTML=sectors.map(x=>`<span style="display:inline-block;border:1px solid var(--line);border-radius:999px;padding:5px 9px;margin:3px">${esc(x)}</span>`).join('');sectorDialog.showModal();}
async function addSector(){const name=newSectorName.value.trim();if(!name)return;const fd=new FormData();fd.append('csrf',csrf);fd.append('name',name);const r=await fetch('api.php?action=add_sector',{method:'POST',body:fd,credentials:'same-origin'}),d=await r.json();if(!d.ok){alert(d.error||'Toevoegen mislukt');return}newSectorName.value='';await openSectors();}
async function suggestSector(){if(!ticker.value)return;try{const r=await fetch('api.php?action=sector_suggest&ticker='+encodeURIComponent(ticker.value)+'&name='+encodeURIComponent(assetName.value),{credentials:'same-origin'}),d=await r.json();if(!d.ok)return;await loadSectors();if(IS_ADMIN&&['AI','Halfgeleiders'].includes(d.sector)){const fd=new FormData();fd.append('csrf',csrf);fd.append('name',d.sector);await fetch('api.php?action=add_sector',{method:'POST',body:fd,credentials:'same-origin'});await loadSectors();}if([...sector.options].some(o=>o.value===d.sector))sector.value=d.sector;sectorSuggestion.innerHTML=`Advies: <b>${esc(d.sector)}</b> — ${esc(d.reason)} <span style="color:var(--muted)">(je kunt dit wijzigen)</span>`;}catch(e){}}
let assetSearchTimer=null;let verifiedAsset=null;assetSearch.addEventListener('input',()=>{verifiedAsset=null;ticker.value='';assetName.value='';assetVerified.classList.remove('show');clearTimeout(assetSearchTimer);const q=assetSearch.value.trim();if(q.length<2){assetSearchResults.innerHTML='';return;}assetSearchTimer=setTimeout(()=>searchAssets(q),250);});document.addEventListener('click',e=>{if(!e.target.closest('.searchbox'))assetSearchResults.innerHTML='';});
async function searchAssets(q){assetSearchResults.innerHTML='<div class="small" style="padding:10px">Zoeken…</div>';try{const r=await fetch('api.php?action=asset_search&q='+encodeURIComponent(q),{credentials:'same-origin'});const d=await r.json();if(!d.ok)throw new Error(d.error||'Zoeken mislukt');if(!d.results?.length){assetSearchResults.innerHTML='<div class="small" style="padding:10px">Geen bestaand aandeel of ETF gevonden.</div>';return;}assetSearchResults.innerHTML=d.results.map(x=>`<button type="button" class="search-item" data-symbol="${esc(x.symbol)}" data-name="${esc(x.name)}"><b>${esc(x.name)}</b><div class="search-meta">${esc(x.symbol)} · ${esc(x.exchange||'')} · ${esc(x.type||'')}</div></button>`).join('');assetSearchResults.querySelectorAll('.search-item').forEach(btn=>btn.addEventListener('click',()=>selectAsset(btn.dataset.symbol,btn.dataset.name)));}catch(e){assetSearchResults.innerHTML='<div class="small" style="padding:10px">Zoeken tijdelijk niet beschikbaar.</div>'}}
async function selectAsset(symbol,name){assetSearchResults.innerHTML='<div class="small" style="padding:10px">Controleren…</div>';try{const r=await fetch('api.php?action=asset_validate&ticker='+encodeURIComponent(symbol),{credentials:'same-origin'});const d=await r.json();if(!d.ok)throw new Error(d.error||'Validatie mislukt');verifiedAsset=d.asset;ticker.value=d.asset.ticker;assetName.value=d.asset.name||name;assetSearch.value=(d.asset.name||name)+' ('+d.asset.ticker+')';if([...currency.options].some(o=>o.value===d.asset.currency))currency.value=d.asset.currency;assetVerifiedText.textContent=`${d.asset.ticker} · ${d.asset.exchange||''} · ${d.asset.currency||''}`;assetVerified.classList.add('show');assetSearchResults.innerHTML='';await suggestSector();}catch(e){verifiedAsset=null;ticker.value='';assetVerified.classList.remove('show');assetSearchResults.innerHTML='<div class="small" style="padding:10px">Dit effect kon niet worden gevalideerd.</div>'}}
function openAdd(){verifiedAsset=null;ticker.value='';assetName.value='';assetSearch.value='';assetSearchResults.innerHTML='';assetVerified.classList.remove('show');document.getElementById('tradeDate').value=new Date().toISOString().slice(0,10);addDialog.showModal()}
async function saveTx(){if(!verifiedAsset||!ticker.value){alert('Zoek het aandeel op naam, ISIN of ticker en selecteer eerst een geverifieerd resultaat.');return}const fd=new FormData();fd.append('csrf',csrf);fd.append('ticker',ticker.value.trim());fd.append('name',assetName.value.trim());fd.append('sector',sector.value);fd.append('currency',currency.value);fd.append('trade_date',tradeDate.value);fd.append('price',price.value);fd.append('quantity',quantity.value);fd.append('platform',txPlatform.value);fd.append('type',type.value);const r=await fetch('api.php?action=save_transaction',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Opslaan mislukt');return}addDialog.close();verifiedAsset=null;ticker.value=assetName.value=assetSearch.value=price.value=quantity.value='';assetVerified.classList.remove('show');await loadDashboard();}
async function openPositions(){const r=await fetch('api.php?action=positions_raw',{credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Fout');return}csrf=d.csrf||csrf;let h='<table><tr><th>Aandeel</th><th>Platform</th><th>Aantal</th><th>Gem. aankoop</th><th>Valuta</th><th>Sector</th><th></th></tr>';(d.positions||[]).sort((a,b)=>(a.name||'').localeCompare(b.name||'')||(a.platform||'').localeCompare(b.platform||'')).forEach(x=>{const payload={asset_id:Number(x.asset_id),name:x.name,platform:x.platform,qty:Number(x.qty),avg:Number(x.avg_cost_native??x.avg_cost_eur),currency:x.currency||'EUR',sector:x.sector||'Overig'};h+=`<tr><td><b>${esc(x.name)}</b><div class="small">${esc(x.ticker)}</div></td><td><b>${esc(x.platform)}</b></td><td>${Number(x.qty).toLocaleString('nl-BE',{maximumFractionDigits:6})}</td><td>${Number(x.avg_cost_native??x.avg_cost_eur).toLocaleString('nl-BE',{maximumFractionDigits:6})}</td><td>${esc(x.currency||'EUR')}</td><td>${esc(x.sector||'Overig')}</td><td><button onclick='editPosition(${JSON.stringify(payload).replace(/'/g,"&#39;")})'>Aanpassen</button></td></tr>`;});h+='</table>';posList.innerHTML=h;if(!posDialog.open)posDialog.showModal();}
async function openNewPosition(){const r=await fetch('api.php?action=assets',{credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Aandelen laden mislukt');return}csrf=d.csrf||csrf;epMode.value='new';epAssetId.value='';epExistingField.style.display='none';epNewField.style.display='';deletePositionBtn.style.display='none';epAssetSelect.innerHTML=(d.assets||[]).map(a=>`<option value="${Number(a.id)}" data-name="${esc(a.name)}" data-currency="${esc(a.currency||'EUR')}" data-sector="${esc(a.sector||'Overig')}">${esc(a.name)} (${esc(a.ticker)})</option>`).join('');if(!epAssetSelect.options.length){alert('Er zijn nog geen aandelen gekend. Voeg eerst een aandeel toe via + Transactie.');return}const opt=epAssetSelect.options[0];epCurrency.value=opt.dataset.currency||'EUR';epSector.value=opt.dataset.sector||'Overig';epPlatform.value='ING';epQty.value='';epAvg.value='';epDate.value=new Date().toISOString().slice(0,10);editPosDialog.showModal();}
epAssetSelect.addEventListener('change',()=>{const opt=epAssetSelect.options[epAssetSelect.selectedIndex];if(opt){epCurrency.value=opt.dataset.currency||'EUR';epSector.value=opt.dataset.sector||'Overig';}});
function editPosition(x){epMode.value='edit';epExistingField.style.display='';epNewField.style.display='none';deletePositionBtn.style.display='';epAssetId.value=x.asset_id;epName.value=x.name;epPlatform.value=x.platform;epQty.value=x.qty;epAvg.value=x.avg;epCurrency.value=x.currency||'EUR';epDate.value=new Date().toISOString().slice(0,10);epSector.value=x.sector||'Overig';editPosDialog.showModal();}
async function deletePosition(){if(epMode.value!=='edit')return;const name=epName.value||'deze positie';const platform=epPlatform.value;if(!confirm(`Positie ${name} op ${platform} volledig verwijderen? Dit verwijdert alle transacties voor deze positie op dit platform.`))return;const fd=new FormData();fd.append('csrf',csrf);fd.append('asset_id',epAssetId.value);fd.append('platform',platform);const r=await fetch('api.php?action=delete_position',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Verwijderen mislukt');return}editPosDialog.close();await loadDashboard();await openPositions();}
async function savePosition(){const isNew=epMode.value==='new';const assetId=isNew?epAssetSelect.value:epAssetId.value;if(!assetId){alert('Selecteer een aandeel.');return}const fd=new FormData();fd.append('csrf',csrf);fd.append('asset_id',assetId);fd.append('platform',epPlatform.value);fd.append('quantity',epQty.value);fd.append('avg_cost_native',epAvg.value);fd.append('currency',epCurrency.value);fd.append('trade_date',epDate.value);let r=await fetch('api.php?action=set_position',{method:'POST',body:fd,credentials:'same-origin'});let d=await r.json();if(!d.ok){alert(d.error||'Opslaan mislukt');return}const afd=new FormData();afd.append('csrf',csrf);afd.append('id',assetId);if(isNew){const opt=epAssetSelect.options[epAssetSelect.selectedIndex];afd.append('name',opt?.dataset.name||opt?.textContent||'Aandeel');}else{afd.append('name',epName.value);}afd.append('sector',epSector.value);afd.append('currency',epCurrency.value);r=await fetch('api.php?action=save_asset',{method:'POST',body:afd,credentials:'same-origin'});d=await r.json();if(!d.ok){alert(d.error||'Sector opslaan mislukt');return}editPosDialog.close();await loadDashboard();await openPositions();}
async function openUsers(){if(!IS_ADMIN)return;const r=await fetch('api.php?action=users',{credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Fout');return}csrf=d.csrf||csrf;let h='<table><tr><th>Gebruiker</th><th>Rol</th><th>Nieuw wachtwoord</th><th></th></tr>';(d.users||[]).forEach(u=>{h+=`<tr><td><b>${esc(u.username)}</b></td><td><select id="role_${u.id}"><option value="guest" ${u.role==='guest'?'selected':''}>Guest</option><option value="admin" ${u.role==='admin'?'selected':''}>Admin</option></select></td><td><input id="pw_${u.id}" type="password" placeholder="Leeg = behouden" style="min-width:160px;background:#07131e;color:#fff;border:1px solid var(--line);padding:8px;border-radius:8px"></td><td><button onclick="saveUser(${Number(u.id)})">Opslaan</button> <button onclick="deleteUser(${Number(u.id)})">Verwijder</button></td></tr>`;});h+='</table>';userList.innerHTML=h;userDialog.showModal();}
async function createUser(){const fd=new FormData();fd.append('csrf',csrf);fd.append('username',newUsername.value.trim());fd.append('password',newPassword.value);fd.append('role',newRole.value);const r=await fetch('api.php?action=create_user',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Aanmaken mislukt');return}newUsername.value='';newPassword.value='';newRole.value='guest';await openUsers();}
async function saveUser(id){const fd=new FormData();fd.append('csrf',csrf);fd.append('id',id);fd.append('role',document.getElementById('role_'+id).value);fd.append('password',document.getElementById('pw_'+id).value);const r=await fetch('api.php?action=update_user',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Opslaan mislukt');return}await openUsers();}
async function deleteUser(id){if(!confirm('Deze gebruiker verwijderen?'))return;const fd=new FormData();fd.append('csrf',csrf);fd.append('id',id);const r=await fetch('api.php?action=delete_user',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error||'Verwijderen mislukt');return}await openUsers();}
async function openTransactions(){const r=await fetch('api.php?action=transactions',{credentials:'same-origin'});const d=await r.json();csrf=d.csrf||csrf;let h='<table><tr><th>Datum</th><th>Aandeel</th><th>Type</th><th>Aantal</th><th>Koers</th><th>Platform</th><th></th></tr>';(d.transactions||[]).forEach(t=>h+=`<tr><td>${esc(t.trade_date)}</td><td><b>${esc(t.name)}</b><div class="small">${esc(t.ticker)}</div></td><td>${t.type==='BUY'?'Koop':'Verkoop'}</td><td>${Number(t.quantity)}</td><td>${Number(t.price_native).toLocaleString('nl-BE')} ${esc(t.currency)}</td><td>${esc(t.platform)}</td><td><button onclick="deleteTx(${Number(t.id)})">Wis</button></td></tr>`);h+='</table>';txList.innerHTML=h;txDialog.showModal();}
async function deleteTx(id){if(!confirm('Deze transactie verwijderen?'))return;const fd=new FormData();fd.append('csrf',csrf);fd.append('id',id);const r=await fetch('api.php?action=delete_transaction',{method:'POST',body:fd,credentials:'same-origin'});const d=await r.json();if(!d.ok){alert(d.error);return}await openTransactions();await loadDashboard()}
function applyTVLayout(){if(!document.body.classList.contains('tv'))return;const n=(lastDashboardData?.positions||[]).length||1;const w=window.innerWidth||1920,h=window.innerHeight||1080;const usableH=Math.max(300,h-300);let best={cols:n,rows:1,score:-Infinity};for(let cols=1;cols<=Math.min(8,n);cols++){const rows=Math.ceil(n/cols);const cardW=w/cols,cardH=usableH/rows;const ratio=cardW/Math.max(cardH,1);const score=-Math.abs(ratio-1.35)*2+Math.min(cardW/260,1)+Math.min(cardH/180,1);if(score>best.score)best={cols,rows,score};}document.documentElement.style.setProperty('--tv-cols',best.cols);document.documentElement.style.setProperty('--tv-rows',best.rows);document.body.classList.toggle('tv-dense',n>12);}
window.addEventListener('resize',()=>{if(document.body.classList.contains('tv'))applyTVLayout()});
function toggleTV(){document.body.classList.toggle('tv');if(document.body.classList.contains('tv')){document.documentElement.requestFullscreen?.();setTimeout(()=>{applyTVLayout();renderFromCache();},180);}else{document.exitFullscreen?.();document.documentElement.style.removeProperty('--tv-cols');document.documentElement.style.removeProperty('--tv-rows');setTimeout(()=>renderFromCache(),100);}}
loadDashboard();setInterval(loadDashboard,5*60*1000);
</script></body></html>
