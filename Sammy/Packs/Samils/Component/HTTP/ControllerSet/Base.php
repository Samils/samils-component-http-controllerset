<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Component\HTTP\ControllerSet
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Sammy\Packs\Samils\Component\HTTP\ControllerSet {
  use Sammy\Packs\Func;
  use Samils\Core\Controller\Error;
  use Sammy\Packs\Samils\Component\HTTP\IControllerSet;
  use Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Param;
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!class_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Base')){
  /**
   * @class Base
   * Base internal class for the
   * Samils\Component\HTTP\ControllerSet module.
   * -
   * This is (in the ils environment)
   * an instance of the php module,
   * wich should contain the module
   * core functionalities that should
   * be extended.
   * -
   * For extending the module, just create
   * an 'exts' directory in the module directory
   * and boot it by using the ils directory boot.
   * -
   */
  class Base implements IControllerSet {
    use Router\Base;
    use Module\Base;
    use Attributes;

    /**
     * @var middles
     * - Application middlewares
     */
    private $middles = [];

    /**
     * @var ApplicationRoutes
     * - A set of list of the application
     * - routes
     */
    private $ApplicationRoutes = array(
      '@get' => array(),
      '@post' => array(),
      '@patch' => array(),
      '@put' => array(),
      '@delete' => array()
    );

    private $ApplicationRoutesBase;
    private $ApplicationRoutesPatt = (
      '/^([a-zA-Z0-9\/:_\-\.@\$])+$/'
    );
    private $params;

    public final function run () {
      $sami = requires ('sami');

      /**
       * -
       * -
       */

      if (is_object ($sami)) {
        $sami->run ($this);
      }

      return $this;
    }

    public final function apply ($closure = null, $args = null){
      $args = is_array ($args) ? $args : [];
      $args = array_merge ($args, [$this]);


      if (is_callable ($closure) || $closure instanceof Func){
        $closure = new Func ($closure);
        $closure->callArray ( $args );
      }

      return $this;
    }

    public final function uses ($middle = null, $s = '') {
      if (is_callable ($middle) || $middle instanceof Func){
        array_push ( $this->middles, new Func ($middle) );
      }

      return $this;
    }

    public final function ApplicationMiddlewares () {
      return array_merge ( $this->middles, [] );
    }

    public function setRouteParamObject ($param = null) {
      if ($param instanceof Param) {
        $this->params = $param;
      }

      return $this;
    }

    public final function routes_base ($base, $trace = null) {
      $trace = $this->isTrace ($trace) ? $trace : null;

      if (!$trace) $tarce = debug_backtrace();

      if (is_string ($base) && preg_match ($this->ApplicationRoutesPatt, $base)) {
        $this->ApplicationRoutesBase = $base;
      } else {
        Error::RoutePathOutOfPattern ($base, $trace);
      }
    }

    private function isTrace ($trace) {
      return ( boolean ) (
        is_array ($trace) &&
        isset ($trace [0]) &&
        is_array ($trace [0]) &&
        isset ($trace [0]['file']) &&
        isset($trace [0]['line'])
      );
    }
  }}
}
