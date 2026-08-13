<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['usuario'] ?? null;
$perfil = $user['usu_perfil'] ?? '';
$active = $active_menu ?? '';

// Função auxiliar para verificar permissão do menu
function hasPermission(string $menuName, string $perfil): bool {
    $permissions = [
        'dashboard'   => ['ADMINISTRADOR', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'],
        'funcionarios'=> ['ADMINISTRADOR', 'RH_ADMINISTRATIVO', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'],
        'epis'        => ['ADMINISTRADOR', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'],
        'entregas'    => ['ADMINISTRADOR', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'],
        'devolucoes'  => ['ADMINISTRADOR', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR'],
        'relatorios'  => ['ADMINISTRADOR', 'TECNICO_SST', 'GESTOR'],
        'usuarios'    => ['ADMINISTRADOR'],
        'auditoria'   => ['ADMINISTRADOR'],
        'configuracoes'=> ['ADMINISTRADOR', 'RH_ADMINISTRATIVO', 'TECNICO_SST', 'ALMOXARIFE_OPERADOR', 'GESTOR']
    ];

    return in_array($perfil, $permissions[$menuName] ?? [], true);
}
?>
<div id="sidebar">
    <div class="brand">
        <i class="bi bi-shield-check"></i>
        <span>Gestão_EPI</span>
    </div>
    
    <ul class="nav-menu">
        <?php if (hasPermission('dashboard', $perfil)): ?>
            <li class="nav-item <?= $active === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('funcionarios', $perfil)): ?>
            <li class="nav-item <?= $active === 'funcionarios' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/funcionarios.php">
                    <i class="bi bi-people"></i>
                    <span>Funcionários</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('epis', $perfil)): ?>
            <li class="nav-item <?= $active === 'epis' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/epis.php">
                    <i class="bi bi-box-seam"></i>
                    <span>EPIs</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('entregas', $perfil)): ?>
            <li class="nav-item <?= $active === 'entregas' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/entregas.php">
                    <i class="bi bi-journal-check"></i>
                    <span>Entregas</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('devolucoes', $perfil)): ?>
            <li class="nav-item <?= $active === 'devolucoes' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/devolucoes.php">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Devoluções</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('relatorios', $perfil)): ?>
            <li class="nav-item <?= $active === 'relatorios' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/relatorios.php">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Relatórios</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('usuarios', $perfil)): ?>
            <li class="nav-item <?= $active === 'usuarios' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/usuarios.php">
                    <i class="bi bi-person-gear"></i>
                    <span>Usuários</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php if (hasPermission('auditoria', $perfil)): ?>
            <li class="nav-item <?= $active === 'auditoria' ? 'active' : '' ?>">
                <a href="<?= APP_ROOT ?>pages/auditoria.php">
                    <i class="bi bi-fingerprint"></i>
                    <span>Auditoria</span>
                </a>
            </li>
        <?php endif; ?>
        
        <li class="nav-item <?= $active === 'configuracoes' ? 'active' : '' ?>">
            <a href="<?= APP_ROOT ?>pages/configuracoes.php">
                <i class="bi bi-gear"></i>
                <span>Configurações</span>
            </a>
        </li>
        
        <li class="nav-item mt-auto">
            <a href="<?= APP_ROOT ?>logout.php" class="text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair</span>
            </a>
        </li>
    </ul>
</div>
