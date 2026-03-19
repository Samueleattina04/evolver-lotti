# 🔧 Guida installazione — Ricerca Lotti Esolver

## Prerequisiti da installare sul server Laravel

### 1. Driver ODBC Microsoft per SQL Server
- Vai su: https://learn.microsoft.com/it-it/sql/connect/odbc/download-odbc-driver-for-sql-server
- Scarica **ODBC Driver 17 for SQL Server** (versione Windows)
- Installalo come un normale programma

### 2. Estensioni PHP per SQL Server
- Vai su: https://github.com/microsoft/msphpsql/releases
- Scarica il pacchetto compatibile con la tua versione PHP
  - Apri il terminale e digita `php -v` per vedere la versione PHP
  - Scarica il file .zip corrispondente (es. PHP 8.1 → `php_sqlsrv_81_ts_x64.dll`)
- Estrai i file `.dll` e copiali nella cartella delle estensioni PHP
  - Per trovare la cartella: `php -i | grep extension_dir`
- Apri `php.ini` e aggiungi:
  ```
  extension=php_sqlsrv.dll
  extension=php_pdo_sqlsrv.dll
  ```
- Riavvia il web server (Apache/Nginx)

---

## Setup del progetto Laravel

### 1. Crea il progetto Laravel (se non esiste già)
```bash
composer create-project laravel/laravel ricerca-lotti
cd ricerca-lotti
```

### 2. Copia i file forniti nelle cartelle corrette
```
.env.example                    → rinomina in .env e mettilo nella root
routes/web.php                  → routes/web.php
app/Http/Controllers/LottiController.php
app/Http/Middleware/SoloReteAziendale.php
resources/views/lotti/index.blade.php  (crea la cartella lotti se non esiste)
```

### 3. Configura il .env
Apri il file `.env` e verifica che ci siano questi valori:
```
DB_CONNECTION=sqlsrv
DB_HOST=192.168.3.210
DB_PORT=1433
DB_DATABASE=ESOLVER
DB_USERNAME=lettore
DB_PASSWORD=lettore
```

### 4. Genera la APP_KEY
```bash
php artisan key:generate
```

### 5. Registra il Middleware nel Kernel
Apri `app/Http/Kernel.php` e nel array `$middlewareAliases` aggiungi:
```php
'rete.aziendale' => \App\Http\Middleware\SoloReteAziendale::class,
```

### 6. Testa la connessione al database
```bash
php artisan tinker
>>> DB::select('SELECT TOP 1 * FROM MagProgrLotto')
```
Se restituisce dati senza errori, la connessione funziona.

### 7. Avvia il server (sviluppo)
```bash
php artisan serve
```
Poi apri http://localhost:8000

---

## Deploy in produzione (sul server aziendale)

Se il server ha già Apache o Nginx, punta il virtual host alla cartella `public/` del progetto Laravel.

### Nginx (esempio)
```nginx
server {
    listen 80;
    server_name 192.168.3.XXX;  # IP del tuo server Laravel
    root /var/www/ricerca-lotti/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Blocca accessi esterni (opzionale, in aggiunta al middleware)
    allow 192.168.3.0/24;
    deny all;
}
```

### Apache (esempio con .htaccess)
Il file `.htaccess` di Laravel nella cartella `public/` funziona già.
Aggiungi nel VirtualHost:
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/ricerca-lotti/public
    <Directory /var/www/ricerca-lotti/public>
        AllowOverride All
        Require ip 192.168.3.0/24
    </Directory>
</VirtualHost>
```

---

## Personalizzazione IP rete aziendale

Se la tua rete aziendale usa un range diverso da 192.168.3.x,
modifica il file `app/Http/Middleware/SoloReteAziendale.php`:

```php
private array $rangeConsentiti = [
    '192.168.X.',  // sostituisci con il tuo range reale
    '127.0.0.1',
    '::1',
];
```

---

## Risoluzione problemi comuni

**Errore: "could not find driver"**
→ Le estensioni php_sqlsrv non sono state installate o non sono nel php.ini

**Errore: "Login failed for user 'lettore'"**
→ Verifica le credenziali nel .env e che l'utente abbia accesso al DB da quell'IP

**Errore: "Connection timeout"**
→ Verifica che la porta 1433 di SQL Server sia aperta nel firewall del server Esolver

**La pagina mostra 403**
→ Il middleware blocca l'IP — verifica il range nel file SoloReteAziendale.php
