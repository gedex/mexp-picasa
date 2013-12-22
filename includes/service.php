<?php

/**
 * Picasa service for Media Explorer.
 *
 * @since 0.1.0
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class MEXP_Picasa_Service extends MEXP_Service {

	/**
	 * Service name.
	 */
	const NAME = 'picasa_mexp_service';

	/**
	 * Number of images to return by default.
	 */
	const DEFAULT_PER_PAGE = 18;

	/**
	 * Constructor.
	 *
	 * Sets template.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct() {
		$this->set_template( new MEXP_Picasa_Template );
	}

	/**
	 * Fired when the service is loaded.
	 *
	 * Enqueue static assets.
	 *
	 * Hooks into MEXP tabs and labels.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function load() {
		add_action( 'mexp_enqueue', array( $this, 'enqueue_statics' ) );
		add_action( 'mexp_tabs',    array( $this, 'tabs' ), 10, 1 );
		add_action( 'mexp_labels',  array( $this, 'labels' ), 10, 1 );
	}


	/**
	 * Enqueue static assets (CSS/JS).
	 *
	 * @since 0.1.0
	 * @action mexp_enqueue
	 * @return void
	 */
	public function enqueue_statics() {
		wp_enqueue_style(
			'mexp-picasa',
			trailingslashit( MEXP_PICASA_URL ) . 'css/mexp-picasa.css',
			array( 'mexp' ),
			MEXP_Picasa::VERSION
		);
	}

	/**
	 * Returns an array of tabs (routers) for the service's media manager panel.
	 *
	 * @since 0.1.0
	 * @filter mexp_tabs.
	 * @param array $tabs Associative array of default tab items.
	 * @return array Associative array of tabs. The key is the tab ID and the value is an array of tab attributes.
	 */
	public function tabs( array $tabs ) {
		$tabs[ self::NAME ] = array(
			'all' => array(
				'text'       => _x( 'All', 'Tab title', 'mexp-picasa' ),
				'defaultTab' => true,
			),
			'tag' => array(
				'text' => _x( 'By Tag', 'Tab title', 'mexp-picasa' ),
			),
			'user' => array(
				'text' => _x( 'By User', 'Tab title', 'mexp-picasa' ),
			),
		);

		return $tabs;
	}

	/**
	 * Returns an array of custom text labels for this service.
	 *
	 * @since 0.1.0
	 * @filter mexp_labels
	 * @param array $labels Associative array of default labels.
	 * @return array Associative array of labels.
	 */
	public function labels( array $labels ) {
		$labels[ self::NAME ] = array(
			'title'     => __( 'Insert Picasa Photos', 'mexp-picasa' ),
			'insert'    => __( 'Insert', 'mexp-picasa' ),
			'noresults' => __( 'No photos matched your search query', 'mexp-picasa' ),
			'loadmore'  => __( 'Load more photos', 'mexp-picasa' ),
		);

		return $labels;
	}

	public function request( array $request ) {
		if ( ! $request['max_id'] )
			$start_index = 1;
		else
			$start_index = $request['max_id'];

		$per_page = (int) apply_filters( 'mexp_picasa_per_page', self::DEFAULT_PER_PAGE );

		$request_args = array(
			'start-index' => $start_index,
			'max-results' => $per_page,
		);

		$params = $request['params'];
		switch ( $params['tab'] ) {
			case 'tag':
				$request_args['tag'] = sanitize_text_field( $params['tag'] );
				break;
			case 'user':
				$request_args['user'] = sanitize_text_field( $params['user'] );
				break;
			case 'all':
			default:
				$request_args['q'] = sanitize_text_field( $params['text'] );
				break;
		}

		$picasa = $this->_get_client();

		// Response from feed.
		$search_response = $picasa->request( $request_args );
		if ( is_wp_error( $search_response ) )
			return $search_response;

		// Creates the response for the API.
		$response = new MEXP_Response();

		if ( ! isset( $search_response['feed'] ) )
			return false;

		if ( ! isset( $search_response['feed']['xmlns$gphoto'] ) )
			return false;

		if ( ! isset( $search_response['feed']['entry'] ) )
			return false;

		$gphoto_ns = $search_response['feed']['xmlns$gphoto'] . '#canonical';

		foreach ( $search_response['feed']['entry'] as $index => $photo ) {
			$item = new MEXP_Response_Item();

			$item->set_id( $start_index + $index );

			$cannonical_url = '#';
			foreach ( $photo['link'] as $link ) {
				if ( $gphoto_ns === $link['rel'] ) {
					$cannonical_url = $link['href'];
					break;
				}
			}
			$item->set_url( $cannonical_url );

			$item->set_content( $photo['title']['$t'] );

			$thumbnail_url = '';
			foreach ( $photo['media$group']['media$thumbnail'] as $thumbnail ) {
				if ( 288 === $thumbnail['width'] ) {
					$thumbnail_url = $thumbnail['url'];
					break;
				}
			}
			if ( ! $thumbnail_url ) {
				$last_thumbnail = array_pop( $photo['media$group']['media$thumbnail'] );
				$thumbnail_url  = $last_thumbnail['url'];
			}
			$item->set_thumbnail( $thumbnail_url );

			if ( 'user' === $params['tab'] ) {
				$owner = $search_response['feed']['author'][0]['name']['$t'];
			} else {
				$owner = '';
				foreach ( $photo['author'] as $author ) {
					if ( 'owner' === $author['type'] ) {
						$owner = $author['name']['$t'];
					}
				}
			}
			$item->add_meta( 'user', $owner );

			$item->set_date( strtotime( $photo['published']['$t'] ) );
			$item->set_date_format( 'g:i A - j M y' );

			$response->add_item( $item );
		}

		$response->add_meta( 'max_id', $start_index + $index + 1 );

		return $response;
	}

	private function _get_client() {
		return new MEXP_Picasa_Feed_Client();
	}
}
