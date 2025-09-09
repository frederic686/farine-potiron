<?php
require_once "modele/_model.php";

class Ingredient extends Model {
    protected $table = "ingredient";

    /** Ingrédients d'une recette */
    public static function getByRecette(int $id_recette): array {
        global $bdd;
        $sql = "SELECT id, nom, quantite, id_recette
                FROM ingredient
                WHERE id_recette = :rid
                ORDER BY id ASC";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['rid' => $id_recette]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Suppression des ingrédients d'une recette */
    public static function deleteByRecette(int $id_recette): bool {
        global $bdd;
        $sql = "DELETE FROM ingredient WHERE id_recette = :rid";
        $stmt = $bdd->prepare($sql);
        return $stmt->execute(['rid' => $id_recette]);
    }

    /** Insertion multiple */
    public static function insertMany(int $id_recette, array $ingredients): void {
        global $bdd;
        $sql = "INSERT INTO ingredient (nom, quantite, id_recette)
                VALUES (:nom, :quantite, :rid)";
        $stmt = $bdd->prepare($sql);

        foreach ($ingredients as $ing) {
            $nom = trim($ing['nom'] ?? '');
            $qte = trim($ing['quantite'] ?? '');
            if ($nom === '' && $qte === '') { continue; }
            $stmt->execute([
                'nom'      => $nom,
                'quantite' => $qte,
                'rid'      => $id_recette
            ]);
        }
    }

    /**
     * Récupération groupée par plusieurs recettes (évite N+1).
     * Retourne: [ id_recette => [ {id,nom,quantite,id_recette}, ... ], ... ]
     */
    public static function mapByRecetteIds(array $ids): array {
        if (empty($ids)) return [];
        global $bdd;

        // Placeholders sécurisés
        $in = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT id, nom, quantite, id_recette
                FROM ingredient
                WHERE id_recette IN ($in)
                ORDER BY id ASC";
        $stmt = $bdd->prepare($sql);
        $stmt->execute($ids);

        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int)$row['id_recette'];
            $map[$rid][] = $row;
        }
        return $map;
    }
}
