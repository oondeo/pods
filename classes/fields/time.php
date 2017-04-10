<?php
require_once( PODS_DIR . 'classes/fields/datetime.php' );
/**
 * @package Pods\Fields
 */
class PodsField_Time extends PodsField_DateTime {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Date / Time';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'time';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Time';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%s';

	/**
	 * Default format.
	 *
	 * @var string
	 * @since 2.7
	 */
	public $default_format = 'H:i:s';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {
	    self::$label = __( 'Time', 'pods' );
    }

    /**
     * Add options and set defaults to
     *
     * @return array
     * @since 2.0
     */
    public function options () {
        $options = array(
            self::$type . '_repeatable' => array(
                'label' => __( 'Repeatable Field', 'pods' ),
                'default' => 0,
                'type' => 'boolean',
                'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
                'boolean_yes_label' => '',
                'dependency' => true,
                'developer_mode' => true
            ),
            self::$type . '_type' => array(
                'label' => __( 'Time Format Type', 'pods' ),
                'default' => '12', // Backwards compatibility
                'type' => 'pick',
                'data' => array(
	                'wp' => __( 'WordPress default', 'pods' ) . ': ' . date_i18n( get_option( 'time_format' ) ),
	                'custom' => __( 'Custom', 'pods' ),
                    '12' => __( '12 hour', 'pods' ),
                    '24' => __( '24 hour', 'pods' ),
                ),
                'dependency' => true
            ),
            self::$type . '_format_custom' => array(
	            'label' => __( 'Custom time format', 'pods' ),
	            'depends-on' => array( self::$type . '_type' => 'custom' ),
	            'default' => '',
	            'type' => 'text',
	            'help' => '<a href="http://php.net/manual/function.date.php" target="_blank">' . __( 'PHP date documentation', 'pods' ) . '</a>',
            ),
            self::$type . '_format' => array(
                'label' => __( 'Time Format', 'pods' ),
                'depends-on' => array( self::$type . '_type' => '12' ),
                'default' => 'h_mma',
                'type' => 'pick',
                'data' => array(
                    'h_mm_A' => date_i18n( 'g:i A' ),
                    'h_mm_ss_A' => date_i18n( 'g:i:s A' ),
                    'hh_mm_A' => date_i18n( 'h:i A' ),
                    'hh_mm_ss_A' => date_i18n( 'h:i:s A' ),
                    'h_mma' => date_i18n( 'g:ia' ),
                    'hh_mma' => date_i18n( 'h:ia' ),
                    'h_mm' => date_i18n( 'g:i' ),
                    'h_mm_ss' => date_i18n( 'g:i:s' ),
                    'hh_mm' => date_i18n( 'h:i' ),
                    'hh_mm_ss' => date_i18n( 'h:i:s' )
                ),
                'dependency' => true
            ),
            self::$type . '_format_24' => array(
                'label' => __( 'Time Format', 'pods' ),
                'depends-on' => array( self::$type . '_type' => '24' ),
                'default' => 'hh_mm',
                'type' => 'pick',
                'data' => array(
                    'hh_mm' => date_i18n( 'H:i' ),
                    'hh_mm_ss' => date_i18n( 'H:i:s' )
                )
            ),
            self::$type . '_allow_empty' => array(
                'label' => __( 'Allow empty value?', 'pods' ),
                'default' => 1,
                'type' => 'boolean'
            ),
            self::$type . '_html5' => array(
                'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
                'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
                'type' => 'boolean'
            )
        );

		$options[ self::$type . '_type' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_type_default', $options[ self::$type . '_type' ][ 'default' ] );
		$options[ self::$type . '_format' ][ 'data' ] = apply_filters( 'pods_form_ui_field_time_format_options', $options[ self::$type . '_format' ][ 'data' ] );
		$options[ self::$type . '_format' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_default', $options[ self::$type . '_format' ][ 'default' ] );
		$options[ self::$type . '_format_24' ][ 'data' ] = apply_filters( 'pods_form_ui_field_time_format_24_options', $options[ self::$type . '_format_24' ][ 'data' ] );
		$options[ self::$type . '_format_24' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_24_default', $options[ self::$type . '_format_24' ][ 'default' ] );

        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $schema = 'TIME NOT NULL default "00:00:00"';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null|string
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00' ) ) ) {
            $date = $this->createFromFormat( 'H:i:s', (string) $value );
            $date_local = $this->createFromFormat( $format, (string) $value );

            if ( false !== $date )
                $value = $date->format( $format );
            elseif ( false !== $date_local )
                $value = $date_local->format( $format );
            else
                $value = date_i18n( $format, strtotime( (string) $value ) );
        }
        elseif ( 0 == pods_var( self::$type . '_allow_empty', $options, 1 ) )
            $value = date_i18n( $format );
        else
            $value = '';

        return $value;
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        // Format Value
        $value = $this->display( $value, $name, $options, null, $pod, $id );

        $field_type = 'time';

        if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_var( 'read_only', $options, false ) ) {
                $options[ 'readonly' ] = true;

                $field_type = 'text';
            }
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
            $options[ 'readonly' ] = true;

            $field_type = 'text';
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @return mixed|string
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && ( 0 == pods_var( self::$type . '_allow_empty', $options, 1 ) || !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00' ) ) ) )
            $value = $this->convert_date( $value, 'H:i:s', $format );
        elseif ( 1 == pods_var( self::$type . '_allow_empty', $options, 1 ) )
            $value = '00:00:00';
        else
            $value = date_i18n( 'H:i:s' );

        return $value;
    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @return mixed|null|string
     * @since 2.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->display( $value, $name, $options, $pod, $id );

        if ( 1 == pods_var( self::$type . '_allow_empty', $options, 1 ) && ( empty( $value ) || in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00' ) ) ) )
            $value = false;

        return $value;
    }

    /**
     * Build date/time format string based on options
     *
     * @param array $options
     * @param bool  $js       Return format for jQuery UI?
     *
     * @return string
     * @since 2.0
     */
    public function format ( $options, $js = false ) {
	    return $this->format_time( $options, $js );
    }

	/**
	 * Build time format string based on options
	 *
	 * @since  2.7
	 *
	 * @param  array $options
	 * @param  bool  $js       Return format for jQuery UI?
	 * @return string
	 */
	public function format_time( $options, $js = false ) {

		switch ( pods_var( static::$type . '_type', $options ) ) {
			case 12:
				$time_format = $this->get_time_formats( $options, $js );
				$format = $time_format[ pods_v( static::$type . '_format', $options, 'hh_mm', true ) ];
			break;
			case 24:
				$time_format_24 = $this->get_time_formats_24( $options, $js );
				$format = $time_format_24[ pods_v( static::$type . '_format_24', $options, 'hh_mm', true ) ];
			break;
			case 'custom':
				$format = pods_v( static::$type . '_format_custom', $options, '' );
				if ( $js ) {
					$format = static::format_php_to_jqueryui( $format );
				}
			break;
			default:
				$format = get_option( 'time_format' );
				if ( $js ) {
					$format = static::format_php_to_jqueryui( $format );
				}
			break;
		}

		return $format;
	}
}
