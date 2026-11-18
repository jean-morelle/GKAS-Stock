<?php

namespace Database\Seeders;

use App\Enums\ProductUnit;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(StockService $stock): void
    {
        $responsable = $this->user('Responsable GKAS', 'admin@gkasgroup.com', UserRole::Responsable);

        $technicians = collect([
            ['Kossi Mensah', 'kossi.mensah@gkasgroup.com'],
            ['Ama Agbeko', 'ama.agbeko@gkasgroup.com'],
            ['Koffi Adjovi', 'koffi.adjovi@gkasgroup.com'],
            ['Yao Kodjo', 'yao.kodjo@gkasgroup.com'],
        ])->map(fn (array $t) => $this->user($t[0], $t[1], UserRole::Technicien));

        $categories = collect([
            'Informatique' => 'Postes de travail, périphériques et composants.',
            'Réseau' => 'Équipements actifs et passifs de réseau.',
            'Électricité' => 'Matériel électrique et protections.',
            'Électronique' => 'Composants et modules électroniques.',
            'Câblage' => 'Câbles, connecteurs et accessoires de câblage.',
            'Outillage' => 'Outils des techniciens.',
            'Consommables' => 'Fournitures à usage courant.',
        ])->mapWithKeys(fn (string $description, string $name) => [
            $name => Category::firstOrCreate(['name' => $name], ['description' => $description]),
        ]);

        // [catégorie, nom, référence, unité, seuil, stock initial]
        $products = [
            ['Câblage', 'Câble réseau CAT6', 'CAT6-305', ProductUnit::Meter, 100, 610],
            ['Câblage', 'Connecteur RJ45', 'RJ45-CAT6', ProductUnit::Piece, 50, 200],
            ['Réseau', 'Switch 8 ports', 'SW-8P-GB', ProductUnit::Unit, 3, 6],
            ['Réseau', 'Routeur', 'RT-AC1200', ProductUnit::Unit, 2, 4],
            ['Informatique', 'Souris USB', 'MS-USB-01', ProductUnit::Unit, 5, 12],
            ['Informatique', 'Clavier USB', 'KB-USB-01', ProductUnit::Unit, 5, 8],
            ['Informatique', 'Disque SSD 512 Go', 'SSD-512', ProductUnit::Unit, 3, 4],
            ['Électricité', 'Multiprise parafoudre 6 prises', 'MP-6P-PF', ProductUnit::Unit, 4, 0],
            ['Outillage', 'Pince à sertir RJ45', 'PS-RJ45', ProductUnit::Unit, 2, 5],
            ['Consommables', 'Colliers de serrage 200 mm', 'CS-200', ProductUnit::Pack, 10, 30],
        ];

        foreach ($products as [$category, $name, $reference, $unit, $threshold, $initial]) {
            $product = Product::firstOrCreate(
                ['reference' => $reference],
                [
                    'category_id' => $categories[$category]->id,
                    'name' => $name,
                    'unit' => $unit,
                    'alert_threshold' => $threshold,
                ],
            );

            // Le stock initial passe par une entrée pour conserver la traçabilité.
            if ($initial > 0 && ! $product->hasMovements()) {
                $stock->addStock($product, $initial, $responsable, 'Inventaire initial');
            }
        }

        // Quelques sorties de démonstration.
        if ($technicians->first()->stockExits()->doesntExist()) {
            $cat6 = Product::where('reference', 'CAT6-305')->first();
            $rj45 = Product::where('reference', 'RJ45-CAT6')->first();
            $ssd = Product::where('reference', 'SSD-512')->first();

            $stock->removeStock($cat6, $technicians[0], 50, $responsable, 'Installation réseau', 'Client ABC');
            $stock->removeStock($rj45, $technicians[0], 20, $responsable, 'Installation réseau', 'Client ABC');
            $stock->removeStock($cat6, $technicians[1], 30, $responsable, 'Extension réseau', 'Client XYZ');
            $stock->removeStock($ssd, $technicians[2], 2, $responsable, 'Remplacement disque', 'Poste comptabilité');
        }
    }

    private function user(string $name, string $email, UserRole $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->fill(['name' => $name, 'password' => 'password']);
            $user->role = $role;
            $user->email_verified_at = now();
            $user->save();
        }

        return $user;
    }
}
