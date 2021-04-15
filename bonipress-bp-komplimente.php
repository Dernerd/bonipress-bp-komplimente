<?php
/**
 * Plugin Name: BoniPress für BP Komplimente
 * Description: Vergebe oder ziehe Punkte von Benutzern in BoniPress ab, die Komplimente über das BP Komplimente-Plugin senden.
 * Version: 1.1.2
 * Tags: points, tokens, credit, management, reward, charge, buddpress, buddypress-komplimente
 * Author: DerN3rd
 * Author URI: https://n3rds.work
 * Requires at least: WP 4.8
 * Tested up to: WP 5.6.1
 * Text Domain: bonipress_bp_komplimente
 * Domain Path: /lang
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require 'psource-plugin-update/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=bonipress-bp-komplimente', 
	__FILE__, 
	'bonipress-bp-komplimente' 
);

if ( ! class_exists( 'boniPRESS_BP_Komplimente' ) ) :
	final class boniPRESS_BP_Komplimente {

		// Plugin Version
		public $version             = '1.1.2';

		// Instnace
		protected static $_instance = NULL;

		// Current session
		public $session             = NULL;

		public $slug                = '';
		public $domain              = '';
		public $plugin              = NULL;
		public $plugin_name         = '';

		/**
		 * Setup Instance
		 * @since 1.0
		 * @version 1.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __clone() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '1.0' ); }

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '1.0' ); }

		/**
		 * Define
		 * @since 1.0
		 * @version 1.0
		 */
		private function define( $name, $value, $definable = true ) {
			if ( ! defined( $name ) )
				define( $name, $value );
		}

		/**
		 * Require File
		 * @since 1.0
		 * @version 1.0
		 */
		public function file( $required_file ) {
			if ( file_exists( $required_file ) )
				require_once $required_file;
		}

		/**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
		public function __construct() {

			$this->slug        = 'bonipress-buddypress-komplimente';
			$this->plugin      = plugin_basename( __FILE__ );
			$this->domain      = 'bonipress_bp_komplimente';
			$this->plugin_name = 'BoniPress für BP Komplimente';

			$this->define_constants();

			add_filter( 'bonipress_setup_hooks',    array( $this, 'register_hook' ) );
			add_action( 'bonipress_init',           array( $this, 'load_textdomain' ) );
			add_action( 'bonipress_all_references', array( $this, 'add_badge_support' ) );
			add_action( 'bonipress_load_hooks',    'bonipress_bp_komplimente_load_hook' );

		}

		/**
		 * Define Constants
		 * @since 1.0
		 * @version 1.0
		 */
		public function define_constants() {

			$this->define( 'BONIPRESS_BP_KOMPLIMENTE_VER',  $this->version );
			$this->define( 'BONIPRESS_BP_KOMPLIMENTE_SLUG', $this->slug );
			$this->define( 'BONIPRESS_DEFAULT_TYPE_KEY',    'bonipress_default' );

		}

		/**
		 * Includes
		 * @since 1.0
		 * @version 1.0
		 */
		public function includes() { }

		/**
		 * Load Textdomain
		 * @since 1.0
		 * @version 1.0
		 */
		public function load_textdomain() {

			// Load Translation
			$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );

			load_textdomain( $this->domain, WP_LANG_DIR . '/' . $this->slug . '/' . $this->domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $this->domain, false, dirname( $this->plugin ) . '/lang/' );

		}

		/**
		 * Register Hook
		 * @since 1.0
		 * @version 1.0
		 */
		public function register_hook( $installed ) {

			if ( ! function_exists( 'bp_komplimente_init' ) ) return $installed;

			$installed['bp-komplimente'] = array(
				'title'       => __( 'BP Komplimente', $this->domain ),
				'description' => __( 'Verleiht Benutzern %_plural% für das Senden oder Empfangen von Komplimenten.', $this->domain ),
				'callback'    => array( 'boniPRESS_Hook_BP_Komplimente' )
			);

			return $installed;

		}

		/**
		 * Add Badge Support
		 * @since 1.0
		 * @version 1.0
		 */
		public function add_badge_support( $references ) {

			if ( ! function_exists( 'bp_komplimente_init' ) ) return $references;

			$references['giving_compliment']    = __( 'Ein Kompliment machen (BP Komplimente)', $this->domain );
			$references['receiving_compliment'] = __( 'Ein Kompliment erhalten (BP Komplimente)', $this->domain );

			return $references;

		}

	}
