<?hh

require_once($_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php');

class LogosController {
  public function render(): :xhp {
    $logos_div = <div class="fb-slider fb-container container--large"></div>;
    $logos_ul = <ul class="slides"></ul>;

    $logos = new Logos();
    foreach ($logos->all_enabled_logos() as $logo) {
      $xlink_href = '#icon--badge-'.$logo['name'];
      $logos_ul->appendChild(
        <li>
          <svg class="icon--badge">
            <use xlink:href={$xlink_href}></use>
          </svg>
        </li>
      );
    }

    $logos_div->appendChild($logos_ul);
    return $logos_div;
  }
}

$logos_generated = new LogosController();
echo $logos_generated->render();