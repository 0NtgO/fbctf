<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_login();

$teams = new Teams();
$levels = new Levels();
$countries = new Countries();

// Object to hold all the data.
$commands_line_data = (object) array();

// Preparing the results_library object.
$results_library = (object) array();
$results_library_key = "results_library";

// List of active countries.
$countries_results = array();
$countries_key = "country_list";
foreach ($countries->all_enabled_countries() as $country) {
	if (
		($country['used'] == 1) && ($countries->is_active_level($country['id']))
	) {
		array_push($countries_results, $country['name']);
	} 
}

// List of modules
$modules_results = array(
	"All", "Leaderboard", "Activity", "Teams", "Filter", "World Domination", "Game Clock"
);
$modules_key = "modules";

// List of active teams.
$teams_results = array();
$teams_key = "teams";
foreach ($teams->all_visible_teams() as $team) {
	array_push($teams_results, $team['name']);
}

// List of level categories.
$categories_results = array();
$categories_key = "categories";
foreach ($levels->all_categories() as $category) {
	array_push($categories_results, $category['category']);
}
array_push($categories_results, "All");
		
$results_library->{$countries_key} = $countries_results;
$results_library->{$modules_key} = $modules_results;
$results_library->{$teams_key} = $teams_results;
$results_library->{$categories_key} = $categories_results;

// Preparing the commands object
$commands = (object) array();
$commands_key = "commands";

// Teams information command: teams
$command_teams = (object) array();
$command_teams_function = (object) array();
$command_teams_function->{"name"} = "show-team";
$command_teams_key = "teams";
$command_teams->{"results"} = $teams_key;
$command_teams->{"function"} = $command_teams_function;
$commands->{$command_teams_key} = $command_teams;

// Attack country command: atk
$command_atk = (object) array();
$command_atk_function = (object) array();
$command_atk_function->{"name"} = "capture-country";
$command_atk_key = "atk";
$command_atk->{"results"} = $countries_key;
$command_atk->{"function"} = $command_atk_function;
$commands->{$command_atk_key} = $command_atk;

// Filter by category command: cat
$command_cat = (object) array();
$command_cat_function = (object) array();
$command_cat_function->{"name"} = "change-radio";
$command_cat_function->{"param"} = "fb--module--filter--category";
$command_cat_key = "cat";
$command_cat->{"results"} = $categories_key;
$command_cat->{"function"} = $command_cat_function;
$commands->{$command_cat_key} = $command_cat;

// Open module command: open
$command_open = (object) array();
$command_open_function = (object) array();
$command_open_function->{"name"} = "open-module";
$command_open_key = "open";
$command_open->{"results"} = $modules_key;
$command_open->{"function"} = $command_open_function;
$commands->{$command_open_key} = $command_open;

// Close module command: close
$command_close = (object) array();
$command_close_function = (object) array();
$command_close_function->{"name"} = "close-module";
$command_close_key = "close";
$command_close->{"results"} = $modules_key;
$command_close->{"function"} = $command_close_function;
$commands->{$command_close_key} = $command_close;

// Show list view command: list
$command_list = (object) array();
$command_list_function = (object) array();
$command_list_function->{"name"} = "open-listview";
$command_list_key = "list";
$command_list->{"results"} = array("On", "Off");
$command_list->{"function"} = $command_list_function;
$commands->{$command_list_key} = $command_list;

// Put it all together and print JSON.
$commands_line_data->{$results_library_key} = $results_library;
$commands_line_data->{$commands_key} = $commands;
header('Content-Type: application/json');
print json_encode($commands_line_data, JSON_PRETTY_PRINT);

?>
