# 🧭 CC Admin Popover Row Actions

<!-- Badges -->
<p align="left">

[![@codecorn/euro-plate-validator](https://img.shields.io/badge/CODECORN-EURO--PLATE--VALIDATOR-gold?style=for-the-badge&logo=vercel)](https://img.shields.io/badge/code%20style-WordPress-21759b)
[![@codecorn/euro-plate-validator](https://img.shields.io/badge/code%20style-WordPress-21759b?style=for-the-badge&logo=wordpress)](https://img.shields.io/badge/code%20style-WordPress-21759b)
[![GitHub stars](https://img.shields.io/github/stars/CodeCornTech/euro-plate-validator?style=for-the-badge&logo=github)](https://img.shields.io/badge/code%20style-WordPress-21759b)

  <!-- Version & License -->
  <a href="https://img.shields.io/badge/code%20style-WordPress-21759b/releases">
    <img alt="Version" src="https://img.shields.io/badge/version-1.0.1-blue.svg">
  </a>
  <a href="https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions/blob/main/LICENSE">
    <img alt="License" src="https://img.shields.io/github/license/CodeCornTech/mu-cc-admin-popover-row-actions.svg">
  </a>
  <img alt="CI" src="https://img.shields.io/github/actions/workflow/status/CodeCornTech/mu-cc-admin-popover-row-actions/ci.yml?branch=main">
  <img alt="Size" src="https://img.shields.io/badge/assets-size%20tiny-lightgrey">

  <!-- WordPress & PHP targets -->
  <img alt="WordPress" src="https://img.shields.io/badge/wordpress-6.x-tested-blue">
  <img alt="Requires at least" src="https://img.shields.io/badge/requires%20WP-5.8+-informational">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777bb3">

  <!-- Plugin nature -->
  <img alt="Type" src="https://img.shields.io/badge/type-MU--plugin-ff6a00">
  <img alt="Scope" src="https://img.shields.io/badge/scope-admin%20only-7952b3">

  <!-- Qualities -->
  <img alt="A11y" src="https://img.shields.io/badge/a11y-friendly-brightgreen">
  <img alt="JS pipeline" src="https://img.shields.io/badge/js-pre%20%E2%86%92%20first%20%E2%86%92%20init-2ea44f">
  <img alt="Filters win" src="https://img.shields.io/badge/config-filters%20%3E%20defines-0f766e">

  <!-- Community -->
  <a href="https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions/issues">
    <img alt="Issues" src="https://img.shields.io/github/issues/CodeCornTech/mu-cc-admin-popover-row-actions">
  </a>
  <a href="https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions/pulls">
    <img alt="PRs" src="https://img.shields.io/badge/PRs-welcome-success">
  </a>
</p>

---

Trasforma le **row-actions** delle WP List Tables in un **popover** elegante e accessibile che si apre dal badge ID ( o da un link personalizzato ).  
Pensato come **MU-plugin** con asset singleton , flag **bilaterali** (`define` + `filters`) e caricamento intelligente solo dove serve .

---

## ✨ Caratteristiche principali

-   🎯 Popover compatto con **Modifica** , **Modifica rapida** , **Cestina** .
-   ⚖️ Flag bilaterali : configurabili via `wp-config.php` oppure via `add_filter()` ( **i filter vincono sempre** ).
-   🚫 Opzione per **nascondere "Modifica rapida"**.
-   🪄 Modalità **on-hover** opzionale oltre al click.
-   💡 Caricamento **una sola volta** e solo in `edit.php` dei post type indicati.
-   ⚡ CSS critico inline , JS modulare in 3 fasi: `pre` → `first` → `init`.
-   ♿ Accessibilità migliorata: `aria-haspopup`, `aria-expanded`, chiusura su `ESC` o click esterno.

---

## 🧩 Struttura e installazione

Copia la struttura seguente in `wp-content/mu-plugins/`:

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

```

> 💡 I MU-plugin vengono caricati automaticamente: **non serve attivarli manualmente**.

---

## ⚙️ Configurazione rapida

### 🔸 Via `wp-config.php` (facoltativo)

```php
define('CC_APRA_ENABLED', true);               // attiva / disattiva globalmente
define('CC_APRA_ON_HOVER', true);              // abilita apertura al passaggio
define('CC_APRA_HIDE_QUICK_EDIT', true);       // nasconde "Modifica rapida"
define('CC_APRA_POST_TYPES', 'wp_ar_clients'); // CSV semplice
define('CC_APRA_SELECTORS', '{"triggerLink":".my-badge"}'); // JSON opzionale
```

> 🧱 `CC_APRA_DEFAULT_POST_TYPES` = `['wp_ar_clients', 'wp_ar_requests']`
> usato come fallback se non definito diversamente .

---

### 🔹 Via filtri (vincono sui define)

```php
add_filter('cc_apra_enabled', function( $v ){ return true; });
add_filter('cc_apra_on_hover', '__return_true');
add_filter('cc_apra_hide_quick_edit', '__return_false');

add_filter('cc_apra_post_types', function( $types ){
    $types[] = 'post';
    return array_values( array_unique( $types ) );
});

add_filter('cc_apra_selectors', function( $sel, $screen ){
    if ( $screen && $screen->post_type === 'product' ) {
        $sel['triggerLink'] = 'a.product_id_badge';
    }
    return $sel;
}, 10, 2);
```

---

## 🧠 Flags disponibili

| Nome flag / filtro                                    | Tipo                                              | Default                              | Descrizione                              |
| ----------------------------------------------------- | ------------------------------------------------- | ------------------------------------ | ---------------------------------------- |
| `CC_APRA_ENABLED` / `cc_apra_enabled`                 | `bool`                                            | `true`                               | Abilita il popover                       |
| `CC_APRA_ON_HOVER` / `cc_apra_on_hover`               | `bool`                                            | `true`                               | Attiva apertura anche al passaggio mouse |
| `CC_APRA_HIDE_QUICK_EDIT` / `cc_apra_hide_quick_edit` | `bool`                                            | `false`                              | Nasconde l’azione “Modifica rapida”      |
| `CC_APRA_POST_TYPES` / `cc_apra_post_types`           | `array<string>`                                   | `['wp_ar_clients','wp_ar_requests']` | Post type supportati                     |
| `CC_APRA_SELECTORS` / `cc_apra_selectors`             | `array{table,primaryCell,triggerLink,rowActions}` | default WP admin                     | Selettori CSS personalizzabili           |

---

## 🧩 API JS

Namespace globale: `CC_APRA`

```js
// Crea un popover manualmente
CC_APRA.core.buildPopover(tdElement, opts);

// Toggle aperto/chiuso
CC_APRA.core.togglePopover(tdElement, true, opts);
```

Gli script sono caricati in ordine:
`pre.js` → `first.js` → `init.js`
Il CSS critico è iniettato inline (`critical.css`).

---

## 🧪 Hook e filtri interni

| Hook / Filtro             | Descrizione                            | Argomenti                               |
| ------------------------- | -------------------------------------- | --------------------------------------- |
| `cc_apra_enabled`         | Abilita / disabilita plugin in runtime | `(bool $enabled, WP_Screen $screen)`    |
| `cc_apra_on_hover`        | Attiva popover anche su hover          | `(bool $hover, WP_Screen $screen)`      |
| `cc_apra_hide_quick_edit` | Nasconde “Modifica rapida”             | `(bool $hide, WP_Screen $screen)`       |
| `cc_apra_post_types`      | Limita ai post type desiderati         | `(array $types, WP_Screen $screen)`     |
| `cc_apra_selectors`       | Personalizza i selettori CSS           | `(array $selectors, WP_Screen $screen)` |
| `cc_apra_critical_css`    | Sovrascrive il CSS inline              | `(string $css)`                         |

---

## 🧱 Architettura tecnica

-   Singleton d’enqueue → evita doppio caricamento JS/CSS.
-   Fallback sicuro per i define assenti.
-   Filtri sempre vincenti per contesto dinamico.
-   Normalizzazione CSV e JSON (post_types e selectors).
-   Accessibilità nativa (`aria` + chiusura su `ESC`).

---

## 🧩 Compatibilità

-   WordPress 6.x+
-   jQuery incluso in admin
-   PHP 7.4+ (compatibile 8.x)

---

## 📜 Licenza

MIT License — © [CodeCorn™ Technology](https://github.com/CodeCornTech)

---

## 🌐 Link utili

-   🔗 Repository: [https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions](https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions)
-   🧑‍💻 Autore: [CodeCorn™](https://github.com/CodeCornTech)
