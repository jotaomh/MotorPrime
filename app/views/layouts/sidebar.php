<?php
use App\Helpers\Permissao;

$cargo = auth()['cargo'] ?? '';
$menuItens = Permissao::getMenuItens();
?>
<aside class="bg-gray-800 text-white w-64 min-h-screen">
    <nav class="mt-5">
        <?php foreach ($menuItens as $item): ?>
        <a href="dashboard.php?page=<?php echo e($item['id']); ?>" class="flex items-center px-6 py-3 hover:bg-gray-700 <?php echo Permissao::menuAtivo($page) === $item['id'] ? 'bg-gray-700' : ''; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($item['icon']); ?>"></path>
            </svg>
            <?php echo e($item['nome']); ?>
        </a>
        <?php endforeach; ?>
        <?php if (podeAcessar('financeiro') && isAdmin()): ?>
        <?php endif; ?>
    </nav>
</aside>
