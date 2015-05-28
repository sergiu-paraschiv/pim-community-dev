<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\Mapper;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Extractor\ProductAttributeFieldExtractor;

class ColumnsMapperSpec extends ObjectBehavior
{
    function it_maps_source_and_destination_columns_name()
    {
        $row = [
            'sku' => 'mysku',
            'famille' => 'myfamilycode',
            'cats' => 'mycatcode1,mycatcode2'
        ];
        $mapping = [
            'famille' => 'family',
            'cats' => 'categories'
        ];
        $resultRow = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];

        $this->map($row, $mapping)->shouldReturn($resultRow);
    }

    function it_does_not_map_when_no_mapping_is_provided()
    {
        $row = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];
        $mapping = [];
        $resultRow = [
            'sku' => 'mysku',
            'family' => 'myfamilycode',
            'categories' => 'mycatcode1,mycatcode2'
        ];

        $this->map($row, $mapping)->shouldReturn($resultRow);
    }
}
