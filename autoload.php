<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @namespace Sammy\Packs\Samils\\Component\HTTP\ControllerSet
 * - Autoload, application dependencies
 */
namespace Sammy\Packs\Samils\Component\HTTP\ControllerSet {
  $autoloadFile = __DIR__ . '/vendor/autoload.php';

  if (is_file ($autoloadFile)) {
    include_once $autoloadFile;
  }
}
