<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route
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
namespace Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route {
  use Samils\Core\Controller\Error;
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route\Factory')){
  /**
   * @trait Factory
   * Base internal trait for the
   * Samils\Component\HTTP\ControllerSet\Router\Route module.
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
  trait Factory {
    use Get;
    use Put;
    use Post;
    use Patch;
    use Delete;
    use Matches;
    /**
     * [delete description]
     * @param  string $path
     * @param  any $ca
     * @param  array $trace
     * @return null
     */
    protected function routeFactory ($type, $path = null, $ca = null, $trace = null) {
      $re = $this->ApplicationRoutesPatt;
      $trace = $this->isTrace ($trace) ? $trace : (
        debug_backtrace ()
      );

      # $pathIsRegExp
      # A boolean value indicatig either the
      # path value is or not a regular expression
      # , if it is, it should be an array and
      # contain a boolean property called 'match'
      $pathIsRegExp = ((boolean) (
        # Verify if the '$ca' value
        # is realy an array according
        # to the verificatiobn above.
        is_array($ca) &&
        # Now verify if the 'match'
        # property is inside the '$ca'
        # array in order considering the
        # '$path' value as a regular expresion
        # that should math a requested route.
        isset($ca['match']) &&
        # Being the 'match' property inside the
        # '$ca' array, make sure it is a boolean
        # value; it may be be true or false.
        # Being that true, means that it is a regular
        # expression and should be compiled matching
        # a requested route and otherwise is false,
        # wich means, is not a regular expression and
        # should not be compiled when matching a requested
        # route.
        is_bool($ca['match'])
      ));

      $routeOptions = array (
        'trace' => $trace
      );

      $routeNameRe = '/(#([a-zA-Z0-9_]+))$/i';

      if (is_string($path) && preg_match ($routeNameRe, $path, $match)) {
        $routeOptions ['name'] = $match [ 2 ];

        $path = preg_replace ($routeNameRe, '', $path);
      }

      $match = !$pathIsRegExp ? false : $ca [ 'match' ];

      if (!(is_string($path) && preg_match($re, $path))) {
        if (!$match) {
          Error::RoutePathOutOfPattern ($path, $trace);
        } else {
          $ca = ['template' => $ca,'match' => true];
        }
      }

      return call_user_func_array (
        [$this, 'ApplicationRoute'],
        [
          $type,
          $path,
          $ca,
          $routeOptions
        ]
      );
    }
  }}
}
