<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week2', 'ddwt18', 'ddwt18');

/* Get number of series */
$nbr_series = count_series($db);

/* Get number of users */
$nbr_users = count_users($db);

/* Display the default cards on every page */
$right_column = use_template('cards');

/* Set the default routes for the navigation bar */
$navigation_tpl = Array(
    0 => Array(
        'name' => 'Login',
        'url' => '/DDWT18/week2/login/'
    ),
    1 => Array(
        'name' => 'Home',
        'url' => '/DDWT18/week2/'
    ),
    2 => Array(
        'name' => 'Overview',
        'url' => '/DDWT18/week2/overview/'
    ),
    3 => Array(
        'name' => 'My Account',
        'url' => '/DDWT18/week2/myaccount/'
    ),
    4 => Array(
        'name' => 'Register',
        'url' => '/DDWT18/week2/register/'
    ),
    5 => Array(
        'name' => 'Add',
        'url' => '/DDWT18/week2/add/'
    )
);

/* Redundant code */
/**
 * $nbr_series = count_series($db); in every route
 *
 * $right_column = use_template('cards'); in every route
 *
 * $navigation = get_navigation([
 *      'Home' => na('/DDWT18/week2/', True),
 *      'Overview' => na('/DDWT18/week2/overview/', False),
 *      'Add series' => na('/DDWT18/week2/add/', False),
 *      'My Account' => na('/DDWT18/week2/myaccount/', False),
 *      'Registration' => na('/DDWT18/week2/register/', False)
 *  ]); in every GET route
 *
 * All the page- and navigation content on every POST route.
 *
 **/

/* Landing page */
if (new_route('/DDWT18/week2/', 'get')) {

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Home' => na('/DDWT18/week2/', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 1);

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT18/week2/overview/', 'get')) {

    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $series = get_series($db);

    /* Get the names from the users from the db */
    $names_user = array();
    foreach ($series as $key => $value){
        $names_user[] = get_user_names($db, $value['user']);
    }
    $left_content = get_serie_table($series, $names_user);


    /* Choose Template */
    include use_template('main');
}

/* Single Serie */
elseif (new_route('/DDWT18/week2/serie/', 'get')) {

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Get series from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = $serie_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Overview' => na('/DDWT18/week2/overview/', False),
        $serie_info['name'] => na('/DDWT18/week2/serie/?serie_id=' . $serie_id, True)
    ]);
    $navigation = get_navigation($navigation_tpl, 2);

    /* Page content */
    $page_subtitle = sprintf("Information about %s", $serie_info['name']);
    $page_content = $serie_info['abstract'];
    $nbr_seasons = $serie_info['seasons'];
    $creators = $serie_info['creator'];
    $user_id = $serie_info['user'];
    $added_by = get_user_names($db, $user_id);
    if (get_user_id() == $user_id) {
        $display_buttons = True;
    } else {
        $display_buttons = False;
    }

    /* Choose Template */
    include use_template('serie');
}

/* Add serie GET */
elseif (new_route('/DDWT18/week2/add/', 'get')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    } else {

        /* Get error msg from POST route */
        if (isset($_GET['error_msg'])) {
            $error_msg = get_error($_GET['error_msg']);
        }
        /* Page info */
        $page_title = 'Add Series';
        $breadcrumbs = get_breadcrumbs([
            'DDWT18' => na('/DDWT18/', False),
            'Week 2' => na('/DDWT18/week2/', False),
            'Add Series' => na('/DDWT18/week2/new/', True)
        ]);
        $navigation = get_navigation($navigation_tpl, 5);

        /* Page content */
        $page_subtitle = 'Add your favorite series';
        $page_content = 'Fill in the details of you favorite series.';
        $submit_btn = "Add Series";
        $form_action = '/DDWT18/week2/add/';

        /* Choose Template */
        include use_template('new');
    }
}

