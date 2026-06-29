<?php
/*
 * Base Controller
 * Loads the models and views
 */
class Controller {
    // Load model
    public function model($model) {
        require_once '../app/Models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        if (file_exists('../app/Views/' . $view . '.php')) {
            // Include main layout which will include the specific view inside it
            require_once '../app/Views/layout/main.php';
        } else {
            die('View does not exist');
        }
    }
}
