<?php

namespace bhr\Admin\Controller;

use bhr\Admin\Model\AdminModel;
use bhr\Admin\Model\Helper as ModelHelper;
use bhr\Includes\Helper as IncludesHelper;
use Error;
use Exception;
use WP_REST_Request;
use WP_REST_Response;

class CallbackController {

	/**
	 * @var AdminModel
	 */
	private $AdminModel;

	public function __construct() {
		$this->AdminModel = new AdminModel();
		$this->AdminModel->getConfigurationFromDb();
		$this->AdminModel->getPlatformSettingsFromDb();
	}

	/**
     * Log message from API v3 callback request
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response response
	 */
	public function log_callback_message( $request )
	{
		try {
			$params = $request->get_params();
			if ( empty( $params['problems'] ) ) {
				ModelHelper::salesmanago_log('There is no data in request body', __FILE__ );
				exit;
			}
			foreach ( $params['problems'] as $problem )
			{
				ModelHelper::salesmanago_log( $problem, __CLASS__, true );
			}
			$this->AdminModel->getConfiguration()->setIsNewApiError( true );
			$this->AdminModel->saveConfiguration();

			return new WP_REST_Response( array (
				'status' => 200,
	            'response' => 'Message logged'
            ) );
		} catch ( Error | Exception $e ) {
			error_log( $e->getMessage() );
			exit;
		}
	}

	/**
	 * User acknowledges being notified about Product API Error from Callback
	 */
	public function acknowledge_callback_message()
	{
		$this->AdminModel->getConfiguration()->setIsNewApiError( false );
		$this->AdminModel->saveConfiguration();
	}

	/**
	 * Validate the request using its token
	 * @param string $token
	 * @return bool is_valid_callback
	 */
	public function validate_api_v3_callback( $token )
	{
		return $token === IncludesHelper::generate_sm_token();
	}
}
