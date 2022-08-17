<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Ui\Component\Listing\Columns\Column;

class Price extends Column
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrency $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrency $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$index]) {
                    $currencyCode = isset($item['base_currency_code'])
                        ? $item['base_currency_code']
                        : null;
                    $item[$index] = $this->priceCurrency->format(
                        $item[$index],
                        false,
                        2,
                        null,
                        $currencyCode
                    );
                }
            }
        }
        return $dataSource;
    }
}
