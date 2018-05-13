<?php

namespace AdSky\Core;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * This class allows to render some view files.
 */

class Renderer {

    private $loader;
    private $twig;

    /**
     * Creates a new Renderer.
     *
     * @param array $paths Where to find templates.
     */

    public function __construct($paths = [__DIR__ . '/../views/']) {
        $this -> loader = new \Twig_Loader_Filesystem($paths);
        $this -> twig = new \Twig_Environment($this -> loader);
    }

    /**
     * Gets current paths.
     *
     * @return array Current paths.
     */

    public function getPaths() {
        return $this -> loader -> getPaths();
    }

    /**
     * Adds a relative path (relative to project's root).
     *
     * @param string $path The path.
     *
     * @throws \Twig_Error_Loader If there is an error while loading the path.
     */

    public function addRelativePath($path) {
        $this -> addPath(__DIR__ . '/../views/' . $path);
    }

    /**
     * Adds an absolute path.
     *
     * @param string $path The absolute path.
     *
     * @throws \Twig_Error_Loader If there is an error while loading the path.
     */

    public function addPath($path) {
        $this -> loader -> addPath($path);
    }

    /**
     * Sets the paths.
     *
     * @param array $paths The paths.
     */

    public function setPaths($paths) {
        $this -> loader -> setPaths($paths);
    }

    /**
     * Renders a template with Ad and Website settings (plus current PayPal currency).
     * This is the minimal package to render a website page.
     *
     * @param string $template The template file (must be in one of the provided paths).
     * @param array $additionalParameters Additional Twig parameters.
     *
     * @return string The rendered template.
     *
     * @throws \Twig_Error_Loader If there's an error while loading the template.
     * @throws \Twig_Error_Runtime If there's a twig runtime error.
     * @throws \Twig_Error_Syntax If there's a twig syntax error.
     */

    public function renderWithDefaultSettings($template, $additionalParameters = []) {
        $adsky = AdSky::getInstance();

        $settings = $adsky -> buildSettingsArray([$adsky -> getAdSettings(), $adsky -> getWebsiteSettings()]);
        $settings['PAYPAL_CURRENCY'] = $adsky -> getPayPalSettings() -> getPayPalCurrency();
        $additionalParameters['settings'] = $settings;

        return $this -> render($template, $additionalParameters);
    }

    /**
     * Renders a template.
     *
     * @param string $template The template file (must be in one of the provided paths).
     * @param array $parameters Twig parameters.
     *
     * @return string The rendered template.
     *
     * @throws \Twig_Error_Loader If there's an error while loading the template.
     * @throws \Twig_Error_Runtime If there's a twig runtime error.
     * @throws \Twig_Error_Syntax If there's a twig syntax error.
     */

    public function render($template, $parameters = []) {
        return $this -> twig -> render($template, $parameters);
    }

}