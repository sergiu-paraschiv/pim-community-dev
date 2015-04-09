<?php

namespace Pim\Component\ReferenceData\Model;

/**
 * Reference data interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReferenceDataInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param $code
     *
     * @return ReferenceDataInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * To string
     *
     * @return string
     */
    public function __toString();
}
