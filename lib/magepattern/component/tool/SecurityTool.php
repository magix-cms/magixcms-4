<?php
declare(strict_types=1);

namespace Magepattern\Component\Tool;

use HTMLPurifier;
use HTMLPurifier_AttrDef_Enum;
use HTMLPurifier_AttrDef_Text;
use HTMLPurifier_Config;

class SecurityTool
{
    private static ?HTMLPurifier $purifier = null;

    /** @var array<string, bool> */
    private static array $registeredDataAttrs = [];

    /**
     * Nettoie une chaîne HTML pour prévenir les attaques XSS
     * tout en conservant le formatage (gras, liens, iframes sécurisées, HTML5, Bootstrap, etc.)
     */
    public static function purifyHtml(string $html): string
    {
        if (empty(trim($html))) {
            return '';
        }

        // Détection des attributs data-* présents dans le contenu
        $hasNewDataAttrs = false;
        if (preg_match_all('/\b(data-[a-z0-9\-]+)\s*=/i', $html, $matches)) {
            foreach ($matches[1] as $attr) {
                $attrName = strtolower($attr);
                // On exclut data-bs-html pour éviter l'injection HTML dans les tooltips/popovers Bootstrap
                if ($attrName !== 'data-bs-html' && !isset(self::$registeredDataAttrs[$attrName])) {
                    self::$registeredDataAttrs[$attrName] = true;
                    $hasNewDataAttrs = true;
                }
            }
        }

        if (self::$purifier === null || $hasNewDataAttrs) {
            $config = HTMLPurifier_Config::createDefault();

            // Autoriser les iframes YouTube/Vimeo
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

            // Autoriser target="_blank" (HTMLPurifier ajoute automatiquement rel="noopener noreferrer")
            $config->set('Attr.AllowedFrameTargets', ['_blank']);

            // Autoriser toutes les classes CSS (indispensable pour Bootstrap 5.3)
            $config->set('Attr.AllowedClasses', null);

            // Désactiver le cache de définition pour permettre la surcharge dynamique
            $config->set('Cache.DefinitionImpl', null);

            // Extension HTML5, Accessibilité (ARIA) & attributs data-*
            $def = $config->getHTMLDefinition(true);
            if ($def !== null) {
                // Médias et légendes
                $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
                $def->addElement('figcaption', 'Flow', 'Flow', 'Common');

                // Balises structurelles HTML5
                $def->addElement('section', 'Block', 'Flow', 'Common');
                $def->addElement('article', 'Block', 'Flow', 'Common');
                $def->addElement('aside', 'Block', 'Flow', 'Common');
                $def->addElement('header', 'Block', 'Flow', 'Common');
                $def->addElement('footer', 'Block', 'Flow', 'Common');
                $def->addElement('nav', 'Block', 'Flow', 'Common');
                $def->addElement('main', 'Block', 'Flow', 'Common');

                // Balises sémantiques en ligne et interactives
                $def->addElement('mark', 'Inline', 'Inline', 'Common');
                $def->addElement('time', 'Inline', 'Inline', 'Common', ['datetime' => 'Text']);
                $def->addElement('details', 'Block', 'Flow', 'Common', ['open' => 'Bool']);
                $def->addElement('summary', 'Flow', 'Inline', 'Common');

                // Attributs globaux d'accessibilité
                $def->info_global_attr['aria-label'] = new HTMLPurifier_AttrDef_Text();
                $def->info_global_attr['aria-hidden'] = new HTMLPurifier_AttrDef_Enum(['true', 'false']);
                $def->info_global_attr['role'] = new HTMLPurifier_AttrDef_Text();

                // Enregistrement global des attributs data-* détectés
                foreach (array_keys(self::$registeredDataAttrs) as $dataAttr) {
                    $def->info_global_attr[$dataAttr] = new HTMLPurifier_AttrDef_Text();
                }
            }

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier->purify($html);
    }
}