# Painel Administrativo Web — Gestão EPI

Este projeto é uma aplicação Web responsiva desenvolvida em **PHP puro**, HTML5, CSS3, JS e **Bootstrap 5** para atuar como módulo administrativo unificado do ecossistema de conformidade **Gestão EPI**, trabalhando em paralelo com o aplicativo Android e consumindo a mesma API REST remota.

---

## 1. ARQUITETURA DO PROJETO

O painel Web foi estruturado de forma modular e limpa, eliminando acoplamentos diretos com o banco de dados MySQL para priorizar o tráfego de dados via API:

```text
gestao_epi-web/
│
├── index.php                 # Roteador de entrada de sessão (Dashboard vs Login)
├── login.php                 # Login institucional e redefinição obrigatória de 1º acesso
├── recuperar-senha.php       # Fluxo de redefinição de senhas provisórias via API
├── logout.php                # Destruição segura de sessão PHP e token JWT no servidor
│
├── config/
│   └── api.php               # Centralização da URL base da API REST
│
├── services/
│   └── ApiService.php        # Client HTTP cURL centralizado com gestão e injeção do JWT
│
├── components/
│   ├── header.php            # Cabeçalho HTML com controle de acessos RBAC
│   ├── footer.php            # Fechamento do layout e inclusão de bibliotecas (Bootstrap/JS)
│   ├── sidebar.php           # Barra lateral com menus condicionados ao perfil
│   └── topbar.php            # Barra superior com perfil logado e alternador Dark Mode
│
├── assets/
│   ├── css/
│   │   └── style.css         # Design System unificado (Light/Dark Mode via HSL, CSS Grid)
│   └── js/
│       └── main.js           # Lógica do modo escuro, recolhimento da sidebar e máscaras (CPF, Moeda)
│
└── pages/
    ├── dashboard.php         # Painel gerencial, financeiro e gráficos (Chart.js)
    ├── funcionarios.php      # Fichas individuais, histórico de EPIs e gestão assíncrona de PINs
    ├── epis.php              # Catálogo, reajuste de preços e rastreabilidade NR-6
    ├── entregas.php          # Histórico de fornecimentos e Ficha do Termo de Ciência assinado
    ├── devolucoes.php        # Controle de posse de EPIs e retornos ao almoxarifado
    ├── relatorios.php        # Relatórios de auditoria, custos e exportações (CSV / PDF)
    ├── usuarios.php          # CRUD de operadores e controle de acesso (Exclusivo Admin)
    ├── auditoria.php         # Logs de auditoria estruturados e decodificação JSON (Exclusivo Admin)
    ├── configuracoes.php     # Meu Perfil, alteração de senha própria e status da API
    ├── 403.php               # Página de Acesso Negado (RBAC)
    └── 404.php               # Página de Recurso Não Localizado
```

---

## 2. CONFIGURAÇÃO DA API (AMBIENTE)

A URL base da API consumida pela Web está centralizada em:
[config/api.php](file:///C:/xampp/htdocs/gestao_epi-web/config/api.php)

*   **Padrão em Nuvem (Render):** O arquivo vem configurado por padrão para consumir a API de produção hospedada na Render:
    `'api_base_url' => 'https://gestao-epi-api.onrender.com/'`
*   **Migração para Local (XAMPP):** Para testar em rede local, basta alterar esse arquivo apontando para a sua API local no Apache:
    `'api_base_url' => 'http://localhost/gestao_epi_api/'`

*(Nota: O banco de dados da API local e de nuvem estão integrados em tempo real na nuvem do **Aiven**, logo as alterações na Web são propagadas imediatamente para o aplicativo Android e vice-versa).*

---

## 3. IDENTIFICAÇÃO DE LIMITAÇÃO DE ENDPOINTS E RESILIÊNCIA

Durante o mapeamento estrutural, foi constatada a ausência de registro dos endpoints `/relatorios/epis/geral` e `/relatorios/epis/consumo` no roteador do backend da API, gerando erro de "Endpoint não encontrado" no aplicativo Android ao acessar tais seções.

**Como o painel Web resolveu isso de forma resiliente?**
1.  **Módulo de Devoluções e Posse:** As devoluções e posses são controladas nas fichas individuais obtendo o histórico de entregas finalizadas do colaborador (`GET /entregas/funcionario/{id}`).
2.  **Relatórios Resilientes:**
    *   **Entregas Gerais:** Consome `/relatorios/entregas`.
    *   **EPIs Vencidos em Posse:** Consome `/relatorios/epis-vencidos` (cruza a vida útil do EPI com a data da entrega para listar descartes pendentes).
    *   **C.A. Vencidos:** Consome `/relatorios/ca-vencidos` (evitando novos fornecimentos sem C.A. válido).
    *   **Custo Mensal:** Consome `/relatorios/custo-mensal` (centro de custo por departamento).
3.  **Auditoria de Reajuste de Preço:** A tabela `Historico_Preco_EPI` é gravada automaticamente pelo model da API. A alteração detalhada (reajustes, valor antigo/novo) é rastreada na Web por meio da decodificação de logs no módulo de **Auditoria** (`GET /logs/{id}`).
4.  **Conformidade de Logs de Exportação:** Toda exportação de relatórios Web (PDF ou CSV) faz uma chamada em background registrando o evento no endpoint `/logs/registrar-exportacao` em conformidade com as regras de governança e LGPD do ecossistema.

---

## 4. CREDENCIAIS DE TESTE (PERFIS DISPONÍVEIS)

Utilize as contas homologadas abaixo para testar os diferentes fluxos operacionais e o controle de acesso dinâmico do painel:

| Perfil | Usuário (Login) | Senha de Teste | Módulos Autorizados |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin` | `admin123` | Acesso completo a todo o sistema, auditoria e usuários. |
| **Técnico SST** | `sst_tecnico` | `sst123` | Dashboard, Funcionários, EPIs (Catálogo), Entregas, Devoluções e Relatórios Operacionais. |
| **Almoxarife** | `almoxarife1` | `almox123` | Dashboard (sem custos), Funcionários, EPIs (Apenas Leitura), Entregas e Devoluções. |
| **Gestor** | `gestor_contrato` | `gestor123` | Dashboard (com custos), Funcionários (Leitura), EPIs (Leitura), Entregas e Relatórios de Custos. |
| **RH Administrativo** | `rh_admin` | `rh123` | Funcionários (CRUD Completo) e Configurações (exclusão de módulos de estoque). |
