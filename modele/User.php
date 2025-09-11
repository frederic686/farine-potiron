<?php
require_once "modele/_model.php";  // ta classe mère Model

class User extends Model {
    protected $table = "utilisateur";

    /**
     * Insérer un nouvel utilisateur
     */
    public function insert() {
        global $bdd;

        $sql = "INSERT INTO {$this->table} (pseudo, email, password) 
                VALUES (:pseudo, :email, :password)";
        $stmt = $bdd->prepare($sql);

        return $stmt->execute([
            "pseudo"   => $this->pseudo,
            "email"    => $this->email,
            "password" => password_hash($this->password, PASSWORD_BCRYPT)
        ]);
    }

    /**
     * Charger un utilisateur par son id
     */
    public static function findById($id) {
        global $bdd;

        $sql = "SELECT * FROM utilisateur WHERE id = :id LIMIT 1";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(["id" => $id]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }


    /**
     * Charger un utilisateur par pseudo ou email
     */
    public function findByLogin($login) {
        global $bdd;

        $sql = "SELECT * FROM {$this->table} WHERE pseudo = :login OR email = :login";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(["login" => $login]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->data = $row;
            return true;
        }
        return false;
    }

    /**
     * Vérifier le mot de passe
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    /**
     * Supprimer l’utilisateur
     */
    public function delete() {
        global $bdd;

        if (!isset($this->id)) {
            throw new Exception("Impossible de supprimer sans id !");
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $bdd->prepare($sql);
        return $stmt->execute(["id" => $this->id]);
    }

        public static function findByIdentifiant($identifiant) {
        global $bdd;
        $sql = "SELECT * FROM utilisateur WHERE pseudo = :identifiant OR email = :identifiant LIMIT 1";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(['identifiant' => $identifiant]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

       /** Retourne une map id_utilisateur => pseudo */
        public static function pseudosByIds(array $ids): array
        {
            global $bdd;
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            if (empty($ids)) return [];

            $in  = implode(',', array_fill(0, count($ids), '?'));
            $sql = "SELECT id, pseudo FROM utilisateur WHERE id IN ($in)";
            $st  = $bdd->prepare($sql);
            $st->execute($ids);

            $map = [];
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $map[(int)$row['id']] = (string)$row['pseudo'];
            }
            return $map;
        }
    }