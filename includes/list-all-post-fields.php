<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML allowed in the field markup this add-on adds to the front end.
 *
 * @return array
 */
function buddyforms_hook_fields_allowed_html() {
	$allowed = wp_kses_allowed_html( 'post' );

	$allowed['source'] = array(
		'src'  => true,
		'type' => true,
	);
	$allowed['select'] = array(
		'id'    => true,
		'class' => true,
		'name'  => true,
	);
	$allowed['option'] = array(
		'value'    => true,
		'class'    => true,
		'id'       => true,
		'selected' => true,
	);

	return $allowed;
}

/**
 * Escape a field value (it can hold HTML built by BuddyForms, or be an array) for the front end.
 *
 * @param mixed $value Field value.
 *
 * @return string
 */
function buddyforms_hook_fields_escape_value( $value ) {
	if ( is_array( $value ) ) {
		$value = implode( ',', array_map( 'strval', array_filter( $value, 'is_scalar' ) ) );
	}

	return wp_kses( (string) $value, buddyforms_hook_fields_allowed_html() );
}

/**
 * Split an attachment mime type into its type and extension.
 *
 * @param int $attachment_id Attachment ID.
 *
 * @return array Type and extension, empty strings when unknown.
 */
function buddyforms_hook_fields_attachment_type( $attachment_id ) {
	$parts = explode( '/', (string) get_post_mime_type( $attachment_id ) );

	return array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
}

/**
 * Define who the form data will be show in a single post view.
 *
 * @param $content
 *
 * @return string
 */
