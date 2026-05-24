# La Compagnia del Gluten Free — WordPress + WooCommerce

E-commerce per Carmelo Lo Porto. **Stack WordPress + WooCommerce** (pivot dal custom Node.js per essere competitivi).

Tutti i prodotti sono **senza glutine e senza lattosio**, prodotti in laboratorio dedicato.

## Stack

- **WordPress** 6.6 + **PHP 8.2** + **Apache** (immagine Docker ufficiale)
- **MySQL** 8.0
- **Docker Compose** (locale) + **Coolify** (deploy Hetzner)
- Tema parent: **Astra** (installato via WP) · child custom in `wp-content/themes/lcgf-child/`

## Plugin da installare (manuale, dopo primo accesso WP)

| Plugin | Scopo |
|---|---|
| **WooCommerce** | E-commerce core |
| **WooCommerce Stripe Gateway** | Carte + Klarna + Apple/Google Pay |
| **WooCommerce PayPal Payments** | PayPal Smart Buttons |
| **Polylang** | Multilingue IT/EN/DE/FR (gratuito; alternativa premium WPML €99/anno) |
| **Yoast SEO** | SEO on-page + sitemap |
| **Complianz** o **Iubenda** | Cookie banner + GDPR |
| **WPForms Lite** | Form contatti |
| **WP Mail SMTP** | Invio email via Gmail EMC |
| **Wordfence Security** | Sicurezza base |
| **UpdraftPlus** | Backup automatici |

## Setup locale

```bash
# 1. copia env
cp .env.example .env
# edita .env e cambia le password

# 2. avvia stack
docker compose up -d

# 3. apri WP
# → http://localhost:8080

# 4. (opzionale) phpmyadmin per dev
docker compose --profile dev up -d phpmyadmin
# → http://localhost:8081
```

## Setup iniziale WordPress

1. Apri `http://localhost:8080` → wizard installazione WP (italiano)
2. Crea utente admin: `admin@lacompagniadelglutenfree.it` / password sicura
3. **Tema**: Aspetto → Temi → cerca "Astra" → installa + attiva il parent → poi attiva il **child `La Compagnia del Gluten Free`**
4. **WooCommerce**: Plugin → Aggiungi → cerca "WooCommerce" → installa + attiva → segui wizard (negozio, valuta EUR, IVA 22%, spedizione)
5. **Import prodotti**: WooCommerce → Prodotti → Importa → upload `imports/products.csv` → mappa colonne → completa import. Le immagini sono già nel tema (`/wp-content/themes/lcgf-child/assets/products/`)
6. **Polylang**: installa → setup lingue IT (default) + EN + DE + FR
7. **Pagamenti**: WooCommerce → Impostazioni → Pagamenti → attiva Stripe + PayPal (chiavi sandbox per test, live al go-live)
8. **GDPR**: installa Complianz → wizard cookie consent
9. **SMTP**: WP Mail SMTP → wizard → Gmail EMC `emcdigitalsolution@gmail.com` con App Password

## Deploy Coolify (Hetzner 49.13.173.127)

1. Coolify dashboard → New Resource → Docker Compose
2. Source: GitHub repo `lacompagniadelglutenfree-wp`
3. Copia il `docker-compose.yml` (Coolify gestisce reverse proxy + SSL Let's Encrypt automatici)
4. **Environment** Coolify: imposta tutte le var da `.env.example` con valori produzione
5. **Domain**: `lacompagniadelglutenfree.it` (DNS A record → 49.13.173.127)
6. **Persistent storage**: assicurarsi che i volumi `wp_data`, `db_data`, `./wp-content/uploads` siano persistenti
7. Deploy → attendere health check OK → primo accesso da dominio finale

## Migrazione da locale a produzione

```bash
# dump locale
docker compose exec db mysqldump -u root -p$MYSQL_ROOT_PASSWORD lcgf_wp > backup.sql

# upload uploads/
tar -czf uploads.tar.gz wp-content/uploads/

# in produzione (via SSH Coolify)
# → ripristina DB e wp-content/uploads/
# → cerca/sostituisci URL: WP-CLI search-replace 'http://localhost:8080' 'https://lacompagniadelglutenfree.it' --all-tables
```

## Materiali cliente (in `imports/raw/`, gitignored)

- Catalogo prodotti
- Foto prodotti originali (già pulite e nel tema)
- Contatti: Carmelo +39 327 699 9897 · Gianluca +39 349 565 8876 · Gaetano +39 351 358 2074

## Servizi separati EMC (preventivi a parte)

- **Social Media Management**: pipeline interna `social-image-generator`
- **Google Business Profile + Maps**: setup post-launch
- **Manutenzione evolutiva**: canone mensile 70 €

## File chiave

- `docker-compose.yml` — stack WP + MySQL (+ phpmyadmin opzionale dev)
- `.env.example` — template credenziali
- `php/uploads.ini` — limiti upload (128MB)
- `wp-content/themes/lcgf-child/` — tema child Astra personalizzato (palette, font, badge GF/LF, WhatsApp float, footer EMC)
- `imports/products.csv` — 13 prodotti pronti per WooCommerce import
- `wp-content/themes/lcgf-child/assets/products/` — 13 foto prodotto già pulite (branding terzi rimosso)

## Contatti EMC

CEO: Enrico Maria Caruso · `emcdigitalsolution@gmail.com` · [emcdigitalsolutions.it](https://www.emcdigitalsolutions.it)
