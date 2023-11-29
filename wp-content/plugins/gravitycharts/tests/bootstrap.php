<?php

require 'vendor/autoload.php';

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Apply filters polyfill for tests.
	 *
	 * @return mixed
	 */
	function apply_filters() {
		return func_get_arg( 1 );
	}
}

if ( ! function_exists( 'apply_filters_deprecated' ) ) {
	/**
	 * Apply filters polyfill for tests.
	 *
	 * @return mixed
	 */
	function apply_filters_deprecated() {
		return func_get_arg( 1 );
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	/**
	 * Polyfill for date_i18n for tests.
	 *
	 * @return string|false
	 */
	function date_i18n() {
		return gmdate( ...func_get_args() );
	}
}
