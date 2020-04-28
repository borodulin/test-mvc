<?php

return [
    'name' => 'Migrations',
    'migrations_namespace' => 'Migrations',
    'table_name' => 'doctrine_migration_versions',
    'column_name' => 'version',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory' => 'src/migrations',
    'all_or_nothing' => true,
];
