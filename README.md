# GKAS Stock — MVP

Application interne de gestion et de traçabilité du stock de matériel de **GKAS-GROUP**.

Stack : PHP 8.2+, Laravel 12, MySQL/MariaDB (XAMPP), Blade, Breeze, Tailwind CSS, Alpine.js, Vite.

## Installation (Windows + XAMPP)

1. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
2. Créer la base `gkas-stockdb` (phpMyAdmin, ou en ligne de commande) :
   ```bash
   C:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE `gkas-stockdb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
3. Installer le projet :
   ```bash
   composer install
   copy .env.example .env      # puis vérifier la section DB_*
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   ```
4. Lancer : `php artisan serve` puis ouvrir http://localhost:8000
   (pour développer l'interface : `npm run dev` en parallèle).

## Comptes de démonstration

| Rôle        | E-mail                        | Mot de passe |
|-------------|-------------------------------|--------------|
| Responsable | admin@gkasgroup.com           | password     |
| Technicien  | kossi.mensah@gkasgroup.com    | password     |
| Technicien  | ama.agbeko@gkasgroup.com      | password     |

Les autres techniciens se créent depuis le menu **Techniciens** (pas d'inscription publique).
Changez les mots de passe avant toute utilisation réelle.

## Règles métier

- Le stock d'un produit **ne se saisit jamais directement** : il démarre à 0 et n'évolue que par des entrées et sorties, toutes enregistrées.
- Toute opération de stock passe par `App\Services\StockService` (transaction + verrou `SELECT … FOR UPDATE`).
- Une sortie supérieure au stock disponible est refusée (« Stock insuffisant »), contrôle effectué côté serveur.
- Quantités entières strictement positives.
- Stock faible : `0 < stock <= seuil` ; rupture : `stock = 0`.
- Un produit avec des mouvements, une catégorie contenant des produits ou un technicien ayant des sorties ne peuvent pas être supprimés (contrôle applicatif + clés étrangères `RESTRICT`).
- Le technicien enregistre lui-même ses sorties (toujours à son nom, contrôlé côté serveur) et ne voit que **ses propres** sorties ; le responsable voit qui a pris quoi.

## Architecture

```
app/
├── Enums/        UserRole, ProductUnit, StockStatus
├── Exceptions/   InsufficientStockException
├── Http/
│   ├── Controllers/   contrôleurs fins (1 par module)
│   ├── Middleware/    EnsureUserHasRole (alias « role »)
│   └── Requests/      validation (Form Requests)
├── Models/       User, Category, Product, StockEntry, StockExit
├── Policies/     autorisations par ressource
└── Services/     StockService (écritures), MovementHistory (historique unifié)
```

## Tests

```bash
php artisan test
```

Couvrent : entrées, sorties, stock insuffisant, quantités invalides, autorisations,
enregistrement de l'historique, scénario complet de recette et affichage de chaque écran.
Les tests utilisent SQLite en mémoire ; pour les exécuter sur MySQL :

```bash
DB_CONNECTION=mysql DB_DATABASE=gkas-stockdb_test php artisan test
```
(la base `gkas-stockdb_test` doit exister ; elle est entièrement réinitialisée.)
