---
name: atualizar-compatibilidade-wp
description: Atualiza o "Tested up to" do plugin quando sai versão nova do WordPress. Use quando houver issue "Atualizar compatibilidade" aberta pelo workflow Check WordPress compatibility, ou quando pedirem para marcar compatibilidade com uma versão do WordPress.
---

# Atualizar compatibilidade com o WordPress

Siga `notes/rotina-compatibilidade-wordpress.md`. Resumo para agentes:

1. Descubra a versão estável: `curl -fsSL https://api.wordpress.org/core/version-check/1.7/ | jq -r '.offers[0].version'`.
   Use só `major.minor` (ex.: 7.1.2 → 7.1). Se já for o `Tested up to` do `README.txt`, não há nada a fazer.
2. Rode `composer check`. Se tiver Docker, suba `npx @wordpress/env start` e confira `[santododia]`.
3. Troque `Tested up to:` em `README.txt` **e** `README.md`.
4. Abra PR; não publique no SVN do WordPress.org nem crie release sem ordem explícita do mantenedor.
