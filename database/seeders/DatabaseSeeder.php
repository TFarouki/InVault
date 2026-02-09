<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $adminRole = Role::create([
            'name' => 'Administrateur',
            'slug' => 'admin',
            'description' => 'Accès complet au système',
        ]);

        $managerRole = Role::create([
            'name' => 'Gestionnaire',
            'slug' => 'manager',
            'description' => 'Gestion des stocks et rapports',
        ]);

        $cashierRole = Role::create([
            'name' => 'Caissier',
            'slug' => 'cashier',
            'description' => 'Opérations de vente uniquement',
        ]);

        // Admin User
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // Categories
        $cat1 = Category::create(['name' => 'Boissons', 'slug' => 'boissons']);
        $cat2 = Category::create(['name' => 'Alimentation', 'slug' => 'alimentation']);
        $cat3 = Category::create(['name' => 'Hygiène', 'slug' => 'hygiene']);

        // Products
        Product::create([
            'code' => 'P001',
            'name' => 'Coca Cola 33cl',
            'category_id' => $cat1->id,
            'price_ht' => 5,
            'price_ttc' => 6,
            'tax_percentage' => 20,
            'stock' => 100,
        ]);

        Product::create([
            'code' => 'P002',
            'name' => 'Bouteille d\'eau 1.5L',
            'category_id' => $cat1->id,
            'price_ht' => 3,
            'price_ttc' => 3.6,
            'tax_percentage' => 20,
            'stock' => 200,
        ]);

        Product::create([
            'code' => 'P003',
            'name' => 'Pain de Mie',
            'category_id' => $cat2->id,
            'price_ht' => 12,
            'price_ttc' => 14.4,
            'tax_percentage' => 20,
            'stock' => 50,
        ]);
    }
}