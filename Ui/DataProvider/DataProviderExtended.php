<?php
namespace Aheadworks\Sarp2\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

/**
 * Class DataProviderExtended
 * @package Aheadworks\Sarp2\Ui\DataProvider
 */
class DataProviderExtended extends DataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            $conditionType = 'eq';
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
                $paramNameToUrl = $paramName;
                $paramValueToUrl = $paramValue;
            } elseif (is_array($paramValue)) {
                $paramValues = [];
                $paramNameToUrl = '';
                foreach ($paramValue as $param) {
                    if ($value = $this->request->getParam($param)) {
                        $paramValues[$param] = $value;
                        $paramNameToUrl .= sprintf('%s/%s/', $param, $value);
                    }
                }
                $paramValue = count($paramValues) == count($paramValue) ? $paramValues : null;
                $conditionType = $paramName;
                $paramValueToUrl = '';
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramNameToUrl,
                    $paramValueToUrl
                );
                $this->addFilter(
                    $this->filterBuilder
                        ->setField($paramName)
                        ->setValue($paramValue)
                        ->setConditionType($conditionType)
                        ->create()
                );
            }
        }
    }
}
