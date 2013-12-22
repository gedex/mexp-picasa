<?php

class MEXP_Picasa_Template extends MEXP_Template {

	/**
	 * Template for single element returned from the API.
	 *
	 * @param  string $id  ID of the view
	 * @param  string $tab Selected tab
	 * @return void
	 */
	public function item( $id, $tab ) {
		?>
		<div id="mexp-item-picasa-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="mexp-item-area mexp-item-picasa" data-id="{{ data.id }}">
			<div class="mexp-item-container clearfix">
				<div class="mexp-item-thumb">
					<img src="{{ data.thumbnail }}">
				</div>
				<div class="mexp-item-main">
					<div class="mexp-item-content">
						{{ data.content }}
					</div>
					<div class="mexp-item-user">
						<?php _e( 'by', 'mexp' ) ?> {{ data.meta.user }}
					</div>
					<div class="mexp-item-date">
						{{ data.date }}
					</div>
				</div>
			</div>
		</div>
		<a href="#" id="mexp-check-{{ data.id }}" data-id="{{ data.id }}" class="check" title="<?php esc_attr_e( 'Deselect', 'mexp' ) ?>">
			<div class="media-modal-icon"></div>
		</a>
		<?php
	}

	public function thumbnail( $id ) {
		?>
		<?php
	}

	/**
	 * Template for the search form.
	 *
	 * @param  string $id ID of the view
	 * @param  string $tab Selected tab
	 * @return void
	 */
	public function search( $id, $tab ) {
		switch ( $tab ) {
			case 'all':
				?>
				<form action="#" class="mexp-toolbar-container clearfix tab-all">
					<input
						type="text"
						name="text"
						value="{{ data.params.text }}"
						class="mexp-input-text mexp-input-search"
						size="40"
						placeholder="<?php echo esc_attr( 'Search Picasa photos', 'mexp-picasa' ); ?>">
					<input type="hidden" name="tab" value="all">
					<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ) ?>">
					<div class="spinner"></div>
				</form>
				<?php
				break;
			case 'tag':
				?>
				<form action="#" class="mexp-toolbar-container clearfix tab-tag">
					<input
						type="text"
						name="tag"
						value="{{ data.params.tag }}"
						class="mexp-input-text mexp-input-search"
						size="40"
						placeholder="<?php echo esc_attr( 'Enter tag', 'mexp-picasa' ); ?>">
					<input type="hidden" name="tab" value="tag">
					<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ) ?>">
					<div class="spinner"></div>
				</form>
				<?php
				break;
			case 'user':
				?>
				<form action="#" class="mexp-toolbar-container clearfix tab-user">
					<input
						type="text"
						name="user"
						value="{{ data.params.user }}"
						class="mexp-input-text mexp-input-search"
						size="40"
						placeholder="<?php echo esc_attr( 'Enter Picasa user', 'mexp-picasa' ); ?>">
					<input type="hidden" name="tab" value="user">
					<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ) ?>">
					<div class="spinner"></div>
				</form>
				<?php
				break;
		}
	}
}
