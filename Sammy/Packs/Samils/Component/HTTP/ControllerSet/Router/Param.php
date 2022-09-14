<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router
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
namespace Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router {
  use Closure;
  use Sammy\Packs\Func;
  use Samils\Core\Controller\Helper;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Param')){
  /**
   * @trait Param
   * Base internal trait for the
   * Samils\Component\HTTP\ControllerSet\Router module.
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
  trait Param {
    private $template;
    private $params_names = [];
    private $props = array();

    public function __construct ($template = null, $params_names = null){
      $this->setTemplate($template);
      $this->params_names = is_array($params_names) ? $params_names : null;
    }

    public function get ($data = null) {
      return isset($this->props[$data]) ? $this->props[$data] : null;
    }

    public function set($data = null, $val = null){
      if (Helper::isRightVarName ($data) && in_array ($data, $this->params_names)){
        $this->props[$data] = $val;
      }
      return $this;
    }

    public function count () {
      return count ($this->params_names);
    }

    public function getTemplate () {
      return $this->template;
    }

    public function setTemplate ($t = null) {
      $this->template = is_string($t) || ($t instanceof Closure) || ($t instanceof Func) || ($t instanceof Param) || is_array($t) ? $t : $this->template;
    }

    public function getParamName ($index = 0) {
      return is_int($index) && isset($this->params_names[$index]) ? $this->params_names[$index] : null;
    }

    public function getParamNames () {
      return $this->params_names;
    }

    public function __get ($key) {
      return $this->get($key);
    }
  }}
}
