`wordpress-tools`
=================

Scripts to work with WordPress, mostly for desaster recovery and debugging.

# Quick start

Checkout the repository including submodules:

```
git clone --recurse-submodules -j8 https://github.com/cmahnke/wordpress-tools.git
cd wordpress-tools
```

**Use at your own risk**

## `unpack.php`

This scripts tries to work with backups created by [All-in-One WP Migration and Backup](https://wordpress.org/plugins/all-in-one-wp-migration/). This used for some (almost certainly bad) reason it'S own file format for the archive. This makes it a very bad choice if you're relying on those files for more then migration (like trying to figure out old versions, changed files etc.). **Keep in mind: This plugin is not a valid backup tool**

This script allows to extract parts fo the contents of such a `.wpress` file.

Only PHP needs to be installed, dependencies are provided as Git submodules, PHP settings are done inside the script.

```bash
./unpack snapshot.wpress out_dir
```
