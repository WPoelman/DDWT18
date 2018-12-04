<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

/* Credentials */
$cred = set_cred('ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

/* Check if the user has the right credentials */
$router->before('GET|POST|PUT|DELETE', '/api/.*', function () use ($cred) {
    if (!check_cred($cred)) {
        $feedback = [
            'type' => 'danger',
            'message' => 'Authentication failed. Please check the credentials.'
        ];
        echo json_encode($feedback);
        exit();
    }
});

/* Routes */
$router->mount('/api', function () use ($router, $db) {
    /* Setting the content type */
    http_content_type("application/json");

    /* GET route: overview all series */
    $router->get('/series', function () use ($db) {
        $series_info = get_series($db);
        echo json_encode($series_info);

    });

    /* POST route: add series */
    $router->post('/series', function () use ($db) {
        $feedback = add_serie($db, $_POST);
        echo json_encode($feedback);

    });

    /* GET route: view single series */
    $router->get('series/(\d+)', function ($id) use ($db) {
        $single_serie_info = get_serieinfo($db, $id);
        echo json_encode($single_serie_info);
    });

    /* DELETE route: delete single series */
    $router->delete('series/(\d+)', function ($id) use ($db) {
        $feedback = remove_serie($db, $id);
        echo json_encode($feedback);
    });

    /* PUT route: update single series */
    $router->put('series/(\d+)', function ($id) use ($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'),$_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });

    /* ERROR: route not found */
    $router->set404(function () {
        header('HTTP/1.1 404 Not Found');
        $feedback = [
            "http-code" => 401,
            "error-message" => "The route you tried to access does not exist.",
        ];
        echo json_encode($feedback);
    });
});

/* Run the router */
$router->run();
