<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NF_FU_Integrations_NinjaForms_Attachments {

	/**
	 * NF_FU_Integrations_NinjaForms_Attachments constructor.
	 */
	public function __construct() {
		add_filter( 'ninja_forms_action_email_settings', array( $this, 'email_settings' ) );
		add_filter( 'ninja_forms_action_email_attachments', array( $this, 'attach_files' ), 10, 3 );
	}

	/**
	 * Allow uploads to be attached to email actions
	 *
	 * @param array $settings
	 *
	 * @return array $settings
	 */
	public function email_settings( $settings ) {
		$form_id = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );

		if ( empty ( $form_id ) ) {
			return $settings;
		}

		if ( ! $this->form_has_file_uploads( $form_id ) ) {
			return $settings;
		}

		$settings['field_list_fu_email_attachments'] = array(
			'name'        => 'field_list_fu_email_attachments',
			'type'        => 'field-list',
			'label'       => __( 'Attach File Uploads', 'ninja-forms-uploads' ),
			'width'       => 'full',
			'group'       => 'advanced',
			'field_types' => array( NF_FU_File_Uploads::TYPE ),
			'settings'    => array(
				array(
					'name'  => 'toggle',
					'type'  => 'toggle',
					'label' => __( 'Field', 'ninja-forms-uploads' ),
					'width' => 'full',
				),
			),
		);

		return $settings;
	}

	/**
	 * Has the form got file upload fields
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	protected function form_has_file_uploads( $form_id ) {
		foreach ( Ninja_Forms()->form( $form_id )->get_fields() as $field_id => $field ) {
			if ( ! is_object( $field ) || ! method_exists( $field, 'get_settings' ) ) {
				continue;
			}

			$get_settings = $field->get_settings();
			if ( NF_FU_File_Uploads::TYPE == $get_settings['type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Attach file uploads to the email
	 *
	 * @param array $attachments
	 * @param array $data
	 * @param array $settings
	 *
	 * @return array
	 */
	public function attach_files( $attachments, $data, $settings ) {
		// Merge stored action settings if attachment toggles are missing (email retrigger scenario)
		$settings = $this->maybe_merge_stored_attachment_settings( $settings, $data );

		foreach ( $settings as $key => $value ) {
			if ( false === strpos( $key, 'field_list_fu_email_attachments-' ) || 1 != $value ) {
				continue;
			}

			if ( ! isset( $data['fields'] ) ) {
				continue;
			}

			$field_key = str_replace( 'field_list_fu_email_attachments-', '', $key );

			foreach ( $data['fields'] as $field ) {
				if ( $field_key != $field['key'] ) {
					continue;
				}

				$files = $this->get_files_for_attachment( $field );

				foreach ( $files as $file ) {
					if ( ! empty( $file['data']['file_path'] ) && file_exists( $file['data']['file_path'] ) ) {
						$attachments[] = $file['data']['file_path'];
					}
				}
			}
		}

		return $attachments;
	}

	/**
	 * Merge stored attachment settings if missing from passed settings
	 *
	 * During email retrigger from the Submissions page, the frontend only sends
	 * a subset of action settings. The file upload attachment toggle settings
	 * (field_list_fu_email_attachments-*) are not included. This method fetches
	 * the complete action settings from the stored form to get these toggles.
	 *
	 * @param array $settings The settings passed to the email action
	 * @param array $data     The form data including form_id
	 *
	 * @return array Settings merged with stored attachment toggles
	 */
	protected function maybe_merge_stored_attachment_settings( $settings, $data ) {
		// Check if attachment settings already exist
		foreach ( $settings as $key => $value ) {
			if ( false !== strpos( $key, 'field_list_fu_email_attachments-' ) ) {
				return $settings; // Already has attachment settings
			}
		}

		// No attachment settings found - try to fetch from stored form
		if ( ! isset( $data['form_id'] ) ) {
			return $settings;
		}

		$form_id = $data['form_id'];
		$actions = Ninja_Forms()->form( $form_id )->get_actions();

		foreach ( $actions as $action ) {
			$action_settings = $action->get_settings();

			// Match by action type and label/order to find the right email action
			if ( ! isset( $action_settings['type'] ) || 'email' !== $action_settings['type'] ) {
				continue;
			}

			// Try to match by label or other identifying info
			$is_match = false;
			if ( isset( $settings['label'] ) && isset( $action_settings['label'] ) ) {
				$is_match = ( $settings['label'] === $action_settings['label'] );
			}
			if ( ! $is_match && isset( $settings['order'] ) && isset( $action_settings['order'] ) ) {
				$is_match = ( $settings['order'] === $action_settings['order'] );
			}
			if ( ! $is_match && isset( $settings['to'] ) && isset( $action_settings['to'] ) ) {
				$is_match = ( $settings['to'] === $action_settings['to'] );
			}

			if ( ! $is_match ) {
				continue;
			}

			// Found matching action - merge attachment settings
			foreach ( $action_settings as $key => $value ) {
				if ( false !== strpos( $key, 'field_list_fu_email_attachments-' ) ) {
					$settings[ $key ] = $value;
				}
			}

			break;
		}

		return $settings;
	}

	/**
	 * Get files array for attachment, reconstructing from field value if necessary
	 *
	 * During email retrigger from the Submissions page, the $field['files'] array
	 * is not populated. This method reconstructs it from $field['value'] which
	 * contains the upload_id => file_url mapping stored in the database.
	 *
	 * @param array $field The field data
	 *
	 * @return array The files array with file_path data
	 */
	protected function get_files_for_attachment( $field ) {
		// If files array exists and has data, use it directly
		if ( isset( $field['files'] ) && ! empty( $field['files'] ) ) {
			return $field['files'];
		}

		// Get the field value, handling serialized data
		$value = $field['value'] ?? null;

		// Unserialize if needed (value from database may be serialized)
		// Use allowed_classes => false to prevent object instantiation for security
		if ( is_string( $value ) && ! empty( $value ) && is_serialized( $value ) ) {
			$unserialized = unserialize( $value, array( 'allowed_classes' => false ) );
			if ( is_array( $unserialized ) ) {
				$value = $unserialized;
			}
		}

		// Reconstruct files array from field value (email retrigger scenario)
		// Field value from database is array(upload_id => file_url)
		if ( ! is_array( $value ) || empty( $value ) ) {
			return array();
		}

		$files = array();
		foreach ( $value as $upload_id => $file_url ) {
			// Fetch full upload record to get file_path
			$upload = NF_File_Uploads()->controllers->uploads->get( $upload_id );

			if ( false === $upload ) {
				continue;
			}

			$files[] = array(
				'data' => array(
					'upload_id' => $upload_id,
					'file_path' => $upload->file_path,
					'file_url'  => $upload->file_url,
				),
			);
		}

		return $files;
	}
}