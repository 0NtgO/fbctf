<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/sessions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/levels.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/attachments.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/links.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/../common/countries.php');

sess_start();
sess_enforce_admin();

function select_countries($selected=0, $used_included = true) {
  $select_html = '<select name="entity_id">';
  $select_html .= '<option value="0">Auto</option>';
  $countries = new Countries();
  $all_countries = ($used_included) 
    ? $countries->all_enabled_countries()
    : $countries->all_available_countries();
  foreach ($all_countries as $country) {
    if ($country['id'] == $selected) {
      $select_html .= '<option value="'.$country['id'].'" selected>'.$country['name'].'</option>';
    } else {
      $select_html .= '<option value="'.$country['id'].'">'.$country['name'].'</option>';
    }
  }
  $select_html .= '</select>';

  return $select_html;
}

function select_categories($selected=0) {
  $select_html = '<select name="category_id">';
  $select_html .= '<option value="">Select</option>';
  $levels = new Levels();
  $categories = $levels->all_categories();
  foreach ($categories as $category) {
    // Skip Quiz in flags
    if ($category['category'] == 'Quiz') {
      continue;
    }
    
    if ($category['id'] == $selected) {
      $select_html .= '<option value="'.$category['id'].'" selected>'.$category['category'].'</option>';
    } else {
      $select_html .= '<option value="'.$category['id'].'">'.$category['category'].'</option>';
    }
  }
  $select_html .= '</select>';

  return $select_html;
}

$select_available_html = select_countries(0, false);
$select_categories_html = select_categories(0);

echo <<< EOT

<header class="admin-page-header">
  <h3>Flags Management</h3>
  <!--
    * @note
    * this will reflect the last saved time inside the
    *  "highlighted" span
-->
<span class="admin-section--status">status_<span class="highlighted">OK</span></span>
</header>

  <div class="admin-sections">
EOT;

$levels = new Levels();
$attachments = new Attachments();
$links = new Links();

// Hidden element for creating new flag level
echo <<< EOT
    <section class="admin-box completely-hidden">
      <form class="level_form flag_form">
        <input type="hidden" name="level_type" value="flag">
        <header class="admin-box-header">
          <h3>New Flag Level</h3>
        </header>

        <div class="fb-column-container">
          
          <div class="col col-pad col-1-2">
            
            <div class="form-el el--block-label el--full-text">
              <label>Description</label>
              <textarea name="description" placeholder="Level description" rows="4"></textarea>
            </div>
            
            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-2 el--block-label el--full-text">
                <label for="">Country</label>
                {$select_available_html}
              </div>

              <div class="col col-1-2 el--block-label el--full-text">
                <label for="">Category</label>
                {$select_categories_html}
              </div>
            </div>

            <div class="form-el el--block-label el--full-text">
              <label>Hint</label>
              <input name="hint" type="text">
            </div>
          </div>

          <div class="col col-pad col-1-2">

            <div class="form-el fb-column-container col-gutters">
              <div class="col el--block-label el--full-text">
                <label>Flag</label>
                <input name="flag" type="text">
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Points</label>
                <input name="points" type="text">
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Bonus</label>
                <input name="bonus" type="text">
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>-Dec</label>
                <input name="bonus_dec" type="text">
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col el--block-label el--full-text">
                <label>Hint Penalty</label>
                <input name="penalty" type="text">
              </div>
            </div>

          </div>
          
        </div>

        <div class="admin-buttons admin-row">
          <div class="button-right">
            <a href="#" class="admin--edit" data-action="edit">EDIT</a>
            <button class="fb-cta cta--red" data-action="delete">Delete</button>
            <button class="fb-cta cta--yellow" data-action="create">Create</button>
          </div>
        </div>
        </form>
      </section>

      <section class="admin-box">
        <header class="admin-box-header">
          <h3>All Flag Levels</h3>
          <form class="all_flag_form">
            <div class="admin-section-toggle radio-inline col">
              <input type="radio" name="fb--admin--all_flag" id="fb--admin--all_flag--on">
              <label for="fb--admin--all_flag--on">On</label>
              <input type="radio" name="fb--admin--all_flag" id="fb--admin--all_flag--off">
              <label for="fb--admin--all_flag--off">Off</label>
            </div>
          </form>
        </header>
      </section>
EOT;

