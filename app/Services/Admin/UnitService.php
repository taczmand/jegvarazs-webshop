<?php

namespace App\Services\Admin;

use App\Models\Unit;
use Exception;

class UnitService
{
    public function store(array $data): array
    {
        try {
            $unit = Unit::create($this->normalizeData($data));
            return ['success' => true, 'data' => $unit, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function update(Unit $unit, array $data): array
    {
        try {
            $unit->update($this->normalizeData($data));
            return ['success' => true, 'data' => $unit, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function delete(Unit $unit): array
    {
        try {
            $unit->delete();
            return ['success' => true, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function normalizeData(array $data): array
    {
        $data['active'] = array_key_exists('active', $data) ? (bool) $data['active'] : false;

        return $data;
    }
}
