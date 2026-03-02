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

namespace Magepattern\Component\Database;

class QueryHelper
{
    /**
     * Parse un ancien tableau de configuration (extendQueryParams)
     * et l'applique dynamiquement sur une instance de QueryBuilder.
     *
     * @param QueryBuilder $qb L'instance du QueryBuilder à hydrater
     * @param array $extendParams Le tableau contenant les instructions SQL
     */
    public static function applyExtendParams(QueryBuilder $qb, array $extendParams): void
    {
        // 1. SELECT
        if (!empty($extendParams['select'])) {
            // L'ancien système pouvait empiler des tableaux dans des tableaux
            $selects = is_array($extendParams['select']) ? $extendParams['select'] : [$extendParams['select']];
            foreach ($selects as $select) {
                $qb->select($select);
            }
        }

        // 2. JOIN
        if (!empty($extendParams['join'])) {
            foreach ($extendParams['join'] as $joinItem) {
                // Si l'ancien système imbrique un niveau supplémentaire (ex: $params['join'][] = [...])
                $joins = isset($joinItem['table']) ? [$joinItem] : $joinItem;

                foreach ($joins as $join) {
                    $condition = "";
                    // Reconstitution de la clause ON depuis l'ancien format
                    if (isset($join['on']['table']) && isset($join['on']['key'])) {
                        $condition = "{$join['on']['table']}.{$join['on']['key']} = {$join['as']}.{$join['on']['key']}";
                    } elseif (isset($join['condition'])) {
                        $condition = $join['condition'];
                    }

                    $type = $join['type'] ?? 'JOIN';
                    $qb->join($join['table'], $join['as'], $condition, $type);
                }
            }
        }

        // 3. WHERE & FILTER
        $wheres = [];
        if (!empty($extendParams['where'])) {
            $wheres = is_array($extendParams['where']) ? $extendParams['where'] : [$extendParams['where']];
        }
        if (!empty($extendParams['filter'])) {
            $filters = is_array($extendParams['filter']) ? $extendParams['filter'] : [$extendParams['filter']];
            $wheres = array_merge($wheres, $filters);
        }

        foreach ($wheres as $whereItem) {
            // Gestion des tableaux imbriqués potentiels
            $conditions = isset($whereItem['condition']) || is_string($whereItem) ? [$whereItem] : $whereItem;

            foreach ($conditions as $where) {
                if (is_array($where) && isset($where['condition'])) {
                    $qb->where($where['condition']);
                } elseif (is_string($where)) {
                    $qb->where($where);
                }
            }
        }

        // 4. GROUP BY
        if (!empty($extendParams['group'])) {
            $groups = is_array($extendParams['group']) ? $extendParams['group'] : [$extendParams['group']];
            foreach ($groups as $group) {
                $qb->groupBy($group);
            }
        }

        // 5. HAVING
        if (!empty($extendParams['having'])) {
            $havings = is_array($extendParams['having']) ? $extendParams['having'] : [$extendParams['having']];
            foreach ($havings as $havingItem) {
                $items = isset($havingItem['condition']) || is_string($havingItem) ? [$havingItem] : $havingItem;
                foreach ($items as $having) {
                    $qb->having(is_array($having) && isset($having['condition']) ? $having['condition'] : $having);
                }
            }
        }

        // 6. ORDER BY
        if (!empty($extendParams['order'])) {
            $orders = is_array($extendParams['order']) ? $extendParams['order'] : [$extendParams['order']];
            foreach ($orders as $orderItem) {
                $items = is_array($orderItem) ? $orderItem : [$orderItem];
                foreach ($items as $order) {
                    // Sépare proprement la colonne et la direction (ex: "price DESC")
                    $parts = explode(' ', trim((string)$order));
                    $qb->orderBy($parts[0], $parts[1] ?? 'ASC');
                }
            }
        }

        // 7. LIMIT
        if (!empty($extendParams['limit'])) {
            // Extraction de la limite qui pouvait être un string "0, 20" ou juste "20"
            $limitVal = is_array($extendParams['limit']) ? current($extendParams['limit']) : $extendParams['limit'];

            if (str_contains((string)$limitVal, ',')) {
                $parts = explode(',', (string)$limitVal);
                // QueryBuilder prend (limit, offset)
                $qb->limit((int)trim($parts[1]), (int)trim($parts[0]));
            } else {
                $qb->limit((int)$limitVal);
            }
        }
    }
}