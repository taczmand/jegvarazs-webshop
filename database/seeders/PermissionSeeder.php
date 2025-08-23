<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['id' => 1, 'name' => 'view-orders', 'label' => 'Rendelések megtekintése', 'group' => 'Rendelések'],
            ['id' => 3, 'name' => 'view-customers', 'label' => 'Vevők és partenerek megtekintése', 'group' => 'Vevők és partnerek'],
            ['id' => 4, 'name' => 'create-customer', 'label' => 'Vevő vagy partner létrehozása', 'group' => 'Vevők és partnerek'],
            ['id' => 5, 'name' => 'view-worksheets', 'label' => 'Munkalapok megtekintése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 6, 'name' => 'view-own-worksheets', 'label' => 'Csak a saját munkalapok megtekintése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 7, 'name' => 'edit-order', 'label' => 'Rendelés szerkesztése', 'group' => 'Rendelések'],
            ['id' => 8, 'name' => 'delete-order', 'label' => 'Rendelés törlése', 'group' => 'Rendelések'],
            ['id' => 9, 'name' => 'edit-customer', 'label' => 'Vevő vagy partner szerkesztése', 'group' => 'Vevők és partnerek'],
            ['id' => 10, 'name' => 'delete-customer', 'label' => 'Vevő vagy partner törlése', 'group' => 'Vevők és partnerek'],
            ['id' => 11, 'name' => 'view-products', 'label' => 'Termékek megtekintése', 'group' => 'Termékek'],
            ['id' => 12, 'name' => 'edit-product', 'label' => 'Termék szerkesztése', 'group' => 'Termékek'],
            ['id' => 13, 'name' => 'delete-product', 'label' => 'Termék törlése', 'group' => 'Termékek'],
            ['id' => 14, 'name' => 'create-product', 'label' => 'Termék létrehozása', 'group' => 'Termékek'],
            ['id' => 15, 'name' => 'view-categories', 'label' => 'Termékkategóriák megtekintése', 'group' => 'Termékek'],
            ['id' => 16, 'name' => 'edit-category', 'label' => 'Termékkategória szerkesztése', 'group' => 'Termékek'],
            ['id' => 17, 'name' => 'delete-category', 'label' => 'Termékkategória törlése', 'group' => 'Termékek'],
            ['id' => 18, 'name' => 'create-category', 'label' => 'Termékkategória létrehozása', 'group' => 'Termékek'],
            ['id' => 19, 'name' => 'view-attributes', 'label' => 'Egyedi tulajdonságok megtekintése', 'group' => 'Termékek'],
            ['id' => 20, 'name' => 'edit-attribute', 'label' => 'Egyedi tulajdonság szerkesztése', 'group' => 'Termékek'],
            ['id' => 21, 'name' => 'create-attribute', 'label' => 'Egyedi tulajdonság létrehozása', 'group' => 'Termékek'],
            ['id' => 22, 'name' => 'delete-attribute', 'label' => 'Egyedi tulajdonság törlése', 'group' => 'Termékek'],
            ['id' => 23, 'name' => 'view-tags', 'label' => 'Címkék megtekintése', 'group' => 'Termékek'],
            ['id' => 24, 'name' => 'create-tag', 'label' => 'Címke létrehozása', 'group' => 'Termékek'],
            ['id' => 25, 'name' => 'edit-tag', 'label' => 'Címke szerkesztése', 'group' => 'Termékek'],
            ['id' => 26, 'name' => 'delete-tag', 'label' => 'Címke törlése', 'group' => 'Termékek'],
            ['id' => 27, 'name' => 'view-brands', 'label' => 'Gyártók megtekintése', 'group' => 'Termékek'],
            ['id' => 28, 'name' => 'create-brand', 'label' => 'Gyártó létrehozása', 'group' => 'Termékek'],
            ['id' => 29, 'name' => 'edit-brand', 'label' => 'Gyártó szerkesztése', 'group' => 'Termékek'],
            ['id' => 30, 'name' => 'delete-brand', 'label' => 'Gyártó törlése', 'group' => 'Termékek'],
            ['id' => 31, 'name' => 'view-taxes', 'label' => 'Adó osztályok megtekintése', 'group' => 'Webshop'],
            ['id' => 32, 'name' => 'edit-tax', 'label' => 'Adó osztály szerkesztése', 'group' => 'Webshop'],
            ['id' => 33, 'name' => 'create-tax', 'label' => 'Adó osztály létrehozása', 'group' => 'Webshop'],
            ['id' => 34, 'name' => 'delete-tax', 'label' => 'Adó osztály törlése', 'group' => 'Webshop'],
            ['id' => 35, 'name' => 'view-downloads', 'label' => 'Letöltések megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 36, 'name' => 'edit-download', 'label' => 'Letöltés szerkesztése', 'group' => 'Tartalomkezelés'],
            ['id' => 37, 'name' => 'create-download', 'label' => 'Letöltés létrehozása', 'group' => 'Tartalomkezelés'],
            ['id' => 38, 'name' => 'delete-download', 'label' => 'Letöltés törlése', 'group' => 'Tartalomkezelés'],
            ['id' => 39, 'name' => 'view-regulations', 'label' => 'Szabályzatok megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 40, 'name' => 'create-regulation', 'label' => 'Szabályzat létrehozása', 'group' => 'Tartalomkezelés'],
            ['id' => 41, 'name' => 'edit-regulation', 'label' => 'Szabályzat szerkesztése', 'group' => 'Tartalomkezelés'],
            ['id' => 42, 'name' => 'delete-regulation', 'label' => 'Szabályzat törlése', 'group' => 'Tartalomkezelés'],
            ['id' => 43, 'name' => 'view-sites', 'label' => 'Telephelyek megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 44, 'name' => 'edit-site', 'label' => 'Telephely szerkesztése', 'group' => 'Tartalomkezelés'],
            ['id' => 45, 'name' => 'create-site', 'label' => 'Telephely létrehozása', 'group' => 'Tartalomkezelés'],
            ['id' => 46, 'name' => 'delete-site', 'label' => 'Telephely törlése', 'group' => 'Tartalomkezelés'],
            ['id' => 47, 'name' => 'edit-settings', 'label' => 'Rendszer beállítások szerkesztése', 'group' => 'Rendszer'],
            ['id' => 48, 'name' => 'view-users', 'label' => 'Felhasználók megtekintése', 'group' => 'Rendszer'],
            ['id' => 49, 'name' => 'edit-user', 'label' => 'Felhasználó szerkesztése', 'group' => 'Rendszer'],
            ['id' => 50, 'name' => 'create-user', 'label' => 'Felhasználó létrehozása', 'group' => 'Rendszer'],
            ['id' => 51, 'name' => 'delete-user', 'label' => 'Felhasználó törlése', 'group' => 'Rendszer'],
            ['id' => 52, 'name' => 'view-blogs', 'label' => 'Blog bejegyzések megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 53, 'name' => 'create-blog', 'label' => 'Blog bejegyzés létrehozása', 'group' => 'Tartalomkezelés'],
            ['id' => 54, 'name' => 'edit-blog', 'label' => 'Blog bejegyzés szerkesztése', 'group' => 'Tartalomkezelés'],
            ['id' => 55, 'name' => 'delete-blog', 'label' => 'Blog bejegyzés törlése', 'group' => 'Tartalomkezelés'],
            ['id' => 56, 'name' => 'create-worksheet', 'label' => 'Munkalap létrehozása', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 57, 'name' => 'edit-worksheet', 'label' => 'Munkalap szerkesztése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 58, 'name' => 'delete-worksheet', 'label' => 'Munkalap törlése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 59, 'name' => 'view-contracts', 'label' => 'Szerződések megtekintése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 60, 'name' => 'create-contract', 'label' => 'Szerződés létrehozása', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 61, 'name' => 'delete-contract', 'label' => 'Szerződés törlése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 62, 'name' => 'view-offers', 'label' => 'Ajánlatok megtekintése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 63, 'name' => 'create-offer', 'label' => 'Ajánlat létrehozása', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 64, 'name' => 'delete-offer', 'label' => 'Ajánlat törlése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 65, 'name' => 'view-appointments', 'label' => 'Időpontfoglalások megtekintése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 66, 'name' => 'create-appointment', 'label' => 'Időpontfoglalás létrehozása', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 67, 'name' => 'edit-appointment', 'label' => 'Időpontfoglalás szerkesztése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 68, 'name' => 'delete-appointment', 'label' => 'Időpontfoglalás törlése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 69, 'name' => 'view-searched-products', 'label' => 'Keresések megtekintése', 'group' => 'Jelentések'],
            ['id' => 70, 'name' => 'view-viewed-products', 'label' => 'Megtekintett termékek megtekintése', 'group' => 'Jelentések'],
            ['id' => 71, 'name' => 'view-admin-logs', 'label' => 'Admin tevékenységek megtekintése', 'group' => 'Jelentések'],
            ['id' => 72, 'name' => 'view-purchased-products', 'label' => 'Vásárolt termékek megtekintése', 'group' => 'Jelentések'],
            ['id' => 73, 'name' => 'delete-worksheet-image', 'label' => 'Munkalap kép törlése', 'group' => 'Ügyviteli folyamatok'],
            ['id' => 74, 'name' => 'view-employees', 'label' => 'Munkatársak megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 75, 'name' => 'edit-employee', 'label' => 'Munkatárs szerkesztése', 'group' => 'Tartalomkezelés'],
            ['id' => 76, 'name' => 'create-employee', 'label' => 'Munkatárs létrehozása', 'group' => 'Tartalomkezelés'],
            ['id' => 77, 'name' => 'delete-employee', 'label' => 'Munkatárs törlése', 'group' => 'Tartalomkezelés'],
            ['id' => 78, 'name' => 'view-settings', 'label' => 'Rendszer beállítások megtekintése', 'group' => 'Rendszer'],
            ['id' => 79, 'name' => 'view-media-settings', 'label' => 'Média beállítások megtekintése', 'group' => 'Tartalomkezelés'],
            ['id' => 80, 'name' => 'edit-media-settings', 'label' => 'Média beállítások szerkesztése', 'group' => 'Tartalomkezelés'],

        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['id' => $perm['id']],
                array_merge($perm, [
                    'guard_name' => 'admin',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}