endif;

function bonipress_bp_komplimente_plugin() {
	return boniPRESS_BP_Komplimente::instance();
}
bonipress_bp_komplimente_plugin();

/**
 * Load BP Komplimente Hook
 * Finally we need to load the hook class. It is recommended you use the bonipress_pre_init
 * action hook because then the class will only load if boniPRESS is installed and will load
 * in the correct moment. I do recommend you still check if class exists boniPRESS_Hook in case
 * someone really nuts on customizing boniPRESS on your website. 
 * @since 1.0
 * @version 1.0.1
 */
if ( ! function_exists( 'bonipress_bp_komplimente_load_hook' ) ) :
	function bonipress_bp_komplimente_load_hook() {

		if ( class_exists( 'boniPRESS_Hook_BP_Komplimente' ) || ! function_exists( 'bp_komplimente_init' ) ) return;

		class boniPRESS_Hook_BP_Komplimente extends boniPRESS_Hook {

			/**
			 * Construct
			 */
			function __construct( $hook_prefs, $type = BONIPRESS_DEFAULT_TYPE_KEY ) {

				parent::__construct( array(
					'id'       => 'bp-komplimente',
					'defaults' => array(
						'giving'    => array(
							'creds'     => 0,
							'log'       => '%plural% für ein Kompliment',
							'limit'     => '0/x'
						),
						'receiving' => array(
							'creds'     => 0,
							'log'       => '%plural% für den Erhalt von Komplimenten',
							'limit'     => '0/x'
						)
					)
				), $hook_prefs, $type );

			}

			/**
			 * Run
			 * This class method is fired of by boniPRESS when it's time to load all hooks.
			 * It should be used to "hook" into the plugin we want to add support for or the
			 * appropriate WordPress instances. Anything that must be loaded for this hook to work
			 * needs to be called here.
			 * @since 1.0
			 * @version 1.0
			 */
			public function run() {

				add_action( 'bp_komplimente_after_save', array( $this, 'new_compliment' ) );

			}

			/**
			 * New Compliment
			 * Not sure but looking at the BP Komplimente plugin, this seems to be the
			 * best place to detect new komplimente being given. We get an object to play with that contains
			 * the senders and receives user IDs, which we need. Otherwise, how will we know who to give points to?
			 * @since 1.0
			 * @version 1.0
			 */
			public function new_compliment( $bp_komplimente_object ) {

				// Can not award guests
				if ( ! is_user_logged_in() ) return;

				// We start with the person giving the kompliment
				if ( $this->prefs['giving']['creds'] != 0 && ! $this->core->exclude_user( $bp_komplimente_object->sender_id ) ) {

					// If we are not over the hook limit, award points
					if ( ! $this->over_hook_limit( 'giving', 'giving_compliment', $bp_komplimente_object->sender_id ) )
						$this->core->add_creds(
							'giving_compliment',
							$bp_komplimente_object->sender_id,
							$this->prefs['giving']['creds'],
							$this->prefs['giving']['log'],
							$bp_komplimente_object->receiver_id,
							array( 'ref_type' => 'user' ),
							$this->bonipress_type
						);

				}

				// We then finish with the person reciving it
				if ( $this->prefs['receiving']['creds'] != 0 && ! $this->core->exclude_user( $bp_komplimente_object->receiver_id ) ) {

					// If we are not over the hook limit, award points
					if ( ! $this->over_hook_limit( 'receiving', 'receiving_compliment', $bp_komplimente_object->receiver_id ) )
						$this->core->add_creds(
							'receiving_compliment',
							$bp_komplimente_object->receiver_id,
							$this->prefs['receiving']['creds'],
							$this->prefs['receiving']['log'],
							$bp_komplimente_object->sender_id,
							array( 'ref_type' => 'user' ),
							$this->bonipress_type
						);

				}

			}

			/**
			 * Preferences
			 * If the hook has settings, it has to be added in using this class method.
			 * @since 1.0
			 * @version 1.0
			 */
			public function preferences() {

				$prefs = $this->prefs;

?>
<label class="subheader"><?php _e( 'Ein Kompliment machen', 'bonipress_bp_komplimente' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'giving' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'giving' => 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['giving']['creds'] ); ?>" size="8" /></div>
	</li>
	<li>
		<label for="<?php echo $this->field_id( array( 'giving' => 'limit' ) ); ?>"><?php _e( 'Limit', 'bonipress_bp_komplimente' ); ?></label>
		<?php echo $this->hook_limit_setting( $this->field_name( array( 'giving' => 'limit' ) ), $this->field_id( array( 'giving' => 'limit' ) ), $prefs['giving']['limit'] ); ?>
	</li>
</ol>
<label class="subheader"><?php _e( 'Protokollvorlage', 'bonipress_bp_komplimente' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'giving' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'giving' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['giving']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'user' ) ); ?></span>
	</li>
</ol>
<label class="subheader"><?php _e( 'Ein Kompliment erhalten', 'bonipress_bp_komplimente' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'receiving' => 'creds' ) ); ?>" id="<?php echo $this->field_id( array( 'receiving' => 'creds' ) ); ?>" value="<?php echo $this->core->number( $prefs['receiving']['creds'] ); ?>" size="8" /></div>
	</li>
	<li>
		<label for="<?php echo $this->field_id( array( 'receiving' => 'limit' ) ); ?>"><?php _e( 'Limit', 'bonipress_bp_komplimente' ); ?></label>
		<?php echo $this->hook_limit_setting( $this->field_name( array( 'receiving' => 'limit' ) ), $this->field_id( array( 'receiving' => 'limit' ) ), $prefs['receiving']['limit'] ); ?>
	</li>
</ol>
<label class="subheader"><?php _e( 'Protokollvorlage', 'bonipress_bp_komplimente' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'receiving' => 'log' ) ); ?>" id="<?php echo $this->field_id( array( 'receiving' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['receiving']['log'] ); ?>" class="long" /></div>
		<span class="description"><?php echo $this->available_template_tags( array( 'general', 'user' ) ); ?></span>
	</li>
</ol>
<?php

			}

			/**
			 * Sanitise Preferences
			 * While boniPRESS does some basic sanitization of the data you submit in the settings,
			 * we do need to handle our hook limits since 1.6. If your settings contain a checkbox (or multiple)
			 * then you should also use this method to handle the submission making sure the checkbox values are
			 * taken care of.
			 * @since 1.0
			 * @version 1.0
			 */
			function sanitise_preferences( $data ) {

				if ( isset( $data['giving']['limit'] ) && isset( $data['giving']['limit_by'] ) ) {
					$limit = sanitize_text_field( $data['giving']['limit'] );
					if ( $limit == '' ) $limit = 0;
					$data['giving']['limit'] = $limit . '/' . $data['giving']['limit_by'];
					unset( $data['giving']['limit_by'] );
				}

				if ( isset( $data['receiving']['limit'] ) && isset( $data['receiving']['limit_by'] ) ) {
					$limit = sanitize_text_field( $data['receiving']['limit'] );
					if ( $limit == '' ) $limit = 0;
					$data['receiving']['limit'] = $limit . '/' . $data['receiving']['limit_by'];
					unset( $data['receiving']['limit_by'] );
				}

				return $data;

			}

		}

	}
endif;
