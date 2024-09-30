<?php
/**
 * All settings related functions
 */
namespace WPPlugines\Restrict_Reviews\App;
use WPPlugines\Restrict_Reviews\Helper;
use Codexpert\Plugin\Base;
use Codexpert\Plugin\Settings as Settings_API;

/**
 * @package Plugin
 * @subpackage Settings
 * @author Codexpert <hi@codexpert.io>
 */
class Settings extends Base {

	public $plugin;

	/**
	 * Constructor function
	 */
	public function __construct( $plugin ) {
		$this->plugin	= $plugin;
		$this->slug		= $this->plugin['TextDomain'];
		$this->name		= $this->plugin['Name'];
		$this->version	= $this->plugin['Version'];
	}
	
	public function init_menu() {
		
		$site_config = [
			'PHP Version'				=> PHP_VERSION,
			'WordPress Version' 		=> get_bloginfo( 'version' ),
			'WooCommerce Version'		=> is_plugin_active( 'woocommerce/woocommerce.php' ) ? get_option( 'woocommerce_version' ) : 'Not Active',
			'Memory Limit'				=> defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT ? WP_MEMORY_LIMIT : 'Not Defined',
			'Debug Mode'				=> defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled',
			'Active Plugins'			=> get_option( 'active_plugins' ),
		];

		$settings = [
			'id'            => $this->slug,
			'label'         => $this->name,
			'title'         => "{$this->name} v{$this->version}",
			'header'        => $this->name,
			// 'parent'     => 'woocommerce',
			// 'priority'   => 10,
			// 'capability' => 'manage_options',
			// 'icon'       => 'dashicons-wordpress',
			// 'position'   => 25,
			// 'topnav'	=> true,
			'sections'      => [
				'plugin-client_basic'	=> [
					'id'        => 'plugin-client_basic',
					'label'     => __( 'Basic Settings', 'plugin-client' ),
					'icon'      => 'dashicons-admin-tools',
					// 'color'		=> '#4c3f93',
					'sticky'	=> false,
					'fields'    => [
						'sample_text' => [
							'id'        => 'sample_text',
							'label'     => __( 'Text Field', 'plugin-client' ),
							'type'      => 'text',
							'desc'      => __( 'This is a text field.', 'plugin-client' ),
							// 'class'     => '',
							'default'   => 'Hello World!',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_number' => [
							'id'      => 'sample_number',
							'label'     => __( 'Number Field', 'plugin-client' ),
							'type'      => 'number',
							'desc'      => __( 'This is a number field.', 'plugin-client' ),
							// 'class'     => '',
							'default'   => 10,
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_email' => [
							'id'      => 'sample_email',
							'label'     => __( 'Email Field', 'plugin-client' ),
							'type'      => 'email',
							'desc'      => __( 'This is an email field.', 'plugin-client' ),
							// 'class'     => '',
							'default'   => 'john@doe.com',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_url' => [
							'id'      => 'sample_url',
							'label'     => __( 'URL Field', 'plugin-client' ),
							'type'      => 'url',
							'desc'      => __( 'This is a url field.', 'plugin-client' ),
							// 'class'     => '',
							'default'   => 'https://johndoe.com',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_password' => [
							'id'      => 'sample_password',
							'label'     => __( 'Password Field', 'plugin-client' ),
							'type'      => 'password',
							'desc'      => __( 'This is a password field.', 'plugin-client' ),
							// 'class'     => '',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
							'default'   => 'uj34h'
						],
						'sample_textarea' => [
							'id'      => 'sample_textarea',
							'label'     => __( 'Textarea Field', 'plugin-client' ),
							'type'      => 'textarea',
							'desc'      => __( 'This is a textarea field.', 'plugin-client' ),
							// 'class'     => '',
							'columns'   => 24,
							'rows'      => 5,
							'default'   => 'lorem ipsum dolor sit amet',
							'readonly'  => false, // true|false
							'disabled'  => false, // true|false
						],
						'sample_radio' => [
							'id'      => 'sample_radio',
							'label'     => __( 'Radio Field', 'plugin-client' ),
							'type'      => 'radio',
							'desc'      => __( 'This is a radio field.', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'item_1'  => 'Item One',
								'item_2'  => 'Item Two',
								'item_3'  => 'Item Three',
							],
							'default'   => 'item_2',
							'disabled'  => false, // true|false
						],
						'sample_select' => [
							'id'      => 'sample_select',
							'label'     => __( 'Select Field', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'This is a select field.', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => 'option_2',
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
						],
						'sample_multiselect' => [
							'id'      => 'sample_multiselect',
							'label'     => __( 'Multi-select Field', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'This is a multiselect field.', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_checkbox' => [
							'id'      => 'sample_checkbox',
							'label'     => __( 'Checkbox Field', 'plugin-client' ),
							'type'      => 'checkbox',
							'desc'      => __( 'This is a checkbox field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'on'
						],
						'sample_multicheck' => [
							'id'      => 'sample_multicheck',
							'label'     => __( 'Multi-check Field', 'plugin-client' ),
							'type'      => 'checkbox',
							'desc'      => __( 'This is a multi-check field.', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_switch' => [
							'id'      => 'sample_switch',
							'label'     => __( 'Switch Field', 'plugin-client' ),
							'type'      => 'switch',
							'desc'      => __( 'This is a switch field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'on'
						],
						'sample_multiswitch' => [
							'id'      => 'sample_multiswitch',
							'label'     => __( 'Multi-switch Field', 'plugin-client' ),
							'type'      => 'switch',
							'desc'      => __( 'This is a multi-switch field.', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
						],
						'sample_range' => [
							'id'      => 'sample_range',
							'label'     => __( 'Range Field', 'plugin-client' ),
							'type'      => 'range',
							'desc'      => __( 'This is a range field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'min'		=> 0,
							'max'		=> 16,
							'step'		=> 2,
							'default'   => 4,
						],
						'sample_date' => [
							'id'      => 'sample_date',
							'label'     => __( 'Date Field', 'plugin-client' ),
							'type'      => 'date',
							'desc'      => __( 'This is a date field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => '1971-12-16',
						],
						'sample_time' => [
							'id'      => 'sample_time',
							'label'     => __( 'Time Field', 'plugin-client' ),
							'type'      => 'time',
							'desc'      => __( 'This is a time field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => '15:45',
						],
						'sample_color' => [
							'id'      => 'sample_color',
							'label'     => __( 'Color Field', 'plugin-client' ),
							'type'      => 'color',
							'desc'      => __( 'This is a color field.', 'plugin-client' ),
							// 'class'     => '',
							// 'default'   => '#f0f'
						],
						'sample_wysiwyg' => [
							'id'      => 'sample_wysiwyg',
							'label'     => __( 'WYSIWYG Field', 'plugin-client' ),
							'type'      => 'wysiwyg',
							'desc'      => __( 'This is a wysiwyg field.', 'plugin-client' ),
							// 'class'     => '',
							'width'     => '100%',
							'rows'      => 5,
							'teeny'     => true,
							'text_mode'     => false, // true|false
							'media_buttons' => false, // true|false
							'default'       => 'Hello World'
						],
						'sample_file' => [
							'id'      => 'sample_file',
							'label'     => __( 'File Field' ),
							'type'      => 'file',
							'upload_button'     => __( 'Choose File', 'plugin-client' ),
							'select_button'     => __( 'Select File', 'plugin-client' ),
							'desc'      => __( 'This is a file field.', 'plugin-client' ),
							// 'class'     => '',
							'disabled'  => false, // true|false
							'default'   => 'http://example.com/sample/file.txt'
						],
					]
				],
				'plugin-client_advanced'	=> [
					'id'        => 'plugin-client_advanced',
					'label'     => __( 'Advanced Settings', 'plugin-client' ),
					'icon'      => 'dashicons-admin-generic',
					// 'color'		=> '#d30c5c',
					'sticky'	=> false,
					'fields'    => [
						'sample_select3' => [
							'id'      => 'sample_select3',
							'label'     => __( 'Select with Chosen', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Chosen plugin enabled. <a href="https://harvesthq.github.io/chosen/" target="_blank">[See more]</a>', 'plugin-client' ),
							// 'class'     => '',
							'options'   => Helper::get_posts( [ 'post_type' => 'page' ], false, true ),
							'default'   => 2,
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
							'chosen'    => true
						],
						'sample_multiselect3' => [
							'id'      => 'sample_multiselect3',
							'label'     => __( 'Multi-select with Chosen', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Chosen plugin enabled. <a href="https://harvesthq.github.io/chosen/" target="_blank">[See more]</a>', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
							'chosen'    => true
						],
						'sample_select2' => [
							'id'      => 'sample_select2',
							'label'     => __( 'Select with Select2', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Select2 plugin enabled. <a href="https://select2.org/" target="_blank">[See more]</a>', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => 'option_2',
							'disabled'  => false, // true|false
							'multiple'  => false, // true|false
							'select2'   => true
						],
						'sample_multiselect2' => [
							'id'      => 'sample_multiselect2',
							'label'     => __( 'Multi-select with Select2', 'plugin-client' ),
							'type'      => 'select',
							'desc'      => __( 'jQuery Select2 plugin enabled. <a href="https://select2.org/" target="_blank">[See more]</a>', 'plugin-client' ),
							// 'class'     => '',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'default'   => [ 'option_2', 'option_3' ],
							'disabled'  => false, // true|false
							'multiple'  => true, // true|false
							'select2'   => true
						],
						'sample_group' => [
							'id'      => 'sample_group',
							'label'     => __( 'Field Group' ),
							'type'      => 'group',
							'desc'      => __( 'A group of fields.', 'plugin-client' ),
							'items'     => [
								'sample_group_select1' => [
									'id'      => 'sample_group_select1',
									'label'     => __( 'First Item', 'plugin-client' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_2',
								],
								'sample_group_select2' => [
									'id'      => 'sample_group_select2',
									'label'     => __( 'Second Item', 'plugin-client' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_1',
								],
								'sample_group_select3' => [
									'id'      => 'sample_group_select3',
									'label'     => __( 'Third Item', 'plugin-client' ),
									'type'      => 'select',
									'options'   => [
										'option_1'  => 'Option One',
										'option_2'  => 'Option Two',
										'option_3'  => 'Option Three',
									],
									'default'   => 'option_3',
								],
							],
						],
						'sample_conditional' => [
							'id'      => 'sample_conditional',
							'label'     => __( 'Conditional Field', 'plugin-client' ),
							'type'      => 'select',
							'options'   => [
								'option_1'  => 'Option One',
								'option_2'  => 'Option Two',
								'option_3'  => 'Option Three',
							],
							'desc'      => __( 'Shows up if the third option in the  \'Field Group\' above is set as \'Option Two\'', 'plugin-client' ),
							'default'   => 'option_2',
							'condition'	=> [
								'key'		=> 'sample_group_select3',
								'value'		=> 'option_2',
								'compare'	=> '==',
							]
						],
						'sample_repeater'	=> [
							'id'		=> 'sample_repeater',
							'label'		=> __( 'Sample Repeater' ),
							'type'		=> 'repeater',
							'items'		=> [
								'text_repeat' => [
									'id'		=> 'text_repeat',
									'label'		=> __( 'Repeat Text Field', 'plugin-client' ),
									'type'		=> 'text',
									'placeholder'=> __( 'Repeat Text', 'plugin-client' ),
									'desc'		=> __( 'This field will be repeated.', 'plugin-client' ),
								],
								'number_repeat' => [
									'id'		=> 'number_repeat',
									'label'		=> __( 'Repeat Number Field', 'plugin-client' ),
									'type'		=> 'number',
									'placeholder'=> __( 'Repeat Number', 'plugin-client' ),
									'desc'		=> __( 'This field will be repeated.', 'plugin-client' ),
								],
							]
						],
						'sample_tabs' => [
							'id'      => 'sample_tabs',
							'label'     => __( 'Sample Tabs' ),
							'type'      => 'tabs',
							'items'     => [
								'sample_tab1' => [
									'id'      => 'sample_tab1',
									'label'     => __( 'First Tab', 'plugin-client' ),
									'fields'    => [
										'sample_tab1_email' => [
											'id'      => 'sample_tab1_email',
											'label'     => __( 'Tab Email Field', 'plugin-client' ),
											'type'      => 'email',
											'desc'      => __( 'This is an email field.', 'plugin-client' ),
											// 'class'     => '',
											'default'   => 'john@doe.com',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
										'sample_tab1_url' => [
											'id'      => 'sample_tab1_url',
											'label'     => __( 'Tab URL Field', 'plugin-client' ),
											'type'      => 'url',
											'desc'      => __( 'This is a url field.', 'plugin-client' ),
											// 'class'     => '',
											'default'   => 'https://johndoe.com',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
									],
								],
								'sample_tab2' => [
									'id'      => 'sample_tab2',
									'label'     => __( 'Second Tab', 'plugin-client' ),
									'fields'    => [
										'sample_tab2_text' => [
											'id'        => 'sample_tab2_text',
											'label'     => __( 'Tab Text Field', 'plugin-client' ),
											'type'      => 'text',
											'desc'      => __( 'This is a text field.', 'plugin-client' ),
											// 'class'     => '',
											'default'   => 'Hello World!',
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
										'sample_tab2_number' => [
											'id'      => 'sample_tab2_number',
											'label'     => __( 'Tab Number Field', 'plugin-client' ),
											'type'      => 'number',
											'desc'      => __( 'This is a number field.', 'plugin-client' ),
											// 'class'     => '',
											'default'   => 10,
											'readonly'  => false, // true|false
											'disabled'  => false, // true|false
										],
									],
								],
							],
						],
					]
				],
				'plugin-client_tools'	=> [
					'id'        => 'plugin-client_tools',
					'label'     => __( 'Tools', 'plugin-client' ),
					'icon'      => 'dashicons-hammer',
					'sticky'	=> false,
					'fields'    => [
						'enable_debug' => [
							'id'      	=> 'enable_debug',
							'label'     => __( 'Enable Debug', 'plugin-client' ),
							'type'      => 'switch',
							'desc'      => __( 'Enable this if you face any CSS or JS related issues.', 'plugin-client' ),
							'disabled'  => false,
						],
						'report' => [
							'id'      => 'report',
							'label'     => __( 'Report', 'plugin-client' ),
							'type'      => 'textarea',
							'desc'     	=> '<button id="plugin-client_report-copy" class="button button-primary"><span class="dashicons dashicons-admin-page"></span></button>',
							'columns'   => 24,
							'rows'      => 10,
							'default'   => json_encode( $site_config, JSON_PRETTY_PRINT ),
							'readonly'  => true,
						],
					]
				],
				'plugin-client_table' => [
					'id'        => 'plugin-client_table',
					'label'     => __( 'Table', 'plugin-client' ),
					'icon'      => 'dashicons-editor-table',
					// 'color'		=> '#28c9ee',
					'hide_form'	=> true,
					'template'  => WPPRR_DIR . '/views/settings/table.php',
				],
			],
		];

		new Settings_API( $settings );
	}
}