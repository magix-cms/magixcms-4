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

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;

class PaginationTool
{
    private int $totalItems = 0;
    private int $itemsPerPage;
    private int $currentPage;

    public function __construct(int $itemsPerPage = 20, int $currentPage = 1)
    {
        $this->itemsPerPage = $itemsPerPage > 0 ? $itemsPerPage : 20;
        $this->currentPage = $currentPage > 0 ? $currentPage : 1;
    }

    /**
     * Applique la pagination au QueryBuilder et récupère les métadonnées
     */
    /**
     * Applique la pagination au QueryBuilder et récupère les métadonnées
     */
    public function paginate(QueryBuilder $qb): array
    {
        $layer = Layer::getInstance();

        // 1. Calcul du total (Optimisé)
        $countQb = clone $qb;

        // C'EST ICI : On nettoie d'abord les anciennes colonnes, puis on ajoute le COUNT
        $countQb->clearSelect()
            ->select('COUNT(*)')
            ->clearOrderBy() // On ne trie pas pour compter
            ->clearLimit();  // On compte TOUT, pas juste la page

        // On utilise la méthode fetchColumn ajoutée au Layer
        $this->totalItems = (int)$layer->fetchColumn($countQb->getSql(), $countQb->getParams());

        // 2. Application de la limite à la requête originale (la vraie)
        $offset = ($this->currentPage - 1) * $this->itemsPerPage;
        $qb->limit($this->itemsPerPage, $offset);

        $totalPages = (int)ceil($this->totalItems / $this->itemsPerPage);

        return [
            'total_items'    => $this->totalItems,
            'total_pages'    => $totalPages > 0 ? $totalPages : 1,
            'current_page'   => $this->currentPage,
            'items_per_page' => $this->itemsPerPage,
            'offset'         => $offset
        ];
    }
}