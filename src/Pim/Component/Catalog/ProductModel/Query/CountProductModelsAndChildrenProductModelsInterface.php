<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

/**
 * Count the total numbers of product models including their children for a given list of product models.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo pull-up 3.x Move to `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query`
 */
interface CountProductModelsAndChildrenProductModelsInterface
{
    /**
     * @param string[] $productModelCodes
     */
    public function forProductModelCodes(array $productModelCodes): int;
}
