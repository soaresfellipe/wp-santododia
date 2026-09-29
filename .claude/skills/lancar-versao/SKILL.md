---
name: lancar-versao
description: Prepara uma versão nova do plugin (bump de versão, changelog, checagens). Use quando pedirem para lançar, bumpar ou preparar release do WP Santo do Dia.
---

# Preparar versão nova

1. Escolha a versão (SemVer): correção → patch, recurso → minor.
2. Atualize **todos** juntos, senão o WordPress.org publica a versão errada:
   - cabeçalho `Version:` e `define( 'SANTO_DO_DIA_VERSION', ... )` em `santododia.php`;
   - `Stable tag:` em `README.txt` e `README.md`;
   - entrada no topo do changelog dos dois readmes: `README.txt` em inglês (`= X.Y.Z =`), `README.md` em português (`#### X.Y.Z`).
3. Confira a consistência: `grep -nE "Version:|SANTO_DO_DIA_VERSION'|Stable tag" santododia.php README.*`.
4. `composer check` precisa passar.
5. Abra PR. Publicar no SVN do WordPress.org e criar a release no GitHub é decisão do mantenedor:
   não faça sem ordem explícita. Quando autorizado, o workflow `Release` gera o zip e as notas
   a partir da tag (`.github/workflows/release.yml`).
