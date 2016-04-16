<?hh

require_once('../vendor/autoload.php');

sess_start();
sess_enforce_login();

$filters = array(
  'POST' => array(
    'level_id'    => FILTER_VALIDATE_INT,
    'answer'      => FILTER_UNSAFE_RAW,
    'csrf_token'  => FILTER_UNSAFE_RAW,  
    'action'      => array(
      'filter'      => FILTER_VALIDATE_REGEXP,
      'options'     => array(
        'regexp'      => '/^[\w-]+$/'
      ),
    ),
    'page'      => array(
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
$request = new Request($filters, $actions, array());
$request->processRequest();

if ($request->action !== 'none') {
  // CSRF check
  if ($request->parameters['csrf_token'] !== sess_csrf_token()) {
    error_page();
  }
}

switch ($request->action) {
  case 'none':
    game_page();
    break;
  case 'answer_level':
    if (Configuration::get('scoring')->getValue() === '1') {
      // Check if answer is valid
      if (Level::checkAnswer(
        intval($request->parameters['level_id']),
        $request->parameters['answer']
      )) {
        // Give points!
        Level::scoreLevel(
          intval($request->parameters['level_id']),
          intval(sess_team())
        );
        // Update teams last score
        Team::lastScore(intval(sess_team()));
        ok_response('Success', 'game');
      } else {
        Level::logFailedScore(
          intval($request->parameters['level_id']),
          intval(sess_team()),
          $request->parameters['answer']
        );
        error_response('Failed', 'game');
      }
    } else {
      error_response('Failed', 'game');
    }
    break;
  case 'get_hint':
    $requested_hint = Level::getLevelHint(
      intval($request->parameters['level_id']),
      intval(sess_team())
    );
    if ($requested_hint) {
      hint_response($requested_hint, 'OK');
    } else {
      hint_response('', 'ERROR');
    }
    break;
  case 'open_level':
    ok_response('Success', 'admin');
    break;
  default:
    game_page();
    break;
}
