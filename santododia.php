<?php

/*
Plugin Name: Santo do Dia
Plugin URI: https://fellipesoares.com.br/wp-santo-do-dia
Description: Exiba o Santo do dia através de um shortcode [santododia]
Version: 2.1
Author: Fellipe Soares
Author URI: https://fellipesoares.com.br
License: GPL2
*/

// Os dados dos santos serão utilizados do site catolicoapp.com
// Eles serão obtidos através de JSON (https://catolicoapp.com/wp-json/wp/v2/santos)
// Exemplo de chamada: https://catolicoapp.com/wp-json/wp/v2/santos?dia=3&mes=1

// Durante a instalação do plugin, crio uma tabela para armazenar os dados do santo do dia,  evitando muitas requisições ao site santo.app.br
// Os campos que deverão ser criados: id, dia, mes, nome, URL da imagem e URL do santo.app.br

// Função para criar a tabela no banco de dados
function santo_do_dia_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        dia tinyint(2) NOT NULL,
        mes tinyint(2) NOT NULL,
        nome varchar(255) NOT NULL,
        imagem varchar(255) NOT NULL,
        url varchar(255) NOT NULL,
        atualizado timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY data (dia,mes)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    santo_do_dia_obter_dados();
    
    // Programar atualização principal à meia-noite
    if (!wp_next_scheduled('santo_do_dia_cron_diario')) {
        wp_schedule_event(strtotime('midnight'), 'daily', 'santo_do_dia_cron_diario');
    }
    
    // Programar verificação de fallback a cada 6 horas
    if (!wp_next_scheduled('santo_do_dia_cron_fallback')) {
        wp_schedule_event(time(), 'sixhours', 'santo_do_dia_cron_fallback');
    }
}

register_activation_hook(__FILE__, 'santo_do_dia_install');

// Função para obter os dados do santo do dia e gravar na tabela
function santo_do_dia_obter_dados() {
    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    
    // Utiliza a timezone do WordPress
    $timezone = wp_timezone();
    $now = new DateTime('now', $timezone);
    
    $dia = (int) $now->format('d');
    $mes = (int) $now->format('m');

    // Verifica se já existe um registro para o dia atual
    $santo_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE dia = %d AND mes = %d",
        $dia, $mes
    ));

    if ($santo_exists > 0) {
        return;
    }

    $url = esc_url_raw("https://catolicoapp.com/wp-json/wp/v2/santos?dia=$dia&mes=$mes");
    
    // Usa a API HTTP do WordPress em vez de cURL
    $response = wp_remote_get($url, [
        'timeout' => 15,
        'headers' => ['Accept' => 'application/json'],
    ]);
    
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return;
    }
    
    $dados = json_decode(wp_remote_retrieve_body($response));
    
    if (!is_array($dados) || empty($dados)) {
        return;
    }
    
    // Replace or insert usando o método replace do WPDB
    $wpdb->replace($table_name, [
        'dia' => $dia,
        'mes' => $mes,
        'nome' => sanitize_text_field($dados[0]->title->rendered),
        'imagem' => esc_url_raw($dados[0]->imagem_destacada),
        'url' => esc_url_raw($dados[0]->link)
    ]);
}

// Função principal de atualização diária
add_action('santo_do_dia_cron', 'santo_do_dia_obter_dados');

// Registrar intervalo personalizado de 6 horas
function santo_do_dia_cron_schedules($schedules) {
    $schedules['sixhours'] = array(
        'interval' => 21600, // 6 horas em segundos
        'display'  => __('A cada 6 horas')
    );
    return $schedules;
}
add_filter('cron_schedules', 'santo_do_dia_cron_schedules');

// Função de verificação e fallback
function santo_do_dia_verificar_dados() {
    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    
    $timezone = wp_timezone();
    $now = new DateTime('now', $timezone);
    
    $dia = (int) $now->format('d');
    $mes = (int) $now->format('m');
    
    // Verifica se os dados existem para o dia atual
    $santo_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE dia = %d AND mes = %d",
        $dia, $mes
    ));
    
    // Se não existir, tenta obter novamente
    if ($santo_exists == 0) {
        santo_do_dia_obter_dados();
    }
}
add_action('santo_do_dia_cron_fallback', 'santo_do_dia_verificar_dados');

// Função para exibir o santo do dia com cache transient
function santo_do_dia() {
    // Verifica se existe dados em cache
    $cache_key = 'santo_do_dia_html_' . date('d_m');
    $html = get_transient($cache_key);
    
    if ($html === false) {
        global $wpdb;
        
        // Utiliza a timezone do WordPress
        $timezone = wp_timezone();
        $now = new DateTime('now', $timezone);
        
        $dia = (int) $now->format('d');
        $mes = (int) $now->format('m');
        
        $table_name = $wpdb->prefix . "santo_do_dia";
        $santo = $wpdb->get_row($wpdb->prepare(
            "SELECT nome, imagem FROM $table_name WHERE dia = %d AND mes = %d LIMIT 1",
            $dia, $mes
        ));
        
        if (!$santo) {
            // Se não encontrar dados, tenta obter na hora
            santo_do_dia_obter_dados();
            // Tenta buscar novamente após a atualização
            $santo = $wpdb->get_row($wpdb->prepare(
                "SELECT nome, imagem FROM $table_name WHERE dia = %d AND mes = %d LIMIT 1",
                $dia, $mes
            ));
        }

        $base_url = "https://catolicoapp.com/santo-do-dia";
        
        $html = '<div class="santo-do-dia-container">';
        $html .= '<h3>Santo do Dia</h3>';
        $html .= '<div class="santo-do-dia-card">';
        $html .= '<a href="' . esc_url($base_url) . '" target="_blank"><img src="' . esc_url($santo->imagem) . '" alt="' . esc_attr($santo->nome) . '" /></a>';
        $html .= '<p><a href="' . esc_url($base_url) . '" target="_blank">' . esc_html($santo->nome) . '</a></p>';
        $html .= '</div></div>';
        
        // Guarda em cache por 12 horas
        set_transient($cache_key, $html, 12 * HOUR_IN_SECONDS);
    }
    
    return $html;
}

add_shortcode('santododia', 'santo_do_dia');

// Carrega CSS apenas quando o shortcode é usado
function santo_do_dia_enqueue_scripts() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'santododia')) {
        wp_enqueue_style('santododia-style', plugin_dir_url(__FILE__) . 'css/santododia.css', [], '2.1.0');
    }
}
add_action('wp_enqueue_scripts', 'santo_do_dia_enqueue_scripts');

// Função para desinstalar o plugin
function santo_do_dia_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    wp_clear_scheduled_hook('santo_do_dia_cron_diario');
    wp_clear_scheduled_hook('santo_do_dia_cron_fallback');
    
    // Remove transients relacionados
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_santo_do_dia_html_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_santo_do_dia_html_%'");
}

register_deactivation_hook(__FILE__, 'santo_do_dia_uninstall');