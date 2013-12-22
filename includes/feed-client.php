<?php

class MEXP_Picasa_Feed_Client {

	const BASE_URL = 'https://picasaweb.google.com/data/feed/api';

	/**
	 * Makes a request to Picasa Feed API.
	 *
	 * @param array $args
	 * @return WP_Error|array
	 */
	public function request( array $args ) {
		if ( ! isset( $args['kind'] ) )
			$args['kind'] = 'photo';

		if ( ! isset( $args['alt'] ) ) {
			$args['alt'] = 'json';
		}

		if ( ! isset( $args['user'] ) || ( isset( $args['user'] ) && empty( $args['user'] ) ) ) {
			$base_url = self::BASE_URL . '/all';
		} else {
			$base_url = self::BASE_URL . "/user/{$args['user']}";
		}

		if ( isset( $args['user'] ) ) {
			unset( $args['user'] );
		}

		foreach ( $args as $key => $value ) {
			$args[ $key ] = urlencode( $value );
		}

		$url      = add_query_arg( $args, $base_url );
		$response = (array) wp_remote_get( $url );

		if ( ! isset( $response['response']['code'] ) || 200 !== (int) $response['response']['code'] ) {
			return new WP_Error(
				'mexp_picasa_unexpected_response',
				sprintf( __( 'Unexpected response from Picasa feed with status code %s', 'mexp-picasa' ), $response['response']['code'] )
			);
		}

		$decoded_response = json_decode( $response['body'], true );
		if ( ! is_array( $decoded_response ) ) {
			return new WP_Error(
				'mexp_picasa_unexpected_response',
				$response['body']
			);
		}

		return $decoded_response;
	}
}
