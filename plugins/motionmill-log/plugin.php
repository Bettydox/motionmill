<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists('MM_Log') )
{
    class MM_Log extends MM_Plugin
    {
        protected $file    = null;

        public function __construct()
        {
            parent::__construct(array
            (
                'helpers' => array('array', 'wordpress')
            ));
        
            $this->file = plugin_dir_path(__FILE__) . 'log.txt';

            if ( ! file_exists($this->file) )
            {
                trigger_error( sprintf('Log file %s does not exist.', $this->file), E_USER_WARNING );
            }

            if ( ! is_readable($this->file) || ! is_writeable($this->file) )
            {
                trigger_error( sprintf('Log file %s must be readable and writable.', $this->file), E_USER_WARNING );
            }
        }

        public function initialize()
        {
            add_action( 'motionmill_admin_menu', array(&$this, 'on_admin_menu') );
            add_action( 'admin_enqueue_scripts', array(&$this, 'on_admin_enqueue_scripts') );
        }

        public function add_message($text, $type = 'notice', $group = 'general')
        {
            if ( ! is_writable($this->file) )
                return false;

            // creates message
            $message = array
            (
                    'date'  => date('Y-m-d'),
                    'time'  => date('H:i:s'),
                    'text'  => $text,
                    'type'  => $type,
                    'group' => $group
            );

            // writes message to file
            if ( $fh = fopen( $this->file, 'a') )
            {
                // message format = [date][time][group][type] text
                $written = fwrite($fh, sprintf( "[%s][%s][%s][%s] %s\n",
                        $message['date'], $message['time'], $message['group'], $message['type'], $message['text'] ) );

                fclose($fh);

                return $written !== false;
            }

            return false;
        }

        protected function get_messages()
        {
            // gets all messages from file
            $messages = array();

            if ( is_readable($this->file) )
            {
               if ( $fh = fopen($this->file, 'rb') )
                {
                    $i = -1;

                    while ( ($line = fgets($fh) ) !== false)
                    {
                        $i++;

                        $messages[] = $this->create_message($line, $i);
                    }

                    fclose($fh);
                }
            }

            return $messages;
        }

        protected function create_message($line, $num = -1)
        {
            preg_match('/^\[(.*?)\]\[(.*?)\]\[(.*?)\]\[(.*?)\]\s(.*?)$/', $line, $matches);

            $message = array
            (
                'date'    => $matches[1],
                'time'    => $matches[2],
                'group'   => $matches[3],
                'type'    => $matches[4],
                'text'    => $matches[5],
                'raw'     => trim($line),
                'num'     => $num
            );

            return $message;
        }

        protected function delete_messages($group = 'general')
        {
            if ( ! is_readable($this->file) )
                return;

             // gets messages that don't need to be deleted
             $out = array();

             foreach( file($this->file)  as $line )
             {
                 $message = $this->create_message($line);

                 if ( $message['group'] == $group )
                    continue;

                $out[] = $line;
             }

             if ( count($out) == 0  )
                return;

             // erases file and writes messages
             $fh = @fopen( $this->file, 'w+' );
            
             flock($fh, LOCK_EX);

             foreach( $out as $line )
             {
                 fwrite($fh, $line);
             }

             flock($fh, LOCK_UN);

             fclose($fh);
        }

        public function on_admin_enqueue_scripts()
        {
            $screen = get_current_screen();

            if ( $screen->id != 'motionmill_page_motionmill_log' )
                    return;

            wp_enqueue_style( 'mm-log', plugins_url( 'css/style.css', __FILE__ ), null, '1.0.0', 'all' );
        }

        public function on_admin_menu()
        {
            add_submenu_page( $this->mm->menu_page, __('Motionmill Log', MM_TEXTDOMAIN), __('Log', MM_TEXTDOMAIN), 'manage_options', 'motionmill_log', array(&$this, 'on_print_menu_page') );
        }

        public function on_print_menu_page()
        {
            $group_messages = array();

            // gets all groups
            $groups = mm_get_element_values( 'group', $this->get_messages() );

            // finds current group
            $current_group = null;

            // group is user defined
            if ( ! empty($_GET['group']) )
            {
                // checks if group exists
                if ( in_array($_GET['group'], $groups) )
                {
                    $current_group = $_GET['group'];

                    // gets group messages
                    $group_messages = mm_get_elements_by( "group={$current_group}", $this->get_messages() );
                }
            }
            else
            {
                $current_group = '';
                $group_messages = $this->get_messages();
            }

            $messages = array();

            if ( $current_group !== null )
            {
                    $types = mm_get_element_values( 'type', $group_messages );

                    $current_type = null;

                    if ( ! empty($_GET['type']) )
                    {
                        // checks if group exists
                        if ( in_array($_GET['type'], $types) )
                        {
                                $current_type = $_GET['type'];

                                $messages = mm_get_elements_by( "type={$current_type}", $group_messages );
                        }
                    }
                    else
                    {
                        $current_type = '';
                        $messages = $group_messages;
                    }
            }

            // filter
            $current_date = null;

            if ( isset($_GET['date']) )
            {
                    $current_date = $_GET['date'];
            }
            elseif ( count($messages) > 0 )
            {
                $current_date = $messages[ count($messages)-1 ]['date'];
            }

            if ( $current_date  )
            {
                $messages = mm_get_elements_by( "date=$current_date", $messages );
            }

            ?>

            <div class="wrap" id="mm-log">

                    <!-- heading -->
                    <?php screen_icon('options-general'); ?>
                    <h2><?php _e('Motionmill Log'); ?></h2>

                    <?php if ( ! file_exists($this->file) ) : ?>
                    <p><?php echo mm_error_message( sprintf( __('The log file <code>%s</code> does not exist.', MM_TEXTDOMAIN), basename(WP_PLUGIN_DIR) . '/' . plugin_basename($this->file) ) ) ; ?></p>
                    <?php elseif ( ! is_readable($this->file) || ! is_writable($this->file) ) : ?>
                    <p><?php echo mm_error_message( sprintf( __('The log file <code>%s</code> must be readable and writable.', MM_TEXTDOMAIN), basename(WP_PLUGIN_DIR) . '/' . plugin_basename($this->file) ) ); ?></p>
                    <?php endif; ?>
                        
                    <?php if ( count($groups) > 0 ) : ?>
                    <h2 class="nav-tab-wrapper">
                        <a href="?page=motionmill_log&group=" class="nav-tab<?php echo $current_group === '' ? ' nav-tab-active' : ''; ?>"><?php _e('All', MM_TEXTDOMAIN); ?></a>
                        <?php foreach ( $groups as $group ) : ?>
                        <a href="?page=motionmill_log&group=<?php echo $group; ?>" class="nav-tab<?php echo $current_group && $group == $current_group ? ' nav-tab-active' : ''; ?>"><?php echo esc_html($group); ?></a>
                        <?php endforeach; ?>
                    </h2>
                    <?php endif; ?>

                    <?php if ( $current_group !== null ): ?>

                    <?php if ( count($types) > 0 ) : ?>
                    <ul class="subsubsub">
                            <li><a href="?page=motionmill_log&group=<?php echo $current_group; ?>&type=" class="<?php echo $current_type === '' ? 'current' : ''; ?>"><?php _e('All', MM_TEXTDOMAIN); ?></a> (<?php echo count($group_messages); ?>) | </li>
                            <?php $i=0; foreach ( $types as $type ) : ?>
                            <li><a href="?page=motionmill_log&group=<?php echo $current_group; ?>&type=<?php echo $type; ?>" class="<?php echo $current_type && $type == $current_type ? 'current' : ''; ?>"><?php echo $type; ?></a> (<?php echo count( mm_get_elements_by( "type={$type}", $group_messages ) ); ?>) <?php if ($i < count($types) - 1) : ?>|<?php endif; ?></li>
                            <?php $i++; endforeach; ?>
                    </ul><br class="clear" />
                    <?php endif; ?>

                    <?php if ( count($groups) > 0 ) : ?>
                    <h2>
                            <?php echo esc_html( $current_group ? ucfirst($current_group) : __('All', MM_TEXTDOMAIN) ); ?>
                            <?php if ( $current_type ) : ?>
                            (<?php echo esc_html( ucfirst($current_type) ); ?>)
                            <?php endif; ?>
                    </h2>
                    <?php endif; ?>

                    <?php if ( $current_type !== null ) : ?>

                    <?php if ( count($messages) == 0 ) : ?>
                    <p><?php _e('No messages found.', MM_TEXTDOMAIN); ?></p>
                    <?php else : ?>

                    <form class="mm-log-filter" action="admin.php" method="GET">

                            <input type="hidden" name="page" value="motionmill_log" />
                            <input type="hidden" name="group" value="<?php echo esc_attr($current_group); ?>" />
                            <input type="hidden" name="type" value="<?php echo esc_attr($current_type); ?>" />

                            <label for="date"><?php _e('Date', MM_TEXTDOMAIN); ?></label>
                            <select name="date">
                                    <option value=""<?php selected('', $current_date); ?>><?php _e('All'); ?></option>
                                    <?php foreach ( mm_get_element_values('date', $messages) as $date ) : ?>
                                    <option value="<?php echo esc_attr($date); ?>"<?php selected($date, $current_date); ?>><?php echo esc_html( strftime('%d/%m/%Y', strtotime($date) ) ); ?></option>
                                    <?php endforeach; ?>
                            </select>

                            <?php submit_button( __('Filter'), 'secondary', 'submit', false ); ?>

                    </form>

                    <ul class="mm-log-messages">
                        <?php foreach ( $messages as $message ) : ?>
                            <li class="mm-log-message mm-log-type-<?php echo esc_attr($message['type']); ?>">
                                    
                                    [
                                    <span class="mm-log-date"><?php echo esc_html( strftime('%d/%m/%Y', strtotime($message['date']) ) ); ?></span>
                                    <span class="mm-log-time"><?php echo esc_html($message['time']); ?></span>
                                    <?php if ( ! $current_group ) : ?>
                                    | <span class="mm-log-group"><?php _e('group: ', MM_TEXTDOMAIN); ?><?php echo esc_html($message['group']); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! $current_type ) : ?>
                                    | <span class="mm-log-type"><?php _e('type: ', MM_TEXTDOMAIN); ?><?php echo esc_html($message['type']); ?></span>
                                    <?php endif; ?>
                                    ]
                                    <span class="mm-log-text"><?php echo esc_html($message['text']); ?></span>
                            </li>
                            <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <?php endif; ?>

                    <?php endif; ?>

            </div><!-- .wrap -->

            <?php
        }
    }

    function motionmill_log_register($plugins)
    {
        $plugins[] = 'MM_Log';

        return $plugins;
    }

    add_action( 'motionmill_plugins', 'motionmill_log_register', 1 );
}

?>