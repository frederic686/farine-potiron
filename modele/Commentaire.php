<?php
// Commentaire.php
require_once "modele/_model.php";

class Commentaire extends Model
{
    protected $table = "commentaire";

    public static function create(array $data): int
    {
        global $bdd;

        $idRecette = (int)($data['id_recette'] ?? 0);
        $idUser    = (int)($data['id_utilisateur'] ?? 0);
        $texte     = (string)($data['texte'] ?? '');

        if ($idRecette <= 0 || $idUser <= 0) {
            throw new InvalidArgumentException("Paramètres invalides pour Commentaire::create");
        }

        $sql = "INSERT INTO commentaire (id_recette, id_utilisateur, texte)
                VALUES (:rid, :uid, :txt)";
        $st  = $bdd->prepare($sql);
        $st->execute([':rid'=>$idRecette, ':uid'=>$idUser, ':txt'=>$texte]);

        return (int)$bdd->lastInsertId();
    }

    /** Vérifie que le commentaire appartient bien à l’utilisateur */
    public static function existsForUser(int $commentId, int $userId): bool
    {
        global $bdd;
        if ($commentId <= 0 || $userId <= 0) return false;

        $sql = "SELECT 1 FROM commentaire WHERE id = :id AND id_utilisateur = :uid LIMIT 1";
        $st  = $bdd->prepare($sql);
        $st->execute([':id' => $commentId, ':uid' => $userId]);
        return (bool)$st->fetchColumn();
    }

    /** Dernier commentaire de l’utilisateur pour chaque recette (map [rid] => row) */
    public static function latestByUserForRecetteIds(array $recetteIds, int $userId): array
    {
        global $bdd;
        $recetteIds = array_values(array_unique(array_filter(array_map('intval', $recetteIds))));
        if (empty($recetteIds) || $userId <= 0) return [];

        $in  = implode(',', array_fill(0, count($recetteIds), '?'));
        $sql = "SELECT c.*
                FROM commentaire c
                WHERE c.id_recette IN ($in) AND c.id_utilisateur=?
                ORDER BY c.id_recette, c.date_creation DESC, c.id DESC";
        $st  = $bdd->prepare($sql);
        $st->execute([...$recetteIds, $userId]);

        $map = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int)$row['id_recette'];
            // On garde le premier rencontré (le plus récent grâce à l'ORDER BY)
            if (!isset($map[$rid])) {
                $map[$rid] = $row;
            }
        }
        return $map;
    }

    /**
     * Sécurise la mise à jour d’un commentaire (seul l’auteur peut le modifier)
     * - met à jour texte + date_update (sans dépendre d’un ON UPDATE au niveau SQL)
     * - renvoie true si l’UPDATE s’est bien exécuté (même si rowCount==0 quand texte identique)
     */
    public static function updateByIdAndUser(int $commentId, int $userId, string $texte): bool
    {
        global $bdd;
        if ($commentId <= 0 || $userId <= 0) return false;

        // Sécurité propriété
        if (!self::existsForUser($commentId, $userId)) {
            return false;
        }

        $sql = "UPDATE commentaire
                SET texte = :txt, date_update = NOW()
                WHERE id = :id AND id_utilisateur = :uid";
        $st = $bdd->prepare($sql);
        $ok = $st->execute([':txt'=>$texte, ':id'=>$commentId, ':uid'=>$userId]);

        // On considère la MAJ réussie si l’exécution s’est bien passée, même si rowCount()==0 (texte identique)
        return (bool)$ok;
    }

    /** Liste des commentaires par recette avec pseudo ET dates (inclut date_update) */
    public static function mapByRecetteIdsWithUsers(array $ids): array
    {
        global $bdd;
        if (empty($ids)) return [];

        $marks = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT
                    c.id,
                    c.id_recette,
                    c.id_utilisateur,
                    u.pseudo,
                    c.texte,
                    c.date_creation,
                    c.date_update
                FROM commentaire c
                JOIN utilisateur u ON u.id = c.id_utilisateur
                WHERE c.id_recette IN ($marks)
                ORDER BY c.date_creation DESC, c.id DESC";

        $st = $bdd->prepare($sql);
        $st->execute($ids);

        $out = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $rid = (int)$row['id_recette'];
            $out[$rid][] = [
                'id'             => (int)$row['id'],
                'id_utilisateur' => (int)$row['id_utilisateur'],
                'pseudo'         => (string)$row['pseudo'],
                'texte'          => (string)$row['texte'],
                'date_creation'  => (string)$row['date_creation'],
                'date_update'    => (string)($row['date_update'] ?? ''),
            ];
        }
        return $out;
    }


}
