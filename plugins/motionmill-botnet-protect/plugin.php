<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Botnet_Protect') )
{
	class MM_Botnet_Protect extends MM_Plugin
	{
		public function __construct()
		{
			parent::__construct(array
            (
                'helpers' => array()
            ));
		}

		public function initialize()
		{			
			register_deactivation_hook( MM_FILE, array(&$this, 'on_motionmill_deactivate') );

			add_filter( 'motionmill_settings_sections', array(&$this, 'on_settings_sections') );
			add_filter( 'motionmill_settings_enqueue_scripts', array(&$this, 'on_settings_enqueue_scripts') );
			
			add_action( 'init', array( $this, 'set_cookie' ) );
			add_action( 'wp_ajax_mm_botnet_protect_write_to_htaccess', array(&$this, 'write_to_htaccess_ajax') );
			add_action( 'wp_ajax_mm_botnet_protect_remove_from_htaccess', array(&$this, 'remove_from_htaccess_ajax') );
		
			add_action( 'motionmill_deactivate', array(&$this, 'on_deactivate') );
		}

		public function on_settings_sections($sections)
		{
			$sections[] = array
			(
			  'name'          => 'botnet-protect',
			  'title'         => __('Botnet protect', MM_TEXTDOMAIN),
			  'description'   => array(&$this, 'on_settings_section_content'),
			  'submit_button' => false,
			  'parent' 		  => ''
			);

			return $sections;
		}

		public function get_htaccess_code()
		{
			return '# BEGIN Botnet Protect
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{HTTP_COOKIE} !mm_enigma_prime=d966373717658638de006bb7d2958b33 [NC]
RewriteRule wp-login.php - [R=404]
</IfModule>
# END Botnet Protect';
		}

		public function on_settings_section_content()
		{
			?>

			<p><?php _e('Protects against Botnet attack directly targeting wp-login.php.', MM_TEXTDOMAIN); ?></p>

			<p><?php _e('These are the mod_rewrite rules you should have at the beginning of your <code>.htaccess</code> file.', MM_TEXTDOMAIN); ?></p>

			<p><textarea class="large-text code readonly" readonly="readonly" rows="10"><?php echo esc_html( $this->get_htaccess_code() ); ?></textarea></p>

			<p class="hide-if-no-js">
				<input type="button" id="mm-botnet-protect-add-htaccess-button" class="button" name="" value="<?php printf( __('Add to %s', MM_TEXTDOMAIN), 'htaccess'); ?>" />
				<input type="button" id="mm-botnet-protect-remove-htaccess-button" class="button" name="" value="<?php printf( __('Remove from %s', MM_TEXTDOMAIN), 'htaccess'); ?>" />
				<div id="mm-botnet-protect-output"></div>
			</p>

			<?php
		}

		public function set_cookie()
		{
			setcookie( 'mm_enigma_prime', 'd966373717658638de006bb7d2958b33', strtotime('+1 day'), '/' );
		}

		public function write_to_htaccess()
		{
			$file = ABSPATH . '.htaccess';
			$message = '';

			// checks if file exists
			if ( file_exists($file) )
			{
				$contents = @file_get_contents($file);

				// checks if file is readable
				if ( $contents !== false )
				{
					// checks if code is already written
					if ( stripos($contents, '# BEGIN Botnet Protect') === false )
					{
						$contents = $this->get_htaccess_code() . "\n" . $contents;

						if ( @file_put_contents($file, $contents) )
						{
							$message = __('Data successfully written.', MM_TEXTDOMAIN);
						}

						else
						{
							$message = __('Unable to write to file.', MM_TEXTDOMAIN);
						}
					}
					else
					{
						$message = __('Data already written.', MM_TEXTDOMAIN);
					}
				}
				else
				{
					$message = __( 'Cannot read file.', MM_TEXTDOMAIN );
				}
			}
			else
			{
				$message = __('File does not exist.', MM_TEXTDOMAIN);
			}

			return $message;
		}

		public function remove_from_htaccess()
		{
			$file = ABSPATH . '.htaccess';
			$message = '';

			// checks if file exists
			if ( file_exists($file) )
			{
				$contents = @file_get_contents($file);

				// checks if file is readable
				if ( $contents !== false )
				{
					// checks if code is already written
					if ( stripos($contents, '# BEGIN Botnet Protect') === false )
					{
						$message = __('No Data found.', MM_TEXTDOMAIN);
					}
					else
					{
						$start = stripos($contents, '# BEGIN Botnet Protect');
						$end   = stripos($contents, '# END Botnet Protect');

						$contents = substr_replace($contents, '', $start, $end + strlen('# END Botnet Protect') );

						if ( @file_put_contents($file, $contents) )
						{
							$message = __('Data successfully removed.', MM_TEXTDOMAIN);
						}

						else
						{
							$message = __('Data could not be removed.', MM_TEXTDOMAIN);
						}
					}
				}
				else
				{
					$message = __( 'Cannot read file.', MM_TEXTDOMAIN );
				}
			}
			else
			{
				$message = __('File does not exist.', MM_TEXTDOMAIN);
			}

			return $message;
		}

		public function write_to_htaccess_ajax()
		{
			echo json_encode(array
			(
				'message' => $this->write_to_htaccess()
			));

			die();
		}

		public function remove_from_htaccess_ajax()
		{
			echo json_encode(array
			(
				'message' => $this->remove_from_htaccess()
			));

			die();
		}

		public function on_plugin_deactivate()
		{
			// removes code from htaccess
			$this->remove_from_htaccess();

			// removes cookie
			setcookie( 'mm_enigma_prime', 'd966373717658638de006bb7d2958b33', strtotime('-1 day'), '/' );
		}

		public function on_settings_enqueue_scripts($section)
		{
			if ( $section != 'botnet-protect' )
				return;

			wp_enqueue_script( 'mm-botnet-protect-scripts', plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.0.0', false );
		}

		public function on_motionmill_deactivate()
		{
			$this->remove_from_htaccess();
		}
	}

	function mm_botnet_protect_register($plugins)
	{
		$plugins[] = 'MM_Botnet_Protect';

		return $plugins;
	}

	add_action( 'motionmill_plugins', 'mm_botnet_protect_register', 5 );

}

?>