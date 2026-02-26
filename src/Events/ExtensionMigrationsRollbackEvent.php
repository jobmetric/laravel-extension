<?php

namespace JobMetric\Extension\Events;

use JobMetric\Extension\Contracts\AbstractExtension;

class ExtensionMigrationsRollbackEvent
{
    public function __construct(
        public AbstractExtension $extension,
        /** @var array<int, string> Migration filenames that were rolled back (e.g. 2024_01_15_120000__1_0_0__create_banners.php) */
        public array $rollbackMigrations = []
    ) {
    }
}
