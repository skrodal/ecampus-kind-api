# UNINETT (eCampus) Kind API

REST-API Proxy til Kind-endepunkt for uthenting av informasjon, primært knyttet til eCampus-tjenester.

APIet er skrudd sammen sånn at tilgang ikke krever brukerautentisering i DATAPORTEN, kun `client_auth`. 
Dette må selvfølgelig konfigureres i Dataportens API GK samt enhver klient som ønsker tilgang. 

## Notater

- Tilgang til endepunkt på `drift.uninett.no` er begrenset på IP.
- Bruker Dataporten GateKeeper (API må altså registreres i Dataporten Dashboard).
- Bruker AltoRouter (https://github.com/dannyvankooten/AltoRouter/blob/master/AltoRouter.php)
- Implementerer APCu for enkel caching.