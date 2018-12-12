<?php
/**
 * = Programme de gestion de tirelire.
 * Un tirelire peut contenir des pièces de 1CHF, 2CHF et 5CHF.
 * L'utilisateur peut ajouter ou retirer une ou plusieurs pièces de la tirelire
 * La tirelire peut contenir entre 0 et NB_MAX pièces de chaque sorte.
 *
 * == Conception
 *  
 * L'application est séparée en 2 classes : 
 *
 * . Coin: Est responsable de mémoriser les informations liées à 1 sorte de pièce
 * . Tirelire: Est responsable de gérer l'application web
 *
 * === Gestion de la session
 * 
 * Pour éviter de devoir accéder directement à la variable super globale $_SESSION,
 * la classe Tirelire doit être instanciée à l'aide de la méthode statique *Open*
 * qui tente de désérialiser l'objet depuis la session en cours. Si l'objet
 * n'existe pas, il sera crée par un appel au constructeur privé.
 *
 * Lorsque la page PHP se termine, l'objet de la classe Tirelire est automatiquement 
 * sauvé en session grâce à l'appel de __destructor par le PHP.
 * 
 * @author Nicolas Wanner
 * @version 1.0 12.12.2018 Première version finalisée
 */

class Coin {
    public $count;      // Combien de pièces
    public $value;      // Valeur de la pièce
    public $image;      // L'image
    public $message;    // Le message

    public function __construct($aValue = 1, $aImage='img/CHF1.png') {
        $this->count   = 0;
        $this->value   = $aValue;
        $this->image   = $aImage;
        $this->message = '';
    }

    /**
     * Calculer le total d'une sorte de pièce
     * @return Integer Total d'argent d'une sorte de pièce
     */
    public function total() {
        return $this->count * $this->value;
    }
}

class Tirelire {
    // Maximum de pièces par section
    public const NB_MAX = 10;

    // Tableau des pièces
    public $coins = [];

    /**
     * Initialiser les éléments de l'application
     */
    public function Initialize() {
        $this->coins = [
            "CHF1" => new Coin(1, 'img/CHF1.jpg'),
            "CHF5" => new Coin(2, 'img/CHF2.jpg'),
            "CHF2" => new Coin(5, 'img/CHF5.jpg'),
        ];
    }

    /**
     * Calculer le total de la tirelire
     * @return Integer Total d'argent dans la tirelire
     */
    public function total() {
        $total = 0;
        foreach ($this->coins as $coin) {
            $total += $coin->total();
        }
        return $total;
    }

    /**
     * Ajouter des pièces
     * @param String  $coinId   Nom de la pièce
     * @param Integer $quantity Nombre de pièces à ajouter
     */
    public function add($index, $quantity) {
        $theCoin = $this->coins[$index];
        $theCoin->count += max($quantity, 0);
        if ($theCoin->count > Tirelire::NB_MAX) {
            $added = $quantity - ($theCoin->count - Tirelire::NB_MAX);
            $theCoin->message = "il n'y a plus de place! $added pièce(s) ajoutée(s)";
            $theCoin->count   = Tirelire::NB_MAX;
        }
    }

    /**
     * Enlever des pièces
     * @param String  $coinId   Nom de la pièce
     * @param Integer $quantity Nombre de pièces à enlever
     */
    public function sub($index, $quantity) {
        $theCoin = $this->coins[$index];
        $theCoin->count -= max($quantity, 0);
        if ($theCoin->count < 0) {
            $removed = $quantity + $theCoin->count;
            $theCoin->message = "il n'y a plus de pièces! $removed pièce(s) retirée(s)";
            $theCoin->count   = 0;
        } 
    }

    /**
     * Gestion des actions liées au formulaire.Dépend de la page HTML
     */
    public function controller() {
        $action   = filter_input(INPUT_POST, 'action');

        // Effacer les messages dans les pièces
        foreach ($this->coins as $coin) $coin->message = '';

        // Traitement seulement si les 2 champs sont valides et renseignés
        if ($action) {

            // Si l'action contient un espace alors le contenu après l'espace
            // identifie la sorte de pièce qui est traitée. avec explode et list
            // on obtient le résultat dans 2 variables
            list($action, $coinId) = explode(' ', $action);

            // Lire uniquement l'input concerné par l'action ($coinId)
            $quantity = filter_input(INPUT_POST, $coinId, FILTER_VALIDATE_INT);

            switch($action) {
                case 'vider' :
                    $this->Initialize();
                    break;

                case 'ajouter' :
                    if (isset($this->coins[$coinId])) {
                        $this->add($coinId, $quantity);
                    }
                    break;

                case 'enlever' :
                    if (isset($this->coins[$coinId])) {
                        $this->sub($coinId, $quantity);
                    }
                    break;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Les trucs ici servent à sauver / charger l'application en session

    /**
     * Nom de l'application en session ($_SESSION[$this->name])
     * @var string
     */
    public $name  = '';

    /**
     * Méthode statique pour récupérer la référence de l'opbjet application
     * Si l'application existe dans la session elle est retournée, sinon
     * une nouvelle instance est créée
     * @param string  $name       Nom de l'application en session
     * @param boolean $forceReset Force la création d'une nouvelle instance
     */
    static function Open($name, $forceReset=false) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!$forceReset && isset($_SESSION[$name])) {
            return unserialize($_SESSION[$name]);
        }
        return new Tirelire($name);
    }

    /**
     * Constructeur privé, il fait appel à la méthode Initialize
     * pour initialiser les attributs de l'application. Ne devrait pas être
     * modifié.
     *
     * Le constructeur est privé pour forcer l'utilisation de la méthode open
     * pour ouvrir l'application
     */
    private function __construct($name) {
        $this->name = $name;
        $this->Initialize();
    }

    /**
     * Le destructeur est appelé automatiquement. Son rôle est de sauver le
     * contenu de l'application en session
     */
    public function __destruct() {
        $_SESSION[$this->name] = serialize($this);
    }
}

