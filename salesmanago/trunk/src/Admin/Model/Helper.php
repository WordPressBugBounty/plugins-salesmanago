<?php

namespace bhr\Admin\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Error;
use Exception;
use ErrorException;
use SALESmanago\Exception\Exception as SmException;
use RGFormsModel;


class Helper
{
	use \bhr\Includes\Helper;

	/**
	 * @param $array
	 * @return bool
	 */
	public static function isEmpty( $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $value ) {
				if ( ! empty( $value ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * @param $arr
	 *
	 * @return array
	 * @throws SmException
	 */
	public static function filterArray( $arr ) {
		if ( ! is_array( $arr ) ) {
			throw new SmException( "$arr is not an array!" );
		}
		$out = array();
		foreach ( $arr as $item ) {
			$out[] = $item;
		}
		return $out;
	}

	/**
	 * @param $param
	 *
	 * @return bool
	 */
	public static function isPluginActive( $param ) {
		if ( function_exists( 'is_plugin_active' ) ) {
			return is_plugin_active( $param );
		}
		return false;
	}

	/**
	 * @param $param
	 *
	 * @return string
	 */
	public static function pluginDirPath( $param ) {
		if ( function_exists( 'plugin_dir_path' ) ) {
			return plugin_dir_path( $param );
		}
		return '';
	}

	/**
	 * @param $param
	 *
	 * @return string
	 */
	public static function pluginDirUrl( $param ) {
		if ( function_exists( 'plugin_dir_url' ) ) {
			return plugin_dir_url( $param );
		}
		return '';
	}

	/**
	 * @param $param
	 *
	 * @return \stdClass|\WC_Order[]
	 */
	public static function wcGetOrders( $param ) {
		return wc_get_orders( $param );
	}

	/**
	 * @retrun void
	 */
	public static function setErrorHandler() {
		set_error_handler( 'bhr\Admin\Model\ErrorToExceptionHandler' );
	}

	/**
	 * @param $param
	 *
	 * @return false|int[]|\WP_Post[]
	 */
	public static function getCf7Forms( $param ) {
		if ( function_exists( 'get_posts' ) ) {
			return get_posts( $param );
		}
		return false;
	}

	/**
	 * @return array|false|object
	 */
	public static function getFfForms() {
		if ( function_exists( 'wpFluent' ) ) {
            try {
                return wpFluent()->table('fluentform_forms')
                                 ->orderBy('id')
                                 ->select(array('id', 'title'))
                                 ->get();
            } catch (\WpFluent\Exception $e) {
                error_log($e->getMessage());
            }
		}
		return false;
	}

	/**
	 * @return array
	 * @throws SmException
	 */
	public static function getGfForms() {
		if ( ! class_exists( 'RGFormsModel' ) ) {
			throw new SmException( 'Gravity Forms class not found', 672 );
		} elseif ( ! method_exists( 'RGFormsModel', 'get_forms' ) ) {
			throw new SmException( 'Gravity Forms class not found', 673 );
		}
		return RGFormsModel::get_forms();
	}

	/**
	 * @param $scope
	 * @param $label
	 * @param $name
	 */
	public static function iclRegisterString( $scope, $label, $name ) {
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( $scope, $label, $name );
		}
	}

	/**
	 * @param $parent_slug
	 * @param $page_title
	 * @param $menu_title
	 * @param $capability
	 * @param $menu_slug
	 * @param string      $function
	 */
	public static function addSubmenuPage( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
		if ( function_exists( 'add_submenu_page' ) ) {
			add_submenu_page(
				$parent_slug,
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				$function
			);
		}
	}

	/**
	 * @param $page_title
	 * @param $menu_title
	 * @param $capability
	 * @param $menu_slug
	 * @param string     $function
	 * @param string     $icon_url
	 * @param null       $position
	 */
	public static function addMenuPage( $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null ) {
		if ( function_exists( 'add_menu_page' ) ) {
			add_menu_page(
				$page_title,
				$menu_title,
				$capability,
				$menu_slug,
				$function,
				$icon_url,
				$position
			);
		}
	}

	/**
	 * @param $handle
	 * @param string $src
	 * @param array  $deps
	 * @param false  $ver
	 * @param string $media
	 */
	public static function wpEnqueueStyle( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
	}

	/**
	 * @param $handle
	 * @param string $src
	 * @param array  $deps
	 * @param false  $ver
	 * @param false  $in_footer
	 */
	public static function wpEnqueueScript( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	}


    /**
     * @param $role
     *
     * @return bool
     */
    public static function grantAccessToSalesmanagoPlugin( $role ) {
        if ( ! user_can( $role, SALESMANAGO ) ) {
            get_role($role)->add_cap(SALESMANAGO);
        }
        return true;
    }


    /**
     * @return array
     */
    public static function wcGetOrderStatuses() {
        if (function_exists('wc_get_order_statuses')) {
            return wc_get_order_statuses();
        }
        return array();
    }

	/**
	 * Write an entry to a log file in the uploads directory.
	 *
	 * @param mixed $entry String or array to be written to the log.
	 * @param string $source File or function where the exception occurred.
	 * @param boolean $is_api_v3 Is the log for Product Api.
	 * @param string $mode File mode open
	 * @return boolean|int Number of bytes written to the log file, false otherwise.
	 */
	public static function salesmanago_log( $entry, $source, $is_api_v3 = false, $mode = 'a' ) {
		try {
			$upload_dir = wp_upload_dir( null, false );

			$sm_log_dir = $is_api_v3 ? $upload_dir['basedir'] . '/sm-logs/api-v3' : $upload_dir['basedir'] . '/sm-logs';
			if ( !is_dir( $sm_log_dir ) )
			{
				//recursive set to true to make sure both directories are created
				mkdir( $sm_log_dir, 0700 , true);
			}
			$decorator = PHP_EOL . '*----------------------------------------------------------------------------------------------*' . PHP_EOL;
			$template = 'Source: ' . $source . PHP_EOL;
			if ( $is_api_v3 ) {
				$template .= 'Reason code: ' .  $entry['reasonCode'] . PHP_EOL;
				$template .= 'Message: ' .  $entry['message'] . PHP_EOL;
			} else {
				$template .= $entry;
			}
			$file  = $sm_log_dir . '/' . date( 'd-m-Y' ) .'.log';
			if ( is_writable( $sm_log_dir ) ) {
				$file  = fopen( $file, $mode );
				$bytes = fwrite( $file, $decorator . current_time( 'mysql' ) . "::" .'SALESmanago' . PHP_EOL . $template );
				fclose( $file );
				return $bytes;
			} else {
				error_log( $entry );
				throw new SmException( 'Permission to open SALESmanago log file denied' );
			}
		} catch ( Exception | Error $e) {
			return false;
		}
	}
}

/**
 * @param $severity
 * @param $message
 * @param $file
 * @param $line
 * @throws ErrorException
 */
function ErrorToExceptionHandler( $severity, $message, $file, $line ) {
	if ( ! ( error_reporting() & $severity ) ) {
		return;
	}
	throw new ErrorException( $message, 0, $severity, $file, $line );
}

