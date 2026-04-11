<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Oficina Mecânica e Autopeças</title>
    <meta name="description" content="Motor Prime - Gestão profissional para oficina mecânica e loja de autopeças. Qualidade e confiança em primeiro lugar.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: '#3b82f6',
                        accentLight: '#60a5fa'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .hero-bg {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
        }
        .fade-in { animation: fadeIn 0.8s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="hero-bg min-h-screen text-gray-100">
    <!-- Header -->
    <header class="p-6">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#sobre" class="text-gray-400 hover:text-white text-sm transition">Sobre</a>
                <a href="#servicos" class="text-gray-400 hover:text-white text-sm transition">Serviços</a>
                <a href="#contato" class="text-gray-400 hover:text-white text-sm transition">Contato</a>
                <a href="cliente.php" class="text-gray-300 hover:text-white text-sm transition">Área do Cliente</a>
                <a href="login.php" class="bg-accent hover:bg-blue-500 px-5 py-2 rounded-lg text-sm font-semibold transition">Funcionário</a>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-6 py-16 md:py-24">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <span class="bg-accent/20 text-accent px-4 py-1 rounded-full text-sm">Desde 2020</span>
                <h1 class="text-4xl md:text-5xl font-bold text-white mt-4 mb-4">
                    Cuidamos do seu<br>
                    <span class="text-accent">veículo como família</span>
                </h1>
                <p class="text-gray-400 text-lg mb-8 max-w-lg">
                    Mais de 4 anos de experiência em manutenção automotiva. 
                    Serviços especializados, peças de qualidade e atendimento de excelência.
                </p>
                <div class="flex gap-4">
                    <a href="#contato" class="bg-accent hover:bg-blue-500 px-6 py-3 rounded-lg font-semibold transition">
                        Agendar Serviço
                    </a>
                    <a href="cliente.php" class="border border-gray-600 hover:border-accent px-6 py-3 rounded-lg font-semibold transition">
                        Área do Cliente
                    </a>
                </div>
            </div>
            <div class="hidden md:flex items-center justify-center">
                <div class="relative w-64 h-64">
                    <div class="absolute inset-0 border-4 border-accent/30 rounded-full animate-pulse"></div>
                    <div class="absolute inset-8 border-4 border-accent/50 rounded-full"></div>
                    <div class="absolute inset-16 border-4 border-accent rounded-full flex items-center justify-center">
                        <svg class="w-16 h-16 text-accent" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sobre -->
    <section id="sobre" class="py-16 border-t border-gray-800">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-white text-center mb-12">Nossa História</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-accent/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-accent">4+</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Anos de Experiência</h3>
                    <p class="text-gray-400 text-sm">Tradição e conhecimento acumulado ao longo dos anos</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-accent/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-accent">500+</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Clientes Atendidos</h3>
                    <p class="text-gray-400 text-sm">Famílias que confiam em nosso trabalho</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-accent/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-accent">100%</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Satisfação</h3>
                    <p class="text-gray-400 text-sm">Compromisso com a qualidade</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="py-16 border-t border-gray-800">
        <div class="max-w-6xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-white text-center mb-12">Nossos Serviços</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-darker p-6 rounded-xl border border-gray-800">
                    <svg class="w-10 h-10 text-accent mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Manutenção Geral</h3>
                    <p class="text-gray-400 text-sm">Troca de óleo, filtros, velas e revisão completa do seu veículo.</p>
                </div>
                <div class="bg-darker p-6 rounded-xl border border-gray-800">
                    <svg class="w-10 h-10 text-accent mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2m0-10a2 2 0 01-2-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Freios e Suspensão</h3>
                    <p class="text-gray-400 text-sm">Pastilhas, discos, amortecedores e alinhamento.</p>
                </div>
                <div class="bg-darker p-6 rounded-xl border border-gray-800">
                    <svg class="w-10 h-10 text-accent mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white mb-2">Autopeças</h3>
                    <p class="text-gray-400 text-sm">Diversas peças originais e compatíveis para seu veículo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contato -->
    <section id="contato" class="py-16 border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold text-white mb-8">Entre em Contato</h2>
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <svg class="w-8 h-8 text-accent mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.489a1 1 0 01-.502 1.21l-2.331 1.138a10.972 10.972 0 01-2.417 2.416l1.138 2.331a1 1 0 01.21.502l4.489 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <p class="text-white font-semibold">(11) 99999-9999</p>
                    <p class="text-gray-400 text-sm">WhatsApp</p>
                </div>
                <div>
                    <svg class="w-8 h-8 text-accent mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-5 4v1a3 3 0 003 3h1a3 3 0 003-3v-1m-4-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-white font-semibold">contato@motorprime.com.br</p>
                    <p class="text-gray-400 text-sm">Email</p>
                </div>
                <div>
                    <svg class="w-8 h-8 text-accent mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243A8 8 0 1117.657 16.657z"/>
                    </svg>
                    <p class="text-white font-semibold">Rua Exemplo, 123</p>
                    <p class="text-gray-400 text-sm">São Paulo/SP</p>
                </div>
            </div>
            <a href="cliente.php" class="inline-block bg-accent hover:bg-blue-500 px-8 py-3 rounded-lg font-semibold transition">
                Agendar Agora
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-gray-800 text-center">
        <p class="text-gray-500 text-sm">
            © 2024 Motor Prime - Todos os direitos reservados
        </p>
    </footer>
</body>
</html>