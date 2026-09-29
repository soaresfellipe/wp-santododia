# Rotina: compatibilidade com nova versão do WordPress

Disparada pela issue que o workflow **Check WordPress compatibility**
(`.github/workflows/check-wp-compatibility.yml`) abre toda segunda-feira quando o
`Tested up to` do `README.txt` fica atrás da versão estável do WordPress.

O workflow compara só `major.minor` (convenção do WordPress.org): lançamentos de correção
como 7.1.2 **não** exigem esta rotina.

## Passos

1. **Validar.** Rode `composer check`. Se possível, suba a versão nova com
   `npx @wordpress/env start`, ative o plugin e confira o shortcode `[santododia]`
   numa página (ver `AGENTS.md`).
2. **Atualizar o readme.** `Tested up to: X.Y` no `README.txt` **e** no `README.md`.
3. **Versão do plugin**, se houver release:
   - cabeçalho `Version` e constante `SANTO_DO_DIA_VERSION` em `santododia.php`;
   - `Stable tag` nos dois readmes;
   - entrada nova no changelog dos dois readmes.
   Só atualizar o `Tested up to` não exige versão nova: no WordPress.org basta
   alterar o `README.txt` do trunk e da tag estável.
4. **PR no GitHub.** A CI (`test (*)` e `gitleaks`) precisa ficar verde para o merge.
5. **Publicar no WordPress.org (SVN).** Copie para o `trunk/` do SVN os arquivos
   que não estão no `.distignore` e faça commit; em release nova, crie
   `tags/<versão>` a partir do trunk.
6. **Release no GitHub** com a mesma tag e o trecho do changelog.
7. **Fechar a issue.** O workflow fecha sozinho na execução seguinte; para não esperar,
   rode-o manualmente (`gh workflow run "Check WordPress compatibility"`).

## Se algo der errado

- API do WordPress.org fora do ar: o passo "Detect latest WordPress version" falha e
  nenhuma issue é mexida. Rode de novo mais tarde.
- Versão nova quebra o plugin: não atualize o `Tested up to`; abra uma issue `bug`
  com o erro e mantenha a issue de compatibilidade aberta.
