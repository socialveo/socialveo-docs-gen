Socialveo API documentation generator
=====================================

Internal Socialveo extension what provides an API documentation generator

The generated documentation is stored on https://github.com/socialveo/socialveo-docs

Installation
------------

```
composer require socialveo/socialveo-docs-gen
```

It's require access to private socialveo repos.

Usage
-----

```bash
bash apidoc-gen.sh
bash apidoc-gen.sh --clear-cache # clear cache before launch
```

Force update docs 
-----

```bash
update-docs --force --clear-cache
```
