<?php
declare(strict_types=1);

namespace Magepattern\Component\Tool;

use HTMLPurifier;
use HTMLPurifier_Config;

class SecurityTool
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Nettoie une chaîne HTML pour prévenir les attaques XSS
     * tout en conservant le formatage (gras, liens, iframes sécurisées, etc.)
     */
    public static function purifyHtml(string $html): string
    {
        if (empty(trim($html))) {
            return '';
        }

        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();

            // Configuration de base : Autoriser les iframes YouTube/Vimeo
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

            // On autorise toutes les classes CSS (très important pour les thèmes Bootstrap)
            $config->set('Attr.AllowedClasses', null);

            // IMPORTANT : Désactiver le cache de Purifier si on n'a pas défini de dossier inscriptible,
            // ou bien pointer vers le dossier 'var/' de Magix CMS.
            // Pour être universel sans créer d'erreur de droits d'écriture :
            $config->set('Cache.DefinitionImpl', null);

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier->purify($html);
    }
}
?>