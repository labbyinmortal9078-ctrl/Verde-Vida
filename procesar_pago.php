<?php
session_start();
require_once 'vendor/autoload.php';
include_once("conexion.php");
include_once("alertas.php");
include_once("carrito_funciones.php");
include_once("config_mp.php");

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$items = obtenerItemsCarrito($conex, $usuario_id);

if (empty($items)) {
    mostrarNotificacion('error', 'Tu carrito está vacío');
    header("Location: carrito.php");
    exit();
}

MercadoPagoConfig::setAccessToken(trim(MP_ACCESS_TOKEN));
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

$base = rtrim(MP_BASE_URL, '/');
$back_urls = [
    'success' => $base . '/pago_exitoso.php',
    'failure' => $base . '/pago_fallido.php',
    'pending' => $base . '/pago_pendiente.php'
];

$items_mp = [];
foreach ($items as $item) {
    $items_mp[] = [
        'id'          => (string) $item['inventario_id'],
        'title'       => mb_substr($item['nombre_comun'], 0, 250),
        'description' => mb_substr($item['nombre_cientifico'] ?? '', 0, 250),
        'quantity'    => (int) $item['cantidad'],
        'unit_price'  => (float) $item['precio_unitario'],
        'currency_id' => MP_CURRENCY
    ];
}

try {
    $client = new PreferenceClient();

    $preferenceData = [
        'items' => $items_mp,
        'back_urls' => $back_urls,
        'auto_return' => 'approved',
        'external_reference' => 'user_' . $usuario_id . '_' . time(),
        'statement_descriptor' => 'VERDE VIDA',
        'binary_mode' => false
    ];

    $preference = $client->create($preferenceData);
    $_SESSION['mp_preference_id'] = $preference->id;

    $url_pago = $preference->sandbox_init_point ?? $preference->init_point;

    header("Location: " . $url_pago);
    exit();

} catch (\MercadoPago\Exceptions\MPApiException $e) {
    $response = $e->getApiResponse();
    $apiMessage = $response->getContent()['message'] ?? 'Error desconocido';
    $causes = $response->getContent()['cause'] ?? [];

    error_log("MP Api Error: $apiMessage | " . json_encode($causes));
    mostrarNotificacion('error', 'Error MP: ' . $apiMessage);
    header("Location: carrito.php");
    exit();

} catch (\Exception $e) {
    error_log("Error: " . $e->getMessage());
    mostrarNotificacion('error', 'Error inesperado');
    header("Location: carrito.php");
    exit();
}