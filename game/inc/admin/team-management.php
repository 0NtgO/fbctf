<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/teams.php');

sess_start();
sess_enforce_admin();

echo <<< EOT

<header class="admin-page-header">
    <h3>Team Management</h3>

    <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
    -->
    <span class="admin-section--status">status_<span class="highlighted">2015.10.15</span></span>
</header>

<div class="admin-sections">

<section class="admin-box validate-form section-locked completely-hidden">
  <form class="team_form">
      <header class="admin-box-header">
        <h3>New Team</h3>
      </header>

      <div class="fb-column-container">
        <div class="col col-pad col-1-2">
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label" for="">Team Name</label>
            <input name="team_name" type="text" value="">
          </div>
        </div>
        <div class="col col-pad col-1-2">
        <div class="form-el el--block-label el--full-text">
          <label class="admin-label" for="">Password</label>
          <input name="password" type="password" value="">
        </div>
      </div>
    </div>

  <div class="admin-row el--block-label">
    <label>Team Logo</label>
    <div class="fb-column-container">
      <div class="col col-shrink">
        <div class="post-avatar has-avatar"><svg class="icon icon--badge"><use xlink:href="#icon--badge-" /></svg></div>
        </div>
      <div class="col col-grow">
        <div class="selected-logo">
          <label>Selected Logo: </label>
          <span class="logo-name"></span>
        </div>
        <a href="#" class="alt-link js-choose-logo">Select Logo ></a>
      </div>
      <div class="col col-shrink admin-buttons">
        <a href="#" class="admin--edit" data-action="edit">EDIT</a>
        <button class="fb-cta cta--red" data-action="delete">Delete</button>
        <button class="fb-cta cta--yellow js-confirm-save" data-action="create">Create</button>
      </div>
    </div>
  </div>

  </form>
</section>

EOT;

$c = 1;
$teams = new Teams();
foreach ($teams->all_teams() as $team) {
  $team_name = htmlspecialchars($team['name']);
  $team_on = ($team['active'] == 1)
    ? 'checked'
    : '';
  $team_off = ($team['active'] == 0)
    ? 'checked'
    : '';
  $team_admin_on = ($team['admin'] == 1)
    ? 'checked'
    : '';
  $team_admin_off = ($team['admin'] == 0)
    ? 'checked'
    : '';
  $team_visible_on = ($team['visible'] == 1)
    ? 'checked'
    : '';
  $team_visible_off = ($team['visible'] == 0)
    ? 'checked'
    : '';

  echo <<< EOT
<section class="admin-box validate-form section-locked">
  <form class="team_form" name="team_{$team['id']}">
    <input type="hidden" name="team_id" value="{$team['id']}">
      <header class="admin-box-header">
        <h3>Team {$c}</h3>
        <div class="admin-section-toggle radio-inline">
          <input type="radio" name="fb--admin--team-{$team['id']}-status" id="fb--admin--team-{$team['id']}-status--on" {$team_on}>
          <label for="fb--admin--team-{$team['id']}-status--on">On</label>

          <input type="radio" name="fb--admin--team-{$team['id']}-status" id="fb--admin--team-{$team['id']}-status--off" {$team_off}>
          <label for="fb--admin--team-{$team['id']}-status--off">Off</label>
        </div>
      </header>


      <div class="fb-column-container">
        <div class="col col-pad col-1-3">
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label" for="">Team Name</label>
            <input name="team_name" type="text" value="{$team_name}" disabled>
          </div>
          <div class="form-el el--block-label el--full-text">
            <label class="admin-label" for="">Score</label>
            <input name="points" type="text" value="{$team['points']}" disabled>
          </div>
        </div>
        <div class="col col-pad col-1-3">
        <div class="form-el el--block-label el--full-text">
          <label class="admin-label" for="">Change Password</label>
          <input name="password" type="password" value="{$team['password']}" disabled>
          <input type="hidden" name="password2" value="{$team['password']}">
        </div>
      </div>
      <div class="col col-pad col-1-3">
        <div class="form-el el--block-label">
          <label class="admin-label" for="">Admin Level</label>
          <div class="admin-section-toggle radio-inline">
            <input type="radio" name="fb--admin--team-{$team['id']}-admin" id="fb--admin--team-{$team['id']}-admin--on" {$team_admin_on}>
            <label for="fb--admin--team-{$team['id']}-admin--on">On</label>

            <input type="radio" name="fb--admin--team-{$team['id']}-admin" id="fb--admin--team-{$team['id']}-admin--off" {$team_admin_off}>
            <label for="fb--admin--team-{$team['id']}-admin--off">Off</label>
          </div>
        </div>
      <div class="form-el el--block-label">
        <label class="admin-label" for="">Visibility </label>
        <div class="admin-section-toggle radio-inline">
          <input type="radio" name="fb--admin--team-{$team['id']}-visible" id="fb--admin--team-{$team['id']}-visible--on" {$team_visible_on}>
          <label for="fb--admin--team-{$team['id']}-visible--on">On</label>

          <input type="radio" name="fb--admin--team-{$team['id']}-visible" id="fb--admin--team-{$team['id']}-visible--off" {$team_visible_off}>
          <label for="fb--admin--team-{$team['id']}-visible--off">Off</label>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-row el--block-label">
    <label>Team Logo</label>
    <div class="fb-column-container">
      <div class="col col-shrink">
        <div class="post-avatar has-avatar"><svg class="icon icon--badge"><use xlink:href="#icon--badge-{$team['logo']}" /></svg></div>
        </div>
      <div class="col col-grow">
        <div class="selected-logo">
          <label>Selected Logo: </label>
          <span class="logo-name">{$team['logo']}</span>
        </div>
        <a href="#" class="alt-link js-choose-logo">Select Logo ></a>
      </div>
      <div class="col col-shrink admin-buttons">
        <a href="#" class="admin--edit" data-action="edit">EDIT</a>
        <button class="fb-cta cta--red" data-action="delete">Delete</button>
        <button class="fb-cta cta--yellow js-confirm-save" data-action="save">Save</button>
      </div>
    </div>
  </div>

  </form>
</section>
EOT;
  $c++;
}

echo <<< EOT
</div><!-- .admin-sections -->

<div class="admin-buttons">
    <button class="fb-cta" data-action="add-new">Add Team</button>
</div>
EOT;
?>
