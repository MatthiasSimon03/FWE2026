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
    $routes->get('groups', 'GroupController::index');
    $routes->get('chat', 'ChatController::index');
    $routes->get('profile', 'ProfileController::index');
    $routes->get('meetups/(:num)', 'MeetupController::detail/$1');

    $routes->post('meetups/join/(:num)', 'MeetupController::join/$1');
    $routes->post('meetups/leave/(:num)', 'MeetupController::leave/$1');
    $routes->match(['get', 'post'], 'meetups/create', 'MeetupController::create');
    $routes->match(['get', 'post'], 'meetups/edit/(:num)', 'MeetupController::edit/$1');
    $routes->post('meetups/delete/(:num)', 'MeetupController::delete/$1');
});

// FlightMeet Login (ohne Filter, der Zugriff schützt)
$routes->group('flightmeet', ['namespace' => 'App\\Controllers\\FlightMeet'], static function ($routes) {
    $routes->get('auth/login', 'Auth::login');
    $routes->post('auth/login', 'Auth::login');
    $routes->get('auth/register', 'Auth::register');
    $routes->post('auth/register', 'Auth::register');
    $routes->post('auth/logout', 'Auth::logout');
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



