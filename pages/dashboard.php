<?php
declare(strict_types=1);

$page_title = 'Dashboard';
$active_menu = 'dashboard';
$page_roles = ['ADMINISTRADOR', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'];

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/sidebar.php';
require_once __DIR__ . '/../services/ApiService.php';

use Services\ApiService;

$api = new ApiService();

// Carrega os dados do dashboard em paralelo através da API
$resumo = [];
$custos = [];
$topEpis = [];
$pendencias = [];
$erroApi = null;

try {
    // 1. Resumo Geral
    $resumoRes = $api->get('dashboard/resumo');
    if (isset($resumoRes['success']) && $resumoRes['success']) {
        $resumo = $resumoRes['data'];
    }

    // 2. Custos Consolidados (Apenas Administrador e Gestor possuem permissão)
    if (in_array($userProfile, ['ADMINISTRADOR', 'GESTOR'], true)) {
        $custosRes = $api->get('dashboard/custos');
        if (isset($custosRes['success']) && $custosRes['success']) {
            $custos = $custosRes['data'];
        }
    }

    // 3. Top EPIs mais entregues
    $topRes = $api->get('dashboard/top-epis');
    if (isset($topRes['success']) && $topRes['success']) {
        $topEpis = $topRes['data'];
    }

    // 4. Pendências Operacionais
    $pendRes = $api->get('dashboard/pendencias');
    if (isset($pendRes['success']) && $pendRes['success']) {
        $pendencias = $pendRes['data'];
    }
} catch (Exception $e) {
    $erroApi = 'Não foi possível carregar os dados consolidados do painel: ' . $e->getMessage();
}
?>

<div id="main-content">
    <?php require_once __DIR__ . '/../components/topbar.php'; ?>
    
    <div class="content-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h3 class="fw-bold m-0" style="color: var(--color-primary);">Painel Geral</h3>
                <p class="text-muted">Indicadores e consolidação operacional do ecossistema de EPIs.</p>
            </div>
            <button onclick="window.location.reload();" class="btn btn-light border" title="Atualizar dados">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>
        </div>

        <?php if ($erroApi !== null): ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= htmlspecialchars($erroApi) ?></div>
            </div>
        <?php endif; ?>

        <!-- KPI Cards Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>Funcionários Ativos</h3>
                    <p><?= $resumo['funcionarios_ativos'] ?? 0 ?></p>
                </div>
                <div class="kpi-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>EPIs Ativos</h3>
                    <p><?= $resumo['epis_ativos'] ?? 0 ?></p>
                </div>
                <div class="kpi-icon">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>Entregas Realizadas</h3>
                    <p><?= $resumo['entregas_realizadas'] ?? 0 ?></p>
                </div>
                <div class="kpi-icon">
                    <i class="bi bi-journal-check"></i>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>Assinaturas Ativas</h3>
                    <p><?= $resumo['assinaturas_ativas'] ?? 0 ?></p>
                </div>
                <div class="kpi-icon">
                    <i class="bi bi-fingerprint"></i>
                </div>
            </div>
        </div>

        <!-- Seção de Custos (Exibido apenas para Admin e Gestor) -->
        <?php if (in_array($userProfile, ['ADMINISTRADOR', 'GESTOR'], true)): ?>
            <div class="card-custom">
                <h5 class="fw-bold mb-4" style="color: var(--color-primary);"><i class="bi bi-currency-dollar me-2"></i>Consolidação Financeira de EPIs</h5>
                <div class="row g-4">
                    <div class="col-md-4 border-end border-slate">
                        <div class="p-2 text-center text-md-start">
                            <span class="text-muted d-block mb-1" style="font-size: 13px; text-transform: uppercase;">Custo Total Acumulado</span>
                            <h2 class="fw-bold text-success m-0"><?= isset($custos['custo_total_acumulado']) ? formatarValorMonetario((float)$custos['custo_total_acumulado']) : 'R$ 0,00' ?></h2>
                            <small class="text-muted">Soma histórica de EPIs finalizados e assinados</small>
                        </div>
                    </div>
                    <div class="col-md-4 border-end border-slate">
                        <div class="p-2 text-center text-md-start">
                            <span class="text-muted d-block mb-1" style="font-size: 13px; text-transform: uppercase;">Custo Médio por Item</span>
                            <h2 class="fw-bold text-primary m-0"><?= isset($custos['custo_medio_por_item']) ? formatarValorMonetario((float)$custos['custo_medio_por_item']) : 'R$ 0,00' ?></h2>
                            <small class="text-muted">Média ponderada baseada no snapshot de preços</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 text-center text-md-start">
                            <span class="text-muted d-block mb-1" style="font-size: 13px; text-transform: uppercase;">Atualizações de Preços</span>
                            <h2 class="fw-bold text-dark m-0" style="color: var(--color-text-primary) !important;"><?= $custos['total_atualizacoes_preco'] ?? 0 ?></h2>
                            <small class="text-muted">Logs de reajustes na tabela de preços</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Gráficos Integrados -->
        <div class="row g-4">
            <!-- Gráfico: Top 5 EPIs mais fornecidos -->
            <div class="col-lg-7">
                <div class="card-custom h-100">
                    <h5 class="fw-bold mb-4" style="color: var(--color-primary);"><i class="bi bi-bar-chart-line-fill me-2"></i>EPIs mais Entregues</h5>
                    <div style="height: 300px; position: relative;">
                        <?php if (empty($topEpis)): ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                Nenhum registro de entrega encontrado.
                            </div>
                        <?php else: ?>
                            <canvas id="chartTopEpis"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gráfico/Lista: Pendências SST e Conformidade -->
            <div class="col-lg-5">
                <div class="card-custom h-100">
                    <h5 class="fw-bold mb-4" style="color: var(--color-primary);"><i class="bi bi-shield-fill-exclamation me-2"></i>Pendências e Riscos SST</h5>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 border rounded text-center" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.15) !important;">
                                <span class="text-danger d-block mb-1 fw-semibold" style="font-size: 12px; text-transform: uppercase;">C.A. Vencidos</span>
                                <h3 class="fw-bold text-danger m-0"><?= $pendencias['ca_vencidos'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center" style="background-color: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.15) !important;">
                                <span class="text-warning d-block mb-1 fw-semibold" style="font-size: 12px; text-transform: uppercase;">C.A. a Vencer (30d)</span>
                                <h3 class="fw-bold text-warning m-0"><?= $pendencias['ca_a_vencer_30_dias'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.15) !important;">
                                <span class="text-danger d-block mb-1 fw-semibold" style="font-size: 12px; text-transform: uppercase;">PIN Bloqueados</span>
                                <h3 class="fw-bold text-danger m-0"><?= $pendencias['assinaturas_bloqueadas'] ?? 0 ?></h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded text-center" style="background-color: rgba(6, 182, 212, 0.05); border-color: rgba(6, 182, 212, 0.15) !important;">
                                <span class="text-info d-block mb-1 fw-semibold" style="font-size: 12px; text-transform: uppercase;">EPIs em Posse</span>
                                <h3 class="fw-bold text-info m-0"><?= $pendencias['epis_pendentes_devolucao'] ?? 0 ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4" style="height: 180px; position: relative;">
                        <?php if (empty($pendencias)): ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                Sem indicadores de pendências.
                            </div>
                        <?php else: ?>
                            <canvas id="chartPendencias"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Renderiza Gráfico de Top EPIs
    <?php if (!empty($topEpis)): ?>
    const ctxTop = document.getElementById('chartTopEpis').getContext('2d');
    const topLabels = <?= json_encode(array_column($topEpis, 'epi_nome')) ?>;
    const topValues = <?= json_encode(array_column($topEpis, 'total_entregue')) ?>;
    
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: topLabels.map(l => l.length > 25 ? l.slice(0, 25) + '...' : l),
            datasets: [{
                label: 'Unidades Entregues',
                data: topValues,
                backgroundColor: 'rgba(48, 91, 211, 0.85)',
                borderColor: '#305BD3',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } }
            }
        }
    });
    <?php endif; ?>

    // 2. Renderiza Gráfico de Rosca de Pendências
    <?php if (!empty($pendencias)): ?>
    const ctxPend = document.getElementById('chartPendencias').getContext('2d');
    new Chart(ctxPend, {
        type: 'doughnut',
        data: {
            labels: ['CA Vencidos', 'CA a Vencer (30d)', 'PIN Bloqueados'],
            datasets: [{
                data: [
                    <?= $pendencias['ca_vencidos'] ?? 0 ?>,
                    <?= $pendencias['ca_a_vencer_30_dias'] ?? 0 ?>,
                    <?= $pendencias['assinaturas_bloqueadas'] ?? 0 ?>
                ],
                backgroundColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#64748b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, font: { family: 'Outfit' } }
                }
            },
            cutout: '65%'
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
