<?php

/*
Plugin Name: Santo do Dia
Plugin URI: http://fellipesoares.com.br/wp-santododia
Description: A brief description of the Plugin.
Version: 1.0
Author: Fellipe Soares
Author URI: http://fellipesoares.com.br
License: A "Slug" license name e.g. GPL2
*/

/**
 * Criação do CPT Santo do Dia
 */
function santo_custom_post_type() {
    register_post_type('santo',
        [
            'labels' => [
                'name'          => __('Santos'),
                'singular_name' => __('Santo')
            ],
            'public'        => true,
            'has_archive'   => true,
            'supports'      => array( 'title','editor','custom-fields','thumbnail','excerpt' ),
            'rewrite'       => ['slug' => 'santo'] // slug custom
        ]);
}

add_action('init','santo_custom_post_type');

/**
 * Adiciona a Widget Santo do Dia
 */
class SantodoDia_Widget extends WP_Widget {

    /**
     * Registro da Widget no WordPress
     */
    public function __construct() {
        parent::__construct(
            'santododia_widget', // ID Base
            'SantodoDia_Widget', // Nome
            array( 'description' => __('Widget do Santo do Dia','text_domain'), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }



	    // Fazer a consulta do Santo do Dia
	    $dia = date( 'j' );
        $mes = date('n');
	    $args = array(
		    'posts_per_page'   => 5,
		    'offset'           => 0,
		    'orderby'          => 'date',
		    'order'            => 'DESC',
		    'post_type'        => 'santo',
		    'post_status'      => 'publish',
		    'date_query'       => array(array(
			    'month' => $mes,
			    'day' => $dia,
		    ),),
		    'suppress_filters' => true
	    );

	    // query
	    $santo_do_dia = new WP_Query( $args );


	    if ( $santo_do_dia->have_posts() ) {
		    echo "<div>";
		    echo "<ul style='list-style: none; clear: both;'>";
	        while ( $santo_do_dia->have_posts() ) {
		        $santo_do_dia->the_post();
	            echo "<li style='float: left'><a href='" . get_post_permalink() . "' title='" . get_the_title() . "'>";
                    the_post_thumbnail(array(80, 80), array('class' => 'alignleft'));
                    echo get_the_title();
                echo "</a></li>";
            }
            echo "</ul>";
		    echo "</div>";
		    wp_reset_postdata();
        } else {
		    echo "<div>";
	        echo "Sem santo nesta data";
		    echo "</div>";
        }


        // wp_reset_query();	 // Restore global post data stomped by the_post().

	    echo $after_widget;
    }
	    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
	    if ( isset( $instance['title'] ) ) {
		    $title = $instance['title'];
	    } else {
		    $title = __( 'Santo do Dia', 'text_domain' );
	    }

	    ?>
        <p>
            <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Título:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
	    <?php
    }
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instace, $old_instance) {

    }
} // Classe SantodoDia_Widget

// Register Foo_Widget widget
add_action( 'widgets_init', function() { register_widget( 'SantodoDia_Widget' ); } );