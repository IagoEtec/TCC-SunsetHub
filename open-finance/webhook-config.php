<?php
require_once __DIR__ . '/../back-end/seguranca.php';
$raw = file_get_contents('php://input');
$esperado = afdeConfig('PLUGGY_WEBHOOK_TOKEN', '');
$recebido = $_SERVER['HTTP_X_PLUGGY_WEBHOOK_TOKEN'] ?? '';
if ($esperado === '' || !hash_equals($esperado, $recebido)) {
    http_response_code(401); exit(json_encode(['error' => 'unauthorized']));
}
$event = json_decode($raw, true);
if (!is_array($event)) { http_response_code(400); exit(json_encode(['error' => 'invalid payload'])); }
$eventType = preg_replace('/[^a-zA-Z0-9_\/-]/', '', (string)($event['event'] ?? 'unknown'));
$eventId = preg_replace('/[^a-zA-Z0-9_.:-]/', '', (string)($event['eventId'] ?? 'none'));
error_log("[pluggy-webhook] event={$eventType} id={$eventId}");
// O processamento deve ser idempotente e implementado aqui após definir o
// mapeamento de eventos; eventos desconhecidos são aceitos sem executar ações.
http_response_code(200);
echo json_encode(['received' => true]);
