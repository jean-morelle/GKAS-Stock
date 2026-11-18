<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Nombre d'éléments affichés par page dans toutes les listes
    | (produits, catégories, entrées, sorties, historique, techniciens).
    | Modifiable dans le fichier .env : STOCK_PER_PAGE=10
    |
    */

    'per_page' => (int) env('STOCK_PER_PAGE', 5),

    /*
    |--------------------------------------------------------------------------
    | Visites
    |--------------------------------------------------------------------------
    |
    | Durée d'inactivité (en minutes) au-delà de laquelle une nouvelle visite
    | commence. Sert au bloc « Depuis votre dernière visite » du tableau de bord.
    |
    */

    'visit_gap_minutes' => (int) env('STOCK_VISIT_GAP_MINUTES', 120),

];
