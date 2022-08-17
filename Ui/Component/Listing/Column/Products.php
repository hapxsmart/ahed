<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Products
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class Products extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item['items'])) {
                    continue;
                }
                $productNames = [];
                foreach ($item['items'] as $product) {
                    $productNames[] = $product['name'];
                }
                $item[$index] = implode(', ', $productNames);
            }
        }
        return $dataSource;
    }
}
