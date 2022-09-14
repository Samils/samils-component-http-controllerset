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
  use Closure;
  use Samils\Core\Controller\Error;
  use Samils\Core\Controller\Helper;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Attributes')){
  /**
   * @trait Attributes
   * Base internal trait for the
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
  trait Attributes {
    /**
     * @var static props
     * - Module properties defined by the
     * - module object
     */
    private $props = array (
      '@vars' => array ( 'props' => null ),
      '@bridge' => null,
      'name' => null
    );

    /**
     * [__construct description]
     * @param string $name [description]
     */
    public function __construct ($name = '') {
      $name_ = preg_replace ('/\\\+/', '', $name);

      if (!Helper::isRightVarName ($name_)) {
        $trace = debug_backtrace();
        Error::BadModuleName ($name, $trace[0]);
      }

      $this->props['name'] = ( $name );

      $sub_props = $this->props['@vars'][ 'props' ];

      if (!($sub_props instanceof Module\Property)) {
        $this->props['@vars'][ 'props' ] = ( new Module\Property ( $this ) );
      }

      $calledClassName = strtolower ( get_called_class() );
      if (!isset(self::$ApplicationModules[ $calledClassName ])) {
        self::$ApplicationModules[ $calledClassName ] = [];
      }
    }

    public function setProp ($prop = null, $propValue = null) {
      if (!(Helper::isRightVarName ($prop))) return;

      if (!in_array(strtolower($prop), ['name', 'props'])) {
        $this->props[ $prop ] = ( $propValue );
      }
    }

    public function getProp ($prop = null) {
      if (Helper::isRightVarName ($prop)) {
        if (isset($this->props[ $prop ])) {
          return ( $this->props[ $prop ] );
        } elseif (isset($this->props['@vars'][ $prop ])) {
          return ( $this->props['@vars'][ $prop ] );
        }
      }
    }

    /**
     * @var def
     * Define a var name in this app
     */
    public function def ($name = null, $val = null){
      if (Helper::isRightVarName ($name)){
        $this->setProp ( $name, $val );
      }
      return $this;
    }

    public function defined ($prop = null){
      return ( boolean ) (
        Helper::isRightVarName ($prop) &&
        isset ($this->props [$prop])
      );
    }

    public function define () {
      return call_user_func_array (
        [$this, 'def'], func_get_args()
      );
    }

    public function __get ($prop) {
      return $this->getProp ( $prop );
    }

    public function __set ($prop, $propValue) {
      $this->setProp ( $prop, $propValue );
    }

    public function __isset ($prop) {
      return ( boolean ) (!is_null ($this->getProp ($prop)));
    }

    /**
     * [__call description]
     * @param  string $meth      [description]
     * @param  array  $arguments [description]
     * @return any
     */
    public final function __call ($meth, $arguments = []) {
      $re = '/(extendedinternalclass([0-9]+))$/i';
      $class = strtolower (get_called_class ());

      if (preg_match ($re, $class)) {
        $classModuleName = preg_replace ( $re, '', $class );

        $class = strtolower ($classModuleName . ('controller'));
      }

      $applicationModules = !isset(self::$ApplicationModules[$class]) ? [] : ( self::$ApplicationModules[$class] );

      # Get the 'ApplicationMoudules' list
      # to get whole the imported modules
      # inside the current controller scope
      # , if it get a module containg the
      # called method name, use that and
      # execute the method inside that
      # scope and stops looking for, other
      # wise, keep looking for until the
      # beggining of the list and then try
      # looking at the controller properties
      # in order getting there a closure with
      # the same name to execute.
      if (is_array($applicationModules)) {
        # being 'applicationModules' an array,
        # check if it is empty, and skip otherwise
        if ($applicationModules) {
          $i = (-1 + count( $applicationModules ));

          for ( ; $i >= 0; $i-- ) {
            $applicationModule = $applicationModules [ $i ];

            if (!is_object($applicationModule))
              continue;

            if (method_exists($applicationModule, $meth)) {
              return call_user_func_array(
                [ $applicationModule, $meth ], $arguments
              );
            }
          }
        }
      }

      # Get a property with the same name
      # as the '$meth' argument, finding
      # it inside the '$props' array, verify
      # if that is a closure.
      if (isset($this->props[ $meth ])) {
        # Verify if the found property
        # is an instance of Closure in
        # order executing it in the current
        # class scope.
        if ($this->props[ $meth ] instanceof Closure) {
          # Bind the property closure inside the
          # current class scope in order using it
          # as a child of the current class and
          # have every reference should be had
          # inside a class method.
          # having the '$this' variable refering to the
          # current class and the 'self' constant in order
          # to make reference to the current class static
          # scope.
          $func = Closure::bind($this->props[ $meth ],
            $this, get_class()
          );

          return call_user_func_array( $func, $arguments );
        }
      }

      Error::UncoughtMethodName (
        debug_backtrace(),
        get_called_class()
      );
    }

    /**
     * [hasAMethod description]
     * @param  string  $meth [description]
     * @return boolean
     */
    public final function hasAMethod ($meth = '') {
      if (!(is_string($meth) && $meth)) {
        return null;
      }

      if (method_exists($this, $meth)) {
        return true;
      }
      # Get a property with the same name
      # as the '$meth' argument, finding
      # it inside the '$props' array, verify
      # if that is a closure.
      if (isset($this->props[ $meth ])) {
        # Verify if the found property
        # is an instance of Closure in
        # order executing it in the current
        # class scope.
        if ($this->props[ $meth ] instanceof Closure) {
          # Bind the property closure inside the
          # current class scope in order using it
          # as a child of the current class and
          # have every reference should be had
          # inside a class method.
          # having the '$this' variable refering to the
          # current class and the 'self' constant in order
          # to make reference to the current class static
          # scope.
          return true;
        }
      }

      # return false otherwise
      # , it did not find any
      # property with that name
      # or even a method inside
      # the current class context
      return false;
    }
  }}
}
