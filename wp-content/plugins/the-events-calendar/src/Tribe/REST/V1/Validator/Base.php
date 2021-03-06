<?php


class Tribe__Events__REST__V1__Validator__Base
	extends Tribe__Events__Validator__Base
	implements Tribe__Events__REST__V1__Validator__Interface {

	public function is_venue_id_or_entry( $venue ) {
		if ( ! is_array( $venue ) ) {
			return tribe_is_venue( $venue );
		}

		if ( ! empty( $venue['id'] ) ) {
			return tribe_is_venue( $venue['id'] );
		}

		$request = new WP_REST_Request();
		/** @var Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $venue_endpoint */
		$venue_endpoint = tribe( 'tec.rest-v1.endpoints.single-venue' );

		$request->set_attributes( array( 'args' => $venue_endpoint->CREATE_args() ) );
		foreach ( $venue as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$has_valid_params = $request->has_valid_params();

		return true === $has_valid_params ? true : false;
	}

	public function is_organizer_id_or_entry( $organizer ) {
		if ( ! is_array( $organizer ) ) {
			$organizers = preg_split( '/\\s*,\\s*/', $organizer );
			$numeric = array_filter( $organizers, 'is_numeric' );
			$filtered = array_filter( $numeric, 'tribe_is_organizer' );

			return count( $filtered ) === count( $organizers );
		}

		$organizers = (array) $organizer;
		foreach ( $organizers as $entry ) {
			if ( $this->is_numeric( $entry ) ) {
				if ( ! tribe_is_organizer( $entry ) ) {
					return false;
				}
				continue;
			}

			if ( ! empty( $entry['id'] ) ) {
				if ( tribe_is_organizer( $entry['id'] ) ) {
					continue;
				}

				return false;
			}

			$is_associative_array = is_array( $entry ) && ( array_values( $entry ) !== $entry );
			if ( ! $is_associative_array ) {
				return false;
			}

			$request = new WP_REST_Request();
			/** @var Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface $organizer_endpoint */
			$organizer_endpoint = tribe( 'tec.rest-v1.endpoints.single-organizer' );

			$request->set_attributes( array( 'args' => $organizer_endpoint->CREATE_args() ) );
			foreach ( $entry as $key => $value ) {
				$request->set_param( $key, $value );
			}

			$has_valid_params = $request->has_valid_params();

			if ( true !== $has_valid_params ) {
				return false;
			}
		}

		return true;
	}
}