<?php
declare(strict_types=1);

namespace Plugins\GoogleRecaptcha;

use App\Component\Hook\HookManager;
use Magepattern\Component\Tool\SmartyTool;
use Plugins\GoogleRecaptcha\db\FrontendDb;

class Boot
{
    public function register(): void
    {
        HookManager::register('displayHead', 'GoogleRecaptcha', function(array $params) {

            // 1. Détection de la page courante
            // On récupère le paramètre d'URL (ex: monsite.com/index.php?controller=contact)
            $currentModule = strtolower($_GET['controller'] ?? 'home');

            // 2. Connexion à la DB Frontend du plugin
            $db = new FrontendDb();

            // 3. LE BOUCLIER (C'est ici que ça bloque pour les pages non liées)
            if (!$db->isLinkedToModule($currentModule)) {
                // Si 'home' n'est pas coché dans l'admin, on retourne une chaîne vide.
                // Le script s'arrête proprement et n'injecte rien sur le site.
                return '';
            }

            // 4. Vérification de la configuration
            $keys = $db->getKeys();
            if (empty($keys['site_key'])) {
                // Si l'admin n'a pas encore mis sa clé Google, on ne charge rien
                return '';
            }

            // 5. Injection dans Smarty et affichage !
            $smarty = SmartyTool::getInstance('front');

            $file = ROOT_DIR . 'plugins' . DS . 'GoogleRecaptcha' . DS . 'views' . DS . 'front' . DS . 'hooks' . DS . 'script.tpl';

            return $smarty->fetch($file, ['recaptcha_site_key' => $keys['site_key']]);
        });
    }
}