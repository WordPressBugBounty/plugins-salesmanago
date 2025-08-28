<?php

namespace bhr\Frontend\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use bhr\Admin\Entity\Configuration;
use WPCF7_Submission;

class Helper {

	use \bhr\Includes\Helper;

	const PREVENT_DOI_MAILS_TIME = 30;

    /**
     * @param $string
     * @return string
     */
    private static function atbash($string)
    {
        $atbash = Array(
            "a" => "Z", "g" => "T", "m" => "N", "s" => "H",
            "b" => "Y", "h" => "S", "n" => "M", "t" => "G",
            "c" => "X", "i" => "R", "o" => "L", "u" => "F",
            "d" => "W", "j" => "Q", "p" => "K", "v" => "E",
            "e" => "V", "k" => "P", "q" => "J", "w" => "D",
            "f" => "U", "l" => "O", "r" => "I", "x" => "C",
            "y" => "B", "z" => "A",
            "A" => "z", "G" => "t", "M" => "n", "S" => "h",
            "B" => "y", "H" => "s", "N" => "m", "T" => "g",
            "C" => "x", "I" => "r", "O" => "l", "U" => "f",
            "D" => "w", "J" => "q", "P" => "k", "V" => "e",
            "E" => "v", "K" => "p", "Q" => "j", "W" => "d",
            "F" => "u", "L" => "o", "R" => "i", "X" => "c",
            "Y" => "b", "Z" => "a",
        );

		return strtr( $string, $atbash );
	}

	/**
	 * @param $message
	 * @param bool    $compressed
	 * @return string
	 */
	public static function encrypt( $message, $compressed = true ) {
		if ( $compressed ) {
			return self::atbash( strtr( base64_encode( str_rot13( json_encode( $message ) ) ), '+/=', '._-' ) );
		}
		return self::atbash( strtr( base64_encode( json_encode( $message ) ), '+/=', '._-' ) );
	}

	/**
	 * @param $message
	 * @param bool    $compressed
	 * @return mixed|null
	 */
	public static function decrypt( $message, $compressed = true ) {
		if ( $compressed ) {
			return json_decode( str_rot13( base64_decode( strtr( self::atbash( $message ), '._-', '+/=' ) ) ), true );
		}
		return json_decode( base64_decode( strtr( self::atbash( $message ), '._-', '+/=' ) ), true );
	}

	/**
	 * @return string
	 */
	public static function currentFilter() {
		if ( function_exists( 'current_filter' ) ) {
			return current_filter();
		}
		return '';
	}

	/**
	 * @param $namespace
	 * @param $route
	 * @param $args
	 */
	public static function registerRestRoute( $namespace, $route, $args ) {
		if ( function_exists( 'register_rest_route' ) ) {
			register_rest_route( $namespace, $route, $args );
		}
	}

	/**
	 * @retrun void
	 */
	public static function redirectToCart() {
		if ( function_exists( 'wp_redirect' ) && function_exists( 'wc_get_cart_url' ) ) {
			wp_redirect( wc_get_cart_url() );
		}
	}

	/**
	 * @return int
	 */
	public static function getCurrentUserId() {
		 return get_current_user_id();
	}

	/**
	 * @param $formValues
	 * @param $id
	 *
	 * @return mixed|string|null
	 */
	public static function getGfFieldValue( $formValues, $id ) {
		if ( function_exists( 'rgar' ) ) {
			return rgar( $formValues, $id );
		}
		return null;
	}

	/**
	 * @return string|null
	 */
	public static function getCurrentAction() {
		if ( function_exists( 'current_action' ) ) {
			return current_action();
		}
		return null;
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return int|mixed|void
	 */
	public static function setFilter( $name, $value ) {
		if ( function_exists( 'apply_filters' ) ) {
			return apply_filters( $name, $value );
		}
		return 0;
	}

	/**
	 * @param $id
	 */
	public static function setCurrentUser( $id ) {
		if ( function_exists( 'wp_set_current_user' ) ) {
			wp_set_current_user( $id );
		}
	}

	/**
	 * @param ...$args
	 *
	 * @return string
	 */
	public static function getQueryArgs( ...$args ) {
		if ( function_exists( 'add_query_arg' ) ) {
			return add_query_arg( ...$args );
		}
		return '';
	}

	/**
	 * @param $param
	 *
	 * @return string|void
	 */
	public static function getHomeUrl( $param ) {
		if ( function_exists( 'home_url' ) ) {
			return home_url( $param );
		}
		return '';
	}

	/**
	 * @return WPCF7_Submission|null
	 */
	public static function getCf7SubmissionInstance() {
		if ( class_exists( 'WPCF7_Submission' ) ) {
			return WPCF7_Submission::get_instance();
		}
		return null;
	}

	/**
	 * @param $hook_name
	 * @param ...$arg
	 */
	public static function doAction( $hook_name, ...$arg ) {
		do_action( $hook_name, ...$arg );
	}

	/**
	 * @return bool
	 */
	public static function preventMultipleDoubleOptInMails() {
		session_start();
		if (
			isset( $_SESSION['preventMultipleDoiMails'] )
			&& $_SESSION['preventMultipleDoiMails'] + self::PREVENT_DOI_MAILS_TIME > time()
		) {
			return false;
		} else {
			$_SESSION['preventMultipleDoiMails'] = time();
			return true;
		}
	}
	/**
	 * Send a curl rgif request to SM when a contact fills a form
	 * as an additional mechanism to ensure contact monitoring and website visit count
	 * due to a problem reported on CF7
	 *
	 * @param string $rgif_url
	 *
	 * @return bool|string
	 */
	public static function send_rgif_request( $rgif_url ) {
		$ch = curl_init();

		$headers = array(
			"user-agent: {$_SERVER['HTTP_USER_AGENT']}",
			'accept-language: en, pl',
		);

		curl_setopt( $ch, CURLOPT_URL, $rgif_url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, false );
		curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 200 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $ch );

		curl_close( $ch );
		return $response;
	}


	/**
	 * Build url for SM rgif request
	 *
	 * @param string $contact_id
	 *
	 * @return string
	 */
	public static function build_rgif_url( $contact_id ) {
		$configuration = Configuration::getInstance();
		$site_url      = get_site_url();

		$path      = 'api/r.gif';
		$endpoint  = $configuration->getEndpoint();
		$location  = $_SERVER['HTTP_HOST'] ?? '';
		$uuid      = $_COOKIE['smuuid'] ?? '';
		$referrer  = urlencode( $site_url . '/' );
		$full_url  = $_SERVER['HTTP_REFERER'] ?? '/';
		$uri       = urlencode( str_replace( $site_url, '', $full_url ) );
		$smid      = $configuration->getClientId();
		$time      = urlencode( date( 'Y-m-d' ) . 'T' . date( 'H:i:s' ) . 'Z' );
		$timestamp = time() * 1000;
		$client    = $contact_id ?? '';
		$title     = isset( $_POST['_wpcf7_container_post'] ) ?
			urlencode( get_the_title( $_POST['_wpcf7_container_post'] ) ) : '';

		$session = 1;
		$cp      = $timestamp;
		$ns      = 'false';
		$vs      = $_COOKIE['_smvs'] ?? '';

		return $endpoint . '/' . $path . '?uri=' . $uri . '&location=' . $location . '&uuid=' . $uuid
			   . '&referrer=' . $referrer . '&smid=' . $smid . '&time=' . $time . '&timestamp=' . $timestamp
			   . '&session=' . $session . '&title=' . $title . '&cp=' . $cp . '&ns=' . $ns . '&vs=' . $vs
			   . '&client=' . $client;
	}
}
