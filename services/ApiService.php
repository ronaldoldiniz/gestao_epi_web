<?php
declare(strict_types=1);

namespace Services;

use Exception;

class ApiService {
    private string $baseUrl;
    private string $appRoot;

    public function __construct() {
        $configFile = dirname(__DIR__) . '/config/api.php';
        if (!file_exists($configFile)) {
            throw new Exception("Arquivo de configuração da API não encontrado.");
        }
        $config = require $configFile;
        $this->baseUrl = rtrim($config['api_base_url'], '/') . '/';
        $this->appRoot = $config['app_root_url'] ?? '/gestao_epi-web/';

        // Garante que a sessão esteja iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Executa uma requisição HTTP via cURL à API REST
     */
    public function request(string $method, string $endpoint, ?array $data = null): array {
        $url = $this->baseUrl . ltrim($endpoint, '/');
        $ch = curl_init($url);

        if ($ch === false) {
            return [
                'success' => false,
                'message' => 'Não foi possível inicializar a conexão com a API.',
                'data' => null,
                'status_code' => 500
            ];
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // Injeta automaticamente o token JWT da sessão se o usuário estiver logado
        if (isset($_SESSION['token']) && $_SESSION['token'] !== '') {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout de 15 segundos
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita problemas de SSL em localhost/Render de teste

        if ($data !== null && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'message' => 'Não foi possível conectar ao servidor de API remoto. Verifique se o Apache e a API estão online. Detalhes: ' . $error,
                'data' => null,
                'status_code' => 0
            ];
        }

        $decodedData = json_decode($response, true);
        
        // Se a resposta não for um JSON válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'A API retornou uma resposta em formato inválido. Contate o administrador.',
                'data' => null,
                'status_code' => $statusCode,
                'raw_response' => $response
            ];
        }

        // Adiciona o status code HTTP retornado na resposta para facilitar checagens
        $decodedData['status_code'] = $statusCode;

        // Tratamento centralizado para sessões expiradas (401)
        if ($statusCode === 401 && $endpoint !== 'auth/login') {
            // Limpa dados de sessão
            unset($_SESSION['token']);
            unset($_SESSION['usuario']);
            
            // Redireciona para o login
            $_SESSION['error_message'] = 'Sua sessão expirou ou o acesso é inválido. Por favor, faça login novamente.';
            header('Location: ' . $this->appRoot . 'login.php');
            exit;
        }

        return $decodedData;
    }

    public function get(string $endpoint): array {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data): array {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data): array {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint): array {
        return $this->request('DELETE', $endpoint);
    }
}
