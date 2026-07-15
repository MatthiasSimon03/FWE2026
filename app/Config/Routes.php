<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultController('Home');
$routes->get('/', 'Home::index');
$routes->get('overview', 'OverviewController::index');


// FlightMeet App
$routes->group('flightmeet', ['filter' => 'fm_auth', 'namespace' => 'App\Controllers\FlightMeet'], static function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('meetups', 'MeetupController::index');

    $routes->get('chat', 'ChatController::index');
    $routes->match(['get', 'post'], 'profile', 'ProfileController::index');

    $routes->get('meetups/(:num)', 'MeetupController::detail/$1');
    $routes->post('meetups/join/(:num)', 'MeetupController::join/$1');
    $routes->post('meetups/leave/(:num)', 'MeetupController::leave/$1');
    $routes->match(['get', 'post'], 'meetups/create', 'MeetupController::create');
    $routes->match(['get', 'post'], 'meetups/edit/(:num)', 'MeetupController::edit/$1');
    $routes->post('meetups/delete/(:num)', 'MeetupController::delete/$1');

    $routes->get('chat/getMessages', 'ChatController::getMessages');
    $routes->post('chat/sendMessage', 'ChatController::sendMessage');

    // FlightMeet Gruppen
    $routes->get('groups', 'GroupController::index');
    $routes->match(['get', 'post'], 'groups/create', 'GroupController::create');
    $routes->get('groups/detail/(:num)', 'GroupController::detail/$1');
    $routes->match(['get', 'post'], 'groups/edit/(:num)', 'GroupController::edit/$1');
    $routes->post('groups/delete/(:num)', 'GroupController::delete/$1');

    // Beitritts- & Anfrage-Routen
    $routes->post('groups/join/(:num)', 'GroupController::join/$1');
    $routes->post('groups/leave/(:num)', 'GroupController::leave/$1');
    $routes->post('groups/request-join/(:num)', 'GroupController::requestJoin/$1');
    $routes->post('groups/approve-request/(:num)', 'GroupController::approveRequest/$1');
    $routes->post('groups/reject-request/(:num)', 'GroupController::rejectRequest/$1');

    // Rollen-Management (Befördern / Herabstufen / Löschen von Mitgliedern)
    $routes->post('groups/promote/(:num)/(:num)', 'GroupController::promoteToAdmin/$1/$2');
    $routes->post('groups/demote/(:num)/(:num)', 'GroupController::demoteFromAdmin/$1/$2');
    $routes->post('groups/transfer-owner/(:num)/(:num)', 'GroupController::transferOwner/$1/$2');
    $routes->post('groups/remove-member/(:num)/(:num)', 'GroupController::removeMember/$1/$2');

    $routes->get('admin/personen', 'AdminController::personen');
});

// FlightMeet Login (ohne Filter, der Zugriff schützt)
$routes->group('flightmeet', ['namespace' => 'App\\Controllers\\FlightMeet'], static function ($routes) {
    $routes->get('auth/login', 'Auth::login');
    $routes->post('auth/login', 'Auth::login');
    $routes->get('auth/register', 'Auth::register');
    $routes->post('auth/register', 'Auth::register');
    $routes->post('auth/logout', 'Auth::logout');
});

// FlightMeet REST API Routen für Administratoren
$routes->group('api/flightmeet/admin', ['namespace' => 'App\Controllers\FlightMeet\Admin'], static function ($routes) {

    // Öffentliche Route zum Einloggen (Erhalt des Tokens)
    $routes->post('auth/login', 'ApiAuthController::login');

    // Durch den 'fm_api_admin'-Filter geschützte Admin-Routen
    $routes->group('', ['filter' => 'fm_api_admin'], static function ($routes) {
        $routes->get('users', 'ApiUserController::index');
        $routes->put('users/(:num)', 'ApiUserController::update/$1');
        $routes->delete('users/(:num)', 'ApiUserController::delete/$1');
    });

});



// Stadtrallye App
$routes->group('stadtrallye', ['namespace' => 'App\\Controllers\\Stadtrallye'], static function ($routes) {
	$routes->get('/', 'Home::index');

	$routes->get('auth/register', 'Auth::register');
	$routes->post('auth/register', 'Auth::register');
	$routes->get('auth/login', 'Auth::login');
	$routes->post('auth/login', 'Auth::login');
	$routes->post('auth/logout', 'Auth::logout');

	$routes->get('rally', 'RallyController::index');
	$routes->get('rally/(:num)', 'RallyController::show/$1');

	$routes->get('station/(:num)', 'StationController::show/$1');
	$routes->post('station/task/(:num)/submit', 'StationController::submitAnswer/$1');

	$routes->get('leaderboard', 'LeaderboardController::index');
	$routes->get('leaderboard/(:num)', 'LeaderboardController::index/$1');

	$routes->get('admin', 'Admin\Dashboard::index');
	$routes->get('admin/rallies', 'Admin\Rallies::index');
	$routes->get('admin/rallies/create', 'Admin\Rallies::create');
	$routes->post('admin/rallies/create', 'Admin\Rallies::create');
	$routes->get('admin/rallies/(:num)/edit', 'Admin\Rallies::edit/$1');
	$routes->post('admin/rallies/(:num)/edit', 'Admin\Rallies::edit/$1');
	$routes->post('admin/rallies/(:num)/delete', 'Admin\Rallies::delete/$1');

	$routes->get('admin/stations', 'Admin\Stations::index');
	$routes->get('admin/stations/(:num)', 'Admin\Stations::index/$1');
	$routes->get('admin/stations/(:num)/create', 'Admin\Stations::create/$1');
	$routes->post('admin/stations/(:num)/create', 'Admin\Stations::create/$1');
	$routes->get('admin/stations/edit/(:num)', 'Admin\Stations::edit/$1');
	$routes->post('admin/stations/edit/(:num)', 'Admin\Stations::edit/$1');
	$routes->post('admin/stations/delete/(:num)', 'Admin\Stations::delete/$1');

	$routes->get('admin/tasks/(:num)', 'Admin\Tasks::index/$1');
	$routes->get('admin/tasks/create/(:num)', 'Admin\Tasks::create/$1');
	$routes->post('admin/tasks/create/(:num)', 'Admin\Tasks::create/$1');
	$routes->get('admin/tasks/edit/(:num)', 'Admin\Tasks::edit/$1');
	$routes->post('admin/tasks/edit/(:num)', 'Admin\Tasks::edit/$1');
	$routes->post('admin/tasks/delete/(:num)', 'Admin\Tasks::delete/$1');
});



