<?php
declare(strict_types=1);

/**
 * Proxy Server-Side para chamadas da API REST na nuvem.
 * 
 * Evita bloqueios de CORS Preflight (OPTIONS) no navegador do usuário realizando
 * as requisições de forma direta de servidor para servidor via cURL PHP.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Validação de sessão do painel administrativo
if (!isset($_SESSION['token']) || $_SESSION['token'] === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada ou não autorizada.']);
    exit;
}

require_once __DIR__ . '/../services/ApiService.php';
use Services\ApiService;

$acao = $_GET['acao'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido ou ausente.']);
    exit;
}

try {
    $api = new ApiService();

    switch ($acao) {
        case 'funcionario':
            $res = $api->get("funcionarios/{$id}");
            echo json_encode($res);
            break;
            
        case 'assinatura':
            $res = $api->get("assinaturas/funcionario/{$id}");
            echo json_encode($res);
            break;
            
        case 'entregas':
            $res = $api->get("entregas/funcionario/{$id}");
            echo json_encode($res);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação do proxy não suportada.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no processamento do proxy: ' . $e->getMessage()]);
}
