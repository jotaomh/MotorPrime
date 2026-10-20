<?php

namespace App\Services;

class TransacaoService {
    public static function run(callable $callback) {
        $db = \db()->getConnection();
        $db->beginTransaction();
        try {
            $result = $callback($db);
            $db->commit();
            return $result;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
