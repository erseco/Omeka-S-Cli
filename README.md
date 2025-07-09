# Omeka-S-Cli

Omeka-S-CLI is a command line tool to manage Omeka S installs.

![img.png](img.png)

## Features

- Manage modules
    - Search and download modules from [official Omeka S module repository](https://omeka.org/s/modules/) and [Daniel Berthereau's module repository](https://daniel-km.github.io/UpgradeToOmekaS/en/omeka_s_modules.html)
    - Download modules from git repositories
    - Install, enable, disable, upgrade, uninstall and delete downloaded modules
    - List all downloaded modules and their status
- Manage themes
    - Search and download themes from the [official Omeka S theme repository](https://omeka.org/s/themes/)
    - Download themes from git repositories
    - Install, enable, disable, uninstall and delete downloaded themes
    - List all downloaded themes and their status
- Config
    - Export list of installed modules and themes
    - Get, set and list global settings (`config:get`, `config:set`, `config:list`)
- User Management
    - Add users (`user:add`)
- API Key Management
    - Create API keys (`apikey:create`)
- Core
    - Get current Omeka S version
    - Update Omeka S to the latest version

## Automating Initial Site Configuration

Omeka-S-Cli can be used to automate the initial configuration of an Omeka S instance. This is particularly useful for development and CI/CD pipelines, for example, when using tools like [erseco/omeka-s-docker](https://github.com/erseco/omeka-s-docker).

Key commands for automation include:
- `config:set <id> <value>`: Set global settings (e.g., `installation_title`, `default_locale`, `timezone`).
- `user:add <email> <name> <role> [password]`: Create users, including administrators. For example, to create a global admin: `user:add admin@example.com AdminName global_admin securepassword`.
- `apikey:create <user-id> <label>`: Generate API keys for a specified user ID.

These commands allow for scripting the setup of a fresh Omeka S installation.

## Usage

    omeka-s-cli [ - h | --help ]
    omeka-s-cli <command> --help
    omeka-s-cli <command> [options]

### List modules
```
# omeka-s-cli module:list

Omeka S found at /var/www/omeka-s
+------------+-------------+---------------+-----------------------------+------------------+
| Id         | Name        | State         | Version                     | Update Available |
+------------+-------------+---------------+-----------------------------+------------------+
| Common     | Common      | active        | 3.4.66                      | 3.4.68           |
| EasyAdmin  | Easy Admin  | active        | 3.4.30                      | 3.4.31           |
| IiifServer | IIIF Server | not_installed | 3.6.21                      | 3.6.25           |
| Log        | Log         | needs_upgrade | 3.4.29 (3.4.28 in database) | up to date       |
+------------+-------------+---------------+-----------------------------+------------------+
```

### Download module from the official repository

```
# omeka-s-cli module:download --force common

Omeka S found at /var/www/omeka-s
Download https://github.com/Daniel-KM/Omeka-S-module-Common/releases/download/3.4.68/Common-3.4.68.zip ... done
Remove previous version ... done
Move module to folder /var/www/omeka-s/modules/Common ... done
Cleaning up /tmp/omeka-s-cli.18fb088b ... done
Module 'Common' successfully downloaded.
```

The module already exists, so we use the --force option to replace it with the latest version.

### Download module from git repository

```
# omeka-s-cli module:download https://github.com/GhentCDH/Omeka-S-module-AuthCAS.git

Omeka S found at /var/www/omeka-s
Download https://github.com/GhentCDH/Omeka-S-module-AuthCAS.git ... done
Move module to folder /var/www/omeka-s/modules/AuthCAS ... done
Module 'AuthCAS' successfully downloaded.
```

The installer will run `composer install` in the module directory if a `composer.lock` file is present. Other dependencies must be installed manually.

### Download specific module version

```
omeka-s-cli module:download common:3.4.67
```

```
omeka-s-cli module:download https://github.com/GhentCDH/Omeka-S-module-AuthCAS.git#v1.0.2
```

### Download theme from the official repository

```
# omeka-s-cli theme:download freedom

Omeka S found at /var/www/omeka-s
Download https://github.com/omeka-s-themes/freedom/releases/download/v1.0.7/freedom-v1.0.7.zip ... done
Move theme to folder /var/www/omeka-s/themes/freedom ... done
Cleaning up /tmp/omeka-s-cli.0ea2a8f4 ... done
Theme 'freedom' successfully downloaded.
```

## Requirements

- PHP (>= 8) with PDO_MySQL and Zip enabled
- Omeka S (>= 3.2)

## Installation

- Download [omeka-s-cli.phar](https://github.com/GhentCDH/Omeka-S-Cli/releases/latest/download/omeka-s-cli.phar) from the latest release.
- Run with `php omeka-s-cli.phar` or move it to a directory in your PATH and make it executable.

## Build

This project uses https://github.com/box-project/box to create a phar file.

### box global install

```bash
composer global require humbug/box
```
### compile phar

```bash
box compile
```

## To do

- [ ] Core management (version, latest-version, install, update)
- [ ] Download/update multiple modules at once
- [ ] Module dependency checking

## Credits

Built @ the [Ghent Center For Digital Humanities](https://www.ghentcdh.ugent.be/), Ghent University by:

* Frederic Lamsens

Inspired by:

- [Libnamic Omeka S Cli](https://github.com/Libnamic/omeka-s-cli/)
- [biblibre Omeka CLI](https://github.com/biblibre/omeka-cli)