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

        private string $template;

        private string $bodyClass;

        private array $routes;

        public function __construct(
            string $_queryVar,
            string $_slug = null,
            string $_template = 'index.php',
            string $_bodyClass = ''
        )
        {

            if( is_null( $_slug ) ){

                $_slug = $_queryVar;

            }

            if( strlen( $_bodyClass ) === 0 ){

                $_bodyClass = $_queryVar;

            }

            $this->queryVar = $_queryVar;
            $this->slug = $_slug;
            $this->template = $_template;
            $this->bodyClass = $_bodyClass;

            //TODO:: add_filter query_vars (add unique query_var),
            // add_action pre_get_posts (set explicit headers and wp_query variables)(might not need this, or try to do it in a better way...),
            // add_action init (rewrite rules foreach Route class added to the set),
            // add_action template_include (load a template, index.php may just work...)
            // add_filter body_class (add custom body class for CSS manipulation)

            //TODO:: take in unique query var
            // take in base slug, default to query var
            // take in default template, default to index.php
            // take in body class, default to query var

        }

        public function add_route( \MarsPress\FrontEndRoute\Route $_route )
        {

            if( ! isset( $this->routes ) ){

                $this->routes = [];

            }

            $this->routes[] = $_route;

        }

    }

}