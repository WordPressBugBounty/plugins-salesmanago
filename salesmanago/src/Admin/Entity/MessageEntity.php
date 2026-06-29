<?php

namespace bhr\Admin\Entity;

if(!defined('ABSPATH')) exit;

use SALESmanago\Exception\Exception;

final class MessageEntity
{
    private static $instances = [];
    public $messages = array();
    public $messagesAfterView = false;

    protected function __clone() {}

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * @return mixed|static
     */
    public static function getInstance()
    {
        $cls = MessageEntity::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new self();
        }

        return self::$instances[$cls];
    }

    /**
     * @param $e
     * @param string $type
     */
    public function addException($e, $type='error')
    {
        $this->messages[] = array(
            'type'    => $this->sanitize_type( $type ),
            'message' => $this->sanitize_message( $e->getMessage() ),
            'code'    => $this->sanitize_code( $e->getCode() )
        );
    }

    /**
     * @param string $message
     * @param string $type
     * @param int $code
     */
    public function addMessage($message='', $type='warning', $code = 0)
    {
        if($type === 'success' && $code === 0) {
            $code = 700;
        }
        $this->messages[] = array(
            'type'    => $this->sanitize_type( $type ),
            'message' => $this->sanitize_message( $message ),
            'code'    => $this->sanitize_code( $code )
        );
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param $code
     * @param bool $appendConsoleInfo
     * @return mixed|string
     */
    public function getMessageByCode($code, $appendConsoleInfo = true)
    {
        $checkConsole = "<br>" . __('Check browser console (F12) for details.', 'salesmanago');
        $messages = array(
            0  => __('Unknown error', 'salesmanago'),

            100 => __('Unknown login error', 'salesmanago'),
            101 => __('Authorization error. Make sure email and password are correct', 'salesmanago'),
            102 => __('User inactive. Contact Customer Success for more information', 'salesmanago'),
            110 => __('Error on login page rendering', 'salesmanago'),
            120 => __('Error on getting Owner List', 'salesmanago'),
            150 => __('Error on logout. If this persists, try removing wp_options/salesmanago_configuration from your database.', 'salesmanago'),

            200 => __('Unknown upsert error', 'salesmanago'),

            300 => __('Unknown export (batch upsert) error', 'salesmanago'),

            400 => __('Unknown error while connecting to Manago AI with Guzzle Client', 'salesmanago'),
            401 => __('Specified endpoint cannot be resolved. On Manago AI panel go to Settings -> Integration and check your endpoint in API access tab.', 'salesmanago'),
            403 => __('Not authorized. Please refresh the view', 'salesmanago'),

            500 => __('Unknown error on settings read/write', 'salesmanago'),
            501 => __('Error while reading Configuration from DB', 'salesmanago'),
            502 => __('Error while reading Settings from DB', 'salesmanago'),
            503 => __('Error while writing Configuration to DB', 'salesmanago'),
            504 => __('Error while writing Settings to DB', 'salesmanago'),

            600 => __('Unknown error in Manago AI plugin', 'salesmanago'),
            601 => __('Error on registering menu pages', 'salesmanago'),
            602 => __('Error on rendering settings page', 'salesmanago'),
            603 => __('Error on registering css/js/translations', 'salesmanago'),
            604 => __('Error on routing your request', 'salesmanago'),
            610 => __('Error on Integration Settings page', 'salesmanago'),
            620 => __('Error on Export page', 'salesmanago'),
            630 => __('Error on Plugins settings page', 'salesmanago'),
            640 => __('Error on WordPress plugin settings page', 'salesmanago'),
            650 => __('Error on WooCommerce plugin settings page', 'salesmanago'),
            660 => __('Error on Contact Form 7 plugin settings page', 'salesmanago'),
            661 => __('Error on listing Contact 7 Forms', 'salesmanago'),
            670 => __('Error on Gravity Forms plugin settings page', 'salesmanago'),
            671 => __('Error on listing Gravity Forms', 'salesmanago'),
            680 => __('Error on Fluent Forms plugin settings page', 'salesmanago'),
            681 => __('Error on listing Fluent Forms', 'salesmanago'),
            690 => __('Error on Monitoring code page', 'salesmanago'),

            700 => __('Success.', 'salesmanago'),
            701 => __('Logged in.', 'salesmanago'),
            702 => __('Logged out.', 'salesmanago'),
            703 => __('Settings have been saved.', 'salesmanago'),

            // APIv3 Product Catalog Messages
            704 => __( 'Authentication successful. You can select an existing Product Catalog or create a new one.', 'salesmanago' ),
            705 => __( 'The API key doesn\'t seem valid. Make sure the key is active and all characters have been copied.', 'salesmanago' ),
            706 => __( 'Unknown API error.', 'salesmanago' ),
            707 => __( 'New Product Catalog has been created. Please refresh Catalog list to use it.', 'salesmanago' ),
            708 => __( 'Incorrect location field value. Please check it in the Integration settings tab', 'salesmanago' ),
            709 => __( 'Error on setting the active catalog', 'salesmanago' ),
            710 => __( 'The request has timed out. Please try again', 'salesmanago' ),
            711 => __( 'Product Catalog has not been created. Please check the About tab for error information.', 'salesmanago' )
        );
        if(!isset($messages[$code]) && isset($messages[floor($code/10)*10])) {
            $code = floor($code/10)*10;
        } elseif (!isset($messages[$code]) && isset($messages[floor($code/100)*100])) {
            $code = floor($code/100)*100;
        } elseif (!isset($messages[$code])) {
            $code = 0;
        }
        $message = isset($messages[$code]) ? esc_html( $messages[$code] ) : esc_html( $messages[0] );
        return  ($appendConsoleInfo) ? $message . $checkConsole : $message;
    }

    /**
     * @return string
     */
    public function getMessagesHtml()
    {
        $out = '';
        foreach ( $this->messages as $message ) {
            if( empty( $message['type'] ) ) {
                continue;
            }
            $type = esc_attr( $this->sanitize_type( $message['type'] ) );
            
            if( $message['type'] === 'error' || $message['type'] === 'warning' ) {
                $viewMessage = $this->getMessageByCode( $message['code'], true );
                $consoleMessage = wp_json_encode( $message['message'] );
                
                $out .= '<div class="salesmanago-notice notice notice-' . $type . ' inline">' . $viewMessage . '</div>';
                $out .= '<script>console.warn("SM error ' . esc_js( $message['code'] ) . ': " + ' . $consoleMessage . ')</script>';
            } elseif ( $message['type'] === 'success' || $message['type'] === 'info' ) {
                $viewMessage = $this->getMessageByCode( $message['code'], false );
                $out .= '<div class="salesmanago-notice notice notice-' . $type . ' inline">' . $viewMessage . '</div>';
            } elseif ( $message['type'] === 'apiV3Error' ) {
                $viewMessage = $this->getMessageByCode( $message['code'], false );
                $out .= '<div class="salesmanago-notice notice notice-error inline">' . $viewMessage . '</div>';
            }
        }
        return $out;
    }

    /**
     * @param mixed $messages
     * @return $this
     */
    public function setMessages($messages)
    {
        if ( is_array( $messages ) ) {
            $sanitized = array();
            foreach ( $messages as $msg ) {
                if ( is_array( $msg ) && isset( $msg['type'], $msg['message'], $msg['code'] ) ) {
                    $sanitized[] = array(
                        'type'    => $this->sanitize_type( $msg['type'] ),
                        'message' => $this->sanitize_message( $msg['message'] ),
                        'code'    => $this->sanitize_code( $msg['code'] ),
                    );
                }
            }
            $this->messages = $sanitized;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isMessagesAfterView()
    {
        return $this->messagesAfterView;
    }

    /**
     * @param bool $messagesAfterView
     * @return $this
     */
    public function setMessagesAfterView($messagesAfterView)
    {
        $this->messagesAfterView = (bool) $messagesAfterView;
        return $this;
    }
    
    // ========================================================================
    // SECURITY HELPER METHODS (for sanitization/escaping)
    // ========================================================================
    
    /**
     * Sanitize message type to allowed values only
     *
     * @param string $type
     * @return string
     */
    private function sanitize_type( $type ) {
        $allowed = array( 'error', 'warning', 'success', 'info', 'apiV3Error' );
        return in_array( $type, $allowed, true ) ? $type : 'info';
    }
    
    /**
     * Sanitize message text to prevent XSS and log injection
     *
     * @param string $message
     * @return string
     */
    private function sanitize_message( $message ) {
        if ( ! is_string( $message ) ) {
            return '';
        }
        $sanitized = preg_replace( '/[\r\n\t\x00-\x1F\x7F]/', ' ', $message );
        // Trim and limit length to prevent log flooding/DoS
        return substr( trim( $sanitized ), 0, 2048 );
    }
    
    /**
     * Sanitize message code to integer
     *
     * @param mixed $code
     * @return int
     */
    private function sanitize_code( $code ) {
        return filter_var( $code, FILTER_VALIDATE_INT ) ?: 0;
    }
}
