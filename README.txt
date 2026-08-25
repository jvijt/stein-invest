SHARES.VIJT.BE - Portfolio Dashboard v2 (PHP + MySQL)
=====================================================

Vereisten
---------
- PHP 8.1 of nieuwer
- MySQL/MariaDB
- PHP extensies: PDO MySQL, cURL, JSON, OpenSSL
- HTTPS aanbevolen
- Apache (.htaccess meegeleverd). Op Nginx moet je config.php zelf afschermen.

Installatie
-----------
1. Maak een lege MySQL database + gebruiker aan.
2. Upload alle bestanden uit deze map naar de document root van shares.vijt.be.
3. Open https://shares.vijt.be/install.php
4. Vul databasegegevens + beheerderswachtwoord in.
5. Installer maakt de tabellen en config.php aan.
6. Log in op https://shares.vijt.be/login.php
7. Controleer de vooraf ingevulde ING-posities in Beheer > Transacties.
   De data 24-08-2026 zijn placeholders omdat de echte aankoopdatums niet bekend zijn.
8. Voeg Saxo-transacties toe via Beheer.

Koersbron
---------
De app gebruikt gratis Yahoo Finance chart endpoints zonder API-key.
Dit is een onofficiële bron en kan ooit wijzigen. De app cachet koersen 5 minuten.
15 minuten vertraging is voor deze toepassing prima.

Beurs open/gesloten
--------------------
De rode/groene status wordt afgeleid uit Yahoo Finance marketState wanneer beschikbaar.
REGULAR = groen (open). CLOSED/PRE/POST/etc. = rood (gesloten).
Daarmee worden ook feestdagen doorgaans correct behandeld.

Mobiel
------
De layout is responsive. Op smartphone worden kaarten onder elkaar getoond.
Er is een fullscreen TV-modus via de knop rechtsboven.

Belangrijk
----------
- Geef FTP/MySQL wachtwoorden nooit door in chat.
- Verwijder install.php na succesvolle installatie als extra voorzorg.
- Maak geregeld een database-backup.


NIEUW IN V3
------------
- Posities rechtstreeks aanpassen via knop "Posities" zonder koop/verkoop in te voeren.
- Aantal, gemiddelde aankoopprijs, platform en sector corrigeren.
- Sector per aandeel bewerkbaar.
- Taartgrafiek met actuele marktwaarde per sector + legende.
- Vooraf ingevulde sectoren:
  AB InBev = Consumentengoederen
  ASML = Technologie
  Barco = Technologie
  NVIDIA = Technologie
  Vanguard S&P 500 = Brede ETF

ALS V2 AL GEINSTALLEERD IS
--------------------------
1. Upload de nieuwe bestanden over de bestaande bestanden heen, maar BEWAAR je bestaande config.php.
2. Open https://shares.vijt.be/upgrade_v3.php terwijl je ingelogd bent.
3. Klik "Upgrade uitvoeren".
4. Verwijder upgrade_v3.php daarna via FTP.


NIEUW IN V4
------------
- Valutakeuze bij nieuwe transacties en bij het rechtstreeks aanpassen van posities.
- Ondersteunde valuta: EUR, USD, GBP, CHF, SEK, NOK, DKK, JPY, CAD, AUD, PLN, CZK, HUF.
- NVIDIA kan dus met een gemiddelde aankoopprijs in USD worden ingevoerd/bewerkt.
- Nieuwe effecten worden gezocht op naam, ISIN of ticker.
- Autocomplete gebruikt de gratis Yahoo Finance zoekfunctie.
- Een effect moet geselecteerd en daarna server-side gevalideerd worden voordat het kan worden opgeslagen.
- ETF's worden eveneens ondersteund.
- ISIN-zoekopdrachten worden naar een Yahoo-ticker vertaald wanneer de gratis bron het ISIN herkent.

UPGRADE V3 -> V4
----------------
1. Maak voor alle zekerheid een backup van config.php en je MySQL database.
2. Upload de v4-bestanden over de v3-bestanden.
3. Laat je bestaande config.php staan; die zit niet in dit ZIP-bestand.
4. De cache-map hoeft niet te worden overschreven.
5. Open https://shares.vijt.be/upgrade_v4.php terwijl je ingelogd bent.
6. Klik "Upgrade uitvoeren".
7. Verwijder upgrade_v4.php via FTP.


NIEUW IN V5
------------
- Meerdere gebruikersaccounts.
- Twee rollen:
  * Guest: alleen dashboard bekijken, niets toevoegen, wijzigen of verwijderen.
  * Admin: volledige toegang, hetzelfde niveau als de hoofdgebruiker.
