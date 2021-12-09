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

        private string $title;

        private ?string $template;

        public function __construct(
            $_slug,
            $_title,
            $_template = null
        )
        {

            $this->slug = $_slug;
            $this->title = $_title;
            $this->template = $_template;

        }

        public function get_slug(): string
        {

            return $this->slug;

        }

        public function get_title(): string
        {

            return $this->title;

        }

        public function get_template(): ?string
        {

            return $this->template;

        }

    }

}