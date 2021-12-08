<?php
/*
 * @package marspress/front-end-route
 */

namespace MarsPress\FrontEndRoute;

if( ! class_exists( 'Route' ) )
{

    final class Route
    {

        private string $slug;

        private $template;

        public function __construct(
            $_slug,
            $_template = null
        )
        {

            $this->slug = $_slug;
            $this->template = $_template;

            //TODO:: take in slug
            // take in template (if overriding index.php and the route set template), defaults to null

        }

    }

}