<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Área do Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#0a0a0a',
                        darker: '#121212',
                        darkGray: '#1a1a1a',
                        darkMid: '#2a2a2a',
                        primary: '#1e3a5f',
                        primaryLight: '#2d5a87',
                        accent: '#3b82f6',
                        accentLight: '#60a5fa'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            min-height: 100vh;
        }
        .input-field {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="p-6">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <a href="index.php" class="flex items-center gap-3">
                <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                </svg>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </a>
            <a href="index.php" class="text-gray-400 hover:text-white text-sm">← Voltar</a>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="bg-darker rounded-2xl p-8 border border-gray-800">
                <!-- Tabs -->
                <div class="flex mb-6 p-1 bg-darkGray rounded-lg">
                    <button onclick="showTab('login')" id="tab-login" class="flex-1 py-2 rounded-md text-sm font-medium transition text-white bg-accent">
                        Login
                    </button>
                    <button onclick="showTab('register')" id="tab-register" class="flex-1 py-2 rounded-md text-sm font-medium transition text-gray-400 hover:text-white">
                        Cadastrar
                    </button>
                </div>

                <!-- Messages -->
                <?php if (isset($_GET['msg'])): ?>
                    <div class="bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded-lg mb-4 text-sm">
                        <?= $_GET['msg'] ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['erro'])): ?>
                    <div class="bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm">
                        <?= $_GET['erro'] ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="form-login" method="POST" action="cliente_auth.php?action=login">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">CPF ou Email</label>
                            <input type="text" name="login" required placeholder="Seu CPF ou email"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Senha</label>
                            <input type="password" name="senha" required placeholder="••••••••"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-accent hover:bg-accentLight py-3 rounded-lg font-semibold text-white transition">
                            Entrar
                        </button>
                    </div>
                </form>

                <!-- Register Form -->
                <form id="form-register" method="POST" action="cliente_auth.php?action=register" class="hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Nome Completo</label>
                            <input type="text" name="nome" required placeholder="Seu nome"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">CPF</label>
                            <input type="text" name="cpf" required placeholder="000.000.000-00" maxlength="14"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Telefone</label>
                            <input type="text" name="telefone" required placeholder="(00) 00000-0000"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Email</label>
                            <input type="email" name="email" required placeholder="seu@email.com"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Senha</label>
                            <input type="password" name="senha" required placeholder="••••••••"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Endereço</label>
                            <input type="text" name="endereco" placeholder="Rua, número, bairro..."
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-accent hover:bg-accentLight py-3 rounded-lg font-semibold text-white transition">
                            Criar Conta
                        </button>
                    </div>
                </form>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-800"></div>
                    <span class="px-4 text-gray-600 text-sm">ou</span>
                    <div class="flex-1 border-t border-gray-800"></div>
                </div>

                <div class="text-center">
                    <a href="login.php" class="text-accent hover:text-accentLight text-sm">
                        Sou funcionário - entrar no sistema
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tab) {
            const loginForm = document.getElementById('form-login');
            const registerForm = document.getElementById('form-register');
            const loginTab = document.getElementById('tab-login');
            const registerTab = document.getElementById('tab-register');
            
            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                loginTab.classList.add('bg-accent', 'text-white');
                loginTab.classList.remove('text-gray-400');
                registerTab.classList.remove('bg-accent', 'text-white');
                registerTab.classList.add('text-gray-400');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                registerTab.classList.add('bg-accent', 'text-white');
                registerTab.classList.remove('text-gray-400');
                loginTab.classList.remove('bg-accent', 'text-white');
                loginTab.classList.add('text-gray-400');
            }
        }
    </script>
</body>
</html>