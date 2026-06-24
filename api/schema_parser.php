<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

function parseSchema() {
    $filePath = __DIR__ . '/../api doc/database_schema.sql';
    if (!file_exists($filePath)) {
        return ['error' => 'Schema file not found.'];
    }

    $sql = file_get_contents($filePath);
    
    // Handle possible UTF-16LE BOM from PowerShell/SQL tools
    if (substr($sql, 0, 2) === "\xff\xfe") {
        $sql = mb_convert_encoding(substr($sql, 2), 'UTF-8', 'UTF-16LE');
    }

    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/^\/\*!.*?\*\/;/m', '', $sql);

    $tables = [];

    // Match CREATE TABLE statements
    if (preg_match_all('/CREATE TABLE `?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*(?:ENGINE|;)/is', $sql, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $tableName = $matches[1][$i];
            $body = $matches[2][$i];
            
            $columns = [];
            if (preg_match_all('/^\s*`([a-zA-Z0-9_]+)`\s+([a-zA-Z]+)(?:\([^\)]+\))?/m', $body, $colMatches)) {
                for ($j = 0; $j < count($colMatches[1]); $j++) {
                    $colName = $colMatches[1][$j];
                    $colType = strtolower($colMatches[2][$j]);
                    
                    // Determine semantic type for frontend
                    $semanticType = 'string';
                    if (in_array($colType, ['int', 'tinyint', 'bigint', 'decimal', 'float', 'double'])) {
                        $semanticType = 'number';
                    } elseif (in_array($colType, ['date', 'datetime', 'timestamp'])) {
                        $semanticType = 'date';
                    }

                    $columns[] = [
                        'name' => $colName,
                        'type' => $colType,
                        'semanticType' => $semanticType
                    ];
                }
            }

            // Exclude admin tables to avoid exposing credentials or tokens in the generic builder
            if (!in_array(strtolower($tableName), ['admin', 'admin_tokens'])) {
                $tables[] = [
                    'name' => $tableName,
                    'columns' => $columns
                ];
            }
        }
    }

    return $tables;
}

if (basename($_SERVER['PHP_SELF']) === 'schema_parser.php') {
    jsonResponse(['status' => 'success', 'tables' => parseSchema()]);
}
