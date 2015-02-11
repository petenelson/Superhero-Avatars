<?php

if ( ! defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( ! class_exists( "Meaty_Avatars" ) ) {

	class Meaty_Avatars {

		/**
		 *
		 */
		public function plugins_loaded() {
			add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 5 );
		}


		/**
		 *
		 *
		 * @param unknown $avatar
		 * @param unknown $id_or_email
		 * @param unknown $size
		 * @param unknown $default
		 * @param unknown $alt
		 *
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


			if ( $user && is_object( $user ) ) {

				// get the assigned tag or make a new one
				if ( $tag = get_user_meta( $user->ID, $meta_key, true ) ) {
					$avatar = $this->create_avatar_html( $this->generate_url( $tag, $size ), $size, $user->display_name );
				} else {

					$s = $this->get_baconmockup_tags();

					if ( ! empty( $s ) ) {
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


		function generate_url( $tag, $size ) {

			return implode( '/', array(
					'https://baconmockup.com',
					$size,
					$size,
					$tag,
				)
			);

		}


		/**
		 *
		 *
		 * @param unknown $url
		 * @param unknown $size
		 * @param unknown $alt
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
		 *
		 *
		 * @return array
		 */
		public function get_baconmockup_tags() {
			$return = get_site_transient( 'baconmockup-tags' );
			if ( empty( $return ) ) {

				$return = wp_remote_get( 'http://baconmockup.com/images-api/image-tags/' );


				if ( is_wp_error( $return ) ) {
					return false;
				}
				else {
					$return = json_decode( wp_remote_retrieve_body( $return ) );
				}

				if ( ! empty( $return ) && ! empty( $return->data ) ) {
					$return = $return->data;
					set_site_transient( 'baconmockup-tags', $return, DAY_IN_SECONDS * 1 );
				}

			}
			return $return;
		}

	}
}
