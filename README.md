# WP Santo do Dia

Contributors: fellipesoares
Donate link: https://fellipesoares.com.br/wp-santo-do-dia/
Tags: catholic, saint
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Santo do Dia é um plugin do WordPress para apresentar através de um shortcode o Santo do Dia, conforme a Tradição Católica.

## Descrição

WP Santo do Dia é um plugin do WordPress para apresentar através de um shortcode o Santo do Dia, conforme a Tradição Católica.

A ativação do plugin irá adicionar uma tabela no banco de dados, que será atualizada diariamente com informações do site santo.app.br. Você poderá exibir as informações do Santo do dia por meio do shortcode `[santododia]`. Este shortcode contém uma imagem do Santo assim como o nome, ideal para exibição em barras verticais.

Será exibido no shortcode o santo do dia e mês atual.

## Instalação

1. Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
2. Ative o plugin.
3. Recomendação: Se o seu site não recebe visitas diariamente, faça o agendamento do `wp-cron` para executar ao menos uma ver por dia.

## F.A.Q. (Frequently Asked Questions)

### O plugin adiciona os santos automaticamente?

Sim, não é necessário realizar nenhum tipo de gerenciamento relacionado aos dados.

### Como colaborar com o projeto?

Entre em contato comigo por email: falecom [at] fellipesoares.com.br

## Screenshots



### Changelog

#### 2.3.0
* Após uma falha da API, novas tentativas ficam pausadas por 15 minutos; as páginas não esperam mais o timeout enquanto a API está fora.
* Erros do plugin são registrados como JSON no debug.log quando o WP_DEBUG_LOG está ligado, sem query strings nem e-mails.
* A pausa pendente da API é removida na desinstalação.

#### 2.2.0
* Compatibilidade validada com WordPress 7.1 e PHP 7.4 ou superior.
* Corrigido o evento de atualização diária e adicionada atualização automática para instalações existentes.
* Reforçada a validação das respostas da API e o tratamento quando os dados estão indisponíveis.
* Dados passam a ser removidos somente na desinstalação, não mais na desativação.
* Adicionados escaping, proteção de links externos e limites para requisições HTTP.

#### 2.1
* Melhorias de Performance e Otimização de Carregamento da Página

#### 2.0
* Melhoria: Remoção do modelo de CPT para consulta online da informação via API do santo.app.br

#### 1.1.1
* Bug: Links permanentes do CPT resultavam em página 404 ao instalar o plugin

#### 1.1
* Novo: Widget considera a primeira imagem do post caso não seja definida imagem destacada

#### 1.0
* Versão inicial do plugin

### Upgrade Notice
* A atualização para a versão 2.0 irá remover o CPT "Santo".


### Arbitrary section
