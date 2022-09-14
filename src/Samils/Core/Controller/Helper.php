<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Samils\Core\Controller
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
namespace Samils\Core\Controller {
  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!class_exists('Samils\Core\Controller\Helper')){
  /**
   * @class Base
   * Base internal class for the
   * Controller module.
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
  abstract class Helper {
    /**
     * @method string path2re
     * Convert a path string to a regular expression
     */
    public static function path2re ($path = '') {
      $specialCharsList = '/[\/\^\$\[\]\{\}\(\)\\\\.]/';

      return preg_replace_callback (
        $specialCharsList, function ($match) {
          return '\\' . $match [ 0 ];
      }, self::Stringify ($path));
    }

    public static function createRouteReferences () {
      return function ($ca, $path, $param_rgexp) {
        if (isset($ca) && is_string($ca) && preg_match('/^@/', $ca)) {
          $routeOptionsSent = (boolean) (
            isset ($options) &&
            is_array ($options) &&
            isset ($options['name']) &&
            is_string ($options['name'])
          );

          if (!$routeOptionsSent) {
            $ca_ = preg_replace ('/^@+/', '', $ca);

            # [$ControllerReference description]
            # @var string
            $ControllerReference = preg_split (
              '/(\/|\\\)+/', $ca_
            );

            $s = count ($ControllerReference) >= 2 ? -1 : 0;
            $ControllerReferenceCount = $s + count (
              $ControllerReference
            );
            # singular module
            $singular = requires ('singular');

            $action = 'index';

            if (count ($ControllerReference) >= 2) {
              $action = self::ArrayLastI ($ControllerReference);
            }

            $referenceBase = '';

            for ($i = 0; $i < $ControllerReferenceCount; $i++) {
              $referenceBase .= '_' . $singular->parse (
                $ControllerReference [ $i ]
              );
            }

            $referenceBase = $action.$referenceBase;
          } else {
            $referenceBase = $options ['name'];
          }

          $referenceBase = preg_replace ('/_+/', '_', self::lower ($referenceBase));

          if (preg_match ($param_rgexp, $path)) {

            $pathRewriterCallback = function ($match = null) {
              static $paramNames = [];
              static $i = 0;

              if (is_array ($match)) {
                array_push ($paramNames, $match [ 1 ]);
              }

              return $match ? '{{'.$i++.'}}' : $paramNames;
            };

            $func = requires ('createfunction');
            $rewritenPath = preg_replace_callback (
              $param_rgexp,
              $pathRewriterCallback,
              $path
            );

            $paramNames = $pathRewriterCallback ();

            #echo 'paramNames <pre>';print_r($paramNames);
            #exit ('');

            #$this->parami = 0;
            #$fb = ;

            $callBack = function ($path, $paramNames) {
              $args = array_slice (func_get_args(), 2,
                func_num_args()
              );

              #exit ('--- ' . $path);

              $argsCount = count ($args);

              for ($i = 0; $i < $argsCount; $i++) {
                $path = str_replace (
                  '{{' . $i . '}}',
                  Helper::ParamValue (
                    $args [$i], $i, $paramNames
                  ),
                  $path
                );
              }

              return $path;
            };

            $func->createFunctionMT (
              $referenceBase . '_path',
              [
                $rewritenPath,
                $paramNames
              ],
              $callBack
            );

            $func->createFunctionMT (
              $referenceBase . '_url',
              [
                self::url ($rewritenPath),
                $paramNames
              ],
              $callBack
            );
          } else {

            #echo $path, ' => ', $referenceBase . '_path', "<br>";
            self::DefConst ($referenceBase . '_path', $path);
            self::DefConst ($referenceBase . '_url', self::url ($path));
          }
        }
      };
    }

    public static function ParamValue ($paramRef, $paramRefIndex, array $paramNamesList) {
      if (is_object ($paramRef) || is_array ($paramRef)) {
        $paramName = $paramNamesList [ $paramRefIndex];

        if (is_array ($paramRef)) {

          if (!isset ($paramRef [$paramName])) {
            $paramName = 'id';
          }

          if (isset ($paramRef [$paramName])) {
            $paramRef = $paramRef [ $paramName ];
          }

        } else {

          $paramRefClassNameRe = join ('', [
            '/^(', self::getClassName ($paramRef), ')_?/i'
          ]);

          $paramNameAlternate = preg_replace (
            $paramRefClassNameRe, '', $paramName
          );

          if (isset ($paramRef->$paramNameAlternate)) {
            $paramName = $paramNameAlternate;
          }

          if (!isset ($paramRef->$paramName)) {
            $paramName = 'id';
          }

          if (isset ($paramRef->$paramName)) {
            $paramRef = $paramRef->$paramName;
          }

        }
      }

      return self::Stringify ($paramRef);
    }

    public static function getClassName (object $object) {
      $re = '/\\\+/';
      $objectClassRef = preg_split ($re, get_class ($object));

      return $objectClassRef [-1 + count ($objectClassRef)];
    }

    public static function DefConst ($const, $value) {
      defined ($const) or define ($const, $value);
    }

    public static function isRightVarName ($name = null) {
      return ( boolean ) (
        is_string ($name) &&
        !empty ($name) &&
        preg_match ('/^[a-zA-Z_]([a-zA-Z0-9_\\\]+)$/', $name)
      );
    }

    public static function url ($path = '') {
      # Make sure '$path' is a string
      $path = !is_string ($path) ? '' : $path;

      $serverVariablesAvailables = (boolean) (
        isset($_SERVER ['SERVER_PROTOCOL']) &&
        isset($_SERVER ['SERVER_PORT']) &&
        isset($_SERVER ['SERVER_NAME'])
      );

      if (!$serverVariablesAvailables) {
        return (string)( $path );
      }

      $server_protocol = $_SERVER ['SERVER_PROTOCOL'];
      $server_name = $_SERVER ['SERVER_NAME'];

      preg_match ('/^([^\/]+)/', $server_protocol,
        $match
      );

      $protocol = self::lower ( $match [0] . '://' );

      $port = $_SERVER['SERVER_PORT'] === 80 ? '' : (
        ':' . $_SERVER['SERVER_PORT']
      );

      return join ('', [
        $protocol,
        $server_name,
        $port,
        preg_replace ('/^(\/*)/', '/', $path)
      ]);
    }

    public static function Stringify ($var = null) {
      if (in_array (gettype ($var), ['array', 'object'])){
        return json_encode ($var);
      } else {
        if (is_bool ($var)) {
          return $var ? 'true' : 'false';
        } else {
          return ((string)($var));
        }
      }
    }

    public static function lower ($string) {
      return strtolower (self::Stringify ($string));
    }

    public static function ArrayLastI ($array) {
      if (is_array ($array) && $array) {
        return $array [ -1 + count ($array) ];
      }
    }

    public static function ArrayFirstI ($array) {
      if (is_array ($array) && $array) {
        return $array [ 0 ];
      }
    }
  }}
}
