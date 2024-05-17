<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'before' => 'CREATE TABLE `some_table` (
  `some_column` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
    'after' => 'CREATE TABLE `some_table_renamed` (
  `some_column` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
];
