<?php

namespace App\Helpers;

class Permissao {
    const ADMIN = 'admin';
    const MECANICO = 'mecanico';
    const VENDEDOR = 'vendedor';
    
    public static function pode($acao) {
        $cargo = $_SESSION['usuario']['cargo'] ?? '';
        
        $permissoes = [
            self::ADMIN => [
                'dashboard', 'clientes', 'veiculos', 'funcionarios', 
                'fornecedores', 'os', 'estoque', 'financeiro'
            ],
            self::MECANICO => [
                'dashboard', 'clientes', 'veiculos', 'os'
            ],
            self::VENDEDOR => [
                'dashboard', 'clientes', 'veiculos', 'estoque', 'financeiro'
            ]
        ];
        
        return in_array($acao, $permissoes[$cargo] ?? []);
    }
    
    public static function menuAtivo($page) {
        $paginas = [
            'dashboard' => [''],
            'clientes' => ['clientes'],
            'veiculos' => ['veiculos'],
            'funcionarios' => ['funcionarios'],
            'fornecedores' => ['fornecedores'],
            'os' => ['os', 'os_edit'],
            'estoque' => ['estoque'],
            'financeiro' => ['financeiro']
        ];
        
        foreach ($paginas as $menu => $pages) {
            if (in_array($page, $pages)) {
                return $menu;
            }
        }
        return '';
    }
    
    public static function getMenuItens() {
        $cargo = $_SESSION['usuario']['cargo'] ?? '';
        
        $menus = [
            self::ADMIN => [
                ['id' => 'dashboard', 'nome' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l2 2m-2-2l2 2m-2-2l-2-2m2 2l-2-2m0 0l2-2m-2 4h12m-9-9h6m-6 6h6'],
                ['id' => 'clientes', 'nome' => 'Clientes', 'icon' => 'M12 4.462v3.058l.963.855 1.04 1.04v3.058M21 12c0 4.418-4.03 8-9 8-4.97 0-9-4.582-9-8 0-2.05.806-3.813 2.04-4.962m4.962-1.04c1.344-.053 2.563-.415 3.537-1.038M21 12c0 4.418-4.03 8-9 8-4.97 0-9-4.582-9-8s4.03-8 9-8c2.03 0 3.88.663 5.386 1.752'],
                ['id' => 'veiculos', 'nome' => 'Veículos', 'icon' => 'M5 17h9.5M18.5 17h1.5a1 1 0 001-1v-3a1 1 0 00-1-1h-1a1 1 0 00-1 1v3a1 1 0 001 1h1.5M16 6V3m2 0V1m-2 0h2M8 6V3m2 0V1m-2 0h2m5.333 10.667l-1.067.533-.533.8-.8.267-.8-.267-.533-.8-1.067-.533'],
                ['id' => 'funcionarios', 'nome' => 'Funcionários', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
                ['id' => 'fornecedores', 'nome' => 'Fornecedores', 'icon' => 'M8 17h8m-8 0h.01M4 17H2v-2h2m0-6h8m-8 0h-.01M4 11h8m-8 0H2v2h2m12 0h8m-8 0h-.01M18 11h-2v2h2m0-6h8m-8 0h-.01'],
                ['id' => 'os', 'nome' => 'Ordens de Serviço', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['id' => 'estoque', 'nome' => 'Estoque', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['id' => 'financeiro', 'nome' => 'Financeiro', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1']
            ],
            self::MECANICO => [
                ['id' => 'dashboard', 'nome' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l2 2m-2-2l2 2m-2-2l-2-2m2 2l-2-2m0 0l2-2m-2 4h12m-9-9h6m-6 6h6'],
                ['id' => 'clientes', 'nome' => 'Clientes', 'icon' => 'M12 4.462v3.058l.963.855 1.04 1.04v3.058'],
                ['id' => 'veiculos', 'nome' => 'Veículos', 'icon' => 'M5 17h9.5M18.5 17h1.5a1 1 0 001-1v-3a1 1 0 00-1-1h-1a1 1 0 00-1 1v3a1 1 0 001 1h1.5'],
                ['id' => 'os', 'nome' => 'Ordens de Serviço', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2']
            ],
            self::VENDEDOR => [
                ['id' => 'dashboard', 'nome' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l2 2m-2-2l2 2m-2-2l-2-2m2 2l-2-2m0 0l2-2m-2 4h12m-9-9h6m-6 6h6'],
                ['id' => 'clientes', 'nome' => 'Clientes', 'icon' => 'M12 4.462v3.058l.963.855 1.04 1.04v3.058'],
                ['id' => 'veiculos', 'nome' => 'Veículos', 'icon' => 'M5 17h9.5M18.5 17h1.5a1 1 0 001-1v-3a1 1 0 00-1-1h-1a1 1 0 00-1 1v3'],
                ['id' => 'estoque', 'nome' => 'Estoque', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['id' => 'financeiro', 'nome' => 'Financeiro', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1']
            ]
        ];
        
        return $menus[$cargo] ?? [];
    }
}

function podeAcessar($pagina) {
    return \App\Helpers\Permissao::pode($pagina);
}

function getMenuItens() {
    return \App\Helpers\Permissao::getMenuItens();
}