<?php
/**
 * Copyright © Best4u Media B.V. All rights reserved.
 */
declare(strict_types=1);

namespace Best4u\Base\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Move admin role permissions from the old module name Best4u_Backend to Best4u_Base
 *
 * Roles saved with the old ACL resource IDs would otherwise lose these permissions.
 */
class MigrateAdminRolePermissions implements DataPatchInterface
{
    /**
     * Old ACL resource ID => new ACL resource ID
     */
    private const RESOURCES = [
        'Best4u_Backend::admin' => 'Best4u_Base::admin',
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * Rename the ACL resource IDs in the role rules
     *
     * @return $this
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('authorization_rule');

        foreach (self::RESOURCES as $old => $new) {
            // Exact (case-sensitive) comparison: the column collation is case-insensitive
            $isOld = $connection->quoteInto('CAST(resource_id AS BINARY) = CAST(? AS BINARY)', $old);
            $rolesWithNew = $connection->fetchCol(
                $connection->select()
                    ->from($table, ['role_id'])
                    ->where('CAST(resource_id AS BINARY) = CAST(? AS BINARY)', $new)
            );
            if ($rolesWithNew) {
                $connection->delete($table, [$isOld, $connection->quoteInto('role_id IN (?)', $rolesWithNew)]);
            }
            $connection->update($table, ['resource_id' => $new], $isOld);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
