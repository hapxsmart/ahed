<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Link
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class Link extends Column
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            $config = $this->getConfiguration();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->isLink($item) && $item[$index]) {
                    $item[$index . '_url'] = $this->url->getUrl(
                        $config['linkUrl'],
                        [$config['requestField'] => $item[$config['indexField']]]
                    );
                    $item[$index . '_text'] = $this->prepareLinkText($item);
                } else {
                    $item[$index] = $this->preparePlainText($item);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Check if item should rendered as link
     *
     * @param array $item
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isLink(array $item)
    {
        return true;
    }

    /**
     * Prepare link text
     *
     * @param array $item
     * @return string
     */
    protected function prepareLinkText(array $item)
    {
        return $item[$this->getName()];
    }

    /**
     * Prepare plain text
     *
     * @param array $item
     * @return string
     */
    protected function preparePlainText(array $item)
    {
        return $item[$this->getName()];
    }
}
