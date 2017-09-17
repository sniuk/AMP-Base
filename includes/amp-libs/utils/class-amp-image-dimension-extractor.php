<?php

class amp_base_image_dimension_extractor {
	static $callbacks_registered = false;

	static public function extract( $url ) {
		if ( ! self::$callbacks_registered ) {
			self::register_callbacks();
		}

		$url = self::normalize_url( $url );
		if ( false === $url ) {
			return false;
		}

		return apply_filters( 'amp_base_extract_image_dimensions', false, $url );
	}

	public static function normalize_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		if ( 0 === strpos( $url, 'data:' ) ) {
			return false;
		}

		if ( 0 === strpos( $url, '//' ) ) {
			return set_url_scheme( $url, 'http' );
		}

		$parsed = parse_url( $url );
		if ( ! isset( $parsed['host'] ) ) {
			$path = '';
			if ( isset( $parsed['path'] ) ) {
				$path .= $parsed['path'];
			}
			if ( isset( $parsed['query'] ) ) {
				$path .= '?' . $parsed['query'];
			}
			$url = site_url( $path );
		}

		return $url;
	}

	private static function register_callbacks() {
		self::$callbacks_registered = true;

		add_filter( 'amp_base_extract_image_dimensions', array( __CLASS__, 'extract_from_attachment_metadata' ), 10, 2 );
		
		do_action( 'amp_base_extract_image_dimensions_callbacks_registered' );
	}

	public static function extract_from_attachment_metadata( $dimensions, $url ) {
		if ( is_array( $dimensions ) ) {
			return $dimensions;
		}

		$url = strtok( $url, '?' );
		$attachment_id = attachment_url_to_postid( $url );
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! $metadata ) {
			return false;
		}

		return array( $metadata['width'], $metadata['height'] );
	}

	
}
