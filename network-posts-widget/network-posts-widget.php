<?php
/*
 * Plugin Name: Network posts widget
 * Plugin URI: http://tigerton.se
 * Description: Creates a custom widget which displays the latest posts in your network
 * Author: Jonathan de Jong
 * Text Domain:       network_posts_widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Version: 0.1
 * Author URI: http://jonathandejong.se
 * TODO: 
 	* Setup better exclude sites option
 	* Setup number of posts option
 	* More markup abilities (featured image, title or not, meta etc)
*/


define( 'NPW_VERSION', '0.1' );
define( 'NPW_PLUGIN_FILE', __FILE__ );
if ( ! defined( 'NPW_PLUGIN_DIR' ) )
	define( 'NPW_PLUGIN_DIR', __DIR__ . '/' );


//Init our plugin
add_action( 'widgets_init', 'register_network_posts_widget' );

function register_network_posts_widget() {
	//Register the widget
    register_widget( 'Network_Posts_Widget' );

	if ( !class_exists( 'WP_Query_Multisite' ) ) {
		/* Include the WP_Global_query class
		*  https://github.com/ericandrewlewis/WP_Query_Multisite
		*/
		require_once ( NPW_PLUGIN_DIR . 'global-wp-query.class.php' );
	}
}

class Network_Posts_Widget extends WP_Widget {
 
    public function __construct() {
        parent::__construct(
            'network_posts_widget', // Base ID
            'Network Posts Widget', // Name
            array(
                'description' => __( 'Display List of posts from Network. Currently very basic', 'network_posts_widget' )
            ) // Args
        );
    }
 
    public function form( $instance ) {
    
    	$excluded_sites = (isset($instance['excluded_sites']) ? $instance['excluded_sites'] : '');
    	$title = (isset($instance['title']) ? strip_tags($instance['title']) : '');
    	$excerpt = (isset($instance['show_excerpt']) ? $instance['show_excerpt'] : false);
    	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
    	<p>
            <label for="<?php echo $this->get_field_id('excluded_sites'); ?>"><?php _e('Exclude sites (IDs separated by ,)', 'network_posts_widget'); ?></label><br />
            <input class="widefat" id="<?php echo $this->get_field_id('excluded_sites'); ?>" name="<?php echo $this->get_field_name('excluded_sites'); ?>" type="text" value="<?php echo esc_attr( $excluded_sites ); ?>" />            
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php _e('Show excerpt ', 'network_posts_widget'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" type="checkbox" value="yes" <?php if($excerpt == 'yes'){ echo 'checked'; } ?> />            
        </p>
    	<?php 
    }
 
    public function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['excluded_sites'] = strip_tags($new_instance['excluded_sites']);
        $instance['show_excerpt'] = $new_instance['show_excerpt'];
        return $instance;
    }
 
    public function widget( $args, $instance ) {
    	extract($args);
    	$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        $excluded_sites = (isset($instance['excluded_sites']) ? $instance['excluded_sites'] : false);
        $excerpt = (isset($instance['show_excerpt']) ? $instance['show_excerpt'] : false);
        
        //setup query
        $args = array(
        	'post_type' => 'post',
        	'posts_per_page' => 5,
        	'suppress_filters' => 1
        );
        if($excluded_sites){
        	$excluded_sites_array = explode(',', $excluded_sites);
	        $args['sites']['sites__not_in'] = $excluded_sites_array;
        }
        $network_posts_query = new WP_Query_Multisite($args);
        if($network_posts_query->have_posts()){
        	echo $before_widget;
        	if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
	        echo '<ul class="network-posts-ul">';
	        while($network_posts_query->have_posts()){
		        $network_posts_query->the_post();
		        echo '<li class="network-posts-li">';
		        echo '<h3 class="network-posts-heading"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
		        if($excerpt == 'yes'){
			        echo '<p class="network-posts-excerpt">' . get_the_excerpt() . '</p>';
		        }
		        echo '</li>';
	        }
	        wp_reset_postdata();
	        echo '</ul>';
	        echo $after_widget;
        }
    }
}
?>