/* Add serie POST */
elseif (new_route('/DDWT18/week2/add/', 'post')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    }

    /* Add serie to database */
    $feedback = add_serie($db, $_POST, $_SESSION['user_id']);

    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/add/?error_msg=%s',
        json_encode($feedback)));
}

/* Edit serie GET */
elseif (new_route('/DDWT18/week2/edit/', 'get')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    }

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Get serie info from db */
    $serie_id = $_GET['serie_id'];
    $serie_info = get_serieinfo($db, $serie_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT18/week2/new/', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 2);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT18/week2/edit/';

    /* Choose Template */
    include use_template('new');
}

/* Edit serie POST */
elseif (new_route('/DDWT18/week2/edit/', 'post')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    }

    /* Update serie in database */
    $feedback = update_serie($db, $_POST, $_SESSION['user_id']);
    $error_msg = get_error($feedback);

    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT18/week2/serie/?error_msg=%s&serie_id=%d',
        json_encode($feedback), $_POST['serie_id']));
}

/* Remove serie */
elseif (new_route('/DDWT18/week2/remove/', 'post')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    }

    /* Remove serie in database */
    $serie_id = $_POST['serie_id'];
    $feedback = remove_serie($db, $serie_id, $_SESSION['user_id']);
    $error_msg = get_error($feedback);

    /* Redirect to add GET route */
    redirect(sprintf('/DDWT18/week2/add/?error_msg=%s',
        json_encode($feedback)));
}

/* My account GET */
elseif (new_route('/DDWT18/week2/myaccount/', 'get')) {

    /* Check if user is logged in */
    if (!check_login()) {
        redirect('/DDWT18/week2/login/');
    }

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'My account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        sprintf("My account") => na('/DDWT18/week2/myaccount/', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 3);

    /* Page content */
    $page_subtitle = 'Welcome.';
    $page_content = 'View you account below.';
    $user_id = $_SESSION['user_id'];
    $user_names = get_user_names($db, $user_id);
    $user = $user_names['firstname'];

    /* Choose Template */
    include use_template('account');
}

/* Register GET */
elseif (new_route('/DDWT18/week2/register/', 'get')) {

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }
    /* Page info */
    $page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Register' => na('/DDWT18/week2/register/', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 4);

    /* Page content */
    $page_subtitle = 'Register on Series Overview!';

    /* Choose Template */
    include use_template('register');
}

/* Register POST */
elseif (new_route('/DDWT18/week2/register/', 'post')) {
    /* Register user */
    $feedback = register_user($db, $_POST);

    /* Redirect to homepage */
    redirect(sprintf('/DDWT18/week2/register/?error_msg=%s',
        json_encode($feedback)));
}

/* Log in GET */
elseif (new_route('/DDWT18/week2/login/', 'get')) {

    /* Check if user is already logged in */
    if (check_login()) {
        redirect('/DDWT18/week2/myaccount/');
    }

    /* Get error msg from POST route */
    if (isset($_GET['error_msg'])) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT18' => na('/DDWT18/', False),
        'Week 2' => na('/DDWT18/week2/', False),
        'Login' => na('/DDWT18/week2/login/', True)
    ]);
    $navigation = get_navigation($navigation_tpl, 0);

    /* Page content */
    $page_subtitle = 'Use your username and password to login';

    /* Choose Template */
    include use_template('login');
}

/* Log in POST */
elseif (new_route('/DDWT18/week2/login/', 'post')) {
    /* Log user in */
    $feedback = login_user($db, $_POST);

    /* Redirect to my account */
    redirect(sprintf('/DDWT18/week2/myaccount/?error_msg=%s',
        json_encode($feedback)));
}

/* Log out GET */
elseif (new_route('/DDWT18/week2/logout/', 'get')) {
    /* Log user out */
    $feedback = logout_user();

    /* Redirect to homepage */
    redirect(sprintf('/DDWT18/week2/?error_msg=%s',
        json_encode($feedback)));
}

else {
    http_response_code(404);
}