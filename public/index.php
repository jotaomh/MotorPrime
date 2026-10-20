<?php
require_once __DIR__ . '/../app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Oficina Mecânica e Autopeças</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <!-- Navbar -->
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                </svg>
                <span class="text-xl font-bold">Motor Prime</span>
            </div>
            <div class="flex space-x-4">
                <a href="login.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Entrar</a>
                <a href="cadastro.php" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded">Criar conta</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl font-bold mb-6">Oficina Mecânica e Loja de Autopeças</h1>
            <p class="text-xl text-gray-300 mb-8">Manutenção completa, peças originais e atendimento especializado</p>
            <div class="flex justify-center space-x-4">
                <a href="login.php" class="bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-lg text-lg font-semibold">Entrar</a>
                <a href="cadastro.php" class="bg-gray-700 hover:bg-gray-600 px-8 py-3 rounded-lg text-lg font-semibold">Criar conta</a>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="py-16 bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Nossos Serviços</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-900 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-blue-400">Mecânica Geral</h3>
                    <p class="text-gray-300">Revisões, manutenção preventiva e corretiva para todos os tipos de veículos</p>
                </div>
                <div class="bg-gray-900 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-blue-400">Autopeças</h3>
                    <p class="text-gray-300">Peças originais e paralelas com garantia e os melhores preços</p>
                </div>
                <div class="bg-gray-900 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-blue-400">Diagnóstico</h3>
                    <p class="text-gray-300">Diagnóstico computadorizado para identificar problemas com precisão</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-8">
        <div class="container mx-auto px-4 text-center text-gray-400">
            <p>&copy; <?= date('Y') ?> Motor Prime. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
