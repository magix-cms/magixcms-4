<?php

namespace Magepattern\Component\XML;

use Magepattern\Component\Debug\Logger;
use Exception;
use SimpleXMLElement;

/**
 * Class XMLReader
 * Convertit des flux XML en tableaux PHP, en préservant la structure,
 * les attributs et les listes d'éléments répétés.
 */
class XMLReader
{
    /**
     * Convertit une chaîne XML brute en tableau associatif.
     * @param string $xmlString
     * @return array|null
     */
    public static function toArray(string $xmlString): ?array
    {
        if (trim($xmlString) === '') return null;

        try {
            // LIBXML_NOCDATA : Convertit les CDATA en texte simple
            // LIBXML_NOBLANKS : Supprime les espaces vides non significatifs
            $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);

            if ($xml === false) {
                return null;
            }

            return self::convert($xml);
        } catch (Exception $e) {
            Logger::getInstance()->log($e, "php", "xml_error");
            return null;
        }
    }

    /**
     * Charge un fichier XML et le convertit.
     * @param string $path
     * @return array|null
     */
    public static function fromFile(string $path): ?array
    {
        if (!file_exists($path)) {
            Logger::getInstance()->log("XML File not found: $path", "php", "xml_error");
            return null;
        }

        $content = file_get_contents($path);
        return self::toArray($content);
    }

    /**
     * Convertit récursivement un objet SimpleXMLElement en tableau.
     * Gère les attributs via la clé '@attributes'.
     *
     * @param SimpleXMLElement $node
     * @return mixed
     */
    private static function convert(SimpleXMLElement $node): mixed
    {
        $result = [];

        // 1. Gestion des Attributs
        // On les stocke dans une clé séparée pour ne pas écraser le contenu
        foreach ($node->attributes() as $key => $value) {
            $result['@attributes'][$key] = (string)$value;
        }

        // 2. Gestion des Enfants (Récursion)
        if ($node->count() > 0) {
            foreach ($node->children() as $key => $child) {
                $childData = self::convert($child);

                // Détection des listes (plusieurs enfants avec le même nom)
                if (isset($result[$key])) {
                    if (!is_array($result[$key]) || !isset($result[$key][0])) {
                        // Transforme l'entrée existante en liste
                        $result[$key] = [$result[$key]];
                    }
                    $result[$key][] = $childData;
                } else {
                    $result[$key] = $childData;
                }
            }
        } else {
            // 3. Gestion du contenu texte (Feuille)
            $textValue = (string)$node;

            // Si le nœud a des attributs, on doit garder la structure tableau
            if (!empty($result['@attributes'])) {
                if (trim($textValue) !== '') {
                    $result['@value'] = $textValue;
                }
                return $result;
            }

            // Sinon, on retourne juste la valeur (cas le plus simple)
            return $textValue;
        }

        return $result;
    }
}