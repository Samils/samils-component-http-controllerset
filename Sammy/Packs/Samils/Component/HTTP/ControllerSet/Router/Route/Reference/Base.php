<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route\Reference
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
namespace Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route\Reference {
  use Samils\Core\Controller\Helper;
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Route\Reference\Base')){
  /**
   * @trait Base
   * Base internal trait for the
   * Samils\Component\HTTP\ControllerSet\Router\Route\Reference module.
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
  trait Base {
    /**
     * [getRouteReference description]
     * @param  string $path
     * @return string
     */
    private function getRouteReference ($path = '') {
      # path_arr
      # A array created from the division
      # of the $path str in order getting
      # the route composition.
      # Before splitting, it is done a slice
      # of the first(s) bar(s) at the beggining
      # of the $path string in order avoiding
      # repeatitions of them when creatig the
      # given rute reference
      $path_ar = preg_split('/\/+/', preg_replace('/^(\/+)/', '',
        $path
      ));
      # Number of elements the array has
      # got
      $path_ar_count = count($path_ar);
      # Final reference of the controller
      # and action that'll respond for the
      # current route when coming up a request
      # from the user
      $path_ctrl = '';
      # singular module instance
      $singular = requires ('singular');
      # action datas
      # first index is the action
      # and, 'i' key contains the index
      # of the action inside the '$path_arr'
      # array
      $action = ['', 'i' => null];

      # Run from the last to the first indes
      # of '$path_arr' array in order finding
      # the action name, skip an element when:
      # - This does not match to the php var
      #   name pattern (!Helper::isRightVarName )
      # - This has got the same index than
      #   the action (that means're the same)
      for ($index = $path_ar_count - 1; $index >= 0; $index--) {
        # Skip the current element if this does not
        # math to the php var names pattern
        if (Helper::isRightVarName ($path_ar[$index])) {
          # Set the found action datas to be used late
          $action = [ $path_ar[$index], # The action
            # The action index inside the
            # route division by one or more
            # stuck bars
            'i' => (0 === $index ? -1 : $index)
          ];

          # Having found the action
          # inside the $path_arr array,
          # stop looking for in order
          # avoiding repeatitions of datas
          # or incorrect matching about the
          # real given action name
          break;
        }
      }

      # NOW... Run from the first to the last
      # element (item) inside the $path_arr
      # array in order building the controller
      # name reference to be used as the responsor
      # for the current route

      for ($i = 0; $i < $path_ar_count - 1; $i++) {
        # Skip an item if it is not a right
        # var name or is the found action
        # (the index of both are the same)
        if (Helper::isRightVarName ($path_ar[ $i ]) && $i !== $action['i']) {
          # Capitalize the controller Name
          # After converting whole letter
          # to lower case
          # it is done because of the ils
          # names pattern.
          # And then, turn that to
          # singular
          $path_ctrl .= '\\' . ucfirst (
            strtolower ($singular->parse ($path_ar [ $i ]))
          );
        }
      }

      $path_ctrl .= '/' . (
        (!($action['i'] < 0) ? $action[0] : (
          $singular->parse ($action[0])
        ))
      );

      return ('@' . preg_replace('/^(\\\|\/)+/', '',
        $path_ctrl
      ));
    }
  }}
}
