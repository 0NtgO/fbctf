<?hh

require_once('request.php');
require_once('../common/levels.php');
require_once('../common/teams.php');
require_once('../common/sessions.php');
require_once('../common/utils.php');

sess_start();
sess_enforce_login();

$filters = array(
  'POST' => array(
    'level_id'    => FILTER_VALIDATE_INT,
    'answer'        => FILTER_UNSAFE_RAW,
    'action'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    )
  )
);
$actions = array(
  'answer_level',
  'get_hint',
  'open_level',
);
$request = new Request($filters, $actions);
$request->processRequest();

switch ($request->action) {
  case 'none':
    game_page();
    break;
  case 'answer_level':
    $levels = new Levels();
    // Check if answer is valid
    if ($levels->check_answer(
      $request->parameters['level_id'],
      $request->parameters['answer']
    )) {
      // Give points!
      $levels->score_level(
        $request->parameters['level_id'],
        sess_team()
      );
      // Update teams last score
      $teams = new Teams();
      $teams->last_score(sess_team());
      ok_response();
    } else {
      $levels->log_failed_score(
        $request->parameters['level_id'],
        sess_team(),
        $request->parameters['answer']
      );
      error_response();
    }
    break;
  case 'get_hint':
    $levels = new Levels();
    $requested_hint = $levels->get_hint(
      $request->parameters['level_id'],
      sess_team()
    );
    if ($requested_hint) {
      hint_response($requested_hint, 'OK');
    } else {
      hint_response('', 'ERROR');
    }
    break;
  case 'open_level':
    ok_response();
    break;
  default:
    game_page();
    break;
}
