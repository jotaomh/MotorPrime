<?php

namespace App\Services;

class OsService {
    public static function recalcular($osId) {
        $db = \db()->getConnection();
        $stmt = $db->prepare("SELECT COALESCE(SUM(valor),0) as total FROM servicos_os WHERE os_id = ?");
        $stmt->execute([$osId]);
        $servicos = (float)$stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(valor_unitario * quantidade),0) as total FROM pecas_os WHERE os_id = ?");
        $stmt->execute([$osId]);
        $pecas = (float)$stmt->fetch()['total'];
        
        $total = $servicos + $pecas;
        $stmt = $db->prepare("UPDATE ordens_servico SET valor_mao_obra = ?, valor_pecas = ?, valor_total = ? WHERE id = ?");
        $stmt->execute([$servicos, $pecas, $total, $osId]);
        return $total;
    }

    public static function devolverPeca($osId, $pecaOsId) {
        $db = \db()->getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT peca_id, quantidade FROM pecas_os WHERE id = ? AND os_id = ?");
            $stmt->execute([$pecaOsId, $osId]);
            $item = $stmt->fetch();
            if ($item) {
                $stmt = $db->prepare("UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?");
                $stmt->execute([$item['quantidade'], $item['peca_id']]);
                $usuarioId = $_SESSION['usuario']['id'] ?? null;
                $stmt = $db->prepare("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, observacoes, usuario_id) VALUES (?, 'entrada', ?, ?, ?)");
                $stmt->execute([$item['peca_id'], $item['quantidade'], 'Devolução OS #' . $osId, $usuarioId]);
                $stmt = $db->prepare("DELETE FROM pecas_os WHERE id = ?");
                $stmt->execute([$pecaOsId]);
            }
            self::recalcular($osId);
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
