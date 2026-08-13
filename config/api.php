<?php
declare(strict_types=1);

/**
 * Configuração da URL Base da API do ecossistema Gestão EPI.
 * 
 * Por padrão, conecta-se à API em nuvem na Render.
 * Para testar no ambiente local do XAMPP, altere para: 'http://localhost/gestao_epi_api/'
 */
return [
    'api_base_url' => 'https://gestao-epi-api.onrender.com/',
    
    // Raiz da aplicação Web-PHP.
    // XAMPP local (padrão): '/gestao_epi-web/'
    // Render (nuvem): '/'
    'app_root_url' => '/'
];
