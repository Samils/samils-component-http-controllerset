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
  use Param as RouteParam;
  use Samils\Core\Controller\Helper;

  /**
   * Make sure the module base internal class is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists('Sammy\Packs\Samils\Component\HTTP\ControllerSet\Router\Base')){
  /**
   * @trait Base
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
  trait Base {
    use Route\Factory;
    use Route\Lists\Base;
    use Route\Reference\Base;
    /**
     * [$routerBasePath description]
     * @var string
     */
    private $routerBasePath = '';
    /**
     * [$parami description]
     * @var integer
     * -
     * Parameter index, used when matching
     * whole the declared parameters inside
     * a given route path to the 'ApplicationRoute'
     * method; it is necessary to know exactly
     * the index of the current route in order
     * to identify one per one by a unique id
     * that should be replaced by the sent
     * value when the route helper is used to bind
     * the path or url to the required route.
     */
    protected $parami = 0;
    /**
     * - @method ApplicationRoute
     * - Used to create an application route.
     * - The same (route) is created only inside
     * - the specific application (module), then,
     * - when ils runs it, the created route sould
     * - be requested and then process the specified
     * - informations acording to the give arguments
     * -
     * - @param string $type
     * - The route type identifies the same
     * - acording to the sent request method
     * - whish should be 'GET' or 'POST'.
     * - Being requested a route, ils check the
     * - used request method, acording to the gotten
     * - information, ils try matching the requested
     * - route inside the correspondent group for the
     * - request method gotten by '$req->method'
     * -
     * - @param string $path
     * - The route path
     * - a string containg an exact path
     * - or a regular expression that should
     * - match to the requested route
     * - the route path may contain parameters.
     * - declared with the : operator.
     * -- Eg: /products/:id
     * -- Sending 'id' as a parameter for the given route path
     * -- it should match for /^(\/products\/([^\/]+))$/i
     * -
     * - @param $ca
     * - An abreviation for ControllerAction
     * - This is by default a sequence of strings
     * - determinating the target controller and
     * - action to process the curent (on time)
     * - request and'll give the response
     */
    private function ApplicationRoute ($type, $path, $ca) {
      $options = func_get_arg ( -1 + func_num_args() );

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

      $options = !is_array($options) ? ['trace' => null] : (
        array_merge (['trace' => null], $options)
      );

      if ($this->isTrace($options['trace'])) {
        $trace = $options ['trace'];
      } else {
        $trace = debug_backtrace ();
      }

      # Route Template Object
      # An array containg the route by it self
      # and the template that should be the
      # reference for the target controller
      # and the action contained on it
      # or a function created any where that'll
      # receive the route request and then give a
      # response to the user
      # or should be a Closure (Lambda)
      $routeTemplateObject = [
        # The route by it self,
        # containing the parameters
        # declarations and keeping
        # the route original case
        # in order using the parameters
        # names as they was previously created
        # by the dev and avoid having bugs
        # when trying to acess them by diferent
        # property names
        'route' => $path,
        # The route template
        # Used to know where sending the
        # current (on time) request datas
        # and then, prepare or process
        # the request response
        'template' => $ca,
        # Match
        # if the '$path' string is or
        # not a regular expression to
        # match with a spicific requested
        # route.
        # It should be used only with the
        # match method and not with onther
        # router creators contained inside
        # the module instance
        'match' => !$pathIsRegExp ? false : (
          $ca [ 'match' ]
        ),

        'trace' => $trace
      ];

      # if ControllerAction is null it means that
      # it was not sent and the $path parameter's
      # got the reference for the target controller
      # and action for the current (on time) request.
      # So, redefine the $ca varuiable to the routeReference
      # following the ils 'RouteReference' pattern and try
      # gessing the controller address inside the php global
      # scope and the action name.
      if ( is_null ($ca) ) {
        # Redefine the $ca variable to
        # the routeReference following the
        # ils 'RouteReference' pattern.
        $routeTemplateObject['template'] = $ca = (
          # Get route reference
          $this->getRouteReference (
            # The '$path' parameter sent to the
            # current method
            $path
          )
        );
      }

      $routeMiddlewareRe = '/^([^@]+)@/';

      $routeMiddlewareDefined = ( boolean ) (
        isset ($routeTemplateObject['template']) &&
        is_string ($t = $routeTemplateObject['template']) && (
          preg_match ($routeMiddlewareRe, $t)
        )
      );

      if ( $routeMiddlewareDefined ) {
        preg_match ($routeMiddlewareRe, $t,
          $routeMiddlewareMatch
        );

        $routeTemplateObject ['template'] = preg_replace (
          $routeMiddlewareRe, '@', $t
        );

        #echo $routeTemplateObject ['template'], '<br />';
        $routeTemplateObject ['middleware'] = (
          $routeMiddlewareMatch [ 1 ]
        );
      }

      $param_rgexp = '/:{1,}([^\/\\\]+)/';
      $params_count = preg_match_all ( $param_rgexp, $path, $param_matches );


      # todo:
      #   call Helper::createRouteReferences
      #   and send the right arguments to it
      #require (dirname(__FILE__) . '/.router-helpers.php');
      $helper = Helper::createRouteReferences ();

      call_user_func_array (
        Closure::bind ($helper, $this, Helper::class),
        [$ca, $path, $param_rgexp]
      );

      $basePath = preg_replace ('/(\/*)$/', '/',
        $this->routerBasePath
      );
      $path = preg_replace('/(\/+)$/', '',
        $basePath . preg_replace('/^(\/+)/', '',
          $path
        )
      );

      $path = empty($path) ? '/' : $path;

      if ($params_count >= 1) {
        $param_names = [];
        $r = '' . $path;

        foreach ($param_matches[0] as $k => $v) {
          array_push ( $param_names,
            preg_replace ('/^:{1,2}/', '', trim ($v))
          );
        }

        $param = new RouteParam (
          $routeTemplateObject ['template'],
          $param_names
        );

        # route
        $r = '/^' . Helper::path2re ($r) . '$/i';

        $r = preg_replace_callback (
          '/(:{1,}[^\\\]+)/',
          function ($m) { return '([^\\/]+)'; }, $r
        );

        $r = preg_match ('/(\$\/i)$/', $r) ? $r : ($r . '$/i');

        $path = $r;

        $routeTemplateObject = array_merge ($routeTemplateObject, [
            'route' => $path,
            'template' => $param,
            'match' => false
          ]
        );
      }

      $type = strtolower ($type);
      $path = strtolower ($path);

      $this->ApplicationRoutes [$type][$path] = (
        $routeTemplateObject
      );
    }
  }}
}
