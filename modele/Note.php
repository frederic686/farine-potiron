<?php
// Note.php
require_once "modele/_model.php";

class Note extends Model
{
    protected $table = "note";

    public static function upsert(int $idRecette, int $idUser, int $valeur): void
    {
        global $bdd;
        if ($valeur < 1 || $valeur > 5) return;

        $sql = "INSERT INTO note (id_recette, id_utilisateur, valeur)
                VALUES (:r, :u, :v)
                ON DUPLICATE KEY UPDATE valeur = VALUES(valeur), date_update = CURRENT_TIMESTAMP";
        $st = $bdd->prepare($sql);
        $st->execute([':r'=>$idRecette, ':u'=>$idUser, ':v'=>$valeur]);
    }

    public static function averagesByRecetteIds(array $ids): array
    {
        global $bdd;
        if (empty($ids)) return [];
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id_recette, AVG(valeur) AS moyenne, COUNT(*) AS nb_notes
                FROM note
                WHERE id_recette IN ($in)
                GROUP BY id_recette";
        $st = $bdd->prepare($sql);
        $st->execute($ids);

        $out = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int)$row['id_recette'];
            $out[$rid] = [
                'moyenne'  => (float)$row['moyenne'],
                'nb_notes' => (int)$row['nb_notes'],
            ];
        }
        return $out;
    }

    /** Map [id_recette][id_utilisateur] = valeur (pour pré-remplir le formulaire) */
    public static function mapByRecetteAndUser(array $ids): array
    {
        global $bdd;
        if (empty($ids)) return [];
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id_recette, id_utilisateur, valeur
                FROM note
                WHERE id_recette IN ($in)";
        $st = $bdd->prepare($sql);
        $st->execute($ids);

        $map = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $map[(int)$row['id_recette']][(int)$row['id_utilisateur']] = (int)$row['valeur'];
        }
        return $map;
    }
}
