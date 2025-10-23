# CC Admin Popover Row Actions

Trasforma le **row-actions** delle WP List Tables in un **popover** pulito che si apre dal badge ID ( o link configurabile ) .  
Progettato come **MU-plugin** con asset singleton , flag **bilaterali** ( `define` + `filters` ) e comportamento **accessibile** .

## ✨ Features
- Popover elegante con **Modifica** , **Modifica rapida** , **Cestina** .
- Flag bilaterali : configura in `wp-config.php` , ma i **filter vincono** sempre .
- **Nascondi "Modifica rapida"** con un toggle .
- **On-hover** opzionale oltre al click .
- Caricamento **una sola volta** e solo nelle schermate `edit.php` dei post type desiderati .
- CSS critico inline per performance , JS modulare in tre fasi : `pre` → `first` → `init` .
- A11y : `aria-haspopup` , `aria-expanded` , chiusura su `ESC` e click fuori .

## 📦 Installazione
Copia la cartella in `wp-content/mu-plugins/` come da struttura :

```

mu-plugins/
├── codecorn/
│   └── admin-popover-row-actions/
│       └── assets/
│           ├── css/
│           │   └── critical.css
│           └── js/
│               ├── pre.js
│               ├── first.js
│               └── init.js
└── cc-admin-popover-row-actions.php

````

Non richiede attivazione . I MU-plugin sono caricati automaticamente .

## ⚙️ Configurazione rapida

In `wp-config.php`  ( opzionale ) :

```php
define('CC_APRA_ENABLED', true);
define('CC_APRA_ON_HOVER', true);
define('CC_APRA_HIDE_QUICK_EDIT', true);
define('CC_APRA_POST_TYPES', 'wp_ar_clients'); // CSV
````

### Filters  → hanno priorità sui define

```php
add_filter('cc_apra_enabled', function($v){ return $v; });
add_filter('cc_apra_on_hover', '__return_true');
add_filter('cc_apra_hide_quick_edit', '__return_true');
add_filter('cc_apra_post_types', function($arr){ $arr[]='post'; return array_unique($arr); });
```

## 🧩 API JS minimale

* Namespace globale `CC_APRA_NS` con `core.buildPopover( $td , opts )` e `core.togglePopover( $td , force , opts )` .

## 🛡️ Compatibilità

* Testato su WP 6.x , jQuery in admin .
* Non modifica markup core , clona soltanto le `row-actions` della cella .

## 📝 License

MIT — © CodeCorn™ Technology