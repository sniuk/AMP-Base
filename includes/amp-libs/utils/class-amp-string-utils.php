<?php

class amp_base_string_utils {
	public static function endswith( $haystack, $needle ) {
		return '' !== $haystack
			&& '' !== $needle
			&& $needle === substr( $haystack, -strlen( $needle ) );
	}
}
