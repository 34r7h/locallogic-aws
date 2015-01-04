# Megumi_SwitchTheme() Class

Class for switch WordPress theme.

## How to install in your project.

### Add composer.json like below

    {
        "require": {
            "megumi/switch_theme": "dev-master"
        }
    }

### Download this package in your project.

    composer install

### Load this class.

    require(dirname(__FILE__).'/vendor/autoload.php');

## Example

Basic:

    <?php
    /*
    Plugin Name: Switch Theme
    */

    require(dirname(__FILE__).'/vendor/autoload.php');

    if (isset($_GET['test']) && $_GET['test']) {
        $theme = new Megumi_SwitchTheme('my-mobile-theme');
        $theme->apply();
    }

Theme file is out of the wp-content/themes

    <?php
    /*
    Plugin Name: Switch Theme
    */

    require(dirname(__FILE__).'/vendor/autoload.php');

    if (isset($_GET['test']) && $_GET['test']) {
        $theme = new Megumi_SwitchTheme(
            'my-mobile-theme',
            '/path/to/wp-content/mobile-themes',
            'http://example.com/wp-content/mobile-themes'
        );
        $theme->apply();
    }

## License

GPL2
