<?php
/*
 * @package marspress/front-end-route
 */

namespace MarsPress\FrontEndRoute;

if( ! class_exists( 'Route_Set' ) )
{

    final class Route_Set
    {

        private string $queryVar;

        private string $slug;

        private ?string $template;

        private string $bodyClass;

        private bool $queryVarExists;

        /**
         * @var Route[] $routes
         */
        private array $routes;

        public function __construct(
            string $_queryVar,
            string $_slug = null,
            string $_bodyClass = null,
            string $_template = null
        )
        {

            if( is_null( $_slug ) ){

                $_slug = $_queryVar;

            }

            if( is_null( $_bodyClass ) ){

                $_bodyClass = $_queryVar;

            }

            $this->queryVar = $_queryVar;
            $this->slug = $_slug;
            $this->template = $_template;
            $this->bodyClass = $_bodyClass;
            $this->queryVarExists = false;

            if( ! is_null( $this->template ) && ! file_exists( $this->template ) ){

                $message = "The template file <strong><em>{$this->template}</em></strong> for the route set <strong><em>{$this->queryVar}</em></strong> does not exist on the server. Please update your template to an existing file path on the server.";
                add_action( 'admin_notices', function () use ($message){
                    $output = $this->output_admin_notice($message);
                    echo $output;
                }, 10, 0 );

            }

            add_filter( 'query_vars', [ $this, 'register_query_variable' ], 10, 1 );

            global $wp;
            //calls the query_vars filter as to set the class instance of queryVarExists
            apply_filters( 'query_vars', $wp->public_query_vars );

            if( $this->queryVarExists ){

                $message = "The query variable <strong><em>{$this->queryVar}</em></strong> already exists. Please update your query variable to something unique.";
                add_action( 'admin_notices', function () use ($message){
                    $output = $this->output_admin_notice($message);
                    echo $output;
                }, 10, 0 );

            }else{

                add_action( 'pre_get_posts', [ $this, 'set_explicit_query_variables' ], 66, 1 );
                add_filter( 'status_header', [ $this, 'set_http_response' ], 66, 4 );
                add_action( 'init', [ $this, 'register_rewrite_rules' ], 10, 0 );
                add_filter( 'template_include', [ $this, 'load_template' ], 10, 1 );
                add_filter( 'body_class', [ $this, 'set_body_class' ], 10, 2 );
                add_filter( 'document_title_parts', [ $this, 'set_the_title' ], 10, 3 );

            }

        }

        public function get_query_variable(): string
        {

            return $this->queryVar;

        }

        public function get_slug(): string
        {

            return $this->slug;

        }

        public function is_route()
        {

            if( $route = \get_query_var( $this->queryVar ) ){

                return $route;

            }

            return false;

        }

        /**
         * @filter query_vars
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function register_query_variable
         * @priority 10
         * @param array $_vars
         * @return array
         */
        public function register_query_variable( array $_vars ): array
        {

            if( in_array( $this->queryVar, $_vars ) ){

                $this->queryVarExists = true;

                return $_vars;

            }

            $_vars[] = $this->queryVar;

            return $_vars;

        }

        /**
         * @action pre_get_posts
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function set_explicit_query_variables
         * @priority 66
         * @param \WP_Query $_query
         * @return void
         */
        public function set_explicit_query_variables(\WP_Query $_query)
        {

            if( $_query->is_main_query() ){

                if ( array_key_exists($this->queryVar, $_query->query_vars) ) {

                    $_query->set('post_type', false);
                    $_query->query_vars['post_type'] = false;
                    set_query_var('post_type', false);
                    $_query->is_attachment = false;
                    $_query->is_404 = false;
                    $_query->is_archive = false;
                    $_query->is_home = false;
                    $_query->is_single = false;
                    $_query->is_singular = false;

                }

            }

        }

        /**
         * @filter status_header
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function set_http_response
         * @priority 66
         * @param string $_statusHeader
         * @param int $_code
         * @param string $_description
         * @param string $_protocol
         * @return string
         */
        public function set_http_response( string $_statusHeader, int $_code, string $_description, string $_protocol ): string
        {

            if( $this->is_route() ){

                return 'HTTP/1.1 200 OK';

            }

            return $_statusHeader;

        }

        /**
         * @action init
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function register_rewrite_rules
         * @priority 10
         * @return void
         */
        public function register_rewrite_rules()
        {

            if( isset( $this->routes ) ){

                foreach ( $this->routes as $_route ){

                    $slug = $_route->get_slug();
                    add_rewrite_rule( "^$this->slug/$slug?$", 'index.php?' . $this->queryVar . '=' . $slug, 'top' );

                }

            }

        }

        /**
         * @filter template_include
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function load_template
         * @priority 10
         * @param string $_template
         * @return string
         */
        public function load_template( string $_template ): string
        {

            if( $route = $this->is_route() ){

                $routeObject = $this->get_route_object($route);

                if( is_null( $routeObject ) ){ return $_template; }

                $queryVarFileName = 'marspress-route-' . $this->queryVar . '.php';
                $routeFileName = 'marspress-route-' . $this->queryVar . '-' . $route . '.php';
                $childThemeDirectory = \get_stylesheet_directory();
                $parentThemeDirectory = \get_template_directory();

                if( ! is_null( $this->template ) ){

                    if( file_exists( $this->template ) ){

                        $customTemplate = $this->template;

                    }

                }else if( file_exists( $childThemeDirectory . '/' . $routeFileName ) ){

                    $customTemplate = $childThemeDirectory . '/' . $routeFileName;

                }else if( file_exists( $childThemeDirectory. '/' . $queryVarFileName ) ){

                    $customTemplate = $childThemeDirectory. '/' . $queryVarFileName;

                }else if( file_exists( $parentThemeDirectory . '/' . $routeFileName ) ){

                    $customTemplate = $parentThemeDirectory . '/' . $routeFileName;

                }else if( file_exists( $parentThemeDirectory . '/' . $queryVarFileName ) ){

                    $customTemplate = $parentThemeDirectory . '/' . $queryVarFileName;

                }else{

                    $customTemplate = $parentThemeDirectory . '/index.php';

                }

                if( ! is_null( $overridingTemplate = $routeObject->get_template() ) ){

                    if( file_exists( $overridingTemplate ) ){

                        $customTemplate = $overridingTemplate;

                    }

                }

                if( isset( $customTemplate ) && is_string( $customTemplate ) && strlen( $customTemplate ) > 0 ){

                    global $wp_query;

                    $wp_query->is_404 = false;

                    return $customTemplate;

                }

            }

            return $_template;

        }

        /**
         * @filter body_class
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function set_body_class
         * @priority 10
         * @param array $_classes
         * @param array $_class
         * @return array
         */
        public function set_body_class( array $_classes, array $_class ): array
        {

            if( $route = $this->is_route() ){

                $_classes[] = $this->bodyClass;
                $_classes[] = $this->bodyClass . '-' . $route;

            }

            return $_classes;

        }

        /**
         * @filter document_title_parts
         * @class \MarsPress\FrontEndRoute\Route_Set
         * @function set_the_title
         * @priority 10
         * @param array $_titleParts
         * @return array
         */
        public function set_the_title( array $_titleParts ): array
        {

            if( $route = $this->is_route() ){

                $routeObject = $this->get_route_object($route);

                if( is_null($routeObject) ){ return $_titleParts; }

                $_titleParts['title'] = $routeObject->get_title();

            }

            return $_titleParts;

        }

        private function get_route_object( $_routeSlug ): ?Route
        {

            if( ! isset( $this->routes ) ){ return null; }

            if( array_key_exists( $_routeSlug, $this->routes ) ){

                return $this->routes[$_routeSlug];

            }

            return null;

        }

        /**
         * @param Route[] $_routes
         * @return void
         */
        public function add_routes( \MarsPress\FrontEndRoute\Route ...$_routes )
        {

            if( ! isset( $this->routes ) ){

                $this->routes = [];

            }

            if( count( $_routes ) > 0 ){

                foreach ( $_routes as $_route ){

                    if( ! is_null( $overridingTemplate = $_route->get_template() ) && ! file_exists( $overridingTemplate ) ){

                        $message = "The template file <strong><em>{$overridingTemplate}</em></strong> for the route <strong><em>{$_route->get_slug()}</em></strong> in the route set <strong><em>{$this->queryVar}</em></strong> does not exist on the server. Please update your template to an existing file path on the server.";
                        add_action( 'admin_notices', function () use ($message){
                            $output = $this->output_admin_notice($message);
                            echo $output;
                        }, 10, 0 );

                    }

                    if( ! array_key_exists( $_route->get_slug(), $this->routes ) ){

                        $this->routes[$_route->get_slug()] = $_route;

                    }else{

                        $message = "The slug <strong><em>{$_route->get_slug()}</em></strong> in the route set <strong><em>{$this->queryVar}</em></strong> already exists. Please update your slug to a unique value for the route set.";
                        add_action( 'admin_notices', function () use ($message){
                            $output = $this->output_admin_notice($message);
                            echo $output;
                        }, 10, 0 );

                    }


                }

            }

        }

        private function output_admin_notice( string $_message ): string
        {

            if( strlen( $_message ) > 0 && \current_user_can( 'administrator' ) ){

                return "<div style='background: white; padding: 12px 20px; border-radius: 3px; border-left: 5px solid #dc3545;' class='notice notice-error is-dismissible'><p style='font-size: 16px;'>$_message</p><small><em>This message is only visible to site admins</em></small></div>";

            }

            return '';

        }

    }

}