- Admins krijgen een knop "Gebruikers" om accounts aan te maken, rollen te wijzigen, wachtwoorden te resetten en accounts te verwijderen.
- Een admin kan zijn eigen account niet verwijderen of zijn eigen adminrechten afnemen.

UPGRADE V4 -> V5
----------------
1. Maak een backup van config.php en liefst ook van de database.
2. Upload alle v5-bestanden over v4 heen.
3. Behoud je bestaande config.php.
4. Cache-map hoeft niet te worden overschreven.
5. Open https://shares.vijt.be/upgrade_v5.php terwijl je bent ingelogd.
6. Klik "Upgrade uitvoeren".
7. Je huidige account wordt automatisch Admin.
8. Verwijder upgrade_v5.php via FTP.


NIEUW IN V5.2
-------------
- De positielijst blijft open nadat je een positie hebt aangepast.
- Daardoor kun je snel meerdere posities na elkaar corrigeren.
- Posities worden in Beheer altijd apart per platform weergegeven.
  Voorbeeld:
    AB InBev | ING
    AB InBev | Saxo
- Op het hoofddashboard worden dezelfde effecten over alle platformen automatisch samengevoegd.
- Totale waarde, winst/verlies, sectorverdeling en grootste positie gebruiken dus de gecombineerde positie.
- Nieuwe knop "+ Positie toevoegen" in het positiebeheer:
  hiermee kun je een reeds gekend aandeel rechtstreeks ook op een tweede platform toevoegen zonder een kooptransactie te moeten creëren.

UPGRADE V5.1 -> V5.2
--------------------
1. Bewaar config.php.
2. Upload de bestanden uit v5.2 over de bestaande bestanden.
3. Cache-map hoeft niet overschreven te worden.
4. Open https://shares.vijt.be/upgrade_v5_2.php terwijl je als Admin ingelogd bent.
5. Klik Upgrade uitvoeren.
6. Verwijder upgrade_v5_2.php via FTP.


NIEUW IN V5.3
-------------
- In het bewerkingsscherm van een bestaande positie staat nu "Positie verwijderen".
- Verwijderen gebeurt per platform.
  Voorbeeld: AB InBev op ING verwijderen laat AB InBev op Saxo ongemoeid.
- Na verwijderen blijft de positielijst open en wordt het dashboard automatisch bijgewerkt.
- De knop verschijnt niet bij het toevoegen van een nieuwe positie.
- Er is een bevestigingsmelding om per ongeluk verwijderen te vermijden.

UPGRADE V5.2 -> V5.3
--------------------
1. Behoud config.php.
2. Upload v5.3 over de bestaande bestanden.
3. Cache-map hoeft niet overschreven te worden.
4. Open https://shares.vijt.be/upgrade_v5_3.php als Admin.
5. Klik Upgrade uitvoeren.
6. Verwijder upgrade_v5_3.php via FTP.


NIEUW IN V5.4
-------------
- Admin kan via 'Sectoren' eigen sectoren toevoegen, bv. AI en Halfgeleiders.
- Eigen sectoren blijven in MySQL bewaard en verschijnen in de keuzelijsten.
- Bij het selecteren van een nieuw aandeel geeft het dashboard automatisch een sectoradvies.
- Het advies is niet verplicht: de admin kan het altijd handmatig wijzigen.
- Voor gespecialiseerde bekende chipbedrijven wordt 'Halfgeleiders' voorgesteld.
- Voor gespecialiseerde AI-bedrijven kan 'AI' worden voorgesteld.
- Bestaande algemene categorieën blijven beschikbaar.

UPGRADE V5.3 -> V5.4
--------------------
Behoud config.php, upload v5.4 over de bestaande bestanden, open
https://shares.vijt.be/upgrade_v5_4.php als Admin, voer de upgrade uit
en verwijder daarna upgrade_v5_4.php via FTP.


NIEUW IN V5.5
-------------
- Onder 'Totale waarde' staat nu in kleiner lettertype 'Geïnvesteerd: € ...'.
- Zo zie je onmiddellijk hoeveel de huidige portefeuille boven of onder de totale kostprijs staat.
- De bestaande winst/verlies-tegel blijft behouden voor het exacte verschil in EUR en %.

UPGRADE V5.4 -> V5.5
--------------------
Behoud config.php, upload v5.5 over de bestaande bestanden, open
https://shares.vijt.be/upgrade_v5_5.php als Admin, voer de upgrade uit
en verwijder daarna upgrade_v5_5.php via FTP.


