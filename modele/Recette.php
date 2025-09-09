<?php
require_once "modele/_model.php";

class Recette extends Model {
    protected $table = "recette";

    /** Crée une recette et retourne son id */
    public function insert(): int {
        global $bdd;
        $sql = "INSERT INTO {$this->table} (titre, description, duree, difficulte, id_utilisateur)
                VALUES (:titre, :description, :duree, :difficulte, :id_utilisateur)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'titre'          => $this->titre,
            'description'    => $this->description,
            'duree'          => (int)$this->duree,
            'difficulte'     => $this->difficulte,
            'id_utilisateur' => (int)$this->id_utilisateur
        ]);
        return (int)$bdd->lastInsertId();
    }

    /** Met à jour une recette (sécurisé par id_utilisateur) */
    public function updateOwned(int $userId): bool {
        global $bdd;
        $sql = "UPDATE {$this->table}
                SET titre=:titre, description=:description, duree=:duree, difficulte=:difficulte
                WHERE id=:id AND id_utilisateur=:uid";
        $stmt = $bdd->prepare($sql);
        return $stmt->execute([
            'titre'       => $this->titre,
            'description' => $this->description,
            'duree'       => (int)$this->duree,
            'difficulte'  => $this->difficulte,
            'id'          => (int)$this->id,
            'uid'         => (int)$userId
        ]);
    }

    /** Supprime une recette appartenant à l'utilisateur (cascade via FK) */
    public static function deleteOwned(int $id, int $userId): bool {
        global $bdd;
        $sql = "DELETE FROM recette WHERE id=:id AND id_utilisateur=:uid";
        $stmt = $bdd->prepare($sql);
        return $stmt->execute(['id' => $id, 'uid' => $userId]);
    }

    /** Charge une recette appartenant à l'utilisateur */
    public static function findByIdAndUser(int $id, int $userId) {
        global $bdd;
        $sql = "SELECT * FROM recette WHERE id=:id AND id_utilisateur=:uid LIMIT 1";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Liste des recettes d'un utilisateur */
    public static function findByUser(int $userId): array {
        global $bdd;
        $sql = "SELECT * FROM recette WHERE id_utilisateur=:uid ORDER BY date_creation DESC";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Existe ? */
    public static function exists(int $id): bool {
        global $bdd;
        $stmt = $bdd->prepare("SELECT 1 FROM recette WHERE id=:id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    /** Récupère l'auteur (id_utilisateur) d'une recette */
    public static function ownerId(int $id): ?int {
        global $bdd;
        $stmt = $bdd->prepare("SELECT id_utilisateur FROM recette WHERE id=:id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetchColumn();
        return $res !== false ? (int)$res : null;
    }

    /** Toutes les recettes publiques (avec auteur minimal) */
    public static function allPublic(): array {
        global $bdd;
        $sql = "SELECT id, titre, description, duree, difficulte, date_creation, id_utilisateur
                FROM recette
                ORDER BY date_creation DESC, id DESC";
        return $bdd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche mot-clé dans:
     * - titre/description
     * - ingrédients libres (ingredient.nom)
     * - farines F&P dont le libellé du catalogue matche (via fetchCatalogueFarines())
     */
    public static function searchByKeywordWithIngredients(string $q): array {
        global $bdd;
        $q = trim($q);

        if ($q === '') {
            return self::allPublic();
        }

        // 1) Libellés de farines F&P dont le libellé matche le mot-clé (via fetchCatalogueFarines)
        $labels = [];
        $catalogue = [];
        if (function_exists('fetchCatalogueFarines')) {
            $catalogue = fetchCatalogueFarines(); // [ref => libellé]
        } else {
            // Fallback pour éviter une fatal error si la fonction n'est pas incluse
            $url = "https://api.mywebecom.ovh/play/fep/catalogue.php";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 6,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json']
            ]);
            $resp = curl_exec($ch);
            curl_close($ch);
            $tmp = json_decode($resp ?? '[]', true);
            $catalogue = is_array($tmp) ? $tmp : [];
        }

        if (is_array($catalogue)) {
            $needle = mb_strtolower($q);
            foreach ($catalogue as $ref => $lib) {
                $lib = (string)$lib;
                if (mb_strpos(mb_strtolower($lib), $needle) !== false) {
                    // On garde le libellé pour matcher sur ingredient.nom
                    $labels[] = $lib;
                }
            }
            $labels = array_values(array_unique($labels));
        }

        // 2) Conditions SQL (titre/description + ingrédients libres)
        $where  = [];
        $params = [];

        $where[] = "(r.titre LIKE :kw OR r.description LIKE :kw)";
        $params[':kw'] = "%{$q}%";

        $where[] = "(i.nom LIKE :kwIng)";
        $params[':kwIng'] = "%{$q}%";

        // 3) Requête de base
        $sql = "SELECT DISTINCT r.*, u.pseudo AS auteur_pseudo
                FROM recette r
                JOIN utilisateur u ON u.id = r.id_utilisateur
                LEFT JOIN ingredient i ON i.id_recette = r.id";

        // 4) Si on a des libellés F&P liés au mot-clé, on ajoute un EXISTS sur ingredient.nom
        if (!empty($labels)) {
            $libPlaceholders = [];
            foreach ($labels as $idx => $lib) {
                $ph = ":lib{$idx}";
                $libPlaceholders[] = "fi.nom LIKE {$ph}";
                $params[$ph] = '%' . $lib . '%';
            }

            $existsSql = "EXISTS (
                SELECT 1
                FROM ingredient fi
                WHERE fi.id_recette = r.id
                AND (" . implode(' OR ', $libPlaceholders) . ")
            )";

            $sql .= " WHERE ( " . implode(' OR ', $where) . " OR {$existsSql} )";
        } else {
            $sql .= " WHERE " . implode(' OR ', $where);
        }

        $sql .= " ORDER BY r.date_creation DESC, r.id DESC";

        $st = $bdd->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}