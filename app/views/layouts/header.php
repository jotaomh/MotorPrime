<?php
?>
<header class="bg-blue-900 text-white shadow-lg">
    <div class="px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold">Motor Prime</h1>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm"><?php echo e(auth()['nome'] ?? ''); ?> (<?php echo e(auth()['cargo'] ?? ''); ?>)</span>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded text-sm">Sair</a>
        </div>
    </div>
</header>
