<?php

/*
Plugin Name: Santo do Dia
Plugin URI: https://fellipesoares.com.br/wp-santo-do-dia
Description: Exiba o Santo do dia através de um shortcode [santododia]
Version: 2.0
Author: Fellipe Soares
Author URI: https://fellipesoares.com.br
License: GPL2
*/

// Os dados dos santos serão utilizados do site santo.app.br
// Eles serão obtidos através de JSON (https://santo.app.br/wp-json/wp/v2/santo)
// Exemplo de chamada: https://santo.app.br/wp-json/wp/v2/santo?dia=3&mes=1

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
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Executo a função para obter os dados do santo do dia
    santo_do_dia_obter_dados();
}

register_activation_hook( __FILE__, 'santo_do_dia_install' );

// Função para remover a tabela do banco de dados, caso o plugin seja desinstalado
// Também é necessário remover o agendamento do cron (obter dados)
function santo_do_dia_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    wp_clear_scheduled_hook('santo_do_dia_cron');
}

register_deactivation_hook( __FILE__, 'santo_do_dia_uninstall' );

// Função para obter os dados do santo do dia e gravar na tabela
function santo_do_dia_obter_dados() {

    // Defino o timezone para Americas/Sao_Paulo
    date_default_timezone_set('America/Sao_Paulo');

    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    $dia = (int) date('d');
    $mes = (int) date('m');
    $url = "https://santo.app.br/wp-json/wp/v2/santo?dia=$dia&mes=$mes";
    $json = file_get_contents($url);
    $dados = json_decode($json);
    $wpdb->insert($table_name, array(
        'dia' => $dia,
        'mes' => $mes,
        'nome' => $dados[0]->title->rendered,
        'imagem' => $dados[0]->imagem_destacada,
        'url' => $dados[0]->link
    ));
}

// A função de obter dados será executada todo dia, às 00:10
function santo_do_dia_cron() {
    if (!wp_next_scheduled('santo_do_dia_cron')) {
        wp_schedule_event(time(), 'daily', 'santo_do_dia_cron');
    }
}

// Função para exibir o santo do dia
// Será exibido a imagem do santo, com link para o site santo.app.br
// Abaixo da imagem, será exibido o nome do santo, com link para o site santo.app.br

function santo_do_dia() {

    // Defino o timezone para Americas/Sao_Paulo
    date_default_timezone_set('America/Sao_Paulo');

    global $wpdb;
    $table_name = $wpdb->prefix . "santo_do_dia";
    $dia = (int) date('d');
    $mes = (int) date('m');
    // Aplico parâmetros UTM em $url
    // utm_source = domínio do site, utm_medium = plugin, utm_campaign = plugin_shortcode
    $urlDoSite = $_SERVER['HTTP_HOST'];
    $url = $url . "?utm_source=$urlDoSite&utm_medium=plugin&utm_campaign=plugin_shortcode";
    $santo = $wpdb->get_row("SELECT * FROM $table_name WHERE dia = $dia AND mes = $mes");
    $html = "<a href='$santo->url' target='_blank'><img src='$santo->imagem' alt='$santo->nome' /></a>";
    $html .= "<p><a href='$santo->url' target='_blank'>$santo->nome</a></p>";
    return $html;
}

// Adiciono o shortcode [santododia]
add_shortcode('santododia', 'santo_do_dia');