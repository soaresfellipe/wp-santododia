=== WP Santo do Dia ===

Contributors: fellipesoares
Donate link: https://fellipesoares.com.br/wp-santo-do-dia/
Tags: catholic, saint
Requires at least: 4.6
Tested up to: 6.1.1
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Santo do Dia é um plugin do WordPress para apresentar através de um shortcode o Santo do Dia, conforme a Tradição Católica.

== Description ==

WP Santo do Dia é um plugin do WordPress para apresentar através de um widget o Santo do Dia, conforme a Tradição Católica. Sua ativação adiciona um widget que apresenta o Santo do Dia.

A ativação do plugin irá adicionar uma tabela no banco de dados, que será atualizada diariamente com informações do site santo.app.br. Você poderá exibir as informações do Santo do dia por meio do shortcode `[santododia]`. Este shortcode contém uma imagem do Santo assim como o nome, ideal para exibição em barras verticais.

Será exibido no shortcode o santo do dia e mês atual.

== Installation ==

1. Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
2. Ative o plugin.
3. Recomendação: Se o seu site não recebe visitas diariamente, faça o agendamento do `wp-cron` para executar ao menos uma ver por dia.

== Frequently Asked Questions ==

= O plugin adiciona os santos automaticamente? =

Sim, não é necessário realizar nenhum tipo de gerenciamento relacionado aos dados.

== Screenshots ==

1. Apresentação do widget [link](https://fellipesoares.com.br/wp-content/uploads/2017/11/santododia_widget-242x300.png)
2. Menu do Custom Post Type nomeado Santo [link] (https://fellipesoares.com.br/wp-content/uploads/2017/11/santo_menu.png)
3. Tela para adicionar um santo [link] (https://fellipesoares.com.br/wp-content/uploads/2017/11/santo_adicionar-700x295.png)

== Changelog ==

= 2.0 =
* Melhoria: Remoção do modelo de CPT para consulta online da informação via API do santo.app.br

= 1.1.1 =
* Bug: Links permanentes do CPT resultavam em página 404 ao instalar o plugin

= 1.1 =
* Novo: Widget considera a primeira imagem do post caso não seja definida imagem destacada

= 1.0 =
* Versão inicial do plugin

== Upgrade Notice ==
* A atualização para a versão 2.0 irá remover o CPT "Santo".

== Arbitrary section ==
