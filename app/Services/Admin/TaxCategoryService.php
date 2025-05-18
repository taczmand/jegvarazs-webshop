<?php

namespace App\Services\Admin;

use App\Models\TaxCategory;
use Exception;

class TaxCategoryService
{

    /**
     * Új adó létrehozása
     *
     * @param array $data
     * @return array ['success' => bool, 'data' => TaxCategory|null, 'error' => string|null]
     */
    public function store(array $data)
    {
        try {
            $tax = TaxCategory::create($data);
            return ['success' => true, 'data' => $tax, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Adó frissítése
     *
     * @param TaxCategory $tax
     * @param array $data
     * @return array ['success' => bool, 'data' => TaxCategory|null, 'error' => string|null]
     */
    public function update(TaxCategory $tax, array $data)
    {
        try {
            $tax->update($data);
            return ['success' => true, 'data' => $tax, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Adó törlése
     *
     * @param TaxCategory $tax
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function delete(TaxCategory $tax): array
    {
        try {
            $tax->delete();
            return ['success' => true, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
