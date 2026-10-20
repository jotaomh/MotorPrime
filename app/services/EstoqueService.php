<?php

namespace App\Services;

class EstoqueService {
    public static function baixar($pecaId, $quantidade, $usuarioId = null, $obs = '') {
        $db = \db()->getConnection();
        $stmt = $db->prepare("UPDATE pecas SET quantidade = quantidade - ? WHERE id = ? AND quantidade >= ?");
        $stmt->execute([$quantidade, $pecaId, $quantidade]);
        if ($stmt->rowCount() === 0) {
            throw new \Exception('Estoque insuficiente');
        }
        $stmt = $db->prepare("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, observacoes, usuario_id) VALUES (?, 'saida', ?, ?, ?)");
        $stmt->execute([$pecaId, $quantidade, $obs, $usuarioId]);
        return true;
    }

    public static function devolver($pecaId, $quantidade, $usuarioId = null, $obs = '') {
        $db = \db()->getConnection();
        $stmt = $db->prepare("UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?");
        $stmt->execute([$quantidade, $pecaId]);
        $stmt = $db->prepare("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, observacoes, usuario_id) VALUES (?, 'entrada', ?, ?, ?)");
        $stmt->execute([$pecaId, $quantidade, $obs, $usuarioId]);
        return true;
    }
}
