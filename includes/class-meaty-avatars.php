<?php

if ( ! defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( ! class_exists( "Meaty_Avatars" ) ) {

	class Meaty_Avatars {


		/**
		 * Hooks foor plugins_loaded action
		 *
		 * @return void
		 */
		public function plugins_loaded() {

			// Filter the user avatar
			add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 5 );

			// Hooks for registering admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );

			// Show a the avatar on the user profile with AJAX calls to get a new one
			add_action( 'show_user_profile', array( $this, 'edit_user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'edit_user_profile' ) );

			// Save the new avatar tag
			add_action( 'personal_options_update',  array( $this, 'edit_user_profile_update' ) );
			add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ) );
		}


		/**
		 * Filter for get_avatar
		 *
		 * @param string $avatar      img tag for the user's avatar.
		 * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
		 *                            user email, WP_User object, WP_Post object, or WP_Comment object.
		 * @param int    $size        Square avatar width and height in pixels to retrieve.
		 * @param string $alt         Alternative text to use in the avatar image tag.
		 *                                       Default empty.
		 * @param array  $args        Arguments passed to get_avatar_data(), after processing.
		 * @return string
		 */
		public function get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

			$meta_key ='meaty_avatar_tag';
			$user = false;

			// find the appropriate user
			if ( is_numeric( $id_or_email ) ) {

				$id = (int) $id_or_email;
				$user = get_user_by( 'id' , $id );

			} elseif ( is_object( $id_or_email ) ) {

				if ( ! empty( $id_or_email->user_id ) ) {
					$id = (int) $id_or_email->user_id;
					$user = get_user_by( 'id' , $id );
				}

			} else {
				$user = get_user_by( 'email', $id_or_email );
			}


			if ( ! empty( $user ) && is_object( $user ) ) {

				// get the assigned tag or make a new one
				$tag = get_user_meta( $user->ID, $meta_key, true );
				if ( ! empty( $tag ) ) {
					$avatar = $this->create_avatar_html( $this->generate_url( $tag, $size ), $size, $user->display_name );
				} else {

					$s = $this->get_baconmockup_tags();

					if ( ! empty( $s ) && is_array( $s ) ) {
						// get a random meat
						$random = $s[array_rand( $s, 1 )];

						// assign it to the user
						update_user_meta( $user->ID, $meta_key, $random );

						// generate img tag
						$avatar = $this->create_avatar_html( $this->generate_url( $random, $size ), $size, $random );
					}
				}

			}

			return $avatar;

		}

		/**
		 * Generates a baconmockup.com URL
		 *
		 * @param  string $tag  The specific tag to retrive.
		 * @param  int $size    Used for both the width and height parameters.
		 * @return string
		 */
		public function generate_url( $tag, $size ) {

			// Create the URL
			$url = implode( '/', array(
					'https://baconmockup.com',
					absint( $size ),
					absint( $size ),
					sanitize_key( $tag ),
				)
			);

			// Allow filtering of the returned URL
			return apply_filters( 'meaty-avatars-generate-url', $url, $tag, $size );
		}


		/**
		 * Creates the img tag HTML for the avatar.
		 *
		 * @param string $url  The URL to the avatar image.
		 * @param int    $size The height and width of the image.
		 * @param string $alt  Text for the images alt tag.
		 *
		 * @return string
		 */
		public function create_avatar_html( $url, $size, $alt ) {
			return sprintf( '<img src="%1$s" height="%2$s" width="%2$s" class="avatar avatar-%2$s" style="height:%2$s; width: %2$s" alt="%3$s" title="%3$s" />',
				esc_url( $url ),
				esc_attr( $size ),
				esc_attr( $alt ) );
		}

		/**
		 * Gets a list of mockup image tags (bacon, corned-beef, etc).
		 *
		 * @return array
		 */
		public function get_baconmockup_tags() {

			// See if we have a cached version.
			$tags = get_site_transient( 'baconmockup-tags' );
			if ( empty( $tags ) ) {

				// Call the API to get the tags.
				$response = wp_remote_get( 'https://baconmockup.com/images-api/image-tags/' );

				if ( is_wp_error( $response ) ) {
					return false;
				}
				else {
					$response = json_decode( wp_remote_retrieve_body( $response ) );
				}

				// Store the tags in the cache.
				if ( ! empty( $response ) && ! empty( $response->data ) ) {
					$tags = $response->data;
					set_site_transient( 'baconmockup-tags', $tags, DAY_IN_SECONDS * 1 );
				}

			}

			return $tags;
		}


		/**
		 * Registers admin scripts
		 *
		 * @return void
		 */
		public function admin_scripts() {
			wp_register_script( 'meaty-avatars-admin',
				MEATY_AVATARS_PLUGIN_URL . 'assets/js/meaty-avatars-admin.js',
				array( 'jquery' ),
				'1.1',
				true
			);
		}


		/**
		 * Shows the meaty avatar and tag for the supplied user
		 *
		 * @param  WP_User $user The user being displayed.
		 * @return void
		 */
		public function edit_user_profile( $user ) {

			// Nonce for saving the new tag.
			wp_nonce_field( 'meaty-avatars', 'meaty-avatars-nonce' );

			// Enqueue the admin scripts to handle AJAX calls
			wp_enqueue_script( 'meaty-avatars-admin' );

			// Get the currently assigned tag.
			$tag = get_user_meta( $user->ID, 'meaty_avatar_tag', true );

			?>
				<h3><?php _e( 'Meaty Avatar','meaty-avatars' ); ?></h3>
				<table class="form-table" id="meaty-avatars-form-table">
					<tbody>
						<tr>
							<th>
								<?php _e( 'Current Avatar' , 'meaty-avatars' ); ?>
							</th>
							<td>
								<input type="hidden" name="meaty_avatar_tag" class="meat-avatar-tag" value="<?php echo esc_attr( $tag ); ?>" />
								<span class="meaty-avatar-tag-label"><em><?php echo esc_html( $tag ); ?></em></span>
								<div class="meaty-avatar-container">
									<?php echo get_avatar( $user->ID, 128 ); ?>
								</div>
								<p>
									<a href="#new-meaty-avatar" class="new-meaty-avatar"><?php esc_html_e( 'Get New Meaty Avatar', 'meaty-avatars' ) ?></a>
									<span class="meaty-avatar-spinner spinner hidden" style="float: none;"></span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			<?php 
		}


		/**
		 * Saves the user's meaty avatar tag
		 *
		 * @param  int $user_id The user ID.
		 * @return void
		 */
		public function edit_user_profile_update( $user_id ) {

			if ( ! check_admin_referer( 'meaty-avatars', 'meaty-avatars-nonce' ) ) {
				return;
			}

			if ( ! current_user_can('edit_user', $user_id ) ) {
				wp_die( __( 'You do not have permission to edit this user.' ) );
			}

			$tag = filter_input( INPUT_POST, 'meaty_avatar_tag', FILTER_SANITIZE_STRING );

			// Filter this down to a key value
			$tag = sanitize_key( $tag );

			// Store the tag
			update_user_meta( $user_id, 'meaty_avatar_tag', $tag );
		}

	}
}