$c = 1;
foreach ($levels->all_flag_levels() as $flag) {
  $flag_description = htmlspecialchars($flag['description']);
  $flag_flag = htmlspecialchars($flag['flag']);
  $flag_hint = htmlspecialchars($flag['hint']);
  $flag_countries_select = select_countries($flag['entity_id']);
  $flag_categories_select = select_categories($flag['category_id']);
  $flag_on = ($flag['active'] == 1)
    ? 'checked'
    : '';
  $flag_off = ($flag['active'] == 0)
    ? 'checked'
    : '';

  echo <<< EOT
      <section class="admin-box section-locked">
        <form class="level_form flag_form" name="flag_{$flag['id']}">
        <input type="hidden" name="level_type" value="flag">
        <input type="hidden" name="level_id" value="{$flag['id']}">
        <header class="admin-box-header">
          <h3>Flag Level {$c}</h3>

          <div class="admin-section-toggle radio-inline">
            <input type="radio" name="fb--admin--level-{$flag['id']}-status" id="fb--admin--level-{$flag['id']}-status--on" {$flag_on}>
            <label for="fb--admin--level-{$flag['id']}-status--on">On</label>

            <input type="radio" name="fb--admin--level-{$flag['id']}-status" id="fb--admin--level-{$flag['id']}-status--off" {$flag_off}>
            <label for="fb--admin--level-{$flag['id']}-status--off">Off</label>
          </div>
        </header>

        <div class="fb-column-container">
          <div class="col col-pad col-1-2">
            <div class="form-el el--block-label el--full-text">
              <label>Description</label>
              <textarea name="description" rows="4" disabled>{$flag_description}</textarea>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-2 el--block-label el--full-text">
                <label for="">Country</label>
                {$flag_countries_select}
              </div>
              <div class="col col-1-2 el--block-label el--full-text">
                <label for="">Categories</label>
                {$flag_categories_select}
              </div>
            </div>
            

            <div class="form-el el--block-label el--full-text">
              <label>Hint</label>
              <input name="hint" type="text" value="{$flag_hint}" disabled>
            </div>

          </div>

          <div class="col col-pad col-1-2">

            <div class="form-el fb-column-container col-gutters">
              <div class="col el--block-label el--full-text">
                <label>Flag</label>
                <input name="flag" type="text" value="{$flag_flag}" disabled>
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Points</label>
                <input name="points" type="text" value="{$flag['points']}" disabled>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>Bonus</label>
                <input name="bonus" type="text" value="{$flag['bonus']}" disabled>
              </div>
              <div class="col col-1-3 el--block-label el--full-text">
                <label>-Dec</label>
                <input name="bonus_dec" type="text" value="{$flag['bonus_dec']}" disabled>
              </div>
            </div>

            <div class="form-el fb-column-container col-gutters">
              <div class="col col-1-2 el--block-label el--full-text">
                <label>Hint Penalty</label>
                <input name="penalty" type="text" value="{$flag['penalty']}" disabled>
              </div>
            </div>
          </div>
          </form>
        </div> <!-- fb-column-container -->

        <div class="attachments">

          <div class="new-attachment new-attachment-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="attachment_form">
                  <input type="hidden" name="action" value="create_attachment">
                  <input type="hidden" name="level_id" value="{$flag['id']}">
                  <div class="col el--block-label el--full-text">
                    <label>New Attachment:</label>
                    <input name="filename" type="text">
                    <input name="attachment_file" type="file">
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-attachment">X</button>
                <button class="fb-cta cta--yellow" data-action="create-attachment">Create</button>
              </div>
            </div>
          </div>
EOT;

  if ($attachments->has_attachments($flag['id'])) {
    $a_c = 1;
    foreach ($attachments->all_attachments($flag['id']) as $attachment) {
      echo <<< EOT
          <div class="existing-attachment fb-column-container">
            <div class="col col-pad col-2-3">
              <div class="form-el">
                <form class="attachment_form">
                  <input type="hidden" name="attachment_id" value="{$attachment['id']}">
                  <div class="col el--block-label el--full-text">
                    <label>Attachment {$a_c}:</label>
                    <input name="filename" type="text" value="{$attachment['filename']}" disabled>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-attachment">X</button>
              </div>
            </div>
          </div>
EOT;
      $a_c++;
    }
  }

  echo <<< EOT
        </div> <!-- attachments -->

        <div class="links">

          <div class="new-link new-link-hidden fb-column-container completely-hidden">
            <div class="col col-pad col-1-3">
              <div class="form-el">
                <form class="link_form">
                  <input type="hidden" name="action" value="create_link">
                  <input type="hidden" name="level_id" value="{$flag['id']}">
                  <div class="col el--block-label el--full-text">
                    <label>New Link:</label>
                    <input name="link" type="text">
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-new-link">X</button>
                <button class="fb-cta cta--yellow" data-action="create-link">Create</button>
              </div>
            </div>
          </div>
EOT;

  if ($links->has_links($flag['id'])) {
    $a_c = 1;
    foreach ($links->all_links($flag['id']) as $link) {
      echo <<< EOT
          <div class="existing-link fb-column-container">
            <div class="col col-pad col-2-3">
              <div class="form-el">
                <form class="link_form">
                  <input type="hidden" name="link_id" value="{$link['id']}">
                  <div class="col el--block-label el--full-text">
                    <label>Link {$a_c}:</label>
                    <input name="link" type="text" value="{$link['link']}" disabled>
                  </div>
                </form>
              </div>
            </div>
            <div class="admin-buttons col col-pad col-1-3">
              <div class="col el--block-label el--full-text">
                <button class="fb-cta cta--red" data-action="delete-link">X</button>
              </div>
            </div>
          </div>
EOT;
      $a_c++;
    }
  }

  echo <<< EOT
        </div> <!-- links -->

        <div class="admin-buttons admin-row">
          <div class="button-right">
            <a href="#" class="admin--edit" data-action="edit">EDIT</a>
            <button class="fb-cta cta--red" data-action="delete">Delete</button>
            <button class="fb-cta cta--yellow" data-action="save-no-validation">Save</button>
          </div>
        
          <div class="button-left">
            <button class="fb-cta" data-action="add-attachment">+ Attachment</button>
            <button class="fb-cta" data-action="add-link">+ Link</button>
          </div>
      </div>
    </section>
EOT;
  $c++;
}

echo <<< EOT
  </div><!-- .admin-sections -->

  <div class="admin-buttons">
    <button class="fb-cta" data-action="add-new">Add Flag Level</button>
  </div>
EOT;
?>