function buddyforms_list_all_post_fields( $content ) {
	global $buddyforms, $post;

	if ( ! is_single() ) {
		return $content;
	}

	$form_slug = get_post_meta( $post->ID, '_bf_form_slug', true );

	if ( ! $form_slug ) {
		return $content;
	}
	if ( isset( $buddyforms[ $form_slug ]['hook_fields_show_edit_link'] ) ) {

		$edit_url = get_edit_post_link( $post->ID );

		if ( $edit_url ) {
			$edit_link = '<p><a class="post-edit-link" href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit', 'buddyforms-hook-fields' ) . ' <span class="screen-reader-text">' . esc_html( $post->post_title ) . '</span></a></p>';

			$content = $edit_link . $content;
		}
	}

	if ( empty( $buddyforms ) || empty( $buddyforms[ $form_slug ] ) ) {
		return $content;
	}

	$add_table_content        = ( ! empty( $buddyforms[ $form_slug ]['hook_fields_list_on_single'] ) ) ? $buddyforms[ $form_slug ]['hook_fields_list_on_single'] : '';
	$post_template_id         = ( ! empty( $buddyforms[ $form_slug ]['hook_fields_template_page'] ) ) ? (int) $buddyforms[ $form_slug ]['hook_fields_template_page'] : 'none';
	$is_post_template_enabled = ( ! empty( $post_template_id ) && $post_template_id !== 'none' );

	$hide_title = ( ! empty( $buddyforms[ $form_slug ]['hook_fields_hide_title'] ) ) ? $buddyforms[ $form_slug ]['hook_fields_hide_title'] : '';
	if ( ! isset( $buddyforms[ $form_slug ]['hook_fields_list_on_single'] ) ) {
		return $content;
	}

	wp_enqueue_style( 'bf-hook-fields', plugins_url( 'assets/bf-hook-fields.css', __FILE__ ), array(), BUDDYFORMS_HOOK_FIELDS_VERSION );

	remove_filter( 'the_content', 'buddyforms_list_all_post_fields', 999 );

	$exist_title_in_post = buddyforms_exist_field_type_in_form( $form_slug, 'title' );
	if ( ! empty( $hide_title ) && $exist_title_in_post && ! did_action( 'buddyforms_hook_fields_hide_title' ) ) {
		wp_add_inline_style( 'bf-hook-fields', '.entry-header{display: none;}' );
		do_action( 'buddyforms_hook_fields_hide_title' );
	}
	if ( $is_post_template_enabled ) {
		if ( class_exists( 'Elementor\Plugin' ) ) {
			$template_content = Elementor\Plugin::instance()->frontend->get_builder_content( $post_template_id, true );
		}

		if ( empty( $template_content ) ) {
			$template_content = get_the_content( null, false, $post_template_id );
			$template_content = apply_filters( 'the_content', $template_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter, renders the template like post content.
			$template_content = str_replace( ']]>', ']]&gt;', $template_content );
		}
		$template_content = buddyforms_get_field_value_from_string( $template_content, $post->ID, $form_slug, true );
		if ( ! empty( $template_content ) ) {
			$content = $template_content;
		}
	} elseif ( $add_table_content ) {

		$striped_c   = 0;
		$new_content = '<table rules="all" class="bf-hook-field-container" cellpadding="10">';
		if ( isset( $buddyforms[ $form_slug ]['form_fields'] ) ) {
			foreach ( $buddyforms[ $form_slug ]['form_fields'] as $key => $field ) {

				if ( in_array( $field['slug'], buddyforms_get_exclude_field_slugs() ) || $field['slug'] == 'buddyforms_form_content' || $field['slug'] == 'buddyforms_form_title' || $field['slug'] == 'featured_image' || $field['type'] == 'hidden' ) {
					continue;
				}

				$field = buddyforms_get_field_with_meta( $form_slug, $post->ID, $field['slug'] );

				$field_value = ! empty( $field['value'] ) ? $field['value'] : apply_filters( 'buddyforms_field_shortcode_empty_value', '', $field, $form_slug, $post->ID, $field['slug'] );

				$striped = ( $striped_c ++ % 2 == 1 ) ? "style='background: #eee;'" : '';

				if ( isset( $field['slug'] ) ) {
					if ( $field['type'] === 'upload' || $field['type'] === 'file' ) {
						$upload_field_val = get_post_meta( $post->ID, $field['slug'], true );
						$media_items      = explode( ',', $upload_field_val );
						$result           = '';
						foreach ( $media_items as $attachment_item ) {
							if ( ! empty( $attachment_item ) ) {
								$attachment_full_url      = wp_get_attachment_url( $attachment_item );
								list( $file_type, $file_type_extension ) = buddyforms_hook_fields_attachment_type( $attachment_item );
								$default_thumbnail        = plugin_dir_url( __FILE__ ) . 'assets/images/multimedia.png';
								$attachment_thumbnail_url = wp_get_attachment_thumb_url( $attachment_item ) === false ? $default_thumbnail : wp_get_attachment_thumb_url( $attachment_item );

								$result .= "<a class='image-placeholder' href='" . esc_url( $attachment_full_url ) . "' target='_blank'>";

								switch ( $file_type ) {

									case 'video':
										$result .= "<video width='150' height='150' controls> <source src='" . esc_url( $attachment_full_url ) . "' >  </video>";
										break;
									case 'image':
										$result .= "<img src='" . esc_url( $attachment_thumbnail_url ) . "' />";
										break;
									case 'audio':
										$result .= "<audio controls> <source src='" . esc_url( $attachment_full_url ) . "' ></audio>";
										break;

									default:
									case 'application':
										if ( $file_type_extension == 'pdf' ) {
											$pdf_thumbnail = plugin_dir_url( __FILE__ ) . 'assets/images/pdf.png';
											$result       .= "<img src='" . esc_url( $pdf_thumbnail ) . "' />";
										} elseif ( $file_type_extension == 'zip' || $file_type_extension == 'x-gzip' || $file_type_extension == 'rar' || $file_type_extension == 'x-7z-compressed' ) {
											$compressed_thumbnail = plugin_dir_url( __FILE__ ) . 'assets/images/compressed.png';
											$result              .= "<img src='" . esc_url( $compressed_thumbnail ) . "' />";

										} else {
											$result .= "<img src='" . esc_url( $default_thumbnail ) . "' />";
										}

										break;
								}

								$result .= '</a>';

							}
						}
						$new_content .= '<tr ' . $striped . '><td><strong>' . esc_html( $field['name'] ) . '</strong> </td><td><div>' . trim( $result ) . '</div></td></tr>';
					} else {
						$new_content .= '<tr ' . $striped . '><td><strong>' . esc_html( $field['name'] ) . '</strong> </td><td>' . buddyforms_hook_fields_escape_value( $field_value ) . '</td></tr>';
					}
				}
			}
		}

		// Table end
		$new_content .= '</table>';
		$content     .= wp_kses( $new_content, buddyforms_hook_fields_allowed_html() );
	}

	add_filter( 'the_content', 'buddyforms_list_all_post_fields', 999, 1 );

	// Let us return the form elements table
	return $content;
}

add_filter( 'the_content', 'buddyforms_list_all_post_fields', 999, 1 );

/**
 * Define how to display each field
 */
