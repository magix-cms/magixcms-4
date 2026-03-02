<?php

namespace App\Backend\Model;

trait DataHelperTrait
{
    /**
     * Assigne les données et les métadonnées de pagination à Smarty.
     * * @param string $type Le nom de l'entité (ex: 'page')
     * @param array $data Les données finalisées venant de la base de données
     * @param bool|string $assign Vrai pour assigner à Smarty, ou une string pour forcer le nom de variable
     * @param array|null $paginationMeta Le tableau retourné par PaginationTool->paginate($qb)
     * @return array
     */
    public function getItems(string $type, array $data, $assign = true, ?array $paginationMeta = null): array
    {
        if ($assign && property_exists($this, 'view')) {
            $varName = is_string($assign) ? $assign : $type;
            $this->view->assign($varName, $data);
        }

        // Si le QueryBuilder a fait son travail et renvoyé les métadonnées de pagination
        if ($paginationMeta !== null && property_exists($this, 'view')) {
            $this->view->assign('nbp', $paginationMeta['total_pages'] ?? 1);
            $this->view->assign('offset', $paginationMeta['offset'] ?? 0);
            $this->view->assign('current_page', $paginationMeta['current_page'] ?? 1);
        }

        return $data;
    }

    /**
     * Analyse le schéma brut de la base de données pour configurer Smarty.
     */
    public function getScheme(array $rawDbScheme, array $columns, $assign = null, string $tpl_var = 'scheme'): array
    {
        $formattedScheme = $this->parseScheme($rawDbScheme, $columns, $assign);

        if (property_exists($this, 'view')) {
            $this->view->assign($tpl_var, $formattedScheme);
        }

        return $formattedScheme;
    }

    /**
     * Transforme un jeu de données plat en structure hiérarchique (Arbre).
     * Gère dynamiquement le ré-attachement à 'root' si un parent manque lors d'un filtrage par ID.
     */
    public function setPagesTree(array $data, string $type, string|int $branch = 'root'): array
    {
        $childs = [];
        $idCol = 'id_' . $type;

        foreach ($data as &$item) {
            $currentId = $item[$idCol] ?? ($item['id'] ?? null);
            if ($currentId !== null) {
                $childs[$currentId] = &$item;
                $childs[$currentId]['subdata'] = [];
            }
        }
        unset($item);

        foreach ($data as &$item) {
            $parentId = $item['id_parent'] ?? 'root';
            if ($parentId === null || $parentId === '') {
                $parentId = 'root';
            }

            if ($parentId !== 'root' && !isset($childs[$parentId])) {
                $parentId = 'root';
            }

            if ($parentId === 'root') {
                $childs['root'][] = &$item;
            } else {
                $childs[$parentId]['subdata'][] = &$item;
            }
        }
        unset($item);

        if ($branch === 'root') {
            return $childs['root'] ?? [];
        }

        return isset($childs[$branch]) ? [$childs[$branch]] : [];
    }

    /**
     * Parse le schéma SQL pour configurer les DataTables.
     */
    public function parseScheme(array $rawScheme, array $targetCols, ?array $associations = null): array
    {
        $typeMap = array_column($rawScheme, 'type', 'column');
        $scheme = [];

        foreach ($targetCols as $colName) {
            if (!isset($typeMap[$colName])) continue;

            $fullType = $typeMap[$colName];
            $prefix = strstr($colName, '_', true) ?: $colName;
            $scheme[$colName] = $this->buildColumnConfig($colName, $fullType, $prefix);
        }

        return (!empty($associations)) ? $this->applyAssociations($scheme, $associations) : $scheme;
    }

    private function buildColumnConfig(string $colName, string $fullType, string $prefix): array
    {
        $config = ['type' => 'text', 'class' => '', 'title' => $prefix, 'input' => ['type' => 'text']];
        preg_match('/^([a-z]+)(?:\((.+)\))?/i', $fullType, $matches);
        $baseType = strtolower($matches[1] ?? 'text');
        $arg = $matches[2] ?? null;

        switch ($baseType) {
            case 'tinyint':
            case 'int':
            case 'integer':
                if (($baseType === 'tinyint' && $arg === '1') || preg_match('/^(is_|active|visible)/i', $colName)) {
                    $config = array_merge($config, ['type' => 'bin', 'enum' => 'bin_', 'class' => 'fixed-td-md text-center', 'input' => ['type' => 'select', 'var' => true, 'values' => [['v' => 0], ['v' => 1]]]]);
                } else {
                    $config['class'] = 'fixed-td-md text-center';
                    if (stripos($colName, 'id') === 0) $config['title'] = 'id';
                }
                break;
            case 'enum':
                if ($arg) {
                    $values = explode("','", str_replace("'", "", $arg));
                    $enumOpts = [];
                    foreach ($values as $k => $val) {
                        $nameKey = $prefix . '_' . $k;
                        $translated = (property_exists($this, 'view') && method_exists($this->view, 'getConfigVars')) ? $this->view->getConfigVars($nameKey) : $nameKey;
                        $enumOpts[] = ['v' => $val, 'name' => $translated ?: $nameKey];
                    }
                    $config = array_merge($config, ['type' => 'enum', 'enum' => $prefix . '_', 'class' => 'fixed-td-lg', 'input' => ['type' => 'select', 'values' => $enumOpts]]);
                }
                break;
            case 'varchar':
                $config['class'] = ((int)$arg <= 100) ? 'th-25' : 'th-35';
                break;
            case 'text':
            case 'mediumtext':
            case 'longtext':
                $config['type'] = 'content'; $config['input'] = null;
                break;
            case 'datetime':
            case 'date':
                $config['class'] = 'fixed-td-lg'; $config['type'] = 'date';
                if (stripos($prefix, 'date') === 0) $config['input'] = ['type' => 'text', 'class' => 'date-input', 'placeholder' => '__/__/____'];
                break;
        }
        return $config;
    }

    private function applyAssociations(array $currentScheme, array $associations): array
    {
        $newScheme = [];
        foreach ($associations as $name => $info) {
            if (!is_array($info)) {
                if (isset($currentScheme[$info])) $newScheme[$info] = $currentScheme[$info];
                continue;
            }
            $colKey = $info['col'] ?? $name;
            if (isset($currentScheme[$colKey])) {
                $item = $currentScheme[$colKey];
                if (isset($info['title'])) {
                    $prefix = strstr($name, '_', true) ?: $name;
                    $item['title'] = ($info['title'] === 'pre') ? $prefix : (($info['title'] === 'name') ? $name : $info['title']);
                    unset($info['title']);
                }
                unset($info['col']);
                $mergedItem = array_merge($item, $info);
                if (isset($mergedItem['type']) && $mergedItem['type'] === 'bin' && !isset($info['input'])) {
                    $mergedItem['input'] = ['type' => 'select', 'var' => true, 'values' => [['v' => 0], ['v' => 1]]];
                }
                $newScheme[$name] = $mergedItem;
            }
        }
        return $newScheme;
    }
}