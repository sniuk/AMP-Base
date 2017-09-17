<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Converter
 * @since       1.0.0
 */

define('ETRUEL_AMP_MINIFY_COMMENT_CSS', '/\*[\s\S]*?\*/');
define('ETRUEL_AMP_MINIFY_STRING', '"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'');
define('ETRUEL_AMP_X', "\x1A"); // escape character

if( !class_exists( 'AMP_Base_Converter' ) ) {
	class AMP_Base_Converter {
		public static function minify($pattern, $input) {
		    return preg_split('#(' . implode('|', $pattern) . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		}
		public static function minify_css($input, $comment = 2, $quote = 2) {
		    if (!is_string($input) || !$input = self::normalize_line_break(trim($input))) return $input;
		    $output = $prev = "";
		    foreach (self::minify(array( ETRUEL_AMP_MINIFY_COMMENT_CSS,  ETRUEL_AMP_MINIFY_STRING), $input) as $part) {
		        if (trim($part) === "") continue;
		        if ($comment !== 1 && strpos($part, '/*') === 0 && substr($part, -2) === '*/') {
		            if (
		                $comment === 2 && (
		                    // Detect special comment(s) from the third character. It should be a `!` or `*` → `/*! keep */` or `/** keep */`
		                    strpos('*!', $part[2]) !== false ||
		                    // Detect license comment(s) from the content. It should contains character(s) like `@license`
		                    stripos($part, '@licence') !== false || // noun
		                    stripos($part, '@license') !== false || // verb
		                    stripos($part, '@preserve') !== false
		                )
		            ) {
		                $output .= $part;
		            }
		            continue;
		        }
		        if ($part[0] === '"' && substr($part, -1) === '"' || $part[0] === "'" && substr($part, -1) === "'") {
		            // Remove quote(s) where possible …
		            $q = $part[0];
		            if (
		                $quote !== 1 && (
		                    // <https://www.w3.org/TR/CSS2/syndata.html#uri>
		                    substr($prev, -4) === 'url(' && preg_match('#\burl\($#', $prev) ||
		                    // <https://www.w3.org/TR/CSS2/syndata.html#characters>
		                    substr($prev, -1) === '=' && preg_match('#^' . $q . '[a-zA-Z_][\w-]*?' . $q . '$#', $part)
		                )
		            ) {
		                $part = self::trim_once($part, $q); // trim quote(s)
		            }
		            $output .= $part;
		        } else {
		            $output .= self::minify_css_union($part);
		        }
		        $prev = $part;
		    }
		    return trim($output);
		}
		/**
		* Static function callbac_css_union
		* @access public
		* @return void
		* @since version
		*/
		public static function callback_css_union($m) {
			return $m[1] . preg_replace('#\s+#',  ETRUEL_AMP_X, $m[2]) . ')';
		}

		public static function minify_css_union($input) {
		    if (stripos($input, 'calc(') !== false) {
		        // Keep important white–space(s) in `calc()`
		        $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', array(__CLASS__, 'callback_css_union'), $input);
		    }
		    $input = preg_replace(array(
		        // Fix case for `#foo<space>[bar="baz"]`, `#foo<space>*` and `#foo<space>:first-child` [^1]
		        '#(?<=[\w])\s+(\*|\[|:[\w-]+)#',
		        // Fix case for `[bar="baz"]<space>.foo`, `*<space>.foo`, `:nth-child(2)<space>.foo` and `@media<space>(foo: bar)<space>and<space>(baz: qux)` [^2]
		        '#([*\]\)])\s+(?=[\w\#.])#', '#\b\s+\(#', '#\)\s+\b#',
		        // Minify HEX color code … [^3]
		        '#\#([a-f\d])\1([a-f\d])\2([a-f\d])\3\b#i',
		        // Remove white–space(s) around punctuation(s) [^4]
		        '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
		        // Replace zero unit(s) with `0` [^5]
		        '#\b(?:0\.)?0([a-z]+\b)#i',
		        // Replace `0.6` with `.6` [^6]
		        '#\b0+\.(\d+)#',
		        // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
		        '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
		        // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
		        '#\b(background(?:-position)?):(?:0|none)([;,\}])#i',
		        // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
		        '#\b(border(?:-radius)?|outline):none\b#i',
		        // Remove empty selector(s) [^10]
		        '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
		        // Remove the last semi–colon and replace multiple semi–colon(s) with a semi–colon [^11]
		        '#;+([;\}])#',
		        // Replace multiple white–space(s) with a space [^12]
		        '#\s+#'
		    ), array(
		        // [^1]
		         ETRUEL_AMP_X . '$1',
		        // [^2]
		        '$1' .  ETRUEL_AMP_X,  ETRUEL_AMP_X . '(', ')' .  ETRUEL_AMP_X,
		        // [^3]
		        '#$1$2$3',
		        // [^4]
		        '$1',
		        // [^5]
		        '0',
		        // [^6]
		        '.$1',
		        // [^7]
		        ':0',
		        // [^8]
		        '$1:0 0$2',
		        // [^9]
		        '$1:0',
		        // [^10]
		        '$1',
		        // [^11]
		        '$1',
		        // [^12]
		        ' '
		    ), $input);
		    return trim(str_replace( ETRUEL_AMP_X, ' ', $input));
		}
		// normalize line–break(s)
		public static function normalize_line_break($s) {
		    return str_replace(array("\r\n", "\r"), "\n", $s);
		}
		// trim once
		public static function trim_once($a, $b) {
		    if ($a && strpos($a, $b) === 0 && substr($a, -strlen($b)) === $b) {
		        return substr(substr($a, strlen($b)), 0, -strlen($b));
		    }
		    return $a;
		}
		/**
		* Static function strip_comments
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function strip_comments($buffer) {
			$regex = array(
				"`^([\t\s]+)`ism"=>'',
				"`^\/\*(.+?)\*\/`ism"=>"",
				"`([\n\A;]+)\/\*(.+?)\*\/`ism"=>"$1",
				"`([\n\A;\s]+)//(.+?)[\n\r]`ism"=>"$1\n",
				"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism"=>"\n"
			);
			$buffer = preg_replace(array_keys($regex), $regex, $buffer);
			return $buffer;
		}

	}
}

?>