function buddyforms_form_display_element_frontend() {
	global $buddyforms, $post, $buddyforms_hook_fields_hooked;

	if ( is_admin() ) {
		return;
	}

	if ( ! isset( $post->ID ) ) {
		return;
	}

	if ( $buddyforms_hook_fields_hooked ) {
		return;
	}

	$form_slug = get_post_meta( $post->ID, '_bf_form_slug', true );

	if ( ! isset( $form_slug ) ) {
		return;
	}

	if ( ! isset( $buddyforms[ $form_slug ] ) ) {
		return;
	}

	if ( ! isset( $buddyforms[ $form_slug ]['form_fields'] ) ) {
		return;
	}

	wp_enqueue_style( 'bf-hook-fields', plugins_url( 'assets/bf-hook-fields.css', __FILE__ ), array(), BUDDYFORMS_HOOK_FIELDS_VERSION );

	$before_the_title   = false;
	$after_the_title    = false;
	$before_the_content = false;
	$after_the_content  = false;
	$allowed            = buddyforms_hook_fields_allowed_html();
	foreach ( $buddyforms[ $form_slug ]['form_fields'] as $field_id => $customfield ) {

		if( isset( $customfield['slug'] ) ) {

			if ( ! empty( $customfield['slug'] ) && ( ( isset( $customfield['hook'] ) && ! empty( $customfield['hook'] ) ) || is_single() ) ) {

				$field             = buddyforms_get_field_with_meta( $form_slug, $post->ID, $customfield['slug'] );
				$customfield_value = ! empty( $field['value'] ) ? $field['value'] : apply_filters( 'buddyforms_field_shortcode_empty_value', '', $field, $form_slug, $post->ID, $field['slug'] );
	
				if ( ! empty( $customfield_value ) ) {
					$post_meta_tmp = '<div class="post_meta bf-hook-field ' . esc_attr( $customfield['slug'] ) . '">';
	
					if ( isset( $customfield['display_name'] ) ) {
						$post_meta_tmp .= '<p style="width:100%;">' . esc_html( $customfield['name'] ) . ':</p>';
					}
	
					if ( $field['type'] === 'upload' || $field['type'] === 'file' ) {
						$upload_field_val = get_post_meta( $post->ID, $field['slug'], true );
						$media_items      = explode( ',', $upload_field_val );
						$result           = array();
						$thumbnail_size   = 'thumbnail';
	
						if ( isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['thumbnail_size'] ) ) {
							$thumbnail_size = $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['thumbnail_size'];
						}
	
						foreach ( $media_items as $attachment_item ) {
							if ( ! empty( $attachment_item ) ) {
								$attachment_full_url      = wp_get_attachment_url( $attachment_item );
								$attachment_thumbnail_url = wp_get_attachment_image_src( $attachment_item, $thumbnail_size );
								list( $file_type, $file_type_extension ) = buddyforms_hook_fields_attachment_type( $attachment_item );
	
								$registered_sizes = wp_get_registered_image_subsizes();
								$media_size_width = isset( $registered_sizes[ $thumbnail_size ]['width'] ) ? absint( $registered_sizes[ $thumbnail_size ]['width'] ) : 150;
	
								$default_img  = plugin_dir_url( __FILE__ ) . 'assets/images/multimedia.png';
								$media_output = "<a href='" . esc_url( $attachment_full_url ) . "' target='_blank'>";
	
								switch ( $file_type ) {
	
									case 'video':
										$media_output .= "<video width='" . $media_size_width . "' controls> <source src='" . esc_url( $attachment_full_url ) . "' ></video>";
										break;
	
									case 'image':
										$media_output .= "<img src='" . esc_url( $attachment_thumbnail_url ? $attachment_thumbnail_url[0] : $default_img ) . "' />";
										break;
									case 'audio':
										$media_style   = "style='width: " . $media_size_width . "px;'";
										$media_output .= "<span class='image-placeholder' " . $media_style . '>';
										$media_output .= "<audio  controls style='width:80%'> <source src='" . esc_url( $attachment_full_url ) . "' ></audio>";
										$media_output .= '<p>' . esc_html( basename( (string) get_attached_file( $attachment_item ) ) ) . '</p>';
										$media_output .= '</span>';
	
										break;
	
									default:
									case 'application':
										$media_style   = "style='width: " . $media_size_width . "px;'";
										$media_output .= "<span class='image-placeholder' " . $media_style . '>';
	
										if ( $file_type_extension === 'pdf' ) {
											$pdf_thumbnail = plugin_dir_url( __FILE__ ) . 'assets/images/pdf.png';
											$media_output .= "<img src='" . esc_url( $pdf_thumbnail ) . "' />";
										} elseif ( $file_type_extension === 'zip' || $file_type_extension == 'x-gzip' || $file_type_extension == 'rar' || $file_type_extension == 'x-7z-compressed' ) {
											$compressed_thumbnail = plugin_dir_url( __FILE__ ) . 'assets/images/compressed.png';
											$media_output        .= "<img src='" . esc_url( $compressed_thumbnail ) . "' />";
										} else {
											$media_output .= "<img src='" . esc_url( $default_img ) . "' />";
										}
	
										$media_output .= '<p>' . esc_html( basename( (string) get_attached_file( $attachment_item ) ) ) . '</p>';
										$media_output .= '</span>';
	
										break;
								}
	
								$media_output .= '</a>';
								$result[]      = $media_output;
							}
						}
	
						$meta_tmp = implode( '', $result );
	
					} else {
						$meta_tmp = '<p>' . buddyforms_hook_fields_escape_value( $customfield_value ) . '</p>';
					}
	
					if ( $meta_tmp ) {
						$post_meta_tmp .= apply_filters( 'buddyforms_form_element_display_frontend', $meta_tmp, $customfield );
					}
	
					$post_meta_tmp .= '</div>';
	
					$post_meta_tmp = apply_filters( 'buddyforms_form_element_display_frontend_before_hook', $post_meta_tmp );
	
					if ( isset( $customfield['hook'] ) && ! empty( $customfield['hook'] ) ) {
						add_action(
							$customfield['hook'],
							function () use ( $post_meta_tmp, $allowed ) {
								echo wp_kses( $post_meta_tmp, $allowed );
							}
						);
					}
	
					if ( is_single() && isset( $customfield['display'] ) ) {
						switch ( $customfield['display'] ) {
							case 'before_the_title':
								$before_the_title .= $post_meta_tmp;
								break;
							case 'after_the_title':
								$after_the_title .= $post_meta_tmp;
								break;
							case 'before_the_content':
								$before_the_content .= $post_meta_tmp;
								break;
							case 'after_the_content':
								$after_the_content .= $post_meta_tmp;
								break;
						}
					}
				}
			}
		}

	}

	if ( is_single() ) {

		if ( $before_the_title ) {
			add_filter(
				'the_title',
				function ( $content, $id ) use ( $before_the_title, $allowed ) {
					if ( is_single() && $id == get_the_ID() ) {
						return wp_kses( $before_the_title, $allowed ) . $content;
					}

					return $content;
				},
				9999,
				2
			);
		}

		if ( $after_the_title ) {
			add_filter(
				'the_title',
				function ( $content, $id ) use ( $after_the_title, $allowed ) {
					if ( is_single() && $id == get_the_ID() ) {
						return $content . wp_kses( $after_the_title, $allowed );
					}

					return $content;
				},
				9999,
				2
			);
		}

		if ( $before_the_content ) {
			add_filter(
				'the_content',
				function ( $content ) use ( $before_the_content, $allowed ) {
					return wp_kses( $before_the_content, $allowed ) . $content;
				},
				9999
			);
		}

		if ( $after_the_content ) {

			add_filter(
				'the_content',
				function ( $content ) use ( $after_the_content, $allowed ) {
					return $content . wp_kses( $after_the_content, $allowed );
				},
				9999
			);
		}
	}
	$buddyforms_hook_fields_hooked = true;

}

add_action( 'the_post', 'buddyforms_form_display_element_frontend' );

add_shortcode( 'bfsinglefield', 'buddyforms_hook_fields_single_field' );

/**
 * Render the value of one form field of the current post ([bfsinglefield form-slug="" field-slug=""]).
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function buddyforms_hook_fields_single_field( $atts ) {
	global $buddyforms, $post;

	if ( ! isset( $atts['form-slug'], $atts['field-slug'] ) ) {
		return '';
	}
	if ( ! is_object( $post ) ) {
		return '';
	}

	$form_slug  = (string) $atts['form-slug'];
	$field_slug = (string) $atts['field-slug'];

	if ( empty( $buddyforms[ $form_slug ]['form_fields'] ) ) {
		return '';
	}
	if ( get_post_meta( $post->ID, '_bf_form_slug', true ) !== $form_slug ) {
		return '';
	}

	$selected_field = '';
	foreach ( $buddyforms[ $form_slug ]['form_fields'] as $field ) {
		if ( isset( $field['slug'] ) && $field['slug'] === $field_slug ) {
			$selected_field = $field['slug'];
			break;
		}
	}

	if ( empty( get_post_type() ) ) {
		/* translators: %s: field slug */
		return '<p>' . sprintf( esc_html__( 'Field %s value.', 'buddyforms-hook-fields' ), '<b>"' . esc_html( $field_slug ) . '"</b>' ) . '</p>';
	}

	// Reading the field values renders the post content, which runs this shortcode again when the
	// post content contains it. Return nothing on that nested call instead of recursing forever.
	static $rendering = false;
	if ( $rendering ) {
		return '';
	}

	$rendering  = true;
	$field_data = buddyforms_get_field_with_meta( $form_slug, $post->ID, $selected_field, false, true );
	$rendering  = false;
	if ( ! isset( $field_data['value'] ) ) {
		return '<b>' . esc_html__( 'Sorry, this field was not found in the selected form.', 'buddyforms-hook-fields' ) . '</b>';
	}

	return '<p>' . buddyforms_hook_fields_escape_value( $field_data['value'] ) . '</p>';
}
