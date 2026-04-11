<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Oficina e Autopeças</title>
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
        .hero-bg {
            background: 
                radial-gradient(ellipse at 20% 80%, rgba(30, 58, 95, 0.4) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
                linear-gradient(135deg, #0a0a0a 0%, #121212 50%, #0a0a0a 100%);
        }
        .glass {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .fade-in {
            animation: fadeIn 0.6s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.6); }
        }
    </style>
</head>
<body class="hero-bg min-h-screen">
    <!-- Header -->
    <header class="p-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                <circle cx="12" cy="12" r="3"/>
                <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span class="text-xl font-bold text-white">Motor Prime</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="#features" class="text-gray-400 hover:text-white transition text-sm">Características</a>
            <a href="login.php" class="bg-accent hover:bg-accentLight text-white px-5 py-2 rounded-lg font-semibold transition">Entrar</a>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex items-center justify-center p-6 min-h-[70vh]">
        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left - Company Info -->
            <div class="fade-in text-center lg:text-left">
                <div class="mb-6">
                    <span class="bg-primary/20 text-accentLight px-4 py-1 rounded-full text-sm">Gestão Profissional</span>
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                    Oficina Mecânica<br>
                    <span class="text-accent">+ Autopeças</span>
                </h1>
                <p class="text-gray-400 text-lg mb-8 max-w-lg">
                    Sistema completo para gestão de sua oficina. 
                    Controle ordens de serviço, estoque de peças e financeiro 
                    em um único lugar. Tudo online e em tempo real.
                </p>
                <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                    <a href="login.php" class="bg-accent hover:bg-accentLight text-white px-8 py-3 rounded-lg font-semibold transition pulse-glow inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-5-5m5 5l5-5m-5 5h12m-9-9h6m-6 6h6"/>
                        </svg>
                        Acessar Sistema
                    </a>
                    <a href="#features" class="border border-gray-700 text-gray-300 hover:text-white hover:border-accent px-8 py-3 rounded-lg font-semibold transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Saber Mais
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-10 pt-8 border-t border-gray-800">
                    <div>
                        <div class="text-2xl font-bold text-accent">100%</div>
                        <div class="text-gray-500 text-sm">Online</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-accent">R$</div>
                        <div class="text-gray-500 text-sm">Financeiro</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-accent">+</div>
                        <div class="text-gray-500 text-sm">Estoque</div>
                    </div>
                </div>
            </div>

            <!-- Right - Hero Image/Graphic -->
            <div class="fade-in hidden lg:flex items-center justify-center">
                <div class="relative">
                    <div class="w-64 h-64 rounded-full border-4 border-accent/30 flex items-center justify-center">
                        <div class="w-48 h-48 rounded-full border-4 border-accent/50 flex items-center justify-center">
                            <div class="w-32 h-32 rounded-full border-4 border-accent flex items-center justify-center">
                                <svg class="w-16 h-16 text-accent" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="py-20 border-t border-gray-800">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-white text-center mb-4">Funcionalidades Completas</h2>
            <p class="text-gray-400 text-center mb-12">Tudo que você precisa para gerenciar sua oficina</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Feature 1 -->
                <div class="glass p-6 rounded-xl">
                    <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Ordens de Serviço</h3>
                    <p class="text-gray-400 text-sm">Gerencie OS do orçamento à entrega com controle total de serviços e peças.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="glass p-6 rounded-xl">
                    <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Estoque</h3>
                    <p class="text-gray-400 text-sm">Controle completo de peças com alertas automáticos de estoque baixo.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="glass p-6 rounded-xl">
                    <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Financeiro</h3>
                    <p class="text-gray-400 text-sm">Faturamento, vendas e relatórios por período detalhado.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="glass p-6 rounded-xl">
                    <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-white font-semibold mb-2">Cadastros</h3>
                    <p class="text-gray-400 text-sm">Clientes, veículos, funcionários e fornecedores organizados.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Pronto para melhorar sua gestão?</h2>
            <p class="text-gray-400 mb-8">Acesse o sistema agora mesmo e comece a gerenciar sua oficina de forma profissional.</p>
            <a href="login.php" class="bg-accent hover:bg-accentLight text-white px-8 py-3 rounded-lg font-semibold text-lg transition inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-5-5m5 5l5-5m-5 5h12m-9-9h6m-6 6h6"/>
                </svg>
                Acessar Sistema
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-gray-800">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <span class="text-gray-400">Motor Prime © 2024</span>
            </div>
            <div class="text-gray-500 text-sm">
                Sistema de Gestão para Oficina Mecânica
            </div>
        </div>
    </footer>
</body>
</html>