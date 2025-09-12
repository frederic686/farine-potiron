<?php

// Inclusion du fichier de connexion
require_once "library/init.php";


class Model {
    // Tableau associatif qui contiendra toutes les données de l’objet
    protected $data = [];


    protected $table;

    /**
     * Getter générique
     * Permet d’accéder aux données comme si c’étaient des propriétés
     * Exemple : $user->pseudo
     */
    public function __get($name) {
        return $this->data[$name] ?? null;
    }

    /**
     * Setter générique
     * Permet d’ajouter ou modifier une donnée
     * Exemple : $user->email = "mail@test.com"
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * Vérifie si une propriété existe
     * Exemple : isset($user->pseudo)
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * Supprime une propriété
     * Exemple : unset($user->email)
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * Retourne toutes les données de l’objet sous forme de tableau associatif
     */
    public function toArray(): array {
        return $this->data;
    }

    /**
     * Méthode GET générique
     * Permet de récupérer un enregistrement de la base SQL par ID
     */
public function get($id) {
    global $bdd;
    
    if ($id <= 0) {
        throw new Exception("ID invalide");
    }

    $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $id]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $this->data = $result;
        return $this;
    }

    return null;
}



    /**
     * Méthode UPDATE
     * Met à jour les données de l’objet dans la base SQL
     * ⚠️ Nécessite que :
     * - la table soit définie dans la classe enfant ($this->table)
     * - l’objet contienne une clé primaire "id"
     */
    public function update() {
        global $bdd;

        // Vérification : il faut un id pour mettre à jour
        if (!isset($this->data['id'])) {
            throw new Exception("Impossible de faire un update sans id !");
        }

        // Construction dynamique de la requête SQL
        $champs = [];
        $params = [];
        foreach ($this->data as $key => $value) {
            if ($key != 'id') { // on ne met pas à jour l’id
                $champs[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        // Ajout de l’id pour la clause WHERE
        $params['id'] = $this->data['id'];

        // Exemple de SQL généré :
        // UPDATE utilisateur SET pseudo = :pseudo, email = :email WHERE id = :id
        $sql = "UPDATE {$this->table} SET " . implode(", ", $champs) . " WHERE id = :id";

        $stmt = $bdd->prepare($sql);
        return $stmt->execute($params); // retourne true si succès
    }

    /**
     * Méthode DELETE générique
     * Permet de supprimer un enregistrement de la base SQL par ID
     */
    public function delete() {
        global $bdd;

        // Vérifie qu'il y a un 'id' dans l'objet avant de supprimer
        if (!isset($this->data['id'])) {
            throw new Exception("Impossible de supprimer sans id !");
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $bdd->prepare($sql);
        return $stmt->execute(['id' => $this->data['id']]);
    }
}