NIEUW IN V5.6
-------------
- Beurs open/gesloten-status hersteld.
- De app gebruikt nu eerst de concrete currentTradingPeriod start/eindtijd van de koersbron.
- Als die ontbreekt, gebruikt de app een eigen fallback:
  Euronext Brussel/Amsterdam en Xetra: 09:00-17:30 lokale tijd, maandag-vrijdag.
  Nasdaq/NYSE: 09:30-16:00 lokale tijd, maandag-vrijdag.
- Hierdoor kan een verouderde Yahoo marketState niet langer een geopende Belgische beurs foutief als 'gesloten' tonen.


NIEUW IN V5.7
-------------
- Desktop/tablet: keuze uit 3, 4, 5 of 6 kolommen voor de positiekaarten.
- Mobiel: kolomkeuze wordt verborgen en de layout past zich automatisch aan.
- Sorteeropties:
  * % van portefeuille (hoog-laag / laag-hoog)
  * winst/verlies in %
  * winst/verlies in EUR
  * actuele positiewaarde
  * dagprestatie in %
  * naam A-Z / Z-A
- Op elke kaart staat nu ook het gewicht van die positie in de totale portefeuille.
- Sorteren verandert alleen de weergave, niet de gegevens.

UPGRADE V5.6 -> V5.7
--------------------
Behoud config.php, upload v5.7 over de bestaande bestanden, open
https://shares.vijt.be/upgrade_v5_7.php als Admin, voer de upgrade uit
en verwijder daarna upgrade_v5_7.php via FTP.


NIEUW IN V5.8
-------------
- Fullscreen TV-modus probeert ALLE posities op één scherm te tonen zonder scrollbalk.
- Het dashboard kiest automatisch het aantal kolommen en rijen op basis van:
  * aantal posities
  * schermbreedte
  * schermhoogte
  * verhouding van het scherm
- In fullscreen worden kaarten compacter weergegeven.
- Bij veel posities (>12) wordt de sectorgrafiek tijdelijk verborgen en wordt de kaartweergave extra compact.
- Buiten fullscreen blijven de handmatige kolomkeuze en sorteermogelijkheden gewoon behouden.

UPGRADE V5.7 -> V5.8
--------------------
Behoud config.php, upload v5.8 over de bestaande bestanden, open
https://shares.vijt.be/upgrade_v5_8.php als Admin, voer de upgrade uit
en verwijder daarna upgrade_v5_8.php via FTP.


NIEUW IN V5.9
-------------
- Mobiele filterbalk volledig responsive gemaakt.
- Geen horizontale pagina-scroll meer door filters.
- Tot 680 px: filters worden netjes over maximaal twee kolommen verdeeld.
- Tot 430 px: filters staan onder elkaar over de volledige schermbreedte.
- Kolommenkeuze blijft verborgen op mobiel.
- Selectvelden en knoppen kunnen niet meer buiten het scherm duwen.


NIEUW IN V5.10
--------------
- Goudkleurig punt op de koerslijn voor elk aankoopmoment dat binnen de zichtbare grafiekperiode valt.
- Meerdere aankopen = meerdere punten.
- Bij samengevoegde ING + Saxo-posities worden koopmomenten van beide platformen getoond.
- Goudkleurige stippellijn = gemiddelde aankoopprijs.
- Daardoor zie je onmiddellijk of de huidige koers boven of onder je gemiddelde aankoopprijs ligt.
- Koopmomenten buiten de getoonde grafiekperiode worden niet kunstmatig op de grafiek geplaatst.


NIEUW IN V5.12
--------------
- Mobiel: alleen het sorteerveld blijft zichtbaar in de filterbalk.
- Platformfilter, kolomkeuze, vernieuwen en beheerknoppen worden op mobiel verborgen.
- Geen horizontaal scrollen meer door filtervelden.
- Mobiel: alle positiekaarten staan altijd onder elkaar in één kolom.
- Ook KPI's en sectorblok worden mobiel enkelvoudig gestapeld.


NIEUW IN V5.13
--------------
- Correctie van de platformverdeling onder 'Totale waarde' wanneer 'Alle platformen' geselecteerd is.
- De totale portefeuillewaarde en totale investering waren al correct.
- De fout zat in de onderverdeling ING/Saxo: posities die op beide platformen voorkwamen werden voorheen 50/50 verdeeld.
- Nu wordt de platformwaarde exact berekend op basis van het werkelijk aantal aandelen per platform.
