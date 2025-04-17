=== WP Santo do Dia ===

Contributors: fellipesoares
Donate link: https://fellipesoares.com.br/wp-santo-do-dia/
Tags: catholic, saint
Requires at least: 4.6
Tested up to: 6.8
Stable tag: 2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Santo do Dia é um plugin do WordPress para apresentar através de um shortcode o Santo do Dia, conforme a Tradição Católica.

== Description ==

WP Santo do Dia é um plugin do WordPress para apresentar através de um widget o Santo do Dia, conforme a Tradição Católica. Sua ativação adiciona um widget que apresenta o Santo do Dia.

A ativação do plugin irá adicionar uma tabela no banco de dados, que será atualizada diariamente com informações do site https://catolicoapp.com. Você poderá exibir as informações do Santo do dia por meio do shortcode `[santododia]`. Este shortcode contém uma imagem do Santo assim como o nome, ideal para exibição em barras verticais.

Será exibido no shortcode o santo do dia e mês atual.

== Installation ==

1. Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
2. Ative o plugin.
3. Recomendação: Se o seu site não recebe visitas diariamente, faça o agendamento do `wp-cron` para executar ao menos uma ver por dia.

== Frequently Asked Questions ==

= O plugin adiciona os santos automaticamente? =

Sim, não é necessário realizar nenhum tipo de gerenciamento relacionado aos dados.

= Como faço para exibir o santo do dia no meu site? =

Basta adicionar o shortcode [santododia] em qualquer lugar do seu site.

== Changelog ==

= 2.1.0 =
* Melhorias de Performance e Otimização de Carregamento da Página

= 2.0.9 =
* Melhoria: Atualização da URL exibida no card do santo do dia

= 2.0.8 =
* Melhoria: Atualização da API para CatolicoApp

= 2.0.7 =
* Bug: Substituição da função get_file_contents por cUrl

= 2.0.6 =
* Bug: Correção de problemas no versionamento

= 2.0.5 =
* Bug: Correção de problema no arquivo CSS

= 2.0.4 =
* Melhoria: Adicionada folha de estilos CSS para melhor exibição do widget/shortcode

= 2.0.3 =
* Bug: Correção de problemas no agendamento da tarefa de obter dados da API

= 2.0.2 =
* Melhoria: Adicionadas classes CSS na imagem e título do Santo.

= 2.0.1 =
* Bug: Corrigida a frequência de verificação para de hora em hora
        Implementada uma condição para somente buscar na API se o registro não estiver gravado na tabela

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