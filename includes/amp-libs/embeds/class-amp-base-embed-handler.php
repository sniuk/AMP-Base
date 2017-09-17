<?php

// Used by some children

abstract class amp_base_base_embed_handler {
	protected $DEFAULT_WIDTH = 600;
	protected $DEFAULT_HEIGHT = 480;

	protected $args = array();
	protected $did_convert_elements = false;

	abstract function register_embed();
	abstract function unregister_embed();

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => $this->DEFAULT_WIDTH,
			'height' => $this->DEFAULT_HEIGHT,
		) );
	}

	public function get_scripts() {
		return array();
	}
}
