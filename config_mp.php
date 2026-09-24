<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE MERCADO PAGO
 * ============================================================
 *
 * ⚠️ NUNCA compartas este archivo ni subas el token a Git.
 * ⚠️ Agrega "config_mp.php" a tu .gitignore
 */

// Access Token de Mercado Pago
// Test: empieza con TEST-
// Producción: empieza con APP_USR-
define('MP_ACCESS_TOKEN', 'APP_USR-8703024196909950-091619-0ef071f0196e1599da658517a0ef12a4-3696662814');

// URL base de tu proyecto (ajústala si cambia)
define('MP_BASE_URL', 'https://borrower-error-skating.ngrok-free.dev/verde-vida-master');
define('MP_CURRENCY', 'ARS');
// Moneda (ARS, MXN, BRL, COP, etc.)
