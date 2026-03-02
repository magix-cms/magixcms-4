<?php

/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern\Component\Tool;

use Dompdf\Dompdf;
use Dompdf\Options;
use Magepattern\Bootstrap;

class PdfTool
{
    /**
     * Génère un PDF à partir d'un contenu HTML
     * @param string $html     Le code HTML
     * @param string $filename Nom du fichier en sortie
     * @param bool   $stream   Si true, affiche (inline), sinon retourne le binaire
     * @param string $paper    Format (A4, Letter, etc.)
     * @param string $orient   Orientation (portrait ou landscape)
     */
    public static function generate(
        string $html,
        string $filename = 'document.pdf',
        bool $stream = true,
        string $paper = 'A4',
        string $orient = 'portrait'
    ) {
        Bootstrap::getInstance()->load('dompdf');

        $rootDir = dirname(__DIR__, 3);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('fontCache', $rootDir . '/var/pdf/fonts');

        // Sécurité : On autorise Dompdf à lire les fichiers locaux à partir de la racine
        $options->set('chroot', $rootDir);

        // Vérification du dossier de cache polices
        if (!is_dir($options->getFontCache())) {
            mkdir($options->getFontCache(), 0775, true);
        }

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orient);

        $dompdf->render();

        if ($stream) {
            // "Attachment" => false permet d'ouvrir dans le navigateur sans forcer le téléchargement
            $dompdf->stream($filename, ["Attachment" => false]);
            exit;
        }

        return $dompdf->output();
    }
}