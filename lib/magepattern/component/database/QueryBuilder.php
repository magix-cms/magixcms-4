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

class QueryBuilder
{
    private string $type = 'SELECT'; // SELECT, INSERT, UPDATE, DELETE

    // Propriétés pour la lecture (SELECT)
    private array $select = [];
    private string $from = '';
    private array $joins = [];
    private array $wheres = [];
    private array $orderBys = [];
    private string $limit = '';

    // Propriétés pour l'écriture (CRUD)
    private string $actionTable = '';
    private array $insertCols = [];
    private array $insertPlaceholders = [];
    private array $updateSets = [];

    // Gestion des paramètres PDO
    private array $params = [];
    private int $paramIndex = 0;

    private array $groupBys = [];
    private array $havings = [];

    // -------------------------------------------------------------------------
    // LECTURE (SELECT)
    // -------------------------------------------------------------------------

    public function select(array|string $fields): self
    {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->select = array_merge($this->select, $fields);
        return $this;
    }

    public function from(string $table, string $alias = ''): self
    {
        $this->from = trim("$table $alias");
        return $this;
    }

    public function join(string $table, string $alias, string $condition, string $type = 'JOIN'): self
    {
        $this->joins[] = "$type $table $alias ON ($condition)";
        return $this;
    }

    public function leftJoin(string $table, string $alias, string $condition): self
    {
        return $this->join($table, $alias, $condition, 'LEFT JOIN');
    }

    public function where(string $condition, array $binds = []): self
    {
        $this->wheres[] = $condition;
        $this->params = array_merge($this->params, $binds);
        return $this;
    }

    public function whereIn(string $field, array $values, string $operator = 'IN'): self
    {
        if (empty($values)) return $this;

        $placeholders = [];
        foreach ($values as $val) {
            $key = 'in_' . $this->paramIndex++;
            $placeholders[] = ':' . $key;
            $this->params[$key] = trim($val);
        }

        $this->wheres[] = "$field $operator (" . implode(',', $placeholders) . ")";
        return $this;
    }

    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->orderBys[] = "$field $direction";
        return $this;
    }

    public function orderByField(string $field, array $values): self
    {
        if (empty($values)) return $this;

        $placeholders = [];
        foreach ($values as $val) {
            $key = 'fld_' . $this->paramIndex++;
            $placeholders[] = ':' . $key;
            $this->params[$key] = trim($val);
        }

        $this->orderBys[] = "FIELD($field, " . implode(',', $placeholders) . ")";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = $offset > 0 ? "LIMIT $offset, $limit" : "LIMIT $limit";
        return $this;
    }

    public function groupBy(array|string $fields): self
    {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->groupBys = array_merge($this->groupBys, $fields);
        return $this;
    }

    public function having(string $condition, array $binds = []): self
    {
        $this->havings[] = $condition;
        $this->params = array_merge($this->params, $binds);
        return $this;
    }

    // -------------------------------------------------------------------------
    // ÉCRITURE (INSERT, UPDATE, DELETE)
    // -------------------------------------------------------------------------

    public function insert(string $table, array $params): self
    {
        $this->type = 'INSERT';
        $this->actionTable = $table;

        foreach ($params as $column => $value) {
            $key = 'ins_' . $this->paramIndex++;
            $this->insertCols[] = $column;
            $this->insertPlaceholders[] = ':' . $key;
            $this->params[$key] = $value;
        }

        return $this;
    }

    public function update(string $table, array $params): self
    {
        $this->type = 'UPDATE';
        $this->actionTable = $table;

        foreach ($params as $column => $value) {
            $key = 'upd_' . $this->paramIndex++;
            $this->updateSets[] = "$column = :$key";
            $this->params[$key] = $value;
        }

        return $this;
    }

    public function delete(string $table): self
    {
        $this->type = 'DELETE';
        $this->actionTable = $table;
        return $this;
    }

    // -------------------------------------------------------------------------
    // GÉNÉRATION SQL & PARAMÈTRES
    // -------------------------------------------------------------------------

    public function getSql(): string
    {
        $sql = '';

        switch ($this->type) {
            case 'INSERT':
                $sql = "INSERT INTO {$this->actionTable} (" . implode(', ', $this->insertCols) . ") VALUES (" . implode(', ', $this->insertPlaceholders) . ")";
                break;

            case 'UPDATE':
                $sql = "UPDATE {$this->actionTable} SET " . implode(', ', $this->updateSets);
                if (!empty($this->wheres)) {
                    $sql .= " WHERE " . implode(' AND ', $this->wheres);
                }
                break;

            case 'DELETE':
                $sql = "DELETE FROM {$this->actionTable}";
                if (!empty($this->wheres)) {
                    $sql .= " WHERE " . implode(' AND ', $this->wheres);
                }
                break;

            case 'SELECT':
            default:
                $sql = "SELECT " . (!empty($this->select) ? implode(', ', $this->select) : '*');
                $sql .= " FROM " . $this->from;
                if (!empty($this->joins)) {
                    $sql .= " " . implode(' ', $this->joins);
                }
                if (!empty($this->wheres)) {
                    $sql .= " WHERE " . implode(' AND ', $this->wheres);
                }
                // --- AJOUTS ICI ---
                if (!empty($this->groupBys)) {
                    $sql .= " GROUP BY " . implode(', ', $this->groupBys);
                }
                if (!empty($this->havings)) {
                    $sql .= " HAVING " . implode(' AND ', $this->havings);
                }
                // ------------------
                if (!empty($this->orderBys)) {
                    $sql .= " ORDER BY " . implode(', ', $this->orderBys);
                }
                if ($this->limit !== '') {
                    $sql .= " " . $this->limit;
                }
                break;
        }

        return $sql;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    // -------------------------------------------------------------------------
    // MÉTHODES DE NETTOYAGE (Utiles pour PaginationTool)
    // -------------------------------------------------------------------------

    /**
     * Réinitialise les colonnes sélectionnées
     * @return self
     */
    public function clearSelect(): self
    {
        $this->select = [];
        return $this;
    }

    /**
     * Réinitialise les tris (ORDER BY)
     * @return self
     */
    public function clearOrderBy(): self
    {
        $this->orderBys = [];
        return $this;
    }

    /**
     * Réinitialise la limite
     * @return self
     */
    public function clearLimit(): self
    {
        $this->limit = '';
        return $this;
    }

    /**
     * Réinitialise les groupements (GROUP BY)
     * @return $this
     */
    public function clearGroupBy(): self
    {
        $this->groupBys = [];
        return $this;
    